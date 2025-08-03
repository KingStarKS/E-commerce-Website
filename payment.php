<?php
session_start();

// Calculate total
$grandTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $grandTotal += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pay with PayPal</title>
  <script src="https://www.paypal.com/sdk/js?client-id=AYkM-GQlSRQl-VFalE73hLib3d4L6clpI6uXuqxdAB1GIYNfVJ9JU4cYov-mBsj2DjawJ4iCljjZyB_C&currency=USD"></script>
</head>
<body>
  <h2>Total Amount: $<?= number_format($grandTotal, 2) ?></h2>
  <div id="paypal-button-container"></div>

  <script>
    paypal.Buttons({
      createOrder: function(data, actions) {
        return actions.order.create({
          purchase_units: [{
            amount: {
              value: '<?= number_format($grandTotal, 2) ?>'
            }
          }]
        });
      },
      onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
          // Redirect after successful payment
          alert('Transaction completed by ' + details.payer.name.given_name);
          window.location.href = 'payment-success.php?orderID=' + data.orderID;
        });
      }
    }).render('#paypal-button-container');
  </script>
</body>
</html>
