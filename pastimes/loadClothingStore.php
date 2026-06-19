<?php
// loadClothingStore.php
// Creates all ClothingStore tables (drops existing), loads fixture data

require_once 'includes/DBConn.php';

$conn = initDatabase();
$errors = [];
$log = [];

// ── Helper ──────────────────────────────────────────────────────────────────
function run(mysqli $conn, string $sql, string $label, array &$log, array &$errors): void {
    if ($conn->query($sql)) {
        $log[] = "✔ $label";
    } else {
        $errors[] = "✘ $label: " . $conn->error;
    }
}

// ── Drop all tables (order respects FK deps) ─────────────────────────────────
$drops = ['tblReview','tblMessage','tblOrder','tblListingPhoto','tblListing','tblWallet','tblAdmin','tblUser'];
foreach ($drops as $t) {
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    run($conn, "DROP TABLE IF EXISTS `$t`", "Dropped $t", $log, $errors);
}
$conn->query("SET FOREIGN_KEY_CHECKS=1");

// ── Create tblUser ────────────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblUser` (
    `user_id`           INT AUTO_INCREMENT PRIMARY KEY,
    `first_name`        VARCHAR(50)  NOT NULL,
    `last_name`         VARCHAR(50)  NOT NULL,
    `email`             VARCHAR(150) NOT NULL UNIQUE,
    `phone_number`      VARCHAR(20),
    `password_hash`     VARCHAR(255) NOT NULL,
    `profile_picture`   VARCHAR(255) DEFAULT NULL,
    `shop_name`         VARCHAR(100) DEFAULT NULL UNIQUE,
    `shop_description`  TEXT,
    `province`          VARCHAR(50),
    `city`              VARCHAR(50),
    `reputation_score`  DECIMAL(3,2) DEFAULT 0.00,
    `total_sales`       INT          DEFAULT 0,
    `is_top_seller`     BOOLEAN      DEFAULT FALSE,
    `holiday_mode`      BOOLEAN      DEFAULT FALSE,
    `role`              ENUM('buyer','seller','admin') DEFAULT 'buyer',
    `account_status`    ENUM('active','pending','suspended','deleted') DEFAULT 'pending',
    `created_at`        DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblUser", $log, $errors);

// ── Create tblAdmin ───────────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblAdmin` (
    `admin_id`      INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL UNIQUE,
    `access_level`  ENUM('super','standard') DEFAULT 'standard',
    `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `tblUser`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblAdmin", $log, $errors);

// ── Create tblListing ─────────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblListing` (
    `listing_id`        INT AUTO_INCREMENT PRIMARY KEY,
    `seller_id`         INT NOT NULL,
    `title`             VARCHAR(200) NOT NULL,
    `description`       TEXT,
    `category`          VARCHAR(100),
    `sub_category`      VARCHAR(100),
    `brand`             VARCHAR(100),
    `condition_grade`   ENUM('new','like_new','good','fair','poor') DEFAULT 'good',
    `size`              VARCHAR(20),
    `colour`            VARCHAR(50),
    `price`             DECIMAL(10,2) NOT NULL,
    `quantity`          INT DEFAULT 1,
    `listing_type`      ENUM('p2p','curated') DEFAULT 'p2p',
    `listing_status`    ENUM('active','sold','draft','removed') DEFAULT 'active',
    `is_verified`       BOOLEAN DEFAULT FALSE,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`seller_id`) REFERENCES `tblUser`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblListing", $log, $errors);

// ── Create tblListingPhoto ────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblListingPhoto` (
    `photo_id`      INT AUTO_INCREMENT PRIMARY KEY,
    `listing_id`    INT NOT NULL,
    `photo_url`     VARCHAR(255) NOT NULL,
    `is_cover`      BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`listing_id`) REFERENCES `tblListing`(`listing_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblListingPhoto", $log, $errors);

// ── Create tblWallet ──────────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblWallet` (
    `wallet_id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`           INT NOT NULL UNIQUE,
    `buyer_balance`     DECIMAL(10,2) DEFAULT 0.00,
    `seller_balance`    DECIMAL(10,2) DEFAULT 0.00,
    `pending_balance`   DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (`user_id`) REFERENCES `tblUser`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblWallet", $log, $errors);

// ── Create tblOrder ───────────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblAorder` (
    `order_id`          INT AUTO_INCREMENT PRIMARY KEY,
    `buyer_id`          INT NOT NULL,
    `seller_id`         INT NOT NULL,
    `listing_id`        INT NOT NULL,
    `price_paid`        DECIMAL(10,2) NOT NULL,
    `delivery_method`   VARCHAR(100),
    `delivery_fee`      DECIMAL(10,2) DEFAULT 0.00,
    `delivery_address`  TEXT,
    `payment_method`    VARCHAR(50),
    `payment_status`    ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    `order_status`      ENUM('placed','confirmed','shipped','delivered','cancelled','disputed') DEFAULT 'placed',
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`buyer_id`)   REFERENCES `tblUser`(`user_id`),
    FOREIGN KEY (`seller_id`)  REFERENCES `tblUser`(`user_id`),
    FOREIGN KEY (`listing_id`) REFERENCES `tblListing`(`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblAorder", $log, $errors);

// ── Create tblMessage ─────────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblMessage` (
    `message_id`    INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id`     INT NOT NULL,
    `receiver_id`   INT NOT NULL,
    `listing_id`    INT,
    `message_text`  TEXT NOT NULL,
    `is_read`       BOOLEAN DEFAULT FALSE,
    `sent_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`)   REFERENCES `tblUser`(`user_id`),
    FOREIGN KEY (`receiver_id`) REFERENCES `tblUser`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblMessage", $log, $errors);

// ── Create tblReview ──────────────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tblReview` (
    `review_id`         INT AUTO_INCREMENT PRIMARY KEY,
    `order_id`          INT NOT NULL,
    `reviewer_id`       INT NOT NULL,
    `reviewed_user_id`  INT NOT NULL,
    `rating`            INT CHECK (rating BETWEEN 1 AND 5),
    `review_text`       TEXT,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`)          REFERENCES `tblAorder`(`order_id`),
    FOREIGN KEY (`reviewer_id`)       REFERENCES `tblUser`(`user_id`),
    FOREIGN KEY (`reviewed_user_id`)  REFERENCES `tblUser`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Created tblReview", $log, $errors);

// ── Seed tblUser with 7 users ────────────────────────────────────────────────
$users = [
    ['John','Doe','j.doe@abc.co.za','27831234567',md5('password123'),'buyer','active','Gauteng','Johannesburg'],
    ['Sarah','Nkosi','s.nkosi@gmail.com','27729876543',md5('seller456'),'seller','active','Western Cape','Cape Town'],
    ['Lebo','Mokoena','l.mokoena@outlook.com','27641112233',md5('lebo789'),'buyer','active','Gauteng','Pretoria'],
    ['Thabo','Dlamini','t.dlamini@yahoo.com','27823334455',md5('thabo321'),'seller','pending','KwaZulu-Natal','Durban'],
    ['Aisha','Patel','a.patel@hotmail.com','27714445566',md5('aisha654'),'buyer','active','Gauteng','Sandton'],
    ['Mpho','Sithole','m.sithole@gmail.com','27835556677',md5('mpho987'),'seller','active','Gauteng','Soweto'],
    ['Admin','User','admin@pastimes.co.za','27700000001',md5('adminpass'),'admin','active','Gauteng','Johannesburg'],
];

foreach ($users as $u) {
    $stmt = $conn->prepare("INSERT IGNORE INTO tblUser 
        (first_name,last_name,email,phone_number,password_hash,role,account_status,province,city) 
        VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssssss', $u[0],$u[1],$u[2],$u[3],$u[4],$u[5],$u[6],$u[7],$u[8]);
    $stmt->execute();
    $stmt->close();
}
$log[] = "✔ Seeded tblUser";

// ── Seed tblAdmin ─────────────────────────────────────────────────────────────
$res = $conn->query("SELECT user_id FROM tblUser WHERE email='admin@pastimes.co.za' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $adminId = $row['user_id'];
    $stmt = $conn->prepare("INSERT IGNORE INTO tblAdmin (user_id, access_level) VALUES (?, 'super')");
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $stmt->close();
    $log[] = "✔ Seeded tblAdmin";
}

// ── Seed tblWallet for all users ──────────────────────────────────────────────
$res = $conn->query("SELECT user_id FROM tblUser");
while ($row = $res->fetch_assoc()) {
    $uid = $row['user_id'];
    $stmt = $conn->prepare("INSERT IGNORE INTO tblWallet (user_id, buyer_balance, seller_balance) VALUES (?, ?, ?)");
    $b = round(rand(0,5000)/100*rand(1,10),2);
    $s = round(rand(0,10000)/100*rand(1,10),2);
    $stmt->bind_param('idd', $uid, $b, $s);
    $stmt->execute();
    $stmt->close();
}
$log[] = "✔ Seeded tblWallet";

// ── Seed tblListing ───────────────────────────────────────────────────────────
$sellerRes = $conn->query("SELECT user_id FROM tblUser WHERE role='seller' AND account_status='active' LIMIT 2");
$sellerIds = [];
while ($r = $sellerRes->fetch_assoc()) $sellerIds[] = $r['user_id'];

if (count($sellerIds) >= 2) {
    $listings = [
        [$sellerIds[0],'Vintage Levi 501 Jeans','Classic 90s Levi 501 straight-leg jeans in dark wash.','Women','Bottoms','Levi\'s','like_new','32','Indigo',450.00,1,'p2p','active',0],
        [$sellerIds[0],'Zara Floral Midi Dress','Beautiful floral print midi dress, worn twice.','Women','Dresses','Zara','good','S','Multicolour',280.00,1,'p2p','active',0],
        [$sellerIds[1],'Nike Air Max 90 Sneakers','White Nike Air Max, size 9, minimal wear.','Shoes','Men','Nike','like_new','9','White',1200.00,1,'p2p','active',0],
        [$sellerIds[1],'Louis Vuitton Neverfull Tote','Authenticated LV Neverfull MM in Damier Ebene.','Accessories','Bags','Louis Vuitton','good','OS','Brown',18500.00,1,'curated','active',1],
        [$sellerIds[0],'H&M Oversized Blazer','Camel oversized blazer, perfect for layering.','Women','Outerwear','H&M','new','M','Camel',150.00,2,'p2p','active',0],
    ];
    foreach ($listings as $l) {
        $stmt = $conn->prepare("INSERT INTO tblListing 
            (seller_id,title,description,category,sub_category,brand,condition_grade,size,colour,price,quantity,listing_type,listing_status,is_verified) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('issssssssdissi', $l[0],$l[1],$l[2],$l[3],$l[4],$l[5],$l[6],$l[7],$l[8],$l[9],$l[10],$l[11],$l[12],$l[13]);
        $stmt->execute();
        $stmt->close();
    }
    $log[] = "✔ Seeded tblListing";
}

$conn->close();

// ── Output ────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>ClothingStore Setup</title>
<style>body{font-family:monospace;padding:2rem;background:#0f0f0f;color:#e0e0e0;}
h1{color:#c8a96e;}
.ok{color:#4ade80;}.err{color:#f87171;}
</style></head>
<body>
<h1>ClothingStore — Database Setup</h1>
<?php foreach($log as $l): ?><p class="ok"><?= htmlspecialchars($l) ?></p><?php endforeach; ?>
<?php foreach($errors as $e): ?><p class="err"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
<p style="margin-top:2rem;color:#888;">
  <?= empty($errors) ? '✅ All tables created and seeded successfully.' : '⚠ Completed with errors above.' ?>
</p>
<p><a href="index.php" style="color:#c8a96e;">→ Go to Pastimes</a></p>
</body></html>
