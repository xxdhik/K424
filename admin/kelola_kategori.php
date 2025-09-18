<?php
include 'admin_header.php';

// --- LOGIKA PEMROSESAN AKSI (TAMBAH & HAPUS) ---
// ... (Logika PHP untuk proses form tetap sama persis) ...
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        try {
            $sql = "INSERT INTO categories (name) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category_name]);
            $message = "Kategori '$category_name' berhasil ditambahkan.";
        } catch (PDOException $e) { $error = "Gagal menambahkan kategori."; }
    } else { $error = "Nama kategori tidak boleh kosong."; }
}
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $category_id = (int)$_GET['id'];
    try {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id]);
        header('Location: kelola_kategori.php');
        exit();
    } catch (PDOException $e) { $error = "Gagal menghapus kategori."; }
}

// --- LOGIKA UNTUK MENAMPILKAN DATA ---
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Kelola Kategori Produk</h1>
<p><a href="index.php">← Kembali ke Dashboard</a></p>

<h2>Tambah Kategori Baru</h2>
<?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
<?php if(isset($error)) echo "<div class='alert alert-error'>$error</div>"; ?>
<form action="kelola_kategori.php" method="post" style="max-width: 400px; display:flex; gap: 10px;">
    <input type="text" id="category_name" name="category_name" placeholder="Nama Kategori Baru" required>
    <button type="submit" name="add_category">Tambah</button>
</form>
<hr>

<h2>Daftar Kategori yang Ada</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
        <tr>
            <td><?php echo $category['id']; ?></td>
            <td><?php echo htmlspecialchars($category['name']); ?></td>
            <td>
                <a href="kelola_kategori.php?action=delete&id=<?php echo $category['id']; ?>" onclick="return confirm('Yakin?');">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
include 'admin_footer.php';
?>