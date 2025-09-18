<?php
include 'admin_header.php';

// --- LOGIKA AKSI HAPUS PENGGUNA ---
// ... (Logika PHP untuk proses form tetap sama persis) ...
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = (int)$_GET['id'];
    if ($user_id_to_delete == $_SESSION['user_id']) {
        $error = "Anda tidak bisa menghapus akun Anda sendiri.";
    } else {
        try {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id_to_delete]);
            header('Location: kelola_pengguna.php');
            exit();
        } catch (PDOException $e) { $error = "Gagal menghapus pengguna."; }
    }
}

// --- LOGIKA MENGAMBIL DATA PENGGUNA ---
$stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Kelola Pengguna</h1>
<p><a href="index.php">← Kembali ke Dashboard</a></p>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Bergabung</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
            <td><?php echo date('d-m-Y', strtotime($user['created_at'])); ?></td>
            <td>
                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <a href="kelola_pengguna.php?action=delete&id=<?php echo $user['id']; ?>" onclick="return confirm('Yakin?');">Hapus</a>
                <?php else: ?>
                    (Akun Anda)
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
include 'admin_footer.php';
?>