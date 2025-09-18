<?php
include 'includes/db.php';

// Ambil order_id dari URL untuk ditampilkan
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil - K424</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <h1>Terima Kasih!</h1>
        <p>Pesanan Anda telah berhasil kami terima.</p>
        
        <?php if ($order_id > 0): ?>
            <p>Nomor pesanan Anda adalah: <strong><?php echo $order_id; ?></strong></p>
            
            <p><a href="generate_report.php?order_id=<?php echo $order_id; ?>" target="_blank" class="buy-button">Unduh Laporan (PDF)</a></p>
            
        <?php endif; ?>
        
        <p>Kami akan segera memproses pesanan Anda. Sesuai spesifikasi, laporan pembelian akan dikirim ke email Anda.</p>
        <p><a href="index.php">Kembali ke Halaman Utama</a></p>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>