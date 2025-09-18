<?php
include 'admin_header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    die("Produk tidak valid.");
}

// Logika untuk memproses form UPDATE saat di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $current_image = $_POST['current_image']; // Ambil nama gambar yang lama

    $new_image_filename = $current_image; // Defaultnya, tetap gunakan gambar lama

    // Cek apakah ada file gambar baru yang di-upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $upload_dir = '../assets/uploads/';
        $new_image_filename = time() . '_' . basename($_FILES['product_image']['name']);
        $target_file = $upload_dir . $new_image_filename;

        // Pindahkan file baru
        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $error = "Gagal mengupload gambar baru.";
            $new_image_filename = $current_image; // Jika gagal, kembalikan ke gambar lama
        } else {
            // Jika berhasil upload gambar baru, hapus gambar lama (jika ada)
            if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                unlink($upload_dir . $current_image);
            }
        }
    }
    
    // Hanya lanjut jika tidak ada error upload
    if (!isset($error)) {
        $sql = "UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image_url=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $new_image_filename, $product_id])) {
            $message = "Produk berhasil diperbarui.";
        } else {
            $error = "Gagal memperbarui produk.";
        }
    }
}

// Ambil data produk saat ini untuk ditampilkan di form
$stmt_product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt_product->execute([$product_id]);
$product = $stmt_product->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Produk tidak ditemukan.');
}

$stmt_cat = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Edit Produk: <?php echo htmlspecialchars($product['name']); ?></h1>
<p><a href="kelola_produk.php">← Kembali ke Daftar Produk</a></p>

<?php if (isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
<?php if (isset($error)) echo "<div class='alert alert-error'>$error</div>"; ?>

<form action="edit_produk.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">

    <label for="name">Nama Produk:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

    <label for="description">Deskripsi:</label>
    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
    
    <label for="price">Harga (Rp):</label>
    <input type="number" id="price" name="price" value="<?php echo $product['price']; ?>" required>
    
    <label for="stock">Stok:</label>
    <input type="number" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>

    <label for="category">Kategori:</label>
    <select id="category" name="category_id" required>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>" <?php if($product['category_id'] == $category['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($category['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <label>Gambar Saat Ini:</label>
    <div>
        <?php if (!empty($product['image_url'])): ?>
            <img src="../assets/uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="Gambar produk" width="150">
        <?php else: ?>
            <p>Tidak ada gambar.</p>
        <?php endif; ?>
    </div>

    <label for="product_image">Ganti Gambar (Opsional):</label>
    <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/jpg">
    
    <button type="submit">Simpan Perubahan</button>
</form>

<?php
include 'admin_footer.php';
?>