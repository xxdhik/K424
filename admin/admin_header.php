<?php
// Memanggil file koneksi utama, yang juga akan memulai session
include_once '../includes/db.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    die('Akses ditolak.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Panel Admin - K424</title>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <nav class="admin-nav">
                <a href="index.php" class="admin-brand">Admin K424</a>
                <a href="../index.php" class="admin-link">Lihat Toko</a>
            </nav>
        </div>
    </header>
    <main class="container admin-main">