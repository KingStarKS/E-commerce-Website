<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Product ID not specified.";
    exit;
}

$product_id = intval($_GET['id']);
$product_query = $conn->prepare("SELECT * FROM products WHERE id = ?");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product_result = $product_query->get_result();

if ($product_result->num_rows == 0) {
    echo "Product not found.";
    exit;
}

$product = $product_result->fetch_assoc();

// Fetch sizes and stocks for this product from product_sizes table
$sizeStock = [];
$sizesResult = $conn->prepare("SELECT size, stock FROM product_sizes WHERE product_id = ?");
$sizesResult->bind_param("i", $product_id);
$sizesResult->execute();
$sizesRes = $sizesResult->get_result();
while ($row = $sizesRes->fetch_assoc()) {
    $sizeStock[$row['size']] = intval($row['stock']);
}
$sizesResult->close();

// Define all allowed sizes for consistent order
$allSizes = ['S', 'M', 'L', 'XL', 'XXL'];

// Filter only sizes available in product_sizes (optional, or you can show all and disable zero stock)
$availableSizes = array_filter($allSizes, function($sz) use ($sizeStock) {
    return isset($sizeStock[$sz]);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .star {
      color: gold;
      font-size: 20px;
    }
  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5 mb-5">
  <div class="card mb-4" style="max-width: 900px; margin: auto;">
    <div class="row g-0">
      <div class="col-md-5">
        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($product['name']) ?>" style="object-fit: cover; height: 100%;">
      </div>
      <div class="col-md-7">
        <div class="card-body d-flex flex-column h-100">
          <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
          <p class="card-text flex-grow-1"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
          <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>

          <!-- Sizes -->
          <div class="mb-3">
            <label for="sizeSelect" class="form-label">Select Size</label>
            <select class="form-select" id="sizeSelect">
              <?php foreach ($allSizes as $size): 
                $stockForSize = $sizeStock[$size] ?? 0;
                $disabled = ($stockForSize == 0) ? 'disabled' : '';
              ?>
                <option value="<?= $size ?>" <?= $disabled ?>><?= $size ?><?= $disabled ? " (Out of stock)" : "" ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Quantity -->
          <div class="mb-3">
            <label for="quantitySelect" class="form-label">Quantity</label>
            <select class="form-select" id="quantitySelect" name="quantity">
              <!-- options added by JS -->
            </select>
          </div>

          <!-- Stock -->
          <p><strong>In Stock:</strong> <span id="stockText"></span></p>

          <!-- Rating -->
          <p><strong>Rating:</strong>
            <?php
              $rating = rand(3, 5); // Simulated rating for now
              for ($i = 1; $i <= 5; $i++) {
                echo '<span class="star">' . ($i <= $rating ? '★' : '☆') . '</span>';
              }
            ?>
          </p>

          <!-- Add to Cart -->
          <form action="cart.php" method="GET" class="mt-auto" id="addToCartForm">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            <input type="hidden" name="size" id="selectedSize" value="">
            <input type="hidden" name="quantity" id="selectedQuantity" value="1">
            <button type="submit" class="btn btn-primary" id="addToCartBtn">Add to Cart</button>
          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <h4 class="text-center mb-4">Related Products</h4>
  <div class="row">
    <?php
      $related_query = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
      $related_query->bind_param("si", $product['category'], $product_id);
      $related_query->execute();
      $related_result = $related_query->get_result();

      if ($related_result->num_rows > 0):
        while ($rel = $related_result->fetch_assoc()):
    ?>
      <div class="col-md-3 mb-4">
        <div class="card h-100">
          <img src="uploads/<?= htmlspecialchars($rel['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($rel['name']) ?>" style="height: 200px; object-fit: cover;">
          <div class="card-body d-flex flex-column">
            <h6 class="card-title"><?= htmlspecialchars($rel['name']) ?></h6>
            <p class="card-text">$<?= number_format($rel['price'], 2) ?></p>
            <a href="product-details.php?id=<?= $rel['id'] ?>" class="btn btn-sm btn-outline-primary mt-auto">View</a>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <p class="text-center">No related products found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
  // Pass PHP size-stock array to JS
  const sizeStock = <?= json_encode($sizeStock) ?>;

  const sizeSelect = document.getElementById('sizeSelect');
  const quantitySelect = document.getElementById('quantitySelect');
  const stockText = document.getElementById('stockText');
  const addToCartBtn = document.getElementById('addToCartBtn');
  const selectedSizeInput = document.getElementById('selectedSize');
  const selectedQuantityInput = document.getElementById('selectedQuantity');

  function updateStockAndQuantity() {
    const selectedSize = sizeSelect.value;
    const stock = sizeStock[selectedSize] || 0;
    stockText.textContent = stock > 0 ? stock + " items" : "Out of stock";

    // Clear quantity options
    quantitySelect.innerHTML = "";

    if (stock === 0) {
      addToCartBtn.disabled = true;
      selectedSizeInput.value = "";
      selectedQuantityInput.value = 0;
    } else {
      addToCartBtn.disabled = false;
      selectedSizeInput.value = selectedSize;

      // Max quantity is min(stock, 10)
      const maxQty = Math.min(stock, 10);
      for (let i = 1; i <= maxQty; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        quantitySelect.appendChild(option);
      }
      selectedQuantityInput.value = 1;
    }
  }

  // On quantity change, update hidden input
  quantitySelect.addEventListener('change', () => {
    selectedQuantityInput.value = quantitySelect.value;
  });

  // On size change
  sizeSelect.addEventListener('change', updateStockAndQuantity);

  // Initialize on load
  updateStockAndQuantity();
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>
