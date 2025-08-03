<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Segoe UI', sans-serif;
      overflow: hidden;
      background: #f4f4f4;
    }

    /* Sidebar */
    #sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 250px;
      background-color: #222;
      color: white;
      padding-top: 60px;
      transition: transform 0.3s ease;
      z-index: 1000;
    }

    #sidebar.hidden {
      transform: translateX(-100%);
    }

    #sidebar a {
      display: block;
      color: #ddd;
      padding: 15px 25px;
      text-decoration: none;
      border-left: 4px solid transparent;
      transition: background 0.2s, border-left-color 0.2s;
    }

    #sidebar a:hover {
      background-color: #444;
      border-left-color: #0d6efd;
      color: white;
    }

    #sidebar a.active {
      background-color: #0d6efd;
      color: white;
    }

    /* Content */
    #content {
      margin-left: 250px;
      height: 100vh;
      transition: margin-left 0.3s ease;
    }

    #sidebar.hidden + #content {
      margin-left: 0;
    }

    #content iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    /* Hamburger menu */
    .hamburger {
      position: fixed;
      top: 15px;
      left: 20px;
      width: 30px;
      height: 22px;
      cursor: pointer;
      z-index: 1100;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .hamburger span {
      height: 4px;
      width: 100%;
      background-color: #0d6efd;
      border-radius: 2px;
      transition: 0.4s;
    }

    .hamburger.open span:nth-child(1) {
      transform: rotate(45deg) translate(5px, 5px);
    }

    .hamburger.open span:nth-child(2) {
      opacity: 0;
    }

    .hamburger.open span:nth-child(3) {
      transform: rotate(-45deg) translate(6px, -6px);
    }

    @media screen and (max-width: 768px) {
      #sidebar {
        width: 200px;
      }
      #content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<!-- Hamburger -->
<div class="hamburger" id="hamburger">
  <span></span>
  <span></span>
  <span></span>
</div>

<!-- Sidebar -->
<div id="sidebar">
  <a href="dashboard.php" target="contentFrame" class="active">Dashboard</a>
  <a href="add-product.php" target="contentFrame">Add Product</a>
  <a href="manage-products.php" target="contentFrame">Manage Products</a>
  <a href="manage-orders.php" target="contentFrame">Manage Orders</a>
  <a href="manage-users.php" target="contentFrame">Manage Users</a>
</div>

<!-- Main content -->
<div id="content">
  <iframe src="dashboard.php" name="contentFrame"></iframe>
</div>

<!-- Scripts -->
<script>
  const sidebar = document.getElementById('sidebar');
  const content = document.getElementById('content');
  const hamburger = document.getElementById('hamburger');
  const links = sidebar.querySelectorAll('a');

  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    hamburger.classList.toggle('open');
  });

  // Highlight active link
  links.forEach(link => {
    link.addEventListener('click', () => {
      links.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    });
  });
</script>

</body>
</html>
