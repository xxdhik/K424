<?php
include 'includes/db.php';

// --- BAGIAN KEAMANAN DAN PERSIAPAN DATA (TETAP SAMA) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    die("Akses tidak sah.");
}
if (empty($_SESSION['cart'])) {
    die("Keranjang belanja kosong.");
}
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

// --- Mulai Transaksi Database ---
try {
    $pdo->beginTransaction();

    // 1. Simpan data ke tabel `orders`
    $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method) VALUES (?, ?, ?)";
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([$user_id, $total_price, $payment_method]);
    $order_id = $pdo->lastInsertId();

    // 2. Simpan setiap item di keranjang ke tabel `order_items`
    $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price_per_item) VALUES (?, ?, ?, ?)";
    $stmt_items = $pdo->prepare($sql_items);
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $price_per_item = $products_data[$product_id];
        $stmt_items->execute([$order_id, $product_id, $quantity, $price_per_item]);
    }

    // 3. Kurangi stok untuk setiap produk di tabel `products`
    $sql_update_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
    $stmt_update_stock = $pdo->prepare($sql_update_stock);
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt_update_stock->execute([$quantity, $product_id]);
    }

    // 4. Konfirmasi transaksi jika semua berhasil
    $pdo->commit();

    // 5. Kosongkan keranjang belanja
    unset($_SESSION['cart']);

    // 6. Arahkan ke halaman sukses
    header('Location: order_success.php?order_id=' . $order_id);
    exit();

} catch (Exception $e) {
    // Jika terjadi error, batalkan semua perubahan
    $pdo->rollBack();
    die("Terjadi kesalahan saat memproses pesanan: " . $e->getMessage());
}
?>