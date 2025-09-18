<?php
// Memanggil header admin (sudah termasuk koneksi db dan cek keamanan)
include 'admin_header.php';

// --- Logika untuk memproses form saat di-submit ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (Logika PHP untuk proses form tetap sama persis) ...
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image_filename = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $upload_dir = '../assets/uploads/';
        $image_filename = time() . '_' . basename($_FILES['product_image']['name']);
        $target_file = $upload_dir . $image_filename;
        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $error = "Maaf, terjadi kesalahan saat mengupload file gambar.";
            $image_filename = null;
        }
    }
    if (!isset($error)) {
        $sql = "INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$name, $description, $price, $stock, $category_id, $image_filename]);
            $message = "Produk baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            $error = "Gagal menambahkan produk: " . $e->getMessage();
        }
    }
}

// --- Logika untuk mengambil data kategori ---
try {
    $stmt_cat = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Tidak bisa mengambil data kategori: " . $e->getMessage());
}
?>

<h1>Form Tambah Produk Baru</h1>
<p><a href="index.php">← Kembali ke Dashboard</a></p>

<?php if (isset($message)): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form action="tambah_produk.php" method="post" enctype="multipart/form-data">
    <label for="name">Nama Produk:</label>
    <input type="text" id="name" name="name" required>

    <label for="description">Deskripsi:</label>
    <textarea id="description" name="description" rows="4"></textarea>

    <label for="price">Harga (Rp):</label>
    <input type="number" id="price" name="price" required step="100">

    <label for="stock">Stok:</label>
    <input type="number" id="stock" name="stock" required>

    <label for="category">Kategori:</label>
    <select id="category" name="category_id" required>
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
        <?php endforeach; ?>
    </select>

    <label for="product_image">Gambar Produk:</label>
    <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/jpg">

    <button type="submit">Tambah Produk</button>
</form>

<?php
// Memanggil footer admin
include 'admin_footer.php';
?>