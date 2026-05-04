<?php $root = $rootPath ?? ''; ?>
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">Pastimes</div>
        <p>South Africa's pre-owned fashion marketplace. From R150 vintage finds to R25,000 verified luxury — all in one beautiful space.</p>
      </div>
      <div>
        <h4>Shop</h4>
        <ul>
          <li><a href="<?= $root ?>browse.php">All Listings</a></li>
          <li><a href="<?= $root ?>browse.php?cat=Women">Women</a></li>
          <li><a href="<?= $root ?>browse.php?cat=Men">Men</a></li>
          <li><a href="<?= $root ?>browse.php?cat=Shoes">Shoes</a></li>
          <li><a href="<?= $root ?>browse.php?verified=1">Pastimes Verified</a></li>
        </ul>
      </div>
      <div>
        <h4>Sell</h4>
        <ul>
          <li><a href="<?= $root ?>register.php">Start Selling</a></li>
          <li><a href="<?= $root ?>sell.php">List an Item</a></li>
          <li><a href="<?= $root ?>contact.php">Seller Support</a></li>
        </ul>
      </div>
      <div>
        <h4>Help</h4>
        <ul>
          <li><a href="<?= $root ?>contact.php">Contact Us</a></li>
          <li><a href="<?= $root ?>contact.php#faq">FAQs</a></li>
          <li><a href="<?= $root ?>login.php">Sign In</a></li>
          <li><a href="<?= $root ?>register.php">Register</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Pastimes (Pty) Ltd. All rights reserved.</p>
      <p>Made with ♥ for South African fashion lovers.</p>
    </div>
  </div>
</footer>
</body>
</html>