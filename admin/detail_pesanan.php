<?php
include '../includes/db.php';

// --- BAGIAN KEAMANAN ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    die('Akses ditolak.');
}

// Ambil ID pesanan dari URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    die("ID pesanan tidak valid.");
}

// --- LOGIKA MENGAMBIL DATA ---
try {
    // 1. Ambil data utama pesanan (sama seperti sebelumnya tapi dengan filter ID)
    $sql_order = "SELECT o.*, u.username, u.full_name, u.address 
                  FROM orders o JOIN users u ON o.user_id = u.id 
                  WHERE o.id = ?";
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([$order_id]);
    $order = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Pesanan tidak ditemukan.");
    }

    // 2. Ambil semua item produk yang ada di dalam pesanan ini
    $sql_items = "SELECT oi.quantity, oi.price_per_item, p.name 
                  FROM order_items oi JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = ?";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$order_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan #<?php echo $order_id; ?> - Admin K424</title>
</head>
<body>
    <a href="kelola_pesanan.php">Kembali ke Daftar Pesanan</a>
    <h1>Detail Pesanan #<?php echo $order['id']; ?></h1>

    <h2>Informasi Pelanggan</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
    <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($order['full_name'] ?? 'N/A'); ?></p>
    <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></p>
    
    <h2>Informasi Pesanan</h2>
    <p><strong>Tanggal Pesan:</strong> <?php echo date('d-m-Y H:i', strtotime($order['order_date'])); ?></p>
    <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
    <p><strong>Total Belanja:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
    
    <hr>
    <h2>Item yang Dipesan</h2>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>Rp <?php echo number_format($item['price_per_item'], 0, ',', '.'); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>Rp <?php echo number_format($item['price_per_item'] * $item['quantity'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>