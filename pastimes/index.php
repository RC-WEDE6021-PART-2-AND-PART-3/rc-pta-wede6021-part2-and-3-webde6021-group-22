<?php
// index.php — Pastimes Homepage
// Correct order: session → DB → logic → header → HTML
$pageTitle = 'Home';
$cssPath   = 'css/style.css';

require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';

// Fetch latest listings
$listings = [];
$conn = getDBConnection();
$result = $conn->query(
    "SELECT l.*, u.shop_name, u.first_name, u.reputation_score
     FROM tblListing l
     JOIN tblUser u ON l.seller_id = u.user_id
     WHERE l.listing_status = 'active'
     ORDER BY l.created_at DESC
     LIMIT 8"
);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
}
$conn->close();

// Colour swatches for listing cards (no images yet)
$swatches = ['#E8D5C4','#C4D5E8','#D5E8C4','#E8C4D5','#D5C4E8','#E8E4C4','#C4E8E4','#E8C4C4'];

// Output starts here — header must be after all PHP logic
require_once 'includes/header.php';
?>

<!-- ── Hero ─────────────────────────────────────────────── -->
<section class="hero">
  <div class="container">
    <div class="hero-eyebrow">South Africa's Fashion Marketplace</div>
    <h1>Fashion with a <em>Past,</em><br>A Future Worth Wearing</h1>
    <p>Buy and sell pre-owned clothing, accessories, and verified luxury items — all in one trusted South African platform.</p>
    <div class="hero-actions">
      <a href="browse.php" class="btn btn-primary">Start Shopping</a>
      <a href="register.php" class="btn btn-outline">Start Selling Free</a>
    </div>
    <div class="hero-stats">
      <div class="stat-item"><div class="num">500+</div><div class="lbl">Listings</div></div>
      <div class="stat-item"><div class="num">50+</div><div class="lbl">Sellers</div></div>
      <div class="stat-item"><div class="num">100%</div><div class="lbl">SA Based</div></div>
      <div class="stat-item"><div class="num">R0</div><div class="lbl">Listing Fee</div></div>
    </div>
  </div>
</section>

<!-- ── Feature Strip ───────────────────────────────────── -->
<section style="background:var(--warm-white);border-bottom:1px solid var(--light-grey);padding:1.5rem 0;">
  <div class="container" style="display:flex;justify-content:space-around;flex-wrap:wrap;gap:1.5rem;">
    <?php
    $features = [
      ['🤖', 'AI Smart Listing',     'List in seconds'],
      ['✓',  'Pastimes Verified',    'Authenticated luxury'],
      ['💳', 'BNPL Available',       'Split payments over R1,000'],
      ['💬', 'Offer & Negotiate',    'Make offers on any item'],
    ];
    foreach ($features as $f): ?>
    <div style="display:flex;align-items:center;gap:.75rem;">
      <span style="font-size:1.5rem;"><?= $f[0] ?></span>
      <div>
        <div style="font-weight:700;font-size:.9rem;color:var(--charcoal);"><?= $f[1] ?></div>
        <div style="font-size:.78rem;color:var(--mid-grey);"><?= $f[2] ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Featured Listings ──────────────────────────────── -->
<section class="section">
  <div class="container">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2.5rem;">
      <div>
        <p style="color:var(--gold);font-size:.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;margin-bottom:.5rem;">Just In</p>
        <h2>Latest Listings</h2>
      </div>
      <a href="browse.php" class="btn btn-outline btn-sm">View All</a>
    </div>

    <?php if (empty($listings)): ?>
    <div class="card card-body text-center" style="padding:4rem;">
      <div style="font-size:3rem;margin-bottom:1rem;">👗</div>
      <h3 style="margin-bottom:1rem;">No listings yet — be the first to sell!</h3>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="register.php" class="btn btn-primary">Start Selling</a>
        <a href="loadClothingStore.php" class="btn btn-outline">Load Sample Data</a>
      </div>
    </div>
    <?php else: ?>
    <div class="grid-4">
      <?php foreach ($listings as $i => $item): ?>
      <div class="card product-card" style="animation-delay:<?= $i*0.07 ?>s;">
        <?php if ($item['is_verified']): ?>
          <div class="verified-badge">✓ Verified</div>
        <?php endif; ?>
        <div class="img-wrap" style="background:<?= $swatches[$i % count($swatches)] ?>;min-height:220px;display:flex;align-items:center;justify-content:center;">
          <span style="font-size:3rem;opacity:.3;">👗</span>
        </div>
        <div class="product-info">
          <div class="product-brand"><?= htmlspecialchars($item['brand'] ?? '') ?></div>
          <div class="product-title"><?= htmlspecialchars($item['title']) ?></div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.5rem;">
            <div class="product-price">R<?= number_format($item['price'], 2) ?></div>
            <span class="badge badge-grey"><?= ucfirst(str_replace('_',' ', $item['condition_grade'])) ?></span>
          </div>
          <div style="font-size:.75rem;color:var(--mid-grey);margin-top:.4rem;">
            by <?= htmlspecialchars($item['shop_name'] ?: $item['first_name']) ?>
          </div>
          <a href="browse.php" class="btn btn-primary btn-block btn-sm" style="margin-top:.75rem;">View Item</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Category Quick Links ───────────────────────────── -->
<section style="background:var(--warm-white);padding:4rem 0;">
  <div class="container">
    <h2 class="text-center" style="margin-bottom:2rem;">Browse by Category</h2>
    <div class="grid-4">
      <?php
      $categories = [
        ['Women',        '👗', '#E8D5C4', 'cat=Women'],
        ['Men',          '👔', '#C4D5E8', 'cat=Men'],
        ['Shoes',        '👟', '#D5E8C4', 'cat=Shoes'],
        ['Bags',         '👜', '#E8C4D5', 'cat=Bags'],
        ['Accessories',  '💍', '#D5C4E8', 'cat=Accessories'],
        ['Kids',         '🧒', '#E8E4C4', 'cat=Kids'],
        ['Activewear',   '🏃', '#C4E8E4', 'cat=Activewear'],
        ['✓ Verified',   '💎', '#1C1C1E', 'verified=1'],
      ];
      foreach ($categories as [$label, $icon, $bg, $param]): ?>
      <a href="browse.php?<?= $param ?>"
         style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1rem;
                background:<?= $bg ?>;border-radius:var(--radius-lg);text-decoration:none;
                transition:transform .2s,box-shadow .2s;color:<?= $bg==='#1C1C1E'?'var(--gold)':'var(--charcoal)' ?>;"
         onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
        <span style="font-size:2.5rem;margin-bottom:.75rem;"><?= $icon ?></span>
        <span style="font-weight:700;font-size:.95rem;"><?= $label ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Style Feed Teaser ──────────────────────────────── -->
<section id="style-feed" style="background:var(--charcoal);padding:5rem 0;">
  <div class="container text-center">
    <p style="color:var(--gold);font-size:.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;margin-bottom:1rem;">Discover</p>
    <h2 style="color:var(--cream);margin-bottom:1rem;">The Style Feed</h2>
    <p style="max-width:500px;margin:0 auto 2rem;color:#777;">Browse outfit collages built from real listed items. Click any piece and go straight to its listing.</p>
    <a href="browse.php" class="btn btn-primary">Explore Styles</a>
  </div>
</section>

<!-- ── How It Works ──────────────────────────────────── -->
<section class="section" style="background:var(--warm-white);">
  <div class="container">
    <div class="text-center mb-4">
      <h2>How Pastimes Works</h2>
      <p style="max-width:500px;margin:.75rem auto 0;">Whether you're buying or selling, getting started takes minutes.</p>
    </div>
    <div class="grid-3" style="margin-top:3rem;">
      <?php
      $steps = [
        ['01', 'Register Free',   'Create your account in 60 seconds. No listing fees, ever.'],
        ['02', 'List or Browse',  'Use our AI tool to list items, or browse thousands of pre-owned pieces.'],
        ['03', 'Buy or Sell',     'Secure checkout with PayFast or BNPL. Earnings go straight to your wallet.'],
      ];
      foreach ($steps as $s): ?>
      <div class="card card-body text-center">
        <div style="font-family:var(--font-display);font-size:3rem;color:var(--light-grey);font-weight:700;line-height:1;margin-bottom:1rem;"><?= $s[0] ?></div>
        <h3 style="margin-bottom:.75rem;"><?= $s[1] ?></h3>
        <p><?= $s[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="register.php" class="btn btn-primary">Get Started — It's Free</a>
      &nbsp;
      <a href="browse.php" class="btn btn-outline">Browse Now</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>