<?php
session_start(); // Mulai session
session_unset(); // Hapus semua variabel session
session_destroy(); // Hancurkan session
header('Location: index.php'); // Arahkan kembali ke halaman utama
exit();
?>