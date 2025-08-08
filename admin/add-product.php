<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
  header('Location: ../login.php');
  exit;
}

$message = '';
$categories = [];
$subcategories = [];

// Fetch categories
$cat = mysqli_query($conn, "SELECT id,name FROM categories ORDER BY name");
while ($c = mysqli_fetch_assoc($cat)) {
  $categories[$c['id']] = $c['name'];
}

// Fetch subcategories
$sub = mysqli_query($conn, "SELECT id,category_id,name FROM subcategories ORDER BY name");
while ($s = mysqli_fetch_assoc($sub)) {
  $subcategories[$s['category_id']][] = ['id'=>$s['id'],'name'=>$s['name']];
}

$sizes = ["S","M","L","XL","XXL"];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = trim($_POST['name']);
  $desc = trim($_POST['description']);
  $price = floatval($_POST['price']);
  $category_id = intval($_POST['category_id']);
  $subcategory_id = intval($_POST['subcategory_id']);
  $stock_input = $_POST['stock'] ?? [];

  // handle image upload
  $image = '';
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
    if ($name && $desc && $price>0 && $image && $category_id && $subcategory_id) {
      $stmt = $conn->prepare("INSERT INTO products (name,description,price,image,category,subcategory,created_at) VALUES (?,?,?,?,?,?,NOW())");
      $stmt->bind_param("ssdssi",$name,$desc,$price,$image,$category_id,$subcategory_id);
      if ($stmt->execute()) {
        $pid = $stmt->insert_id;
        $size_stmt = $conn->prepare("INSERT INTO product_sizes (product_id,size,stock) VALUES (?,?,?)");
        foreach ($sizes as $sz) {
          $v = intval($stock_input[$sz] ?? 0);
          if ($v > 0) {
            $size_stmt->bind_param("isi",$pid,$sz,$v);
            $size_stmt->execute();
          }
        }
        $size_stmt->close();
        $message = "Product added successfully.";
      } else {
        $message = "DB error: ".$stmt->error;
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
  <title>Add Product</title>
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

    button,
    .btn-back {
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
       top: 100px;  /* distance from top of viewport */
       position: sticky;
        height: fit-content;
    }

    /* Size badges with sharp corners */
    #previewSizes .size-badge {
      background-color: #0d6efd; /* bootstrap primary */
      color: #fff;
      padding: 0.35em 0.75em;
      font-weight: 600;
      font-size: 0.8rem;
      border-radius: 0; /* sharp corners */
      user-select: none;
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

      function updSubcats() {
        sub.innerHTML = '<option value="">Select Subcategory</option>';
        (subcats[cat.value] || []).forEach(o => {
          let opt = document.createElement('option');
          opt.value = o.id; opt.textContent = o.name;
          sub.appendChild(opt);
        });
        updatePreview(); // Update preview to clear/change subcategory
      }
      cat.addEventListener('change', updSubcats);
      sub.addEventListener('change', updatePreview);

      function updatePreview() {
        previewName.textContent = nameInput.value || 'Product Name';
        previewDesc.textContent = descInput.value || 'No description yet.';
        previewPrice.textContent = priceInput.value ? `Price: $${parseFloat(priceInput.value).toFixed(2)}` : 'Price: $0.00';

        previewCategory.textContent = cat.value ? `Category: ${cat.options[cat.selectedIndex].text}` : 'Category: -';
        previewSubcategory.textContent = sub.value ? `Subcategory: ${sub.options[sub.selectedIndex].text}` : 'Subcategory: -';

        // Clear sizes preview
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
      imageInput.addEventListener('change', () => {
        const f = imageInput.files[0];
        if (f) {
          previewImg.src = URL.createObjectURL(f);
        } else {
          previewImg.src = '';
        }
      });

      // Stock inputs listener
      sizes.forEach(sz => {
        const stockInput = document.querySelector(`input[name="stock[${sz}]"]`);
        if (stockInput) stockInput.addEventListener('input', updatePreview);
      });

      // Initialize subcategories and preview
      updSubcats();
      updatePreview();
    });
  </script>
</head>
<body>
  <div class="container d-flex flex-wrap justify-content-center gap-4 align-items-start">
    <div class="card card-sharp p-4" style="max-width: 600px; flex: 1 1 600px;">
      <h2>Add Product</h2>
      <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form method="post" enctype="multipart/form-data" novalidate>
        <label class="form-label mt-3">Name</label>
        <input name="name" type="text" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required class="form-control">

        <label class="form-label mt-3">Description</label>
        <textarea name="description" required class="form-control"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        <label class="form-label mt-3">Price</label>
        <input name="price" type="number" step="0.01" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required class="form-control">

        <label class="form-label mt-3">Product Image</label>
        <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.gif" required class="form-control">

        <label class="form-label mt-3">Sizes & Stock</label>
        <?php foreach($sizes as $sz): ?>
          <div class="stock-row">
            <label for="stock-<?= $sz ?>"><?= $sz ?></label>
            <input id="stock-<?= $sz ?>" name="stock[<?= $sz ?>]" type="number" min="0" value="<?= htmlspecialchars($_POST['stock'][$sz] ?? 0) ?>" class="form-control">
          </div>
        <?php endforeach; ?>

        <label class="form-label mt-3">Category</label>
        <select name="category_id" id="category_id" required class="form-select">
          <option value="">Select Category</option>
          <?php foreach($categories as $id => $n): ?>
            <option value="<?= $id ?>"<?= isset($_POST['category_id']) && $_POST['category_id']==$id?' selected':''?>><?= htmlspecialchars($n) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label mt-3">Subcategory</label>
        <select name="subcategory_id" id="subcategory_id" required class="form-select">
          <option value="">Select Subcategory</option>
        </select>

        <div class="actions mt-4 d-flex justify-content-between">
          <button type="submit" class="btn btn-dark">Add Product</button>
          <a href="index.php" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>

    <!-- Live Preview Panel -->
    <div id="livePreview" class="card card-sharp p-3" aria-label="Live product preview">
      <img src="" alt="Product Image Preview" id="previewImg" class="card-img-top mb-3 bg-light">
      <div class="card-body px-0">
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