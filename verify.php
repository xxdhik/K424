<?php
include 'includes/db.php';
$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cari pengguna dengan token yang sesuai DAN belum kedaluwarsa
    $sql = "SELECT * FROM users WHERE verification_token = ? AND token_expires_at > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Jika ditemukan, update status verifikasi dan hapus token
        $update_sql = "UPDATE users SET is_verified = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$user['id']]);
        $message = "Verifikasi berhasil! Akun Anda kini aktif. Silakan login.";
    } else {
        $message = "Token verifikasi tidak valid atau sudah kedaluwarsa.";
    }
} else {
    $message = "Tidak ada token verifikasi yang diberikan.";
}

include 'includes/header.php';
?>
<div class="container text-center mt-5">
    <h1>Status Verifikasi Akun</h1>
    <div class="alert alert-info mt-4">
        <?php echo $message; ?>
    </div>
    <a href="login.php" class="btn btn-primary mt-3">Ke Halaman Login</a>
</div>
<?php include 'includes/footer.php'; ?>