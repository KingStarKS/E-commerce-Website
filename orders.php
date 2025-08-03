<?php
session_start();
include 'includes/db.php';

// Get user ID from session
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view orders.";
    exit;
}
$user_id = $_SESSION['user_id'];

// Fetch orders for this user
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
  <h2>Your Orders</h2>

  <?php if ($result->num_rows > 0): ?>
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
        <?php while ($order = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $order['id'] ?></td>
            <td><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></td>
            <td>$<?= number_format($order['total'], 2) ?></td>
            <td><?= htmlspecialchars($order['STATUS']) ?></td>
            <td><a href="order-confirmation.php?order_id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">View</a></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>You have no orders yet.</p>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
