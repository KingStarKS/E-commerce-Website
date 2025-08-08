<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB connection
include 'includes/db.php';

// Initialize
$products = [];

// Build filtering query parts
$conditions = [];
$params = [];
$types = "";

// Join with product_sizes for filtering sizes and fetching stock info
$join = " INNER JOIN product_sizes ps ON products.id = ps.product_id ";

// Size Filter
if (!empty($_GET['size']) && is_array($_GET['size'])) {
    $size_filter = $_GET['size'];
    $valid_sizes = ["S", "M", "L", "XL", "XXL"];
    $size_filter = array_intersect($size_filter, $valid_sizes);
    if (count($size_filter) > 0) {
        $placeholders = implode(',', array_fill(0, count($size_filter), '?'));
        $conditions[] = "ps.size IN ($placeholders)";
        $types .= str_repeat("s", count($size_filter));
        $params = array_merge($params, $size_filter);
    }
}

// Price Filter
if (isset($_GET['price_min'], $_GET['price_max']) && $_GET['price_min'] !== '' && $_GET['price_max'] !== '') {
    $conditions[] = "products.price BETWEEN ? AND ?";
    $types .= "dd";
    $params[] = (float)$_GET['price_min'];
    $params[] = (float)$_GET['price_max'];
}

// Sorting
$sort_sql = " ORDER BY products.id DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'low_high':
            $sort_sql = " ORDER BY products.price ASC";
            break;
        case 'high_low':
            $sort_sql = " ORDER BY products.price DESC";
            break;
        case 'newest':
        default:
            $sort_sql = " ORDER BY products.id DESC";
            break;
    }
}

// Final query
$query = "SELECT DISTINCT products.* FROM products $join";
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= $sort_sql;

$stmt = mysqli_prepare($conn, $query);

if (!empty($params)) {
    $tmp = [];
    $tmp[] = &$types;
    foreach ($params as $key => $value) {
        $tmp[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $tmp);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die("Error fetching products: " . mysqli_error($conn));
}

while ($product = mysqli_fetch_assoc($result)) {
    $products[] = $product;
}
mysqli_stmt_close($stmt);

// Fetch sizes and stocks for the displayed products
$product_ids = array_column($products, 'id');
$product_sizes = [];
if (count($product_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types2 = str_repeat('i', count($product_ids));

    $query2 = "SELECT product_id, size, stock FROM product_sizes WHERE product_id IN ($placeholders) ORDER BY product_id, size";
    $stmt2 = mysqli_prepare($conn, $query2);

    // Bind params dynamically
    $tmp2 = [];
    $tmp2[] = &$types2;
    foreach ($product_ids as $key => $val) {
        $tmp2[] = &$product_ids[$key];
    }
    call_user_func_array([$stmt2, 'bind_param'], $tmp2);

    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);

    while ($row = mysqli_fetch_assoc($result2)) {
        $product_sizes[$row['product_id']][] = ['size' => $row['size'], 'stock' => $row['stock']];
    }
    mysqli_stmt_close($stmt2);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>All Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .product-card {
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .product-card img {
      object-fit: cover;
      height: 200px;
    }
    .sizes-stock {
      font-size: 0.9rem;
      margin-top: 0.5rem;
    }
    .size-stock-item {
      display: inline-block;
      margin-right: 10px;
    }
    .size-stock-item span.stock {
      font-weight: bold;
      color: green;
    }
  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>All Products</h2>
    <!-- Filter button -->
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
      Filters
    </button>
  </div>

  <div class="row">
    <div class="col-12">
      <?php if (empty($products)): ?>
        <div class="alert alert-info" role="alert">
          No products found.
        </div>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
          <?php foreach ($products as $product): ?>
            <div class="col">
              <div class="card product-card h-100">
                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                  <p class="card-text">$<?php echo number_format($product['price'], 2); ?></p>

                  <div class="sizes-stock">
                    <strong>Sizes & Stock:</strong><br />
                    <?php
                    $sizes_list = $product_sizes[$product['id']] ?? [];
                    if (count($sizes_list) === 0) {
                        echo '<em>No size/stock info</em>';
                    } else {
                        foreach ($sizes_list as $sz) {
                            echo '<span class="size-stock-item">' .
                                htmlspecialchars($sz['size']) .
                                ': <span class="stock">' . intval($sz['stock']) . '</span></span>';
                        }
                    }
                    ?>
                  </div>

                  <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary mt-auto">View Details</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Offcanvas filter panel -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filter Products</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form method="GET">
      <!-- No category/subcategory hidden inputs here since we show all products -->

      <div class="mb-4">
        <label class="form-label fw-bold">Sizes</label>
        <?php foreach (["S", "M", "L", "XL", "XXL"] as $size): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="size[]" value="<?php echo $size; ?>" id="offcanvas_size_<?php echo $size; ?>"
              <?php echo in_array($size, $_GET['size'] ?? []) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="offcanvas_size_<?php echo $size; ?>"><?php echo $size; ?></label>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mb-3">
        <label for="offcanvas_price_min" class="form-label fw-bold">Price Min</label>
        <input type="number" class="form-control" name="price_min" id="offcanvas_price_min" value="<?php echo htmlspecialchars($_GET['price_min'] ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label for="offcanvas_price_max" class="form-label fw-bold">Price Max</label>
        <input type="number" class="form-control" name="price_max" id="offcanvas_price_max" value="<?php echo htmlspecialchars($_GET['price_max'] ?? ''); ?>">
      </div>

      <div class="mb-4">
        <label for="offcanvas_sort" class="form-label fw-bold">Sort By</label>
        <select name="sort" id="offcanvas_sort" class="form-select">
          <option value="newest" <?php echo ($_GET['sort'] ?? '') == 'newest' ? 'selected' : ''; ?>>Newest</option>
          <option value="low_high" <?php echo ($_GET['sort'] ?? '') == 'low_high' ? 'selected' : ''; ?>>Price: Low to High</option>
          <option value="high_low" <?php echo ($_GET['sort'] ?? '') == 'high_low' ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<br>
</body>
<br>
<?php include 'includes/footer.php'; ?>
</html>
