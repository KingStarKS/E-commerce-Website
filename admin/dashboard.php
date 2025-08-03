<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Total Sales
$salesResult = mysqli_query($conn, "SELECT SUM(total) AS total_sales FROM orders");
$sales = $salesResult ? mysqli_fetch_assoc($salesResult)['total_sales'] ?? 0 : 0;

// Total Orders
$ordersResult = mysqli_query($conn, "SELECT COUNT(*) AS total_orders FROM orders");
$totalOrders = $ordersResult ? mysqli_fetch_assoc($ordersResult)['total_orders'] : 0;

// Total Users (non-admin)
$usersResult = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users WHERE is_admin = 0");
$totalUsers = $usersResult ? mysqli_fetch_assoc($usersResult)['total_users'] : 0;

// New Users This Month
$currentMonth = date('Y-m');
$newUsersResult = mysqli_query($conn, "SELECT COUNT(*) AS new_users FROM users WHERE is_admin = 0 AND created_at LIKE '$currentMonth%'");
$newUsers = $newUsersResult ? mysqli_fetch_assoc($newUsersResult)['new_users'] : 0;

// Top Products by Sales
$topProductsQuery = "
    SELECT p.name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
";
$topProducts = mysqli_query($conn, $topProductsQuery);

// Stock Summary (total products and stock)
$stockResult = mysqli_query($conn, "
    SELECT COUNT(DISTINCT p.id) AS total_products, SUM(ps.stock) AS total_stock
    FROM products p
    JOIN product_sizes ps ON p.id = ps.product_id
");
$stockData = $stockResult ? mysqli_fetch_assoc($stockResult) : ['total_products' => 0, 'total_stock' => 0];

// Low Stock Products (stock ≤ 5)
$lowStockProducts = mysqli_query($conn, "
    SELECT p.name, SUM(ps.stock) AS stock_sum
    FROM products p
    JOIN product_sizes ps ON p.id = ps.product_id
    GROUP BY p.id
    HAVING stock_sum <= 5
");

// All Products with Stock by Size
$allStockBySizeQuery = "
    SELECT p.name, ps.size, ps.stock 
    FROM products p
    JOIN product_sizes ps ON p.id = ps.product_id
    ORDER BY p.name, FIELD(ps.size, 'S', 'M', 'L', 'XL', 'XXL')
";
$allStockProducts = mysqli_query($conn, $allStockBySizeQuery);

// Yearly Revenue Data for current year (Jan-Dec)
$currentYear = date('Y');
$monthlyRevenueQuery = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, 
           IFNULL(SUM(total), 0) AS revenue
    FROM orders
    WHERE created_at LIKE '$currentYear-%'
    GROUP BY month
    ORDER BY month
";
$monthlyRevenueResult = mysqli_query($conn, $monthlyRevenueQuery);

// Initialize all months with 0 revenue
$monthlyLabels = [];
$monthlyData = [];
for ($m = 1; $m <= 12; $m++) {
    $monthStr = sprintf("%04d-%02d", $currentYear, $m);
    $monthlyLabels[] = date('M', strtotime($monthStr . '-01')); // Jan, Feb, ...
    $monthlyData[$monthStr] = 0;
}

if ($monthlyRevenueResult) {
    while ($row = mysqli_fetch_assoc($monthlyRevenueResult)) {
        $monthlyData[$row['month']] = (float)$row['revenue'];
    }
}

// Prepare data array for Chart.js
$monthlyRevenueValues = [];
foreach ($monthlyLabels as $i => $label) {
    $monthKey = sprintf("%04d-%02d", $currentYear, $i + 1);
    $monthlyRevenueValues[] = $monthlyData[$monthKey] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - Sharp Bento Style</title>
  <style>
    /* Reset & base */
    *, *::before, *::after {
      box-sizing: border-box;
    }
    body {
      margin: 0; padding: 20px; background: #fafafa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #222;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    h1 {
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 40px;
      color: #111;
      user-select: none;
      text-align: center;
      width: 100%;
      max-width: 1200px;
    }

    /* Bento Container Grid */
    .bento-container {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      grid-auto-rows: 150px;
      gap: 20px;
      max-width: 1200px;
      width: 100%;
    }

    /* Bento Box Base */
    .bento-box {
      background: #fff;
      border-radius: 6px;
      border: 1.5px solid #ccc;
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      color: #111;
      transition: border-color 0.2s ease, transform 0.15s ease;
      box-shadow: none;
      cursor: default;
      overflow: hidden;
    }
    .bento-box:hover {
      border-color: #2563eb;
      transform: scale(1.03);
      box-shadow: 0 0 12px rgba(37, 99, 235, 0.3);
      cursor: pointer;
    }

    /* Headings */
    .bento-box h2 {
      margin: 0 0 12px;
      font-weight: 700;
      font-size: 1.15rem;
      color: #444;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      user-select: none;
    }

    /* Large values */
    .bento-box p.value {
      font-size: 2.5rem;
      font-weight: 800;
      margin: 0;
      color: #222;
      user-select: none;
      line-height: 1;
    }

    /* Lists inside boxes */
    .bento-box ul {
      list-style: none;
      padding: 0;
      margin: 0;
      max-height: 100px;
      overflow-y: auto;
    }
    .bento-box ul li {
      padding: 6px 0;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      font-weight: 600;
      font-size: 1rem;
      color: #222;
      user-select: none;
    }
    .bento-box ul li:last-child {
      border-bottom: none;
    }

    /* Badges */
    .badge {
      background: #2563eb;
      color: white;
      border-radius: 9999px;
      padding: 4px 14px;
      font-weight: 700;
      font-size: 0.85rem;
      user-select: none;
      box-shadow: none;
      transition: background-color 0.2s ease;
      white-space: nowrap;
    }
    .badge-danger {
      background: #dc2626;
    }

    /* Scrollbar styling (WebKit & Firefox) */
    .bento-box ul::-webkit-scrollbar,
    .bento-box table::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .bento-box ul::-webkit-scrollbar-thumb,
    .bento-box table::-webkit-scrollbar-thumb {
      background-color: rgba(37, 99, 235, 0.8);
      border-radius: 3px;
    }
    /* Firefox */
    .bento-box ul {
      scrollbar-width: thin;
      scrollbar-color: rgba(37, 99, 235, 0.8) transparent;
    }

    /* Tables */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
      user-select: none;
      overflow-x: auto;
      max-height: 100px;
      display: block;
    }
    thead tr {
      background: #f0f4ff;
      display: table;
      width: 100%;
      table-layout: fixed;
    }
    tbody {
      display: block;
      overflow-y: auto;
      max-height: 90px;
      width: 100%;
    }
    tr {
      display: table;
      width: 100%;
      table-layout: fixed;
    }
    th, td {
      padding: 8px 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
      color: #222;
      word-wrap: break-word;
    }
    tr:hover {
      background: #e0e7ff;
    }

    /* Bento grid areas and spans */
    .sales {
      grid-column: span 3;
      grid-row: span 1;
    }
    .orders {
      grid-column: span 1;
      grid-row: span 1;
    }
    .users {
      grid-column: span 1;
      grid-row: span 1;
    }
    .new-users {
      grid-column: span 1;
      grid-row: span 1;
    }
    .top-products {
      grid-column: span 3;
      grid-row: span 1;
    }
    .stock-summary {
      grid-column: span 3;
      grid-row: span 1;
    }
    .low-stock {
      grid-column: span 2;
      grid-row: span 1;
    }
    .stock-by-size {
      grid-column: span 4;
      grid-row: span 1;
    }
    .yearly-revenue {
      grid-column: span 6;
      grid-row: span 2; /* double height */
      padding-bottom: 0;
      display: flex;
      flex-direction: column;
    }
    .yearly-revenue-chart {
      flex-grow: 1;
      height: 300px; /* bigger height for better chart view */
      width: 100%;
    }

    /* Responsive */
    @media (max-width: 900px) {
      .bento-container {
        grid-template-columns: repeat(2, 1fr);
        grid-auto-rows: 120px;
      }
      .sales { grid-column: span 2; }
      .top-products, .stock-by-size, .low-stock { grid-column: span 2; }
      .orders, .users, .new-users, .stock-summary { grid-column: span 1; }
      .yearly-revenue { grid-column: span 2; }
    }
    @media (max-width: 500px) {
      .bento-container {
        grid-template-columns: 1fr;
        grid-auto-rows: auto;
      }
      .bento-box {
        height: auto !important;
      }
      .sales, .orders, .users, .new-users, .top-products, .stock-summary, .low-stock, .stock-by-size, .yearly-revenue {
        grid-column: span 1 !important;
        grid-row: auto !important;
      }
      table {
        display: block !important;
        max-height: none !important;
      }
      tbody {
        display: block !important;
        max-height: none !important;
      }
      .yearly-revenue-chart {
        height: 220px !important;
      }
    }

    /* Back button */
    a.btn-back {
      display: inline-block;
      background: #1e40af;
      color: white;
      padding: 14px 28px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 700;
      transition: background 0.3s ease;
      margin: 40px auto 0 auto;
      text-align: center;
      user-select: none;
      max-width: 240px;
      width: 100%;
    }
    a.btn-back:hover {
      background: #1e3a8a;
    }
  </style>
</head>
<body>

  <h1>Admin Dashboard</h1>

  <div class="bento-container">

    <div class="bento-box sales">
      <h2>Total Sales</h2>
      <p class="value">$<?= number_format($sales, 2) ?></p>
    </div>

    <div class="bento-box orders">
      <h2>Total Orders</h2>
      <p class="value"><?= $totalOrders ?></p>
    </div>

    <div class="bento-box users">
      <h2>Total Users</h2>
      <p class="value"><?= $totalUsers ?></p>
    </div>

    <div class="bento-box new-users">
      <h2>New Users (<?= date('F') ?>)</h2>
      <p class="value"><?= $newUsers ?></p>
    </div>

    <div class="bento-box top-products">
      <h2>Top Selling Products</h2>
      <ul>
        <?php if (!$topProducts || mysqli_num_rows($topProducts) === 0): ?>
          <li>No products found.</li>
        <?php else: ?>
          <?php while ($row = mysqli_fetch_assoc($topProducts)): ?>
            <li>
              <?= htmlspecialchars($row['name']) ?>
              <span class="badge">Sold: <?= $row['total_sold'] ?></span>
            </li>
          <?php endwhile; ?>
        <?php endif; ?>
      </ul>
    </div>

    <div class="bento-box stock-summary">
      <h2>Stock Summary</h2>
      <p><strong>Total Products:</strong> <?= $stockData['total_products'] ?></p>
      <p><strong>Total Stock:</strong> <?= $stockData['total_stock'] ?></p>
    </div>

    <div class="bento-box low-stock">
      <h2>Low Stock Alerts</h2>
      <ul>
        <?php if (!$lowStockProducts || mysqli_num_rows($lowStockProducts) === 0): ?>
          <li>No low stock products.</li>
        <?php else: ?>
          <?php while ($low = mysqli_fetch_assoc($lowStockProducts)): ?>
            <li style="color:#dc2626; font-weight:700;">
              <?= htmlspecialchars($low['name']) ?>
              <span class="badge badge-danger">Stock: <?= intval($low['stock_sum']) ?></span>
            </li>
          <?php endwhile; ?>
        <?php endif; ?>
      </ul>
    </div>

    <div class="bento-box stock-by-size">
      <h2>All Products with Stock by Size</h2>
      <table>
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Size</th>
            <th>Stock</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$allStockProducts || mysqli_num_rows($allStockProducts) === 0): ?>
            <tr><td colspan="3" style="text-align:center; padding:12px;">No stock information available.</td></tr>
          <?php else: ?>
            <?php while ($prod = mysqli_fetch_assoc($allStockProducts)): ?>
              <tr>
                <td><?= htmlspecialchars($prod['name']) ?></td>
                <td><?= htmlspecialchars($prod['size']) ?></td>
                <td><?= intval($prod['stock']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="bento-box yearly-revenue">
      <h2>Yearly Revenue (<?= $currentYear ?>)</h2>
      <canvas id="yearlyRevenueChart" class="yearly-revenue-chart"></canvas>
    </div>

  </div>

  <a href="index.php" class="btn-back">Back to Admin Panel</a>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('yearlyRevenueChart').getContext('2d');
    const yearlyRevenueChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= json_encode($monthlyLabels) ?>,
        datasets: [{
          label: 'Revenue ($)',
          data: <?= json_encode($monthlyRevenueValues) ?>,
          fill: true,
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37, 99, 235, 0.2)',
          tension: 0.25,
          pointRadius: 5,
          pointHoverRadius: 7,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '$' + value.toLocaleString();
              }
            }
          }
        },
        plugins: {
          legend: {
            labels: {
              font: {
                size: 14,
                weight: 'bold'
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return '$' + context.parsed.y.toLocaleString();
              }
            }
          }
        }
      }
    });
  </script>

</body>
</html>
