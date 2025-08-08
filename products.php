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

// Initialize variables
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$subcategory_id = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : 0;
$category_name = '';
$subcategory_name = '';
$products = [];

// Fetch category name
if ($category_id > 0) {
    $cat_query = "SELECT name FROM categories WHERE id = ?";
    $cat_stmt = mysqli_prepare($conn, $cat_query);
    mysqli_stmt_bind_param($cat_stmt, "i", $category_id);
    mysqli_stmt_execute($cat_stmt);
    $cat_result = mysqli_stmt_get_result($cat_stmt);
    if ($cat_result && mysqli_num_rows($cat_result) > 0) {
        $category_name = mysqli_fetch_assoc($cat_result)['name'];
    }
    mysqli_stmt_close($cat_stmt);
}

// Fetch subcategory name
if ($subcategory_id > 0) {
    $subcat_query = "SELECT name FROM subcategories WHERE id = ? AND category_id = ?";
    $subcat_stmt = mysqli_prepare($conn, $subcat_query);
    mysqli_stmt_bind_param($subcat_stmt, "ii", $subcategory_id, $category_id);
    mysqli_stmt_execute($subcat_stmt);
    $subcat_result = mysqli_stmt_get_result($subcat_stmt);
    if ($subcat_result && mysqli_num_rows($subcat_result) > 0) {
        $subcategory_name = mysqli_fetch_assoc($subcat_result)['name'];
    }
    mysqli_stmt_close($subcat_stmt);
}

// Build dynamic filtering query
$conditions = " WHERE category = ? AND subcategory = ?";
$params = ["ii", $category_id, $subcategory_id];

// Size Filter
if (!empty($_GET['size']) && is_array($_GET['size'])) {
    $size_filter = $_GET['size'];
    $valid_sizes = ["S", "M", "L", "XL", "XXL"];
    $size_filter = array_intersect($size_filter, $valid_sizes);
    if (count($size_filter) > 0) {
        $placeholders = implode(',', array_fill(0, count($size_filter), '?'));
        $conditions .= " AND size IN ($placeholders)";
        $params[0] .= str_repeat("s", count($size_filter));
        $params = array_merge($params, $size_filter);
    }
}

// Price Filter
if (isset($_GET['price_min'], $_GET['price_max']) && $_GET['price_min'] !== '' && $_GET['price_max'] !== '') {
    $conditions .= " AND price BETWEEN ? AND ?";
    $params[0] .= "dd";
    $params[] = (float)$_GET['price_min'];
    $params[] = (float)$_GET['price_max'];
}

// Sort Order
$sort_sql = " ORDER BY id DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'low_high':
            $sort_sql = " ORDER BY price ASC";
            break;
        case 'high_low':
            $sort_sql = " ORDER BY price DESC";
            break;
        case 'newest':
        default:
            $sort_sql = " ORDER BY id DESC";
            break;
    }
}

// Final query
$query = "SELECT * FROM products" . $conditions . $sort_sql;
$stmt = mysqli_prepare($conn, $query);

// Bind parameters dynamically
$tmp = [];
foreach ($params as $key => $value) {
    $tmp[$key] = &$params[$key];
}
call_user_func_array([$stmt, 'bind_param'], $tmp);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die("Error fetching products: " . mysqli_error($conn));
}
while ($product = mysqli_fetch_assoc($result)) {
    $products[] = $product;
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Shop - <?php echo htmlspecialchars($category_name . ($subcategory_name ? " - $subcategory_name" : '')); ?></title>
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
  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><?php echo htmlspecialchars($category_name . ($subcategory_name ? " - $subcategory_name" : '')); ?></h2>
    <!-- Filter button -->
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
      Filters
    </button>
  </div>

  <div class="row">
    <div class="col-12">
      <?php if (empty($products)): ?>
        <div class="alert alert-info" role="alert">
          No products found in this category.
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
                  <p class="card-text">Size: <?php echo htmlspecialchars($product['size']); ?></p>
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
      <input type="hidden" name="category" value="<?php echo $category_id; ?>">
      <input type="hidden" name="subcategory" value="<?php echo $subcategory_id; ?>">

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

</body>
<br>
<br>
<?php include 'includes/footer.php'; ?>
</html>
