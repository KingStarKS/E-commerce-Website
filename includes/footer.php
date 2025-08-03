<!-- START OF FOOTER -->
<style>
  .site-footer {
    background-color: #f9f9f9;
    padding: 40px 20px 20px;
    font-family: 'Arial', sans-serif;
    border-top: 1px solid #ddd;
  }

  .footer-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    max-width: 1200px;
    margin: auto;
  }

  .footer-section {
    flex: 1 1 220px;
    margin-bottom: 25px;
  }

  .footer-section h4 {
    font-size: 16px;
    margin-bottom: 12px;
    font-weight: bold;
    color: #000;
  }

  .footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .footer-section ul li {
    margin-bottom: 8px;
  }

  .footer-section ul li a {
    text-decoration: none;
    color: #333;
    font-size: 14px;
    transition: color 0.3s ease;
  }

  .footer-section ul li a:hover {
    color: #000;
  }

  .newsletter input[type="email"] {
    width: 80%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    font-size: 14px;
  }

  .newsletter button {
    padding: 8px 14px;
    background-color: #000;
    color: #fff;
    border: none;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .newsletter button:hover {
    background-color: #222;
  }

  .social-icons {
    margin-top: 10px;
  }

  .social-icons a img {
    width: 22px;
    margin-right: 10px;
    filter: grayscale(100%);
    transition: filter 0.3s;
  }

  .social-icons a img:hover {
    filter: grayscale(0%);
  }

  .footer-bottom {
    border-top: 1px solid #ccc;
    padding-top: 15px;
    text-align: center;
    font-size: 13px;
    color: #666;
    margin-top: 20px;
  }

  .payment-icons img {
    height: 20px;
    margin: 0 5px;
    vertical-align: middle;
  }

  @media (max-width: 768px) {
    .footer-container {
      flex-direction: column;
      align-items: center;
    }

    .footer-section {
      text-align: center;
    }

    .newsletter input[type="email"] {
      width: 100%;
    }
  }
</style>

<footer class="site-footer">
  <div class="footer-container">

    <div class="footer-section">
      <h4>Shop</h4>
      <ul>
        <li><a href="products.php?category=men">Men</a></li>
        <li><a href="products.php?category=women">Women</a></li>
        <li><a href="products.php?category=kids">Kids</a></li>
        <li><a href="products.php?tag=new">New Arrivals</a></li>
        <li><a href="sale.php">Sale</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4>Customer Service</h4>
      <ul>
        <li><a href="contact.php">Contact Us</a></li>
        <li><a href="faq.php">FAQs</a></li>
        <li><a href="returns.php">Returns & Refunds</a></li>
        <li><a href="shipping.php">Shipping Info</a></li>
        <li><a href="track-order.php">Track Order</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4>Company</h4>
      <ul>
        <li><a href="about.php">About Us</a></li>
        <li><a href="terms.php">Terms & Conditions</a></li>
        <li><a href="privacy.php">Privacy Policy</a></li>
      </ul>
    </div>

    <div class="footer-section newsletter">
      <h4>Join Our Newsletter</h4>
      <form action="subscribe.php" method="post">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Subscribe</button>
      </form>
      <div class="social-icons">
        <a href="#"><img src="assets/icons/facebook.svg" alt="Facebook"></a>
        <a href="#"><img src="assets/icons/instagram.svg" alt="Instagram"></a>
        <a href="#"><img src="assets/icons/twitter.svg" alt="Twitter"></a>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© 2025 YourStoreName. All rights reserved.</p>
   
  </div>
</footer>
<!-- END OF FOOTER -->
