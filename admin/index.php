<?php
include '../includes/db.php';

// --- BAGIAN KEAMANAN ---
// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    die('Akses ditolak.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - K424</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h1>Selamat Datang di Panel Admin</h1>
        <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
        <p>Dari sini Anda bisa mengelola website K424.</p>

        <h2>Menu Navigasi Admin</h2>
        <ul class="admin-nav-grid"> <li><a href="tambah_produk.php">Tambah Produk Baru</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_kategori.php">Kelola Kategori Produk</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
        </ul>
        <br>
        <a href="../index.php">Kembali ke Tampilan Toko</a> | <a href="../logout.php">Logout</a>
    </main>
</body>
</html>