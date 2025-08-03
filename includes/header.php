<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

// Fetch categories
$categories = [];
$subcategories = [];

$catQuery = $conn->query("SELECT * FROM categories ORDER BY name");
while ($row = $catQuery->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
}

$subQuery = $conn->query("SELECT * FROM subcategories ORDER BY name");
while ($row = $subQuery->fetch_assoc()) {
    $subcategories[$row['category_id']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MyShop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
      font-weight: 600;
      color: #000;
      text-transform: uppercase;
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
      transition: width .3s;
      position: absolute;
      bottom: 0;
      left: 0;
    }

    .nav-link:hover::after {
      width: 100%;
    }

    .nav-item.dropdown:hover .dropdown-menu {
      display: block;
      margin-top: 0;
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
    }

    @media (max-width: 991.98px) {
      .search-bar input {
        width: 100%;
      }

      .dropdown-menu {
        position: static !important;
        float: none;
        width: 100%;
        box-shadow: none;
        border: none;
        padding: 0.5rem;
      }

      .welcome-message {
        margin: 0.5rem 0;
      }
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Shop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="mainNav">
      <ul class="navbar-nav me-auto">
        <?php foreach ($categories as $catId => $catName): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="products.php?category=<?= $catId ?>" role="button">
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
          <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <li class="nav-item">
              <span class="welcome-message">Welcome Admin/<?= htmlspecialchars($_SESSION['is_admin']) ?></span>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                <li><a class="dropdown-item" href="account.php">Account</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="account.php">Account</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
              </ul>
            </li>
          <?php endif; ?>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa-solid fa-user"></i></a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>