<?php
session_start();
// include __DIR__ . '/../../includes/db.php';
include '../includes/db.php';

// Admin access check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Handle promotion/demotion
if (isset($_GET['promote'])) {
    $userId = (int)$_GET['promote'];
    mysqli_query($conn, "UPDATE users SET is_admin = 1 WHERE id = $userId");
    header("Location: manage-users.php");
    exit;
}
if (isset($_GET['demote'])) {
    $userId = (int)$_GET['demote'];
    mysqli_query($conn, "UPDATE users SET is_admin = 0 WHERE id = $userId");
    header("Location: manage-users.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $userId");
    header("Location: manage-users.php");
    exit;
}

// Search
$search = '';
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT * FROM users WHERE username LIKE '%$search%' OR email LIKE '%$search%' ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
}
$result = mysqli_query($conn, $sql);
?>





<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2>Manage Users</h2>
    <form method="get" class="mb-3">
        <input type="text" name="search" class="form-control" placeholder="Search by Name or Email" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary mt-2">Search</button>
    </form>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>User ID</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= ($user['is_admin'] == 1) ? 'Admin' : 'Customer' ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td>
                
  <a href="user-orders.php?user_id=<?= $user['id'] ?>" class="btn btn-info btn-sm">View Orders</a>
                        <a href="edit-user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="manage-users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                        <?php if ($user['is_admin']) : ?>
                            <a href="manage-users.php?demote=<?= $user['id'] ?>" class="btn btn-sm btn-secondary">Demote</a>
                        <?php else : ?>
                            <a href="manage-users.php?promote=<?= $user['id'] ?>" class="btn btn-sm btn-success">Promote</a>
                        <?php endif; ?>
                        <?php if ($user['is_blocked']): ?>
                        <a href="toggle_block.php?id=<?= $user['id'] ?>&action=unblock" class="btn btn-success btn-sm">Unblock</a>
                        <?php else: ?>
                                <a href="toggle_block.php?id=<?= $user['id'] ?>&action=block" class="btn btn-danger btn-sm">Block</a>
                                        <?php endif; ?>




                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
