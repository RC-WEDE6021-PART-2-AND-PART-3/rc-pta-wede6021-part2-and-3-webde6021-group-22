<?php
// createTable.php
// Drops and recreates tblUser, then loads data from userData.txt
// FK checks are disabled first to avoid constraint errors

require_once 'includes/DBConn.php';

$conn = initDatabase();

// MUST disable FK checks before dropping a parent table
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Drop child tables that reference tblUser first, then tblUser itself
$tablesToDrop = [
    'tblReview',
    'tblMessage',
    'tblAorder',
    'tblListingPhoto',
    'tblListing',
    'tblWallet',
    'tblAdmin',
    'tblUser'
];

foreach ($tablesToDrop as $table) {
    $conn->query("DROP TABLE IF EXISTS `$table`");
}

echo "All existing tables dropped.<br>";

// Re-enable FK checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Recreate tblUser (standalone, no FK dependencies needed for this script)
$createSQL = "CREATE TABLE IF NOT EXISTS `tblUser` (
    `user_id`           INT AUTO_INCREMENT PRIMARY KEY,
    `first_name`        VARCHAR(50)  NOT NULL,
    `last_name`         VARCHAR(50)  NOT NULL,
    `email`             VARCHAR(150) NOT NULL UNIQUE,
    `phone_number`      VARCHAR(20)  DEFAULT NULL,
    `password_hash`     VARCHAR(255) NOT NULL,
    `profile_picture`   VARCHAR(255) DEFAULT NULL,
    `shop_name`         VARCHAR(100) DEFAULT NULL,
    `shop_description`  TEXT         DEFAULT NULL,
    `province`          VARCHAR(50)  DEFAULT NULL,
    `city`              VARCHAR(50)  DEFAULT NULL,
    `reputation_score`  DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `total_sales`       INT          NOT NULL DEFAULT 0,
    `is_top_seller`     TINYINT(1)   NOT NULL DEFAULT 0,
    `holiday_mode`      TINYINT(1)   NOT NULL DEFAULT 0,
    `role`              ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
    `account_status`    ENUM('active','pending','suspended','deleted') NOT NULL DEFAULT 'pending',
    `created_at`        DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($createSQL)) {
    die("<span style='color:red;'>Error creating tblUser: " . $conn->error . "</span>");
}

echo "tblUser created successfully.<br>";

// Load data from userData.txt
// Format per line (tab-separated):
// first_name  last_name  email  phone  password_hash  role  account_status  province  city
$filePath = __DIR__ . '/userData.txt';

if (!file_exists($filePath)) {
    die("<span style='color:red;'>userData.txt not found at: $filePath</span>");
}

$lines    = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$inserted = 0;
$skipped  = 0;

foreach ($lines as $lineNum => $line) {
    // Skip comment lines
    if (str_starts_with(trim($line), '#')) continue;

    $cols = explode("\t", $line);

    // Need at least 7 columns
    if (count($cols) < 7) {
        echo "Skipped line " . ($lineNum + 1) . " (not enough columns: " . count($cols) . ")<br>";
        $skipped++;
        continue;
    }

    $first    = trim($cols[0]);
    $last     = trim($cols[1]);
    $email    = trim($cols[2]);
    $phone    = trim($cols[3]);
    $hash     = trim($cols[4]);
    $role     = trim($cols[5]);
    $status   = trim($cols[6]);
    $province = isset($cols[7]) ? trim($cols[7]) : '';
    $city     = isset($cols[8]) ? trim($cols[8]) : '';

    // Basic validation
    if (empty($first) || empty($email) || empty($hash)) {
        echo "Skipped line " . ($lineNum + 1) . " (missing required field)<br>";
        $skipped++;
        continue;
    }

    $stmt = $conn->prepare(
        "INSERT IGNORE INTO tblUser
            (first_name, last_name, email, phone_number, password_hash,
             role, account_status, province, city)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'sssssssss',
        $first, $last, $email, $phone, $hash,
        $role, $status, $province, $city
    );

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $inserted++;
            echo "✔ Inserted: <strong>$first $last</strong> ($email)<br>";
        } else {
            echo "⚠ Skipped duplicate: $email<br>";
            $skipped++;
        }
    } else {
        echo "<span style='color:red;'>✘ Error on line " . ($lineNum + 1) . ": " . $stmt->error . "</span><br>";
        $skipped++;
    }

    $stmt->close();
}

echo "<br><strong>Done.</strong> $inserted inserted, $skipped skipped.<br>";
echo "<br><a href='index.php' style='color:#C8A96E;'>→ Go to Pastimes Homepage</a>";
echo " &nbsp;|&nbsp; <a href='loadClothingStore.php' style='color:#C8A96E;'>→ Run Full Database Setup</a>";

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>createTable.php — Pastimes</title>
<style>
  body { font-family: monospace; background: #111; color: #ccc; padding: 2rem; line-height: 1.8; }
  strong { color: #C8A96E; }
  a { color: #C8A96E; }
</style>
</head>
