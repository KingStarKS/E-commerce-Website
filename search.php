<?php
include 'includes/db.php';
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($query !== '') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search - Zara Style</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .search-wrapper {
      position: relative;
    }
    .clear-btn {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      border: none;
      background: transparent;
      font-size: 1.2rem;
      cursor: pointer;
      color: #999;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-4">
  <form method="GET" action="search.php" class="mb-4">
    <div class="search-wrapper">
      <input type="text" name="q" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($query); ?>">
      <?php if ($query !== ''): ?>
        <button type="button" class="clear-btn" onclick="window.location.href='search.php';">&times;</button>
      <?php endif; ?>
    </div>
  </form>

  <?php if ($query !== ''): ?>
    <h4>Search results for "<?php echo htmlspecialchars($query); ?>"</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4 mt-2">
      <?php if ($results->num_rows > 0): ?>
        <?php while ($product = $results->fetch_assoc()): ?>
          <div class="col">
            <div class="card">
              <img src="images/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" style="height:200px;object-fit:cover;">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="card-text">$<?php echo number_format($product['price'], 2); ?></p>
                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">View</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="alert alert-warning">No products found.</div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
