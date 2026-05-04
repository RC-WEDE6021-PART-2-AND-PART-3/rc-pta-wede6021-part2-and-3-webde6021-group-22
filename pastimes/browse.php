<?php
// browse.php — Shop / Search Results
$pageTitle = 'Browse';
$cssPath   = 'css/style.css';

// 1. Session + DB logic FIRST — before any HTML output
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';

$conn = getDBConnection();

// ── Build WHERE clause from GET params ──────────────────
$where  = ["l.listing_status = 'active'"];
$params = [];
$types  = '';

$search   = trim($_GET['q']   ?? '');
$category = trim($_GET['cat'] ?? '');
$brand    = trim($_GET['brand'] ?? '');
$verified = isset($_GET['verified']) && $_GET['verified'] == '1';
$sort     = $_GET['sort'] ?? 'newest';
$minPrice = isset($_GET['min']) && is_numeric($_GET['min']) ? (float)$_GET['min'] : 0;
$maxPrice = isset($_GET['max']) && is_numeric($_GET['max']) ? (float)$_GET['max'] : 0;

if ($search !== '') {
    $where[]  = "(l.title LIKE ? OR l.brand LIKE ? OR l.description LIKE ?)";
    $s        = '%' . $search . '%';
    $params[] = $s; $params[] = $s; $params[] = $s;
    $types   .= 'sss';
}
if ($category !== '') {
    $where[]  = "l.category = ?";
    $params[] = $category;
    $types   .= 's';
}
if ($brand !== '') {
    $where[]  = "l.brand = ?";
    $params[] = $brand;
    $types   .= 's';
}
if ($verified) {
    $where[] = "l.is_verified = 1";
}
if ($minPrice > 0) {
    $where[]  = "l.price >= ?";
    $params[] = $minPrice;
    $types   .= 'd';
}
if ($maxPrice > 0) {
    $where[]  = "l.price <= ?";
    $params[] = $maxPrice;
    $types   .= 'd';
}

$orderSQL = match($sort) {
    'price_asc'  => 'l.price ASC',
    'price_desc' => 'l.price DESC',
    'oldest'     => 'l.created_at ASC',
    default      => 'l.created_at DESC',
};

$whereSQL = 'WHERE ' . implode(' AND ', $where);
$sql = "SELECT l.*, u.shop_name, u.first_name, u.last_name, u.reputation_score
        FROM tblListing l
        JOIN tblUser u ON l.seller_id = u.user_id
        $whereSQL
        ORDER BY $orderSQL";

$stmt = $conn->prepare($sql);
$listings = [];

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $listings[] = $row;
}
$stmt->close();

// ── Sidebar: distinct categories and brands ─────────────
$cats   = [];
$brands = [];
$cr = $conn->query("SELECT DISTINCT category FROM tblListing WHERE listing_status='active' AND category IS NOT NULL AND category != '' ORDER BY category");
if ($cr) while ($row = $cr->fetch_assoc()) $cats[] = $row['category'];

$br = $conn->query("SELECT DISTINCT brand FROM tblListing WHERE listing_status='active' AND brand IS NOT NULL AND brand != '' ORDER BY brand LIMIT 30");
if ($br) while ($row = $br->fetch_assoc()) $brands[] = $row['brand'];

$conn->close();

// Colour swatches for cards
$swatches = ['#E8D5C4','#C4D5E8','#D5E8C4','#E8C4D5','#D5C4E8','#E8E4C4','#C4E8E4','#E8C4C4'];

// 2. NOW output HTML
require_once 'includes/header.php';
?>

<!-- Page heading -->
<div style="background:var(--warm-white);border-bottom:1px solid var(--light-grey);padding:1.5rem 0;">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
    <div>
      <h1 style="font-size:1.5rem;margin-bottom:.2rem;">
        <?php if ($verified): ?>✓ Pastimes Verified Items
        <?php elseif ($category): ?>Browse: <?= htmlspecialchars($category) ?>
        <?php elseif ($search): ?>Results for "<?= htmlspecialchars($search) ?>"
        <?php else: ?>Shop All Items<?php endif; ?>
      </h1>
      <p style="font-size:.875rem;color:var(--mid-grey);">
        <?= count($listings) ?> item<?= count($listings) !== 1 ? 's' : '' ?> found
      </p>
    </div>
    <!-- Quick category tabs -->
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
      <a href="browse.php" class="btn btn-sm <?= !$category && !$verified ? 'btn-dark' : 'btn-outline' ?>">All</a>
      <a href="browse.php?cat=Women" class="btn btn-sm <?= $category==='Women' ? 'btn-dark' : 'btn-outline' ?>">Women</a>
      <a href="browse.php?cat=Men"   class="btn btn-sm <?= $category==='Men'   ? 'btn-dark' : 'btn-outline' ?>">Men</a>
      <a href="browse.php?cat=Shoes" class="btn btn-sm <?= $category==='Shoes' ? 'btn-dark' : 'btn-outline' ?>">Shoes</a>
      <a href="browse.php?cat=Bags"  class="btn btn-sm <?= $category==='Bags'  ? 'btn-dark' : 'btn-outline' ?>">Bags</a>
      <a href="browse.php?verified=1" class="btn btn-sm <?= $verified ? 'btn-dark' : 'btn-outline' ?>">✓ Verified</a>
    </div>
  </div>
</div>

<div class="container" style="padding-top:2rem;padding-bottom:4rem;">
  <div style="display:grid;grid-template-columns:220px 1fr;gap:2rem;align-items:start;">

    <!-- ── Sidebar Filters ─────────────────────────────── -->
    <aside>
      <form method="GET" action="browse.php">
        <div class="card card-body" style="padding:1.25rem;">
          <h3 style="font-size:1rem;margin-bottom:1rem;">Filter &amp; Sort</h3>

          <div class="form-group">
            <label>Search</label>
            <input type="text" name="q" class="form-control"
                   value="<?= htmlspecialchars($search) ?>"
                   placeholder="Title, brand…">
          </div>

          <div class="form-group">
            <label>Category</label>
            <select name="cat" class="form-control">
              <option value="">All Categories</option>
              <?php foreach ($cats as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>" <?= $category===$c?'selected':'' ?>>
                <?= htmlspecialchars($c) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Brand</label>
            <select name="brand" class="form-control">
              <option value="">All Brands</option>
              <?php foreach ($brands as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>" <?= $brand===$b?'selected':'' ?>>
                <?= htmlspecialchars($b) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Price Range (R)</label>
            <div style="display:flex;gap:.5rem;">
              <input type="number" name="min" class="form-control"
                     placeholder="Min" value="<?= $minPrice ?: '' ?>" min="0" style="width:50%;">
              <input type="number" name="max" class="form-control"
                     placeholder="Max" value="<?= $maxPrice ?: '' ?>" min="0" style="width:50%;">
            </div>
          </div>

          <div class="form-group">
            <label>Sort By</label>
            <select name="sort" class="form-control">
              <option value="newest"     <?= $sort==='newest'    ?'selected':'' ?>>Newest First</option>
              <option value="price_asc"  <?= $sort==='price_asc' ?'selected':'' ?>>Price: Low → High</option>
              <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High → Low</option>
              <option value="oldest"     <?= $sort==='oldest'    ?'selected':'' ?>>Oldest First</option>
            </select>
          </div>

          <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;">
              <input type="checkbox" name="verified" value="1" <?= $verified?'checked':'' ?>
                     style="accent-color:var(--gold);width:16px;height:16px;">
              Pastimes Verified Only
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-sm">Apply Filters</button>
          <a href="browse.php" class="btn btn-outline btn-block btn-sm mt-1"
             style="text-align:center;">Clear All</a>
        </div>
      </form>
    </aside>

    <!-- ── Product Grid ───────────────────────────────── -->
    <div>
      <?php if (empty($listings)): ?>
      <div class="card card-body text-center" style="padding:4rem;">
        <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
        <h3>No items found</h3>
        <p style="margin-bottom:1.5rem;">Try adjusting your search or clearing the filters.</p>
        <a href="browse.php" class="btn btn-primary">View All Items</a>
      </div>

      <?php else: ?>
      <div class="grid-3">
        <?php foreach ($listings as $i => $item): ?>
        <div class="card product-card">
          <?php if ($item['is_verified']): ?>
          <div class="verified-badge">✓ Verified</div>
          <?php endif; ?>

          <!-- Product image placeholder -->
          <div class="img-wrap"
               style="background:<?= $swatches[$i % count($swatches)] ?>;
                      min-height:240px;display:flex;align-items:center;justify-content:center;">
            <span style="font-size:4rem;opacity:.2;">
              <?= match(strtolower($item['category'] ?? '')) {
                'shoes'        => '👟',
                'bags'         => '👜',
                'accessories'  => '💍',
                'men'          => '👔',
                default        => '👗'
              } ?>
            </span>
          </div>

          <div class="product-info">
            <?php if ($item['brand']): ?>
            <div class="product-brand"><?= htmlspecialchars($item['brand']) ?></div>
            <?php endif; ?>
            <div class="product-title"><?= htmlspecialchars($item['title']) ?></div>

            <div style="font-size:.78rem;color:var(--mid-grey);margin:.25rem 0;">
              <?= htmlspecialchars($item['category'] ?? '') ?>
              <?= $item['size'] ? '· Size ' . htmlspecialchars($item['size']) : '' ?>
              · <?= ucfirst(str_replace('_', ' ', $item['condition_grade'])) ?>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.5rem;">
              <div class="product-price">R<?= number_format($item['price'], 2) ?></div>
              <?php if ($item['is_verified']): ?>
              <span class="badge badge-gold">✓ Auth</span>
              <?php endif; ?>
            </div>

            <div style="font-size:.73rem;color:var(--mid-grey);margin-top:.3rem;">
              by <?= htmlspecialchars($item['shop_name'] ?: ($item['first_name'] . ' ' . $item['last_name'])) ?>
              <?php if ($item['reputation_score'] > 0): ?>
              · ⭐ <?= number_format($item['reputation_score'], 1) ?>
              <?php endif; ?>
            </div>

            <!-- Buy button -->
            <div style="margin-top:.75rem;">
              <?php if (isLoggedIn()): ?>
                <a href="checkout.php?listing_id=<?= (int)$item['listing_id'] ?>"
                   class="btn btn-primary btn-block btn-sm">Buy Now — R<?= number_format($item['price'],2) ?></a>
              <?php else: ?>
                <a href="login.php" class="btn btn-outline btn-block btn-sm">Sign In to Buy</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div><!-- /grid -->
      <?php endif; ?>
    </div><!-- /product area -->

  </div><!-- /layout grid -->
</div><!-- /container -->

<?php require_once 'includes/footer.php'; ?>