<?php
include 'includes/db.php';

// --- Logika untuk Filter & Pencarian Produk ---
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search_term = $_GET['search'] ?? '';

$sql = "SELECT * FROM products WHERE stock > 0";
$params = [];

if ($category_id > 0) {
    $sql .= " AND category_id = ?";
    $params[] = $category_id;
}
if (!empty($search_term)) {
    $sql .= " AND name LIKE ?";
    $params[] = '%' . $search_term . '%';
}

$sql .= " ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Logika untuk Mengambil Daftar Kategori ---
$stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-5">
    
    <div class="row">
        <div class="col-md-9">
            <h1 class="mb-4">Etalase Produk</h1>
            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12"><div class="alert alert-info">Produk tidak ditemukan.</div></div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <img src="assets/uploads/<?php echo htmlspecialchars($product['image_url'] ?? 'placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text fw-bold text-success fs-5">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="card-footer d-flex">
                                    <button class="btn btn-secondary btn-sm flex-fill me-1" onclick="showProductDetail(<?php echo $product['id']; ?>)">View</button>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form onsubmit="addToCart(event, <?php echo $product['id']; ?>)" class="flex-fill ms-1">
                                            <button type="submit" class="btn btn-primary btn-sm w-100">Buy</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header fw-bold">Product Category</div>
                <ul class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action <?php if($category_id == 0) echo 'active'; ?>">Semua Kategori</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="index.php?category_id=<?php echo $category['id']; ?>" class="list-group-item list-group-item-action <?php if($category_id == $category['id']) echo 'active'; ?>">
                           <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">Produk berhasil ditambahkan ke keranjang!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="productDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalProductName">Nama Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6"><img id="modalProductImage" src="" class="img-fluid rounded"></div>
            <div class="col-md-6">
                <p id="modalProductDescription">Deskripsi produk...</p>
                <p class="fs-4 fw-bold text-success" id="modalProductPrice">Rp 0</p>
                <p id="modalProductStock">Stok: 0</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form id="modalCartForm">
                        <input type="hidden" id="modalProductId" value="">
                        <button type="submit" class="btn btn-primary w-100">+ Tambah ke Keranjang</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary w-100">Login untuk Membeli</a>
                <?php endif; ?>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const productModalElement = document.getElementById('productDetailModal');
        const liveToastElement = document.getElementById('liveToast');
        
        if (productModalElement && liveToastElement) {
            const productModal = new bootstrap.Modal(productModalElement);

            window.showProductDetail = function(productId) {
                fetch('get_product_detail.php?id=' + productId)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('modalProductName').innerText = data.name;
                        document.getElementById('modalProductImage').src = 'assets/uploads/' + (data.image_url || 'placeholder.png');
                        document.getElementById('modalProductDescription').innerText = data.description;
                        document.getElementById('modalProductPrice').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.price);
                        document.getElementById('modalProductStock').innerText = 'Stok Tersedia: ' + data.stock;
                        
                        const modalProductIdInput = document.getElementById('modalProductId');
                        if(modalProductIdInput) {
                            modalProductIdInput.value = data.id;
                        }
                        
                        productModal.show();
                    }).catch(error => console.error('Error fetching product details:', error));
            };

            const modalCartForm = document.getElementById('modalCartForm');
            if (modalCartForm) {
                modalCartForm.addEventListener('submit', function(event) {
                    const productId = document.getElementById('modalProductId').value;
                    window.addToCart(event, productId, true);
                });
            }
        }
    });

    function addToCart(event, productId, fromModal = false) {
        event.preventDefault();
        event.stopPropagation();

        fetch('cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=add&id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const successToast = bootstrap.Toast.getInstance(document.getElementById('liveToast')) || new bootstrap.Toast(document.getElementById('liveToast'));
                successToast.show();
                if(fromModal) {
                    const productModal = bootstrap.Toast.getInstance(document.getElementById('productDetailModal'));
                    productModal.hide();
                }
            } else {
                alert("Gagal menambahkan ke keranjang: " + data.message);
            }
        }).catch(error => console.error('Error adding to cart:', error));
    }
</script>

<?php
include 'includes/footer.php';
?>