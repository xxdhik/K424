<?php
include 'admin_header.php';

// Ambil semua produk untuk ditampilkan
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Kelola Produk</h1>
<p><a href="index.php">← Kembali ke Dashboard</a> | <a href="tambah_produk.php" class="btn btn-primary btn-sm">Tambah Produk Baru</a></p>

<table>
    <thead>
        <tr>
            <th>Gambar</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
            <td>
                <?php if (!empty($product['image_url'])): ?>
                    <img src="../assets/uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="" width="60">
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
            <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
            <td><?php echo $product['stock']; ?></td>
            <td>
                <a href="edit_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
include 'admin_footer.php';
?>