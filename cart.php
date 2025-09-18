<?php
include 'includes/db.php';

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- [BAGIAN BARU] Logika untuk menangani aksi HAPUS dari link (GET) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($action == 'remove' && $id > 0) {
        if (isset($_SESSION['cart'][$id])) {
            // Hapus item dari session cart
            unset($_SESSION['cart'][$id]);
        }
    }
    
    // Redirect kembali ke cart.php untuk membersihkan URL dan refresh tampilan
    header('Location: cart.php');
    exit();
}
// --- AKHIR BAGIAN BARU ---


// --- BAGIAN 1: Merespon permintaan POST dari JavaScript (UNTUK MENAMBAH PRODUK) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($action == 'add' && $id > 0) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        } else {
            $_SESSION['cart'][$id] = 1;
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
    }
    exit(); 
}


// --- BAGIAN 2: Menampilkan halaman keranjang jika diakses biasa ---
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products_in_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products_in_cart as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total_price += $subtotal;
        $cart_items[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <h1>Keranjang Belanja Anda</h1>
    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">Keranjang belanja Anda masih kosong.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td>
                        <?php if(!empty($item['product']['image_url'])): ?>
                            <img src="assets/uploads/<?php echo htmlspecialchars($item['product']['image_url']); ?>" width="50" class="me-2 rounded">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($item['product']['name']); ?>
                    </td>
                    <td>Rp <?php echo number_format($item['product']['price'], 0, ',', '.'); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="cart.php?action=remove&id=<?php echo $item['product']['id']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th colspan="2">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></th>
                </tr>
            </tfoot>
        </table>
        <div class="text-end mt-4">
            <a href="checkout.php" class="btn btn-primary btn-lg">Lanjutkan ke Pembayaran</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>