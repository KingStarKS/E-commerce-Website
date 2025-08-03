<?php
session_start();
include __DIR__ . '/../../includes/db.php';

// Check admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Get user data
if (!isset($_GET['id'])) {
    header("Location: manage-users.php");
    exit;
}

$userId = (int)$_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    echo "User not found.";
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email' WHERE id = $userId");
    header("Location: manage-users.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h2>Edit User</h2>
    <form method="post">
        <div class="mb-3">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="manage-users.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
