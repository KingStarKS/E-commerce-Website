<?php include 'includes/db.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- =========================
     FULLSCREEN HERO SLIDER
     ========================= -->
<div id="heroCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
  <div class="carousel-inner">

    <!-- Slide 1 -->
    <div class="carousel-item active">
      <!-- Unsplash model image -->
      <img src="https://images.unsplash.com/photo-1600185365483-26d7fe0c2971?auto=format&fit=crop&w=1600&q=80"
           class="d-block w-100" alt="Model Fashion 1" style="height: 100vh; object-fit: cover;">
    </div>

    <!-- Slide 2 -->
    <div class="carousel-item">
      <!-- Unsplash model image -->
      <img src="https://images.unsplash.com/photo-1612423284934-a45c667c6979?auto=format&fit=crop&w=1600&q=80"
           class="d-block w-100" alt="Model Fashion 2" style="height: 100vh; object-fit: cover;">
    </div>

    <!-- Slide 3 -->
    <div class="carousel-item">
      <!-- Unsplash model image -->
      <img src="https://images.unsplash.com/photo-1627308595229-7830a5c91f9f?auto=format&fit=crop&w=1600&q=80"
           class="d-block w-100" alt="Model Fashion 3" style="height: 100vh; object-fit: cover;">
    </div>

  </div>

  <!-- Slider Controls -->
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<!-- =========================
     FEATURED PRODUCTS
     ========================= -->
<div class="container">
  <h2 class="mb-4 text-center">Featured Products</h2>
  <div class="row">
    <?php
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
    if ($result->num_rows > 0):
      while ($row = $result->fetch_assoc()):
    ?>
      <div class="col-md-3 mb-4">
        <div class="card h-100">
          <img src="uploads/<?= $row['image'] ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?= $row['name'] ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= $row['name'] ?></h5>
            <p class="card-text">$<?= number_format($row['price'], 2) ?></p>
            <a href="product-details.php?id=<?= $row['id'] ?>" class="btn btn-outline-dark mt-auto">View Details</a>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <p>No featured products found.</p>
    <?php endif; ?>
  </div>

  <!-- =========================
       SECOND SLIDER / BANNER
       ========================= -->
  <div id="secondCarousel" class="carousel slide my-5" data-bs-ride="carousel">
    <div class="carousel-inner">

      <!-- Slide A -->
      <div class="carousel-item active">
        <!-- Unsplash fashion image -->
        <img src="https://images.unsplash.com/photo-1619020567590-9b86b26a72b6?auto=format&fit=crop&w=1600&q=80"
             class="d-block w-100" alt="Model Slide A" style="height: 400px; object-fit: cover;">
      </div>

      <!-- Slide B -->
      <div class="carousel-item">
        <!-- Unsplash fashion image -->
        <img src="https://images.unsplash.com/photo-1585386959984-a4155228e2c9?auto=format&fit=crop&w=1600&q=80"
             class="d-block w-100" alt="Model Slide B" style="height: 400px; object-fit: cover;">
      </div>

    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#secondCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#secondCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>

  <!-- =========================
       ZARA-STYLE VIEW ALL LINK
       ========================= -->
  <style>
    .view-all-link {
      display: inline-block;
      position: relative;
      font-weight: 500;
      font-size: 18px;
      color: #000;
      text-decoration: none;
      padding: 6px 0;
      transition: color 0.3s ease;
    }

    .view-all-link::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 0%;
      height: 1px;
      background-color: #000;
      transition: width 0.3s ease;
    }

    .view-all-link:hover::after {
      width: 100%;
    }
  </style>

  <div class="text-center my-5">
    <a href="allProducts.php" class="view-all-link">View All Products</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
