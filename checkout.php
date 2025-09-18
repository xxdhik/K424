<?php
include 'includes/db.php';

// --- BAGIAN KEAMANAN ---
// 1. Cek apakah pengguna sudah login. Jika belum, lempar ke halaman login.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 2. Cek apakah keranjang belanja kosong. Jika ya, lempar kembali ke keranjang.
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}
// --- AKHIR BAGIAN KEAMANAN ---


// --- Logika untuk mengambil detail item dan menghitung total ---
// (Logika ini mirip dengan yang ada di cart.php)
$cart_items = [];
$total_price = 0;

$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products_in_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products_in_cart as $product) {
    $quantity = $_SESSION['cart'][$product['id']];
    $total_price += $product['price'] * $quantity;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - K424</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <h1>Konfirmasi Pesanan</h1>
        
        <h3>Ringkasan Belanja</h3>
        <p>Total yang harus dibayar: <strong>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></strong></p>
        
        <hr>

        <h3>Metode Pembayaran</h3>
        <form action="process_order.php" method="post">
            <p>Silakan pilih metode pembayaran Anda:</p>
            <input type="radio" id="prepaid" name="payment_method" value="Prepaid" required>
            <label for="prepaid">Prepaid (Kartu Debit/Kredit)</label><br>
            
            <input type="radio" id="postpaid" name="payment_method" value="Postpaid">
            <label for="postpaid">Postpaid (Bayar di Tempat)</label><br><br>
            
            <button type="submit" class="buy-button">Selesaikan Pesanan</button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>