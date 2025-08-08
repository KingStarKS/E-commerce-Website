<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

// Fetch categories & subcategories only if NOT admin (no need to query for admin)
$categories = [];
$subcategories = [];

if (empty($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $catQuery = $conn->query("SELECT * FROM categories ORDER BY name");
    while ($row = $catQuery->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }

    $subQuery = $conn->query("SELECT * FROM subcategories ORDER BY name");
    while ($row = $subQuery->fetch_assoc()) {
        $subcategories[$row['category_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>MyShop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      font-family: 'Helvetica Neue', sans-serif;
    }
    .navbar {
      background-color: #fff;
      border-bottom: 1px solid #ddd;
      padding: 1rem 2rem;
    }
    .navbar-brand {
      font-size: 1.8rem;
      font-weight: 700;
      color: #000;
      text-transform: none;
      letter-spacing: -0.5px;
      font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    .nav-link {
      color: #000 !important;
      margin: 0 1rem;
      font-weight: 500;
      position: relative;
      text-transform: uppercase;
    }
    .nav-link::after {
      content: "";
      display: block;
      width: 0;
      height: 1px;
      background: #000;
      transition: width 0.3s;
      position: absolute;
      bottom: 0;
      left: 0;
    }
    .nav-link:hover::after {
      width: 100%;
    }
    /* Changed from hover to click - removed hover rule and replaced with click */
    .nav-item.dropdown .dropdown-menu {
      display: none;
      margin-top: 0;
    }
    .nav-item.dropdown.show .dropdown-menu {
      display: block;
    }
    .dropdown-menu {
      border-radius: 0;
      border: 1px solid #eee;
      min-width: 200px;
      padding: 1rem;
    }
    .dropdown-menu a {
      color: #000;
      font-weight: 400;
      text-transform: capitalize;
    }
    .dropdown-menu a:hover {
      background-color: transparent;
      text-decoration: underline;
    }
    .search-bar input {
      border-radius: 30px;
      border: 1px solid #ccc;
      padding: 0.5rem 1rem;
      width: 250px;
      transition: border 0.3s ease;
    }
    .search-bar input:focus {
      outline: none;
      border-color: #000;
    }
    .welcome-message {
      color: #000;
      font-weight: 500;
      margin-right: 1rem;
      text-align: center;
      width: 100%;
    }
    /* Simple, clean mobile design */
    @media (max-width: 991.98px) {
      .navbar {
        padding: 0.75rem 1rem;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      }
      
      .navbar-brand {
        font-size: 1.5rem;
      }
      
      .navbar-toggler {
        border: none;
        padding: 0.2rem;
        border-radius: 4px;
        background: none;
      }
      
      .navbar-toggler:focus {
        box-shadow: none;
      }
      
      .navbar-collapse {
        background: white;
        margin-top: 0.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        padding: 1rem;
      }
      
      /* Clean category links - left aligned */
      .nav-link {
        margin: 0;
        padding: 0.75rem 0;
        font-size: 0.95rem;
        font-weight: 500;
        text-transform: none;
        border-bottom: 1px solid #f0f0f0;
        text-align: left;
      }
      
      .nav-link:last-child {
        border-bottom: none;
      }
      
      .nav-link::after {
        display: none;
      }
      
      /* Minimal dropdown styling */
      .dropdown-menu {
        position: static !important;
        float: none;
        width: 100%;
        box-shadow: none;
        border: none;
        padding: 0;
        background: #f8f9fa;
        margin: 0;
        border-radius: 6px;
        margin-top: 0.5rem;
      }
      
      .dropdown-item {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        color: #666;
        border: none;
      }
      
      .dropdown-item:hover {
        background: #e9ecef;
        text-decoration: none;
      }
      
      /* Simple search area - left aligned */
      form.d-inline {
        width: 100%;
        margin: 1rem 0;
        text-align: left;
      }
      
      form.d-inline button {
        width: 100%;
        padding: 0.75rem 0;
        background: none;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        text-align: left;
        font-weight: 500;
        font-size: 0.95rem;
        color: #000;
        text-transform: none;
      }
      
      form.d-inline button:hover {
        background: #f8f9fa;
      }
      
      form.d-inline button:before {
        content: "Search";
        margin-left: 0;
      }
      
      form.d-inline button svg {
        display: none;
      }
      
      /* User actions - left aligned list style */
      .navbar-nav.ms-3 {
        margin: 0 !important;
        flex-direction: column;
        padding: 0;
      }
      
      .navbar-nav.ms-3 .nav-item {
        width: 100%;
      }
      
      .navbar-nav.ms-3 .nav-link {
        border: none;
        padding: 0.75rem 0;
        text-align: left;
        background: none;
        border-radius: 0;
        margin: 0;
        width: 100%;
        font-size: 0.95rem;
        font-weight: 500;
        border-bottom: 1px solid #f0f0f0;
        display: block;
      }
      
      .navbar-nav.ms-3 .nav-link:hover {
        background: #f8f9fa;
      }
      
      .navbar-nav.ms-3 .nav-link:last-child {
        border-bottom: none;
      }
      
      /* Add text after icons for mobile */
      .navbar-nav.ms-3 .nav-link[href="cart.php"]:before {
        content: "Cart";
      }
      
      .navbar-nav.ms-3 .nav-link[href="login.php"]:before {
        content: "Login";
      }
      
      .navbar-nav.ms-3 .dropdown-toggle:before {
        content: "Profile";
      }
      
      /* Hide icons on mobile */
      .navbar-nav.ms-3 .nav-link i {
        display: none;
      }
      
      /* Admin mobile view - super clean */
      .welcome-message {
        font-size: 0.9rem;
        margin: 0.5rem 0;
        color: #666;
        text-align: center;
      }
      
      .navbar-nav.ms-auto {
        margin: 0.5rem 0 !important;
        align-self: center;
      }
      
      .navbar-nav.ms-auto .nav-link {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        border: none;
      }
      
      /* Admin dropdown text for mobile */
      .navbar-nav.ms-auto .dropdown-toggle:before {
        content: "Account";
      }
      
      .navbar-nav.ms-auto .dropdown-toggle i {
        display: none;
      }
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">

    <a class="navbar-brand" href="index.php">Shop</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
      <!-- ADMIN VIEW -->
      <div class="d-flex justify-content-between align-items-center w-100">
        <div class="welcome-message">Welcome Admin/<?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminAccountDropdown" onclick="toggleDropdown(event, this)">
              <i class="fa-solid fa-user"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminAccountDropdown">
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="account.php">Account</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>

    <?php else: ?>
      <!-- USER OR GUEST VIEW -->
      <div class="collapse navbar-collapse justify-content-between" id="mainNav">

        <ul class="navbar-nav me-auto">
          <?php foreach ($categories as $catId => $catName): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="products.php?category=<?= $catId ?>" role="button" onclick="toggleDropdown(event, this)">
                <?= htmlspecialchars($catName) ?>
              </a>
              <?php if (!empty($subcategories[$catId])): ?>
                <ul class="dropdown-menu">
                  <?php foreach ($subcategories[$catId] as $sub): ?>
                    <li><a class="dropdown-item" href="products.php?category=<?= $catId ?>&subcategory=<?= $sub['id'] ?>">
                      <?= htmlspecialchars($sub['name']) ?>
                    </a></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <form action="search.php" method="GET" class="d-inline">
          <button type="submit" style="background:none;border:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
              class="bi bi-search" viewBox="0 0 16 16">
              <path
                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0
                1.415-1.414l-3.85-3.85zm-5.242 1.106a5.5 5.5 0 1 1
                0-11 5.5 5.5 0 0 1 0 11z" />
            </svg>
          </button>
        </form>

        <ul class="navbar-nav ms-3 align-items-center">
          <li class="nav-item"><a class="nav-link" href="cart.php"><i class="fa-solid fa-bag-shopping"></i></a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="userAccountDropdown" onclick="toggleDropdown(event, this)">
                <i class="fa-solid fa-user"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAccountDropdown">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="account.php">Account</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa-solid fa-user"></i></a></li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>

  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleDropdown(event, element) {
    // Check if the category has subcategories or if it's an account dropdown
    const parentLi = element.closest('li.dropdown');
    const dropdownMenu = parentLi.querySelector('.dropdown-menu');
    
    if (dropdownMenu) {
        // Prevent navigation for dropdowns (categories with subcategories or account dropdowns)
        event.preventDefault();
        
        // Close all other dropdowns
        document.querySelectorAll('.nav-item.dropdown.show').forEach(function(dropdown) {
            if (dropdown !== parentLi) {
                dropdown.classList.remove('show');
            }
        });
        
        // Toggle current dropdown
        parentLi.classList.toggle('show');
    }
    // If no dropdown menu, let the link work normally (navigate to category page)
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.nav-item.dropdown')) {
        document.querySelectorAll('.nav-item.dropdown.show').forEach(function(dropdown) {
            dropdown.classList.remove('show');
        });
    }
});
</script>
</body>
</html>