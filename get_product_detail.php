<?php
include 'includes/db.php';

// Pastikan ada ID yang dikirim
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required.']);
    exit();
}

$product_id = (int)$_GET['id'];

try {
    // Ambil data satu produk berdasarkan ID
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Jika produk ditemukan, kirim datanya sebagai JSON
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        // Jika produk tidak ditemukan
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>