<?php
session_start();
// include __DIR__ . '/../../includes/db.php';
include '../includes/db.php';
// Admin access check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Fetch all products
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2>Manage Products</h2>
    <a href="add-product.php" class="btn btn-success mb-3">Add New Product</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Price</th><th>Image</th><th>Created At</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td>$<?= number_format($product['price'], 2) ?></td>
                   <td>
    <?php
    $image = $product['image'];
    $path1 = "../uploads/$image";
    $path2 = "uploads/$image"; // fallback in case image was saved from 'admin/uploads/'

    if (file_exists($path1)) {
        echo "<img src='$path1' height='50'>";
    } elseif (file_exists($path2)) {
        echo "<img src='$path2' height='50'>";
    } else {
        echo "<span class='text-muted'>Image not found</span>";
    }
    ?>
</td>
                    <td><?= $product['created_at'] ?></td>
                    <td>
                        <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete-product.php?id=<?= $product['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
