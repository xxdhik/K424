<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K424 - Toko Alat Kesehatan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php">K424</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                </ul>
            <form class="d-flex" action="index.php" method="get">
                <input class="form-control me-2" type="search" name="search" placeholder="Cari produk...">
                <button class="btn btn-outline-success" type="submit">Cari</button>
            </form>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="cart.php">🛒 Keranjang</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat_pesanan.php">🧾 Riwayat</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link fw-bold" href="admin/index.php">🚀 Panel Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">🟥 Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">🔑 Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">📝 Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>