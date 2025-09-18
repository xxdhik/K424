<?php
include 'includes/db.php';
require 'vendor/fpdf/fpdf.php'; // Panggil library FPDF

// --- BAGIAN KEAMANAN DAN PENGAMBILAN DATA (TETAP SAMA) ---
if (!isset($_SESSION['user_id'])) {
    die("Anda harus login untuk melihat laporan.");
}
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    die("ID pesanan tidak valid.");
}
try {
    // Query diperbarui untuk mengambil lebih banyak data user
    $sql_order = "SELECT o.*, u.username, u.full_name, u.address, u.contact_no 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = ? AND o.user_id = ?";
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Pesanan tidak ditemukan atau Anda tidak memiliki akses ke laporan ini.");
    }

    $sql_items = "SELECT oi.quantity, oi.price_per_item, p.name 
                  FROM order_items oi JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = ?";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$order_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- [PERUBAHAN] PEMBUATAN DOKUMEN PDF DENGAN FPDF ---

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Header Laporan
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'Toko Alat Kesehatan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, 'Laporan Belanja Anda', 0, 1, 'C');
$pdf->Ln(10); // Spasi

// Informasi Pengguna dan Pesanan dalam 2 kolom
$pdf->SetFont('Arial', '', 10);
$lebar_kolom_kiri = 95;
$lebar_kolom_kanan = 95;

// Baris 1: User ID & Tanggal
$pdf->Cell($lebar_kolom_kiri, 6, 'User ID    : ' . $order['username'], 0, 0);
$pdf->Cell($lebar_kolom_kanan, 6, 'Tanggal            : ' . date('d-m-Y', strtotime($order['order_date'])), 0, 1);

// Baris 2: Nama & ID Paypal
$pdf->Cell($lebar_kolom_kiri, 6, 'Nama       : ' . ($order['full_name'] ?? ''), 0, 0);
$pdf->Cell($lebar_kolom_kanan, 6, 'Nama Bank      : ', 0, 1); // Dikosongkan karena tidak ada di DB

// Baris 3: Alamat & Nama Bank
$pdf->Cell($lebar_kolom_kiri, 6, 'Alamat     : ' . ($order['address'] ?? ''), 0, 0);
$pdf->Cell($lebar_kolom_kanan, 6, 'Cara Bayar       : ' . $order['payment_method'], 0, 1);

// Baris 4: No HP & Cara Bayar
$pdf->Cell($lebar_kolom_kiri, 6, 'No HP      : ' . ($order['contact_no'] ?? ''), 0, 0);
$pdf->Ln(10); // Spasi

// Tabel Detail Produk
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 8, 'No.', 1, 0, 'C');
$pdf->Cell(100, 8, 'Nama Produk dengan IDnya', 1, 0, 'C');
$pdf->Cell(30, 8, 'Jumlah', 1, 0, 'C');
$pdf->Cell(50, 8, 'Harga', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$no = 1;
foreach ($items as $item) {
    $pdf->Cell(10, 8, $no++, 1, 0, 'C');
    $pdf->Cell(100, 8, $item['name'], 1, 0);
    $pdf->Cell(30, 8, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(50, 8, 'Rp. ' . number_format($item['price_per_item'] * $item['quantity'], 0, ',', '.'), 1, 1, 'R');
}

// Total Belanja
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(140, 8, 'Total belanja (termasuk pajak):', 1, 0, 'R');
$pdf->Cell(50, 8, 'Rp. ' . number_format($order['total_amount'], 0, ',', '.'), 1, 1, 'R');
$pdf->Ln(20);

// Tanda Tangan
$pdf->Cell(0, 7, 'TANDATANGAN TOKO', 0, 1, 'R');
$pdf->Ln(20);
$pdf->Cell(0, 7, '___________________', 0, 1, 'R');

// Output PDF ke browser
$pdf->Output('D', 'Laporan-Pembelian-K424-'.$order_id.'.pdf');
?>  