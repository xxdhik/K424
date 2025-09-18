<?php
include 'includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Logika untuk pembatalan
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['order_id'])) {
    $order_id_to_cancel = (int)$_GET['order_id'];
    // Hanya batalkan jika statusnya masih 'pending'
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$order_id_to_cancel, $_SESSION['user_id']]);
    header('Location: riwayat_pesanan.php');
    exit();
}

// Ambil data riwayat pesanan
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<div class="container mt-5">
    <h1>Riwayat Pesanan Anda</h1>
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Anda belum memiliki riwayat pesanan.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></td>
                    <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                    <td><?php echo ucfirst($order['status']); ?></td>
                    <td>
                        <a href="generate_report.php?order_id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Lihat Laporan</a>
                        <?php if ($order['status'] == 'pending'): ?>
                            <a href="riwayat_pesanan.php?action=cancel&order_id=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin membatalkan pesanan ini?');">Batalkan</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>