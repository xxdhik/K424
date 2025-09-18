<?php

date_default_timezone_set('Asia/Jakarta');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

include 'includes/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil semua data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'] ?? null;
    $address = $_POST['address'];
    $city = $_POST['city'];
    $contact_no = $_POST['contact_no'];

    // Validasi Sederhana di Sisi Server
    if (empty($username) || empty($password) || empty($email) || empty($date_of_birth) || empty($gender) || empty($address) || empty($city) || empty($contact_no)) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($password !== $re_password) {
        $error = "Password tidak cocok!";
    } else {
        // Lanjutkan proses jika validasi dasar lolos
        $verification_token = bin2hex(random_bytes(32));
        $token_expires_at = date('Y-m-d H:i:s', time() + 3600); // Token valid untuk 1 jam
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Query SQL yang lengkap
        $sql = "INSERT INTO users (username, password, email, date_of_birth, gender, address, city, contact_no, verification_token, token_expires_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$username, $hashed_password, $email, $date_of_birth, $gender, $address, $city, $contact_no, $verification_token, $token_expires_at]);
            
            // Logika Pengiriman Email Verifikasi
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'andhikapuja2004@gmail.com'; // Ganti dengan email Anda
            $mail->Password = 'rwekxmnfsqlctbyv'; // Ganti dengan "App Password" Anda
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('no-reply@k424.com', 'Toko K424');
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Verifikasi Akun Anda - Toko K424';
            $verification_link = "http://localhost/k424/verify.php?token=" . $verification_token;
            $mail->Body    = "Halo $username,<br><br>Terima kasih telah mendaftar. Silakan klik link di bawah ini untuk mengaktifkan akun Anda:<br><br><a href='$verification_link'>Aktifkan Akun Saya</a>";
            
            $mail->send();
            $message = 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi.';

        } catch (Exception $e) {
            $error = "Registrasi gagal. Username atau Email mungkin sudah ada. Error: " . $e->getMessage();
        }
    }
}
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h1 class="text-center">FORM REGISTRASI</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="register.php" method="post" onsubmit="return validateForm()">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username">
            <div id="usernameError" class="error-message"></div>

            <label for="password">Kata Sandi:</label>
            <input type="password" id="password" name="password">
            <div id="passwordError" class="error-message"></div>
            
            <label for="re_password">Ulangi Kata Sandi:</label>
            <input type="password" id="re_password" name="re_password">
            <div id="repasswordError" class="error-message"></div>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email">
            <div id="emailError" class="error-message"></div>

            <label for="date_of_birth">Tanggal Lahir:</label>
            <input type="date" id="date_of_birth" name="date_of_birth">
            <div id="dobError" class="error-message"></div>

            <label>Jenis Kelamin:</label>
            <div class="gender-group">
                <input type="radio" id="male" name="gender" value="Laki-laki"> 
                <label for="male">Laki-laki</label>
                <input type="radio" id="female" name="gender" value="Perempuan">
                <label for="female">Perempuan</label>
            </div>
            <div id="genderError" class="error-message"></div>
            
            <label for="address">Alamat:</label>
            <textarea id="address" name="address" rows="3"></textarea>
            <div id="addressError" class="error-message"></div>

            <label for="city">Kota:</label>
            <input type="text" id="city" name="city">
            <div id="cityError" class="error-message"></div>

            <label for="contact_no">No. Handphone:</label>
            <input type="text" id="contact_no" name="contact_no">
            <div id="contactError" class="error-message"></div>
            
            <div class="d-flex justify-content-center gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>
</div>

<script>
    function validateForm() {
        // Daftar semua ID input dan ID div errornya
        const fields = [
            { inputId: 'username', errorId: 'usernameError', msg: 'Username tidak boleh kosong.' },
            { inputId: 'password', errorId: 'passwordError', msg: 'Kata sandi tidak boleh kosong.' },
            { inputId: 're_password', errorId: 'repasswordError', msg: 'Harap ulangi kata sandi Anda.' },
            { inputId: 'email', errorId: 'emailError', msg: 'Email tidak boleh kosong.' },
            { inputId: 'date_of_birth', errorId: 'dobError', msg: 'Tanggal lahir tidak boleh kosong.' },
            { inputId: 'address', errorId: 'addressError', msg: 'Alamat tidak boleh kosong.' },
            { inputId: 'city', errorId: 'cityError', msg: 'Kota tidak boleh kosong.' },
            { inputId: 'contact_no', errorId: 'contactError', msg: 'No. Handphone tidak boleh kosong.' }
        ];

        let isValid = true;
        
        // Hapus semua error sebelumnya
        fields.forEach(field => {
            document.getElementById(field.errorId).innerText = '';
        });
        document.getElementById('genderError').innerText = '';

        // Validasi setiap field
        fields.forEach(field => {
            const inputElement = document.getElementById(field.inputId);
            if (inputElement.value.trim() === '') {
                document.getElementById(field.errorId).innerText = field.msg;
                isValid = false;
            }
        });

        // Validasi khusus untuk password
        const password = document.getElementById('password').value;
        const re_password = document.getElementById('re_password').value;
        if (re_password !== '' && password !== re_password) {
            document.getElementById('repasswordError').innerText = 'Password tidak cocok.';
            isValid = false;
        }
        
        // Validasi khusus untuk gender
        const genderSelected = document.querySelector('input[name="gender"]:checked');
        if (!genderSelected) {
            document.getElementById('genderError').innerText = 'Harap pilih jenis kelamin.';
            isValid = false;
        }

        return isValid;
    }
</script>

<?php
include 'includes/footer.php';
?>