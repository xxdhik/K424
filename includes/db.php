<?php
// Pengaturan Database
$host = 'localhost';
$db_name = 'k424'; 
$username = 'root';
$password = '';

// Blok ini mencoba terhubung ke database.
try {
    $pdo = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
// Jika koneksi gagal, script akan berhenti dan menampilkan pesan error.
catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Memulai "session" PHP. Ini penting untuk "mengingat" pengguna yang sudah login
// dan menyimpan data sementara seperti isi keranjang belanja.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>