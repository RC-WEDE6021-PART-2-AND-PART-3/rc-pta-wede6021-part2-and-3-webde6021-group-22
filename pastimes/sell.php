<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// sell.php — Create a new listing with image upload
$pageTitle = 'List an Item';
$cssPath   = 'css/style.css';

// 1. Session + DB logic FIRST
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$user    = getCurrentUser();
$errors  = [];
$success = false;
$sticky  = [];

// Handle image upload
function uploadListingImage($file) {
    $targetDir = __DIR__ . '/uploads/listings/full/';
    $thumbDir = __DIR__ . '/uploads/listings/thumbnails/';
    
    // Create directories if they don't exist
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);
    
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $targetPath = $targetDir . $fileName;
    $thumbPath = $thumbDir . $fileName;
    
    // Validate image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['error' => 'File is not an image.'];
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'Image size must be less than 5MB.'];
    }
    
    // Allowed formats
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Only JPG, PNG, GIF, and WEBP are allowed.'];
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Create thumbnail
        createThumbnail($targetPath, $thumbPath, 200, 200);
        return ['success' => true, 'filename' => $fileName];
    }
    
    return ['error' => 'Failed to upload image.'];
}

function createThumbnail($source, $destination, $width, $height) {
    list($origWidth, $origHeight, $type) = getimagesize($source);
    
    // Calculate aspect ratio
    $ratio = min($width / $origWidth, $height / $origHeight);
    $newWidth = $origWidth * $ratio;
    $newHeight = $origHeight * $ratio;
    
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // Load image based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    
    // Save thumbnail
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumb, $destination, 80);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumb, $destination, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumb, $destination);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($thumb, $destination, 80);
            break;
    }
    
    imagedestroy($thumb);
    imagedestroy($sourceImage);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sticky = [
        'title'       => trim($_POST['title']        ?? ''),
        'description' => trim($_POST['description']  ?? ''),
        'category'    => trim($_POST['category']     ?? ''),
        'sub_category'=> trim($_POST['sub_category'] ?? ''),
        'brand'       => trim($_POST['brand']        ?? ''),
        'condition'   => trim($_POST['condition']    ?? ''),
        'size'        => trim($_POST['size']         ?? ''),
        'colour'      => trim($_POST['colour']       ?? ''),
        'price'       => trim($_POST['price']        ?? ''),
        'quantity'    => trim($_POST['quantity']     ?? '1'),
    ];

    // Server-side validation
    if (empty($sticky['title']))       $errors[] = 'Item title is required.';
    if (empty($sticky['category']))    $errors[] = 'Please select a category.';
    if (empty($sticky['condition']))   $errors[] = 'Please select the item condition.';
    if (empty($sticky['price']) || !is_numeric($sticky['price']) || (float)$sticky['price'] <= 0)
        $errors[] = 'Please enter a valid price greater than R0.';
    if ((int)($sticky['quantity']) < 1)
        $errors[] = 'Quantity must be at least 1.';

    // Handle image upload
    $imageFile = $_FILES['listing_image'] ?? null;
    $imageFilename = null;
    
    if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadListingImage($imageFile);
        if (isset($uploadResult['error'])) {
            $errors[] = $uploadResult['error'];
        } else {
            $imageFilename = $uploadResult['filename'];
        }
    } elseif ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Error uploading image. Please try again.';
    }

    if (empty($errors)) {
        $conn = getDBConnection();

        $stmt = $conn->prepare(
            "INSERT INTO tblListing
                (seller_id, title, description, image_url, category, sub_category,
                 brand, condition_grade, size, colour, price, quantity,
                 listing_type, listing_status, is_verified)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'p2p', 'active', 0)"
        );
        $stmt->bind_param(
            'issssssssddi',
            $user['user_id'],
            $sticky['title'],
            $sticky['description'],
            $imageFilename,
            $sticky['category'],
            $sticky['sub_category'],
            $sticky['brand'],
            $sticky['condition'],
            $sticky['size'],
            $sticky['colour'],
            $sticky['price'],
            $sticky['quantity']
        );

        if ($stmt->execute()) {
            $newId   = $conn->insert_id;
            $success = true;
            $sticky  = []; // clear form
        } else {
            $errors[] = 'Database error — could not save your listing. Please try again.';
        }

        $stmt->close();
        $conn->close();
    }
}

// 2. NOW output HTML
require_once 'includes/header.php';
?>

<div class="container" style="max-width:780px;padding-top:2.5rem;padding-bottom:5rem;">

  <!-- Breadcrumb -->
  <div style="font-size:.85rem;color:var(--mid-grey);margin-bottom:1.5rem;">
    <a href="index.php">Home</a> /
    <a href="dashboard.php">My Account</a> /
    <span>List an Item</span>
  </div>

  <h1 style="margin-bottom:.4rem;">List an Item</h1>
  <p style="margin-bottom:2rem;">Fill in the details below. Your listing goes live on the shop immediately.</p>

  <!-- Success state -->
  <?php if ($success): ?>
  <div style="text-align:center;padding:3rem;background:var(--warm-white);border-radius:var(--radius-lg);border:1px solid var(--light-grey);">
    <div style="font-size:3.5rem;margin-bottom:1rem;">🎉</div>
    <h2 style="margin-bottom:.75rem;">Your item is live!</h2>
    <p style="margin-bottom:2rem;">Your listing has been published and is now visible to buyers on Pastimes.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="browse.php"       class="btn btn-primary">View in Shop</a>
      <a href="sell.php"         class="btn btn-outline">List Another Item</a>
      <a href="my-listings.php"  class="btn btn-outline">My Listings</a>
    </div>
  </div>

  <?php else: ?>

  <!-- Errors -->
  <?php if (!empty($errors)): ?>
  <div class="alert alert-error">
    <?php foreach ($errors as $e): ?>
    <div>⚠ <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- AI Banner -->
  <div style="background:linear-gradient(135deg,#1C1C1E,#2a2a2e);
              border-radius:var(--radius-lg);padding:1.25rem 1.5rem;
              margin-bottom:2rem;display:flex;align-items:center;gap:1.25rem;">
    <div style="font-size:2rem;">🤖</div>
    <div style="flex:1;">
      <div style="color:var(--gold);font-weight:700;font-size:.9rem;">AI Smart Listing Tool</div>
      <div style="color:#888;font-size:.8rem;">Upload a photo and AI will auto-fill brand, colour and condition for you.</div>
    </div>
    <button class="btn btn-outline btn-sm" style="flex-shrink:0;"
            onclick="alert('AI photo analysis will be available in the full production build.')">
      Try AI Tool
    </button>
  </div>

  <!-- Form -->
  <form method="POST" action="sell.php" novalidate id="sellForm" enctype="multipart/form-data">

    <!-- Section 1: Item Details -->
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-header"><h3>Item Details</h3></div>
      <div class="card-body">

        <div class="form-group">
          <label for="title">Item Title <span style="color:var(--error);">*</span></label>
          <input type="text" id="title" name="title" class="form-control" required
                 maxlength="200"
                 placeholder="e.g. Vintage Levi 501 Jeans — Dark Wash"
                 value="<?= htmlspecialchars($sticky['title'] ?? '') ?>">
          <div class="form-hint">Be specific — include brand, style and colour in the title.</div>
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" class="form-control" rows="4"
                    placeholder="Describe the item — fit, fabric, any flaws, how often it was worn…"><?= htmlspecialchars($sticky['description'] ?? '') ?></textarea>
        </div>

        <!-- Image Upload -->
        <div class="form-group">
          <label for="listing_image">Product Image</label>
          <div style="border:2px dashed var(--light-grey);border-radius:var(--radius);padding:2rem;text-align:center;cursor:pointer;transition:all var(--transition);"
               id="dropZone" 
               onmouseover="this.style.borderColor='var(--gold)'" 
               onmouseout="this.style.borderColor='var(--light-grey)'">
            <div style="font-size:3rem;margin-bottom:.5rem;">📸</div>
            <div style="font-weight:600;color:var(--charcoal);">Drop your image here or click to browse</div>
            <div style="font-size:.8rem;color:var(--mid-grey);margin-top:.5rem;">
              JPG, PNG, GIF, WEBP · Max 5MB
            </div>
            <input type="file" id="listing_image" name="listing_image" accept="image/*" style="display:none;" 
                   onchange="handleFileSelect(this)">
            <div id="imagePreview" style="display:none;margin-top:1rem;position:relative;display:inline-block;">
              <img id="previewImg" src="#" alt="Preview" style="max-width:200px;max-height:200px;border-radius:var(--radius);">
              <button type="button" onclick="removeImage()" 
                      style="position:absolute;top:-10px;right:-10px;background:var(--error);color:white;border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:14px;line-height:24px;text-align:center;">✕</button>
            </div>
          </div>
          <div class="form-hint">Upload a clear photo of your item for best results.</div>
        </div>

        <div class="grid-2" style="gap:1rem;">
          <div class="form-group">
            <label for="category">Category <span style="color:var(--error);">*</span></label>
            <select id="category" name="category" class="form-control" required>
              <option value="">— Select Category —</option>
              <?php
              $cats = ['Women','Men','Kids','Shoes','Bags','Accessories','Activewear','Vintage and Secondhand'];
              foreach ($cats as $c):
              ?>
              <option value="<?= $c ?>"
                <?= ($sticky['category'] ?? '') === $c ? 'selected' : '' ?>>
                <?= $c ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="sub_category">Sub-Category</label>
            <input type="text" id="sub_category" name="sub_category" class="form-control"
                   placeholder="e.g. Tops, Dresses, Jackets, Sneakers"
                   value="<?= htmlspecialchars($sticky['sub_category'] ?? '') ?>">
          </div>
        </div>

        <div class="grid-2" style="gap:1rem;">
          <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand" class="form-control"
                   placeholder="e.g. Zara, Nike, Woolworths"
                   value="<?= htmlspecialchars($sticky['brand'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="condition">Condition <span style="color:var(--error);">*</span></label>
            <select id="condition" name="condition" class="form-control" required>
              <option value="">— Select Condition —</option>
              <?php
              $conds = [
                'new'      => 'New (with tags)',
                'like_new' => 'Like New — worn once or twice',
                'good'     => 'Good — gently used',
                'fair'     => 'Fair — visible wear',
                'poor'     => 'Poor — heavy wear / flaws',
              ];
              foreach ($conds as $k => $v):
              ?>
              <option value="<?= $k ?>"
                <?= ($sticky['condition'] ?? '') === $k ? 'selected' : '' ?>>
                <?= $v ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid-2" style="gap:1rem;">
          <div class="form-group">
            <label for="size">Size</label>
            <input type="text" id="size" name="size" class="form-control"
                   placeholder="XS / S / M / L / XL / 32 / 38…"
                   value="<?= htmlspecialchars($sticky['size'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="colour">Colour</label>
            <input type="text" id="colour" name="colour" class="form-control"
                   placeholder="Navy Blue, Cream, Black…"
                   value="<?= htmlspecialchars($sticky['colour'] ?? '') ?>">
          </div>
        </div>

      </div>
    </div>

    <!-- Section 2: Pricing -->
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-header"><h3>Pricing &amp; Stock</h3></div>
      <div class="card-body">
        <div class="grid-2" style="gap:1rem;">
          <div class="form-group">
            <label for="price">Listing Price (R) <span style="color:var(--error);">*</span></label>
            <input type="number" id="price" name="price" class="form-control"
                   required min="1" step="0.01" placeholder="350.00"
                   value="<?= htmlspecialchars($sticky['price'] ?? '') ?>">
            <div class="form-hint">Items over R1,000 qualify for BNPL at checkout.</div>
          </div>
          <div class="form-group">
            <label for="quantity">Quantity Available</label>
            <input type="number" id="quantity" name="quantity" class="form-control"
                   min="1" max="999" placeholder="1"
                   value="<?= htmlspecialchars($sticky['quantity'] ?? '1') ?>">
          </div>
        </div>

        <!-- Price guide -->
        <div style="background:var(--cream);border:1px solid var(--light-grey);
                    border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--mid-grey);">
          💡 <strong>Pricing tip:</strong> Pre-owned items typically sell for 30–60% of their original retail price.
          Items in "Like New" condition can be priced higher. Add the original price in your description to help buyers understand the value.
        </div>
      </div>
    </div>

    <!-- Section 3: Delivery -->
    <div class="card" style="margin-bottom:2rem;">
      <div class="card-header"><h3>Delivery Options</h3></div>
      <div class="card-body">
        <p style="font-size:.875rem;margin-bottom:1rem;">Select which delivery methods buyers can choose at checkout:</p>
        <div style="display:flex;flex-direction:column;gap:.75rem;">
          <?php
          $deliveryOpts = [
            'pargo'   => ['Pargo Pickup Point', 'R65 — buyer collects from nearest Pargo point'],
            'courier' => ['Door-to-Door Courier', 'R95 — delivered to buyer\'s door'],
            'postnet' => ['PostNet to PostNet', 'R75 — dropped off and collected at PostNet'],
          ];
          foreach ($deliveryOpts as $k => [$label, $desc]): ?>
          <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;
                        padding:.85rem;border:1.5px solid var(--light-grey);border-radius:var(--radius);
                        background:var(--cream);">
            <input type="checkbox" name="delivery[]" value="<?= $k ?>" checked
                   style="accent-color:var(--gold);margin-top:2px;width:16px;height:16px;flex-shrink:0;">
            <div>
              <div style="font-weight:600;font-size:.9rem;"><?= $label ?></div>
              <div style="font-size:.78rem;color:var(--mid-grey);"><?= $desc ?></div>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div style="display:flex;gap:1rem;justify-content:flex-end;flex-wrap:wrap;">
      <a href="dashboard.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary" style="padding:.85rem 2.5rem;font-size:1rem;">
        🚀 Publish Listing
      </button>
    </div>

  </form>
  <?php endif; ?>
</div>

<script>
// Image upload handlers
function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'inline-block';
            document.querySelector('#dropZone > div:first-child').style.display = 'none';
            document.querySelector('#dropZone > div:nth-child(2)').style.display = 'none';
            document.querySelector('#dropZone > div:nth-child(3)').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    document.getElementById('listing_image').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.querySelector('#dropZone > div:first-child').style.display = 'block';
    document.querySelector('#dropZone > div:nth-child(2)').style.display = 'block';
    document.querySelector('#dropZone > div:nth-child(3)').style.display = 'block';
}

// Drag and drop
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('click', function() {
    document.getElementById('listing_image').click();
});

dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.borderColor = 'var(--gold)';
    this.style.background = 'rgba(200,169,110,.05)';
});

dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.borderColor = 'var(--light-grey)';
    this.style.background = 'transparent';
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = 'var(--light-grey)';
    this.style.background = 'transparent';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('listing_image').files = files;
        handleFileSelect(document.getElementById('listing_image'));
    }
});

// Client-side HTML5 validation
document.getElementById('sellForm')?.addEventListener('submit', function(e) {
  let valid = true;
  const required = [
    {id: 'title',     msg: 'Item title is required'},
    {id: 'category',  msg: 'Please select a category'},
    {id: 'condition', msg: 'Please select item condition'},
    {id: 'price',     msg: 'Please enter a valid price'},
  ];

  required.forEach(({id, msg}) => {
    const el = document.getElementById(id);
    if (!el) return;
    if (!el.value.trim()) {
      el.style.borderColor = 'var(--error)';
      el.style.boxShadow   = '0 0 0 3px rgba(192,57,43,.15)';
      valid = false;
    } else {
      el.style.borderColor = '';
      el.style.boxShadow   = '';
    }
  });

  const price = parseFloat(document.getElementById('price')?.value);
  if (isNaN(price) || price <= 0) {
    document.getElementById('price').style.borderColor = 'var(--error)';
    valid = false;
  }

  if (!valid) {
    e.preventDefault();
    window.scrollTo({top: 0, behavior: 'smooth'});
    if (!document.querySelector('.sell-error-banner')) {
      const banner = document.createElement('div');
      banner.className = 'alert alert-error sell-error-banner';
      banner.innerHTML = '⚠ Please fix the highlighted fields above before publishing.';
      document.querySelector('#sellForm').prepend(banner);
    }
  }
});

// Clear error highlight when user edits a field
document.querySelectorAll('.form-control').forEach(el => {
  el.addEventListener('input', () => {
    el.style.borderColor = '';
    el.style.boxShadow   = '';
  });
});
</script>

<?php require_once 'includes/footer.php'; ?>