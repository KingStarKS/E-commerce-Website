<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
  header('Location: ../login.php');
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid product ID";
  exit;
}

$id = intval($_GET['id']);
$message = '';

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
  echo "Product not found";
  exit;
}

// Fetch stock by size
$sizesResult = $conn->prepare("SELECT size, stock FROM product_sizes WHERE product_id = ?");
$sizesResult->bind_param("i", $id);
$sizesResult->execute();
$sizesRes = $sizesResult->get_result();
$productSizes = [];
while ($row = $sizesRes->fetch_assoc()) {
  $productSizes[$row['size']] = $row['stock'];
}
$sizesResult->close();

// Fetch categories and subcategories
$categories = [];
$subcategories = [];

$cat = mysqli_query($conn, "SELECT id,name FROM categories ORDER BY name");
while ($c = mysqli_fetch_assoc($cat)) {
  $categories[$c['id']] = $c['name'];
}

$sub = mysqli_query($conn, "SELECT id,category_id,name FROM subcategories ORDER BY name");
while ($s = mysqli_fetch_assoc($sub)) {
  $subcategories[$s['category_id']][] = ['id'=>$s['id'],'name'=>$s['name']];
}

$sizes = ["S","M","L","XL","XXL"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $desc = trim($_POST['description']);
  $price = floatval($_POST['price']);
  $category_id = intval($_POST['category_id']);
  $subcategory_id = intval($_POST['subcategory_id']);
  $stock_input = $_POST['stock'] ?? [];

  // Handle image upload
  $image = $product['image'];
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif'])) {
      $filename = uniqid('prd_', true).".".$ext;
      $dest = '../uploads/'.$filename;
      if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $image = $filename;
      } else {
        $message = "Failed to move uploaded file.";
      }
    } else {
      $message = "Invalid image extension.";
    }
  }

  if (!$message) {
    if ($name && $desc && $price > 0 && $category_id && $subcategory_id) {
      $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=?, category=?, subcategory=? WHERE id=?");
      $stmt->bind_param("ssdsiis", $name, $desc, $price, $image, $category_id, $subcategory_id, $id);
      if ($stmt->execute()) {
        // Update stock
        $size_stmt = $conn->prepare("SELECT id FROM product_sizes WHERE product_id=? AND size=?");
        $update_stmt = $conn->prepare("UPDATE product_sizes SET stock=? WHERE id=?");
        $insert_stmt = $conn->prepare("INSERT INTO product_sizes (product_id, size, stock) VALUES (?, ?, ?)");

        foreach ($sizes as $sz) {
          $val = intval($stock_input[$sz] ?? 0);

          $size_stmt->bind_param("is", $id, $sz);
          $size_stmt->execute();
          $size_stmt->store_result();
          if ($size_stmt->num_rows > 0) {
            $size_stmt->bind_result($psid);
            $size_stmt->fetch();
            $update_stmt->bind_param("ii", $val, $psid);
            $update_stmt->execute();
          } else {
            if ($val > 0) {
              $insert_stmt->bind_param("isi", $id, $sz, $val);
              $insert_stmt->execute();
            }
          }
        }
        $size_stmt->close();
        $update_stmt->close();
        $insert_stmt->close();

        $message = "Product updated successfully.";

        // Refresh product data
        $stmt2 = $conn->prepare("SELECT * FROM products WHERE id=?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $product = $result2->fetch_assoc();
        $stmt2->close();

      } else {
        $message = "Error updating product: " . $stmt->error;
      }
      $stmt->close();
    } else {
      $message = "Please fill all required fields.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Product</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      margin:0;
      padding:30px;
      font-family:'Segoe UI', sans-serif;
      background-color:#f5f5f5;
    }

    .card-sharp {
      border-radius: 0 !important;
    }

    .form-control, select, textarea {
      border-radius: 0 !important;
      background: #fafafa;
      border: 1px solid #ccc !important;
      transition: border-color 0.2s ease-in-out;
    }

    .form-control:focus, select:focus, textarea:focus {
      border-color: #0d6efd !important;
      outline: none;
      box-shadow: none;
    }

    label {
      font-weight: 600;
      margin-bottom: 6px;
      display: inline-block;
    }

    .stock-row {
      display:flex;
      align-items:center;
      margin-top:10px;
    }

    .stock-row label {
      margin:0;
      width:40px;
      font-weight:500;
    }

    .stock-row input {
      margin-left:10px;
      flex:1;
      border-radius: 0 !important;
      background: #fafafa;
      border: 1px solid #ccc !important;
    }

    .alert {
      margin-top:15px;
      background:#e6f7ff;
      padding:12px;
      border-left:4px solid #2196f3;
      color:#222;
      border-radius: 0 !important;
    }

    .actions {
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-top:30px;
    }

    button, .btn-back {
      padding:10px 20px;
      font-weight:600;
      border:none;
      text-decoration:none;
      background:#222;
      color:#fff;
      cursor:pointer;
      border-radius: 0 !important;
    }

    .btn-back {
      background:#666;
    }

    #previewImg {
      height: 200px;
      object-fit: contain;
      border: 1px solid #dee2e6;
      background-color: #f8f9fa;
      width: 100%;
    }

    #livePreview {
      flex: 0 0 280px;
      max-width: 280px;
      border: 1px solid #dee2e6;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      border-radius: 0 !important;
      background-color: #fff;
      top: 100px;  
      position: sticky;
      height: fit-content;
      padding: 1rem;
    }

    /* Size badges with sharp corners */
    #previewSizes .size-badge {
      background-color: #0d6efd;
      color: #fff;
      padding: 0.35em 0.75em;
      font-weight: 600;
      font-size: 0.8rem;
      border-radius: 0;
      user-select: none;
      margin-right: 6px;
      margin-bottom: 6px;
      display: inline-block;
    }
  </style>
  <script>
    const subcats = <?= json_encode($subcategories) ?>;
    const sizes = <?= json_encode($sizes) ?>;

    window.addEventListener('DOMContentLoaded', () => {
      const cat = document.getElementById('category_id');
      const sub = document.getElementById('subcategory_id');
      const nameInput = document.querySelector('input[name="name"]');
      const descInput = document.querySelector('textarea[name="description"]');
      const priceInput = document.querySelector('input[name="price"]');
      const imageInput = document.getElementById('image');
      const previewImg = document.getElementById('previewImg');
      const previewName = document.getElementById('previewName');
      const previewDesc = document.getElementById('previewDesc');
      const previewPrice = document.getElementById('previewPrice');
      const previewCategory = document.getElementById('previewCategory');
      const previewSubcategory = document.getElementById('previewSubcategory');
      const previewSizes = document.getElementById('previewSizes');

      // Load subcategories based on selected category
      function updSubcats() {
        sub.innerHTML = '<option value="">Select Subcategory</option>';
        (subcats[cat.value] || []).forEach(o => {
          let opt = document.createElement('option');
          opt.value = o.id; 
          opt.textContent = o.name;
          sub.appendChild(opt);
        });

        // Set selected subcategory if matches
        const selectedSubcat = <?= json_encode($product['subcategory']) ?>;
        if (selectedSubcat) {
          sub.value = selectedSubcat;
        }
        updatePreview();
      }

      cat.addEventListener('change', () => {
        updSubcats();
        updatePreview();
      });
      sub.addEventListener('change', updatePreview);

      function updatePreview() {
        previewName.textContent = nameInput.value || 'Product Name';
        previewDesc.textContent = descInput.value || 'No description yet.';
        previewPrice.textContent = priceInput.value ? `Price: $${parseFloat(priceInput.value).toFixed(2)}` : 'Price: $0.00';

        previewCategory.textContent = cat.value ? `Category: ${cat.options[cat.selectedIndex]?.text || '-'}` : 'Category: -';
        previewSubcategory.textContent = sub.value ? `Subcategory: ${sub.options[sub.selectedIndex]?.text || '-'}` : 'Subcategory: -';

        // Image preview
        const file = imageInput.files[0];
        if (file) {
          previewImg.src = URL.createObjectURL(file);
        } else {
          previewImg.src = '<?= htmlspecialchars("../uploads/".$product['image']) ?>';
          if (!previewImg.src) {
            previewImg.src = 'https://via.placeholder.com/300x300?text=No+Image';
          }
        }

        // Stock preview badges
        previewSizes.innerHTML = '';
        sizes.forEach(sz => {
          const stockInput = document.querySelector(`input[name="stock[${sz}]"]`);
          const val = stockInput ? parseInt(stockInput.value) : 0;
          if (val > 0) {
            const badge = document.createElement('span');
            badge.className = 'size-badge';
            badge.textContent = `${sz}: ${val}`;
            previewSizes.appendChild(badge);
          }
        });
      }

      nameInput.addEventListener('input', updatePreview);
      descInput.addEventListener('input', updatePreview);
      priceInput.addEventListener('input', updatePreview);
      imageInput.addEventListener('change', updatePreview);

      sizes.forEach(sz => {
        const stockInput = document.querySelector(`input[name="stock[${sz}]"]`);
        if (stockInput) stockInput.addEventListener('input', updatePreview);
      });

      // Initial setup
      cat.value = '<?= $product['category'] ?>';
      updSubcats();
      updatePreview();
    });
  </script>
</head>
<body>
  <div class="container d-flex flex-wrap justify-content-center gap-4 align-items-start">
    <div class="card card-sharp p-4" style="max-width: 600px; flex: 1 1 600px;">
      <h2>Edit Product</h2>
      <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form method="post" enctype="multipart/form-data" novalidate>
        <label class="form-label mt-3">Name</label>
        <input name="name" type="text" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required class="form-control">

        <label class="form-label mt-3">Description</label>
        <textarea name="description" required class="form-control"><?= htmlspecialchars($_POST['description'] ?? $product['description']) ?></textarea>

        <label class="form-label mt-3">Price</label>
        <input name="price" type="number" step="0.01" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>" required class="form-control">

        <label class="form-label mt-3">Product Image</label>
        <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.gif" class="form-control">
        <div class="form-text">Current image: <?= htmlspecialchars($product['image']) ?></div>

        <label class="form-label mt-3">Sizes & Stock</label>
        <?php foreach($sizes as $sz): ?>
          <div class="stock-row">
            <label for="stock-<?= $sz ?>"><?= $sz ?></label>
            <input id="stock-<?= $sz ?>" name="stock[<?= $sz ?>]" type="number" min="0" value="<?= htmlspecialchars($_POST['stock'][$sz] ?? ($productSizes[$sz] ?? 0)) ?>" class="form-control">
          </div>
        <?php endforeach; ?>

        <label class="form-label mt-3">Category</label>
        <select name="category_id" id="category_id" required class="form-select">
          <option value="">Select Category</option>
          <?php foreach($categories as $id => $n): ?>
            <option value="<?= $id ?>"<?= ((isset($_POST['category_id']) && $_POST['category_id']==$id) || (!isset($_POST['category_id']) && $product['category']==$id))?' selected':''?>><?= htmlspecialchars($n) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label mt-3">Subcategory</label>
        <select name="subcategory_id" id="subcategory_id" required class="form-select">
          <option value="">Select Subcategory</option>
          <!-- options added by JS -->
        </select>

        <div class="actions mt-4 d-flex justify-content-between">
          <button type="submit" class="btn btn-dark">Update Product</button>
          <a href="index.php" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>

    <div id="livePreview" class="card card-sharp" aria-label="Live product preview">
      <img src="<?= htmlspecialchars("../uploads/".$product['image']) ?>" alt="Product Image Preview" id="previewImg" class="card-img-top mb-3 bg-light" />
      <div class="card-body px-3">
        <h5 id="previewName" class="card-title">Product Name</h5>
        <p id="previewDesc" class="card-text text-muted small">No description yet.</p>
        <p id="previewPrice" class="fw-bold">Price: $0.00</p>
        <p id="previewCategory" class="mb-1"><strong>Category:</strong> -</p>
        <p id="previewSubcategory" class="mb-3"><strong>Subcategory:</strong> -</p>
        <div id="previewSizes" class="d-flex flex-wrap gap-2"></div>
      </div>
    </div>
  </div>
</body>
</html>
