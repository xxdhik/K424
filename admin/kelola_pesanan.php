<?php
include 'admin_header.php';

// --- LOGIKA MENGAMBIL DATA ---
$sql = "SELECT o.id, o.total_amount, o.status, o.order_date, u.username 
        FROM orders o JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Kelola Pesanan Pelanggan</h1>
<p><a href="index.php">← Kembali ke Dashboard</a></p>

<table>
    <thead>
        <tr>
            <th>ID Pesanan</th>
            <th>Username</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['username']); ?></td>
            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
            <td><?php echo htmlspecialchars(ucfirst($order['status'])); ?></td>
            <td><?php echo date('d-m-Y H:i', strtotime($order['order_date'])); ?></td>
            <td><a href="detail_pesanan.php?id=<?php echo $order['id']; ?>">Lihat Detail</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
include 'admin_footer.php';
?>