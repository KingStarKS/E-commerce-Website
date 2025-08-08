<?php include 'includes/db.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- =========================
     FULLSCREEN HERO SLIDER
     ========================= -->
<div id="heroCarousel" 
     class="carousel slide mb-5" 
     data-bs-ride="carousel"       
     data-bs-wrap="true"         
     data-bs-interval="5000">     
  <div class="carousel-inner">

    <!-- Slide 1 -->
    <div class="carousel-item active position-relative">
      <img src="assets/images/model4.jpg"
           class="d-block w-100" alt="Model Fashion 1" style="height: 100vh; object-fit: cover;">
      <div class="carousel-caption d-flex flex-column justify-content-center text-start" 
           style="top: 50%; left: 5%; transform: translateY(-50%); max-width: 600px;">
        <h1 style="font-size: 4rem; font-weight: 700; color: white; 
                   text-shadow: 2px 2px 6px rgba(0,0,0,0.7); margin-bottom: 0.5rem;">
          Elevate Your Style
        </h1>
        <p style="font-size: 1.5rem; color: white; 
                  text-shadow: 1px 1px 4px rgba(0,0,0,0.7); margin-bottom: 1.5rem;">
          Discover the latest trends and collections now.
        </p>
        <a href="allProducts.php" 
           class="btn btn-lg" 
           style="background: transparent; border: 2px solid white; color: white; border-radius: 0; 
                  padding: 0.5rem 1.2rem; width: fit-content; transition: background-color 0.3s, color 0.3s;">
          Shop Now
        </a>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="carousel-item position-relative">
      <img src="assets/images/model3.jpg"
           class="d-block w-100" alt="Model Fashion 2" style="height: 100vh; object-fit: cover;">
      <div class="carousel-caption" 
           style="position: absolute; bottom: 5%; right: 5%; left: auto; top: auto; max-width: 600px; text-align: right;">
        <h1 style="font-size: 4rem; font-weight: 700; color: white; 
                   text-shadow: 2px 2px 6px rgba(0,0,0,0.7); margin-bottom: 0.5rem;">
          Explore New Horizons
        </h1>
        <p style="font-size: 1.5rem; color: white; 
                  text-shadow: 1px 1px 4px rgba(0,0,0,0.7); margin-bottom: 1.5rem;">
          Dive into exclusive collections crafted just for you.
        </p>
        <a href="allProducts.php" 
           class="btn btn-lg" 
           style="background: transparent; border: 2px solid white; color: white; border-radius: 0; 
                  padding: 0.5rem 1.2rem; width: fit-content; transition: background-color 0.3s, color 0.3s;">
          Explore
        </a>
      </div>
    </div>

      <!-- Slide 3 -->
<div class="carousel-item position-relative">
  <img src="assets/images/model2.jpg"
       class="d-block w-100" alt="Model Fashion 3" style="height: 100vh; object-fit: cover;">
  <div class="carousel-caption d-flex flex-column justify-content-center align-items-center text-center" style="top: 50%; transform: translateY(-50%);">
    <h1 style="font-size: 4rem; font-weight: 700; color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.7); margin-bottom: 0.5rem;">
      Specials Just For You
    </h1>
    <p style="font-size: 1.5rem; color: white; text-shadow: 1px 1px 4px rgba(0,0,0,0.7); margin-bottom: 1.5rem;">
      Grab amazing deals on our exclusive specials.
    </p>
    <a href="allProducts.php" 
       class="btn btn-lg" 
       style="background: transparent; border: 2px solid white; color: white; border-radius: 0; 
              padding: 0.5rem 1.5rem; width: fit-content; transition: background-color 0.3s, color 0.3s;">
      Specials
    </a>
  </div>
</div>


  </div>

  <!-- Slider Controls (prev/next buttons) -->
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

<!-- =========================
     FEATURED PRODUCTS
     ========================= -->
<div class="container">
  <h2 class="mb-4 text-center">Featured Products</h2>
  
  <!-- Desktop: Regular Grid (4 per row) -->
  <div class="d-none d-lg-block">
    <div class="row">
      <?php
      $result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
      if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
      ?>
        <div class="col-md-3 mb-4">
          <div class="card h-100">
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?= htmlspecialchars($row['name']) ?>">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
              <p class="card-text">$<?= number_format($row['price'], 2) ?></p>
              <a href="product-details.php?id=<?= $row['id'] ?>" class="btn btn-outline-dark mt-auto">View Details</a>
            </div>
          </div>
        </div>
      <?php endwhile; else: ?>
        <p>No featured products found.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tablet: Carousel 2 products per slide -->
  <div class="d-none d-md-block d-lg-none">
    <div id="featuredProductsCarouselTablet" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");
        $products = [];
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $products[] = $row;
          }
        }

        // Tablet: 2 products per slide
        $chunks = array_chunk($products, 2);
        $isFirst = true;

        foreach ($chunks as $chunk):
        ?>
          <div class="carousel-item <?= $isFirst ? 'active' : '' ?>">
            <div class="row">
              <?php foreach ($chunk as $product): ?>
                <div class="col-6 mb-4">
                  <div class="card h-100">
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body d-flex flex-column">
                      <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                      <p class="card-text small">$<?= number_format($product['price'], 2) ?></p>
                      <a href="product-details.php?id=<?= $product['id'] ?>" class="btn btn-outline-dark btn-sm mt-auto">View Details</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php 
        $isFirst = false;
        endforeach;
        ?>
      </div>

      <!-- Carousel Indicators -->
      <div class="carousel-indicators" style="margin-bottom: -20px;">
        <?php for ($i = 0; $i < count($chunks); $i++): ?>
          <button type="button" data-bs-target="#featuredProductsCarouselTablet" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?> style="background-color: #666;"></button>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- Mobile: Carousel 1 product per slide -->
  <div class="d-md-none">
    <div id="featuredProductsCarouselMobile" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php
        // Re-use same $products array
        $chunksMobile = array_chunk($products, 1);
        $isFirstMobile = true;

        foreach ($chunksMobile as $chunk):
        ?>
          <div class="carousel-item <?= $isFirstMobile ? 'active' : '' ?>">
            <div class="row">
              <?php foreach ($chunk as $product): ?>
                <div class="col-12 mb-4">
                  <div class="card h-100">
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body d-flex flex-column">
                      <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                      <p class="card-text small">$<?= number_format($product['price'], 2) ?></p>
                      <a href="product-details.php?id=<?= $product['id'] ?>" class="btn btn-outline-dark btn-sm mt-auto">View Details</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php 
        $isFirstMobile = false;
        endforeach;
        ?>
      </div>

      <!-- Carousel Indicators -->
      <div class="carousel-indicators" style="margin-bottom: -20px;">
        <?php for ($i = 0; $i < count($chunksMobile); $i++): ?>
          <button type="button" data-bs-target="#featuredProductsCarouselMobile" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?> style="background-color: #666;"></button>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <div class="text-center my-5">
    <a href="allProducts.php" class="view-all-link">View All Products</a>
  </div>
</div> <!-- container ends here -->

<!-- =========================
     VIDEO SECTION WITH OVERLAY
     ========================= -->
<div class="container my-5">
  <div class="position-relative ratio ratio-16x9" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
    
    <video autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover;">
      <source src="assets/videos/male.mp4" type="video/mp4">
      Your browser does not support the video tag.
    </video>
    
    <!-- Center Overlay -->
    <div class="video-overlay-center">
      Elevate Your Style<br>Discover Your Confidence
    </div>
  </div>
</div>

<style>
  .video-overlay-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-family: 'Brush Script MT', cursive, 'Copperplate', serif;
    font-size: 3rem;
    text-align: center;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
    pointer-events: none;
    user-select: none;
    white-space: pre-line; /* supports line breaks */
    animation: fadeInOverlay 2s ease forwards;
    opacity: 0;
  }

  @keyframes fadeInOverlay {
    to {
      opacity: 1;
    }
  }

  /* Responsive font size */
  @media (max-width: 768px) {
    .video-overlay-center {
      font-size: 2rem;
      padding: 0 10px;
    }
  }

  @media (max-width: 480px) {
    .video-overlay-center {
      font-size: 1.5rem;
    }
  }

  /* View All Products link */
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

  /* Featured Products Carousel Styling */
  #featuredProductsCarouselTablet,
  #featuredProductsCarouselMobile {
    position: relative;
    padding: 0 20px;
  }

  /* Mobile adjustments */
  @media (max-width: 576px) {
    #featuredProductsCarouselMobile {
      padding: 0 30px;
    }
    
    #featuredProductsCarouselMobile .card-img-top {
      height: 150px !important;
    }
    
    #featuredProductsCarouselMobile .card-title {
      font-size: 0.95rem;
    }
    
    #featuredProductsCarouselMobile .btn {
      font-size: 0.8rem;
      padding: 0.4rem 0.8rem;
    }
  }
</style>

<!-- Bootstrap JS (make sure this is included if not already) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/footer.php'; ?>
