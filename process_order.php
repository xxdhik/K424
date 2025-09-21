<?php
// Panggil semua library yang dibutuhkan di awal
include 'includes/db.php';
require 'vendor/fpdf/fpdf.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';
date_default_timezone_set('Asia/Jakarta');

// Blok keamanan dan persiapan data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) die("Akses tidak sah.");
if (empty($_SESSION['cart'])) die("Keranjang belanja kosong.");
$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'];
$total_price = 0;
$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $total_price += $products_data[$product_id] * $quantity;
}

// Validasi stok sebelum transaksi
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $stmt_stock = $pdo->prepare("SELECT name, stock FROM products WHERE id = ?");
    $stmt_stock->execute([$product_id]);
    $product = $stmt_stock->fetch(PDO::FETCH_ASSOC);
    if (!$product || $product['stock'] < $quantity) {
        die("Maaf, stok untuk produk '" . htmlspecialchars($product['name']) . "' tidak mencukupi.");
    }
}

try {
    $pdo->beginTransaction();
    // Simpan pesanan, detail item, dan kurangi stok
    $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method) VALUES (?, ?, ?)";
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([$user_id, $total_price, $payment_method]);
    $order_id = $pdo->lastInsertId();
    $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price_per_item) VALUES (?, ?, ?, ?)";
    $stmt_items = $pdo->prepare($sql_items);
    $sql_update_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
    $stmt_update_stock = $pdo->prepare($sql_update_stock);
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $price_per_item = $products_data[$product_id];
        $stmt_items->execute([$order_id, $product_id, $quantity, $price_per_item]);
        $stmt_update_stock->execute([$quantity, $product_id]);
    }
    $pdo->commit();

    // --- MULAI PROSES GENERATE PDF DAN KIRIM EMAIL ---
    $sql_report = "SELECT o.*, u.username, u.full_name, u.email, u.address, u.contact_no FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?";
    $stmt_report = $pdo->prepare($sql_report);
    $stmt_report->execute([$order_id]);
    $order_details = $stmt_report->fetch(PDO::FETCH_ASSOC);
    $sql_report_items = "SELECT oi.quantity, oi.price_per_item, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
    $stmt_report_items = $pdo->prepare($sql_report_items);
    $stmt_report_items->execute([$order_id]);
    $items = $stmt_report_items->fetchAll(PDO::FETCH_ASSOC);

    // 1. Generate PDF dengan format lengkap
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,7,'Toko Alat Kesehatan',0,1,'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(95, 6, 'User ID: ' . $order_details['username'], 0, 1);
    $pdf->Cell(95, 6, 'Tanggal: ' . date('d-m-Y', strtotime($order_details['order_date'])), 0, 1);
    $pdf->Ln(10);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10, 8, 'No.', 1, 0, 'C');
    $pdf->Cell(85, 8, 'Nama Produk', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Jumlah', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Harga Satuan', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Subtotal', 1, 1, 'C');
    $pdf->SetFont('Arial','',10);
    $no = 1;
    foreach ($items as $item) {
        $pdf->Cell(10, 8, $no++, 1, 0, 'C');
        $pdf->Cell(85, 8, $item['name'], 1, 0);
        $pdf->Cell(25, 8, $item['quantity'], 1, 0, 'C');
        $pdf->Cell(35, 8, 'Rp ' . number_format($item['price_per_item'], 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(35, 8, 'Rp ' . number_format($item['price_per_item'] * $item['quantity'], 0, ',', '.'), 1, 1, 'R');
    }
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(155, 8, 'Total belanja:', 1, 0, 'R');
    $pdf->Cell(35, 8, 'Rp ' . number_format($order_details['total_amount'], 0, ',', '.'), 1, 1, 'R');
    
    // 2. Simpan PDF ke folder sementara
    $pdf_filename = 'invoice-K424-'.$order_id.'.pdf';
    $pdf_filepath = 'temp_invoices/' . $pdf_filename;
    $pdf->Output('F', $pdf_filepath);

    // 3. Kirim email dengan lampiran PDF
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'andhikapuja2004@gmail.com'; // Ganti dengan email Anda
        $mail->Password   = 'rwekxmnfsqlctbyv'; // Ganti dengan App Password 16 karakter
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('no-reply@k424.com', 'Toko K424');
        $mail->addAddress($order_details['email'], $order_details['username']);
        $mail->addAttachment($pdf_filepath, $pdf_filename); // Ini bagian pentingnya
        $mail->isHTML(true);
        $mail->Subject = 'Invoice Pembelian Anda di Toko K424 (Order #' . $order_id . ')';
        $mail->Body    = 'Halo ' . $order_details['username'] . ',<br><br>Terima kasih telah berbelanja. Berikut kami lampirkan invoice untuk pesanan Anda.';
        $mail->send();
    } catch (Exception $e) {
        // Jika email gagal, proses tidak berhenti. Anda bisa mencatat error ini.
        error_log("Gagal mengirim email invoice untuk order #$order_id: " . $mail->ErrorInfo);
    }

    // 4. Hapus file PDF sementara
    if (file_exists($pdf_filepath)) unlink($pdf_filepath);

    // Kosongkan keranjang dan arahkan ke halaman sukses
    unset($_SESSION['cart']);
    header('Location: order_success.php?order_id=' . $order_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Terjadi kesalahan saat memproses pesanan: " . $e->getMessage());
}
?>