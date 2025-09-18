<?php
include 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['user_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi password
    if ($user && password_verify($password, $user['password'])) {
        
        // [PENYESUAIAN] Cek apakah akun sudah diverifikasi
        if ($user['is_verified'] == 1) {
            // Jika sudah terverifikasi, lanjutkan login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            // Jika password benar, tapi akun belum aktif
            $error = "Akun Anda belum diverifikasi. Silakan cek email Anda untuk link aktivasi.";
        }

    } else {
        // Jika username atau password salah
        $error = "User ID atau Password salah!";
    }
}
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form login-layout">
        <div class="row align-items-center">
            
            <div class="col-md-5">
                <div class="logo-placeholder">
                    LOGO
                </div>
            </div>

            <div class="col-md-7">
                <h4 class="text-center mb-4">Selamat datang di<br>Toko Alat Kesehatan</h4>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div class="alert alert-success">Registrasi berhasil! Silakan login setelah memverifikasi email Anda.</div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <label for="user_id">User ID:</label>
                    <input type="text" id="user_id" name="user_id" required class="form-control mb-3">
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required class="form-control mb-3">
                    
                    <button type="submit" class="btn btn-primary w-100">LOGIN</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>