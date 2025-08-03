<?php
session_start();
// include __DIR__ . '/../../includes/db.php';
include '../includes/db.php';

// Admin check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Check user ID in query param
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "Invalid user ID.";
    exit;
}

$user_id = (int)$_GET['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    echo "User not found.";
    exit;
}

$user = $user_result->fetch_assoc();

// Fetch orders of the user
$stmt_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$orders_result = $stmt_orders->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders of <?= htmlspecialchars($user['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2>Order History for <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</h2>
    <?php if ($orders_result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></td>
                        <td>$<?= number_format($order['total'], 2) ?></td>
                        <td><?= htmlspecialchars($order['STATUS']) ?></td>
                        <td><a href="order-confirmation.php?id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">View</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>This user has no orders.</p>
    <?php endif; ?>
    <a href="manage-users.php" class="btn btn-secondary mt-3">Back to Manage Users</a>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
