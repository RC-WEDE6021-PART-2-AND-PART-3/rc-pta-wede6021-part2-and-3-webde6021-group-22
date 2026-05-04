-- ============================================================
-- myClothingStore.sql  (FIXED - no apostrophes, no subqueries)
-- Pastimes Web Application - ClothingStore Database
-- Compatible with MariaDB / MySQL 5.7+
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';

CREATE DATABASE IF NOT EXISTS `ClothingStore`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ClothingStore`;

-- Drop all tables
DROP TABLE IF EXISTS `tblReview`;
DROP TABLE IF EXISTS `tblMessage`;
DROP TABLE IF EXISTS `tblAorder`;
DROP TABLE IF EXISTS `tblListingPhoto`;
DROP TABLE IF EXISTS `tblListing`;
DROP TABLE IF EXISTS `tblWallet`;
DROP TABLE IF EXISTS `tblAdmin`;
DROP TABLE IF EXISTS `tblUser`;

-- ── tblUser ───────────────────────────────────────────────────────
CREATE TABLE `tblUser` (
  `user_id`           INT           NOT NULL AUTO_INCREMENT,
  `first_name`        VARCHAR(50)   NOT NULL,
  `last_name`         VARCHAR(50)   NOT NULL,
  `email`             VARCHAR(150)  NOT NULL,
  `phone_number`      VARCHAR(20)   DEFAULT NULL,
  `password_hash`     VARCHAR(255)  NOT NULL,
  `profile_picture`   VARCHAR(255)  DEFAULT NULL,
  `shop_name`         VARCHAR(100)  DEFAULT NULL,
  `shop_description`  TEXT          DEFAULT NULL,
  `province`          VARCHAR(50)   DEFAULT NULL,
  `city`              VARCHAR(50)   DEFAULT NULL,
  `reputation_score`  DECIMAL(3,2)  NOT NULL DEFAULT 0.00,
  `total_sales`       INT           NOT NULL DEFAULT 0,
  `is_top_seller`     TINYINT(1)    NOT NULL DEFAULT 0,
  `holiday_mode`      TINYINT(1)    NOT NULL DEFAULT 0,
  `role`              ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
  `account_status`    ENUM('active','pending','suspended','deleted') NOT NULL DEFAULT 'pending',
  `created_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_shop_name` (`shop_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTE: password plaintext shown in comments. Stored as MD5 hash.
-- Buyers: user_id 1-14 | Sellers: 15-25 | Pending: 26-29 | Admin: 30
INSERT INTO `tblUser`
  (first_name, last_name, email, phone_number, password_hash,
   shop_name, province, city, role, account_status, reputation_score, total_sales)
VALUES
('John',     'Doe',        'j.doe@abc.co.za',          '27831234567', MD5('password123'), NULL,               'Gauteng',      'Johannesburg',    'buyer',  'active',    0.00,  0),
('Lebo',     'Mokoena',    'l.mokoena@outlook.com',     '27641112233', MD5('lebo789'),     NULL,               'Gauteng',      'Pretoria',        'buyer',  'active',    0.00,  0),
('Aisha',    'Patel',      'a.patel@hotmail.com',       '27714445566', MD5('aisha654'),    NULL,               'Gauteng',      'Sandton',         'buyer',  'active',    0.00,  0),
('Zanele',   'Khumalo',    'z.khumalo@gmail.com',       '27726667788', MD5('zanele111'),   NULL,               'Gauteng',      'Randburg',        'buyer',  'suspended', 0.00,  0),
('Nomsa',    'Zulu',       'n.zulu@yahoo.com',          '27729998877', MD5('nomsa333'),    NULL,               'KZN',          'Pietermaritzburg','buyer',  'active',    0.00,  0),
('Farah',    'Adams',      'f.adams@gmail.com',         '27711112233', MD5('farah555'),    NULL,               'Western Cape', 'Cape Town',       'buyer',  'active',    0.00,  0),
('Claudia',  'Ferreira',   'c.ferreira@gmail.com',      '27727778899', MD5('claudia777'),  NULL,               'Gauteng',      'Roodepoort',      'buyer',  'active',    0.00,  0),
('Lerato',   'Sefatsa',    'l.sefatsa@gmail.com',       '27712223344', MD5('lerato999'),   NULL,               'Free State',   'Bloemfontein',    'buyer',  'active',    0.00,  0),
('Dineo',    'Mokoena',    'd.mokoena@yahoo.com',       '27722334455', MD5('dineo1313'),   NULL,               'Gauteng',      'Johannesburg',    'buyer',  'active',    0.00,  0),
('Ayesha',   'Ismail',     'a.ismail@hotmail.com',      '27719990011', MD5('ayesha1515'),  NULL,               'Gauteng',      'Lenasia',         'buyer',  'active',    0.00,  0),
('Candice',  'Swart',      'c.swart@outlook.com',       '27726001122', MD5('candi1717'),   NULL,               'Northern Cape','Kimberley',       'buyer',  'active',    0.00,  0),
('Jessica',  'Louw',       'j.louw@gmail.com',          '27711334455', MD5('jess1919'),    NULL,               'Gauteng',      'Centurion',       'buyer',  'active',    0.00,  0),
('Emma',     'Britz',      'e.britz@gmail.com',         '27729001122', MD5('emma2121'),    NULL,               'North West',   'Potchefstroom',   'buyer',  'active',    0.00,  0),
('Priya',    'Govender',   'p.govender@gmail.com',      '27712334466', MD5('priya2323'),   NULL,               'KZN',          'Durban North',    'buyer',  'active',    0.00,  0),
('Sarah',    'Nkosi',      's.nkosi@gmail.com',         '27729876543', MD5('seller456'),   'Vintage Vibes',    'Western Cape', 'Cape Town',       'seller', 'active',    4.70, 34),
('Mpho',     'Sithole',    'm.sithole@gmail.com',       '27835556677', MD5('mpho987'),     'Mpho Fashion',     'Gauteng',      'Soweto',          'seller', 'active',    4.20, 12),
('Pieter',   'van Wyk',    'p.vanwyk@outlook.com',      '27824443333', MD5('pieter444'),   'Cape Couture',     'Western Cape', 'Stellenbosch',    'seller', 'active',    4.80, 56),
('Kagiso',   'Motsepe',    'k.motsepe@gmail.com',       '27831112222', MD5('kagiso222'),   'Kagi Kloset',      'Gauteng',      'Midrand',         'seller', 'active',    3.90,  7),
('Keegan',   'Jacobs',     'k.jacobs@gmail.com',        '27831334455', MD5('keegan1414'),  'KJ Vintage',       'Western Cape', 'Cape Town',       'seller', 'active',    3.70,  5),
('Bongani',  'Nzama',      'b.nzama@hotmail.com',       '27835667788', MD5('bongani888'),  'B-Style',          'Gauteng',      'Tembisa',         'seller', 'active',    4.10, 19),
('Nina',     'du Plessis', 'nina.dup@gmail.com',        '27829990011', MD5('nina1010'),    'Nina Preloved',    'Western Cape', 'George',          'seller', 'active',    4.60, 28),
('Marcus',   'Olivier',    'm.olivier@gmail.com',       '27841233211', MD5('marcus1212'),  'Marc Luxe',        'Gauteng',      'Pretoria',        'seller', 'active',    4.90, 73),
('Siya',     'Ntanzi',     's.ntanzi@gmail.com',        '27835112233', MD5('siya1616'),    'Siya Sneaks',      'KZN',          'Durban',          'seller', 'active',    4.30, 22),
('Vuyo',     'Mbeki',      'v.mbeki@co.za',             '27832001199', MD5('vuyo2020'),    'Vuyo Collections', 'Eastern Cape', 'Port Elizabeth',  'seller', 'active',    4.50, 41),
('Dumisani', 'Ntuli',      'd.ntuli@yahoo.com',         '27824556677', MD5('dumi1818'),    'Dumi Drip',        'Gauteng',      'Kempton Park',    'seller', 'suspended', 2.10,  3),
('Thabo',    'Dlamini',    't.dlamini@yahoo.com',       '27823334455', MD5('thabo321'),    'Urban Style Co',   'KZN',          'Durban',          'seller', 'pending',   0.00,  0),
('Sipho',    'Mthembu',    'sipho.m@gmail.com',         '27834445566', MD5('sipho666'),    'Sipho Threads',    'Gauteng',      'Alexandra',       'seller', 'pending',   0.00,  0),
('Thandi',   'Majola',     't.majola@outlook.com',      '27731112244', MD5('thandi1111'),  NULL,               'KZN',          'Durban',          'buyer',  'pending',   0.00,  0),
('Lwazi',    'Mhlongo',    'l.mhlongo@yahoo.com',       '27836557788', MD5('lwazi2222'),   'Lwazi Style Lab',  'Gauteng',      'Randburg',        'seller', 'pending',   0.00,  0),
('Admin',    'User',       'admin@pastimes.co.za',      '27700000001', MD5('adminpass'),   NULL,               'Gauteng',      'Johannesburg',    'admin',  'active',    0.00,  0);

-- ── tblAdmin ──────────────────────────────────────────────────────
CREATE TABLE `tblAdmin` (
  `admin_id`      INT NOT NULL AUTO_INCREMENT,
  `user_id`       INT NOT NULL,
  `access_level`  ENUM('super','standard') NOT NULL DEFAULT 'standard',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `uq_admin_user` (`user_id`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `tblUser` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- user_id 30 = admin@pastimes.co.za
INSERT INTO `tblAdmin` (user_id, access_level) VALUES (30, 'super');

-- ── tblWallet ─────────────────────────────────────────────────────
CREATE TABLE `tblWallet` (
  `wallet_id`         INT           NOT NULL AUTO_INCREMENT,
  `user_id`           INT           NOT NULL,
  `buyer_balance`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `seller_balance`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `pending_balance`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`wallet_id`),
  UNIQUE KEY `uq_wallet_user` (`user_id`),
  CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `tblUser` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblWallet` (user_id, buyer_balance, seller_balance, pending_balance) VALUES
(1,  1250.00,    0.00,    0.00),
(2,   800.00,    0.00,    0.00),
(3,  3000.00,    0.00,    0.00),
(4,     0.00,    0.00,    0.00),
(5,   450.00,    0.00,    0.00),
(6,  1100.00,    0.00,    0.00),
(7,   200.00,    0.00,    0.00),
(8,   950.00,    0.00,    0.00),
(9,  2200.00,    0.00,    0.00),
(10,  600.00,    0.00,    0.00),
(11,  750.00,    0.00,    0.00),
(12, 1800.00,    0.00,    0.00),
(13,  300.00,    0.00,    0.00),
(14, 1400.00,    0.00,    0.00),
(15,  500.00,  8200.00,  320.00),
(16,  200.00,  3100.00,  150.00),
(17,  800.00, 12500.00,  650.00),
(18,  100.00,  1800.00,   80.00),
(19,   50.00,   900.00,    0.00),
(20,  400.00,  4300.00,  200.00),
(21,  700.00,  6100.00,  400.00),
(22,  250.00, 18000.00, 1200.00),
(23,  350.00,  5200.00,  300.00),
(24,  900.00,  9800.00,  700.00),
(25,    0.00,   400.00,    0.00),
(26,    0.00,     0.00,    0.00),
(27,    0.00,     0.00,    0.00),
(28,    0.00,     0.00,    0.00),
(29,    0.00,     0.00,    0.00),
(30,    0.00,     0.00,    0.00);

-- ── tblListing ────────────────────────────────────────────────────
CREATE TABLE `tblListing` (
  `listing_id`        INT            NOT NULL AUTO_INCREMENT,
  `seller_id`         INT            NOT NULL,
  `title`             VARCHAR(200)   NOT NULL,
  `description`       TEXT           DEFAULT NULL,
  `category`          VARCHAR(100)   DEFAULT NULL,
  `sub_category`      VARCHAR(100)   DEFAULT NULL,
  `brand`             VARCHAR(100)   DEFAULT NULL,
  `condition_grade`   ENUM('new','like_new','good','fair','poor') NOT NULL DEFAULT 'good',
  `size`              VARCHAR(20)    DEFAULT NULL,
  `colour`            VARCHAR(50)    DEFAULT NULL,
  `price`             DECIMAL(10,2)  NOT NULL,
  `quantity`          INT            NOT NULL DEFAULT 1,
  `listing_type`      ENUM('p2p','curated') NOT NULL DEFAULT 'p2p',
  `listing_status`    ENUM('active','sold','draft','removed') NOT NULL DEFAULT 'active',
  `is_verified`       TINYINT(1)     NOT NULL DEFAULT 0,
  `created_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`listing_id`),
  CONSTRAINT `fk_listing_seller` FOREIGN KEY (`seller_id`) REFERENCES `tblUser` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- seller IDs: 15=Sarah, 16=Mpho, 17=Pieter, 18=Kagiso,
--             19=Keegan, 20=Bongani, 21=Nina, 22=Marcus, 23=Siya, 24=Vuyo
INSERT INTO `tblListing`
  (seller_id, title, description, category, sub_category, brand,
   condition_grade, size, colour, price, quantity, listing_type, listing_status, is_verified)
VALUES
(15, 'Vintage Levi 501 Jeans',         'Classic 90s straight-leg in dark wash, minimal wear.',                 'Women',       'Bottoms',    'Levis',               'like_new', '32',  'Indigo',       450.00, 1, 'p2p',     'active', 0),
(15, 'Zara Floral Midi Dress',          'Beautiful floral print, worn twice, excellent condition.',             'Women',       'Dresses',    'Zara',                'good',     'S',   'Multicolour',  280.00, 1, 'p2p',     'active', 0),
(17, 'Nike Air Max 90 Sneakers',        'White Nike Air Max, size 9, minimal wear.',                           'Shoes',       'Sneakers',   'Nike',                'like_new', '9',   'White',       1200.00, 1, 'p2p',     'active', 0),
(17, 'Louis Vuitton Neverfull Tote',    'Authenticated LV Neverfull MM in Damier Ebene, includes dustbag.',    'Bags',        'Totes',      'Louis Vuitton',       'good',     'OS',  'Brown',      18500.00, 1, 'curated', 'active', 1),
(15, 'H and M Oversized Camel Blazer',  'Camel oversized blazer, unworn with tags.',                           'Women',       'Outerwear',  'H and M',             'new',      'M',   'Camel',        150.00, 2, 'p2p',     'active', 0),
(16, 'Guess Black Handbag',             'Genuine Guess crossbody in black faux leather, good shape.',          'Bags',        'Crossbody',  'Guess',               'good',     'OS',  'Black',        650.00, 1, 'p2p',     'active', 0),
(16, 'Adidas Ultraboost 22',            'Triple white Ultraboost, size 8, worn 5 times.',                      'Shoes',       'Sneakers',   'Adidas',              'like_new', '8',   'White',        900.00, 1, 'p2p',     'active', 0),
(19, '90s Denim Jacket Vintage',        'True vintage 90s denim jacket, distressed finish.',                   'Men',         'Outerwear',  'Vintage',             'fair',     'L',   'Blue',         320.00, 1, 'p2p',     'active', 0),
(17, 'Chanel Classic Flap Bag',         'Small Chanel classic flap in black caviar leather, silver hardware.', 'Bags',        'Handbags',   'Chanel',              'good',     'OS',  'Black',      45000.00, 1, 'curated', 'active', 1),
(20, 'Woolworths Linen Trousers',       'Wide-leg linen trousers, stone colour, size 14, lightly worn.',       'Women',       'Bottoms',    'Woolworths',          'good',     '14',  'Stone',        180.00, 1, 'p2p',     'active', 0),
(21, 'Cotton On Graphic Tee Bundle x5', 'Five assorted Cotton On graphic tees, sizes M to L.',                 'Men',         'Tops',       'Cotton On',           'good',     'M',   'Various',      200.00, 1, 'p2p',     'active', 0),
(22, 'Gucci Marmont Belt',              'Gucci GG Marmont belt, black leather, size 85, authenticated.',       'Accessories', 'Belts',      'Gucci',               'like_new', '85',  'Black',       8500.00, 1, 'curated', 'active', 1),
(16, 'Jordan 1 Retro High OG',          'Chicago colourway, size 10, deadstock in box.',                       'Shoes',       'Sneakers',   'Jordan',              'new',      '10',  'Red and White',3200.00, 1, 'p2p',     'active', 0),
(24, 'Printed Ankara Dress',            'Handmade Ankara wrap dress, one size, vibrant print.',                'Women',       'Dresses',    'Handmade',            'new',      'OS',  'Multicolour',  420.00, 1, 'p2p',     'active', 0),
(23, 'New Balance 550 Cream',           'NB 550 in cream and green, size 11, worn once.',                      'Shoes',       'Sneakers',   'New Balance',         'like_new', '11',  'Cream',       1100.00, 1, 'p2p',     'active', 0),
(18, 'Puma Track Jacket Vintage',       'Vintage Puma track jacket, red and white, size L.',                   'Men',         'Outerwear',  'Puma',                'good',     'L',   'Red',          350.00, 1, 'p2p',     'active', 0),
(21, 'Burberry Plaid Scarf',            'Classic Burberry nova check scarf, authenticated.',                   'Accessories', 'Scarves',    'Burberry',            'like_new', 'OS',  'Camel',       4200.00, 1, 'curated', 'active', 1),
(20, 'Mr Price Mom Jeans',              'High-waist stone wash mom jeans, size 34, once worn.',                'Women',       'Bottoms',    'Mr Price',            'like_new', '34',  'Stone Wash',   120.00, 1, 'p2p',     'active', 0),
(17, 'Balenciaga Triple S Sneakers',    'Triple S in grey white and red, size 42, worn twice.',                'Shoes',       'Sneakers',   'Balenciaga',          'like_new', '42',  'Grey',       12000.00, 1, 'curated', 'active', 1),
(19, 'Levis Sherpa Trucker Jacket',     'Sherpa-lined trucker in indigo, size M, great condition.',            'Men',         'Outerwear',  'Levis',               'good',     'M',   'Indigo',       480.00, 1, 'p2p',     'active', 0),
(24, 'Nike Sportswear Hoodie',          'Fleece pullover hoodie, grey, size L, worn a few times.',             'Men',         'Tops',       'Nike',                'good',     'L',   'Grey',         320.00, 1, 'p2p',     'active', 0),
(15, 'Faithfull the Brand Dress',       'Kea ditsy floral sundress, size S, worn once.',                      'Women',       'Dresses',    'Faithfull the Brand', 'like_new', 'S',   'Floral',       680.00, 1, 'p2p',     'active', 0),
(16, 'Ray-Ban Wayfarer Sunglasses',     'Classic black Wayfarer, original case included.',                     'Accessories', 'Eyewear',    'Ray-Ban',             'like_new', 'OS',  'Black',        950.00, 1, 'p2p',     'active', 0),
(22, 'Hermes Oran Sandals',             'Hermes Oran sandals in gold leather, size 37, authenticated.',        'Shoes',       'Sandals',    'Hermes',              'good',     '37',  'Gold',        9800.00, 1, 'curated', 'active', 1),
(21, 'Linen Button-Up Shirt',           'White linen shirt, relaxed fit, size M, perfect for summer.',         'Women',       'Tops',       'Country Road',        'good',     'M',   'White',        160.00, 1, 'p2p',     'active', 0),
(20, 'Lacoste Polo Shirt',              'Navy Lacoste polo, size M, excellent condition.',                     'Men',         'Tops',       'Lacoste',             'like_new', 'M',   'Navy',         380.00, 1, 'p2p',     'active', 0),
(18, 'Tommy Hilfiger Cap',              'Classic Tommy logo cap, adjustable, barely worn.',                    'Accessories', 'Hats',       'Tommy Hilfiger',      'like_new', 'OS',  'Navy',         220.00, 1, 'p2p',     'active', 0),
(23, 'Vans Old Skool Checkerboard',     'Black and white checkerboard, size 9, good condition.',               'Shoes',       'Sneakers',   'Vans',                'good',     '9',   'Checkerboard', 550.00, 1, 'p2p',     'active', 0),
(24, 'Shweshwe Wrap Skirt',             'Handmade traditional shweshwe print wrap skirt, size M.',             'Women',       'Bottoms',    'Handmade',            'new',      'M',   'Blue Print',   280.00, 2, 'p2p',     'active', 0),
(17, 'Prada Re-Edition 2005 Bag',       'Mini nylon Prada Re-Edition, black, includes dustbag, authenticated.','Bags',        'Mini Bags',  'Prada',               'good',     'OS',  'Black',      16500.00, 1, 'curated', 'active', 1);

-- ── tblListingPhoto ───────────────────────────────────────────────
CREATE TABLE `tblListingPhoto` (
  `photo_id`      INT          NOT NULL AUTO_INCREMENT,
  `listing_id`    INT          NOT NULL,
  `photo_url`     VARCHAR(255) NOT NULL,
  `is_cover`      TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`photo_id`),
  CONSTRAINT `fk_photo_listing` FOREIGN KEY (`listing_id`) REFERENCES `tblListing` (`listing_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblListingPhoto` (listing_id, photo_url, is_cover) VALUES
(1,  'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600', 1),
(2,  'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600', 1),
(3,  'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600',    1),
(4,  'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600',    1),
(5,  'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600', 1),
(6,  'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600',    1),
(7,  'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600',    1),
(8,  'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=600',    1),
(9,  'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600',    1),
(10, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600',    1);

-- ── tblAorder ─────────────────────────────────────────────────────
CREATE TABLE `tblAorder` (
  `order_id`          INT            NOT NULL AUTO_INCREMENT,
  `buyer_id`          INT            NOT NULL,
  `seller_id`         INT            NOT NULL,
  `listing_id`        INT            NOT NULL,
  `price_paid`        DECIMAL(10,2)  NOT NULL,
  `delivery_method`   VARCHAR(100)   DEFAULT NULL,
  `delivery_fee`      DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `delivery_address`  TEXT           DEFAULT NULL,
  `payment_method`    VARCHAR(50)    DEFAULT NULL,
  `payment_status`    ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status`      ENUM('placed','confirmed','shipped','delivered','cancelled','disputed') NOT NULL DEFAULT 'placed',
  `created_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  CONSTRAINT `fk_order_buyer`   FOREIGN KEY (`buyer_id`)   REFERENCES `tblUser`    (`user_id`),
  CONSTRAINT `fk_order_seller`  FOREIGN KEY (`seller_id`)  REFERENCES `tblUser`    (`user_id`),
  CONSTRAINT `fk_order_listing` FOREIGN KEY (`listing_id`) REFERENCES `tblListing` (`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblAorder`
  (buyer_id, seller_id, listing_id, price_paid, delivery_method, delivery_fee,
   delivery_address, payment_method, payment_status, order_status)
VALUES
(1,  15,  1,   450.00, 'Pargo Pickup Point',    65.00, '14 Rockey St, Yeoville, Johannesburg, 2198', 'PayFast', 'paid', 'delivered'),
(2,  17,  3,  1200.00, 'Door-to-Door Courier',  95.00, '7 Church St, Pretoria, 0002',                'PayFast', 'paid', 'shipped'),
(3,  16,  6,   650.00, 'Pargo Pickup Point',    65.00, 'Sandton City, Sandton, 2196',                'Wallet',  'paid', 'delivered'),
(5,  15,  2,   280.00, 'PostNet to PostNet',    75.00, '3 Main Rd, Pietermaritzburg, 3201',          'EFT',     'paid', 'delivered'),
(6,  17,  4, 18500.00, 'Door-to-Door Courier',  95.00, '22 Long St, Cape Town, 8001',                'PayFast', 'paid', 'confirmed'),
(1,  16,  7,   900.00, 'Pargo Pickup Point',    65.00, '14 Rockey St, Yeoville, Johannesburg, 2198', 'Wallet',  'paid', 'placed'),
(9,  22, 12,  8500.00, 'Door-to-Door Courier',  95.00, '10 Park Ave, Johannesburg, 2001',            'PayFast', 'paid', 'delivered'),
(12, 23, 15,  1100.00, 'PostNet to PostNet',    75.00, '5 Long Ave, Centurion, 0157',                'EFT',     'paid', 'shipped');

-- ── tblMessage ────────────────────────────────────────────────────
CREATE TABLE `tblMessage` (
  `message_id`    INT          NOT NULL AUTO_INCREMENT,
  `sender_id`     INT          NOT NULL,
  `receiver_id`   INT          NOT NULL,
  `listing_id`    INT          DEFAULT NULL,
  `message_text`  TEXT         NOT NULL,
  `is_read`       TINYINT(1)   NOT NULL DEFAULT 0,
  `sent_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  CONSTRAINT `fk_msg_sender`   FOREIGN KEY (`sender_id`)   REFERENCES `tblUser` (`user_id`),
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `tblUser` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblMessage` (sender_id, receiver_id, listing_id, message_text, is_read) VALUES
(1,  15,  1, 'Hi! Is this still available? Would you take R400?',           0),
(15,  1,  1, 'Yes still available! Lowest I can go is R420.',               1),
(2,  17,  3, 'What condition are the soles on the Nike Air Max?',           0),
(17,  2,  3, 'The soles are in great shape, barely any wear at all.',       0),
(3,  16,  6, 'Does the Guess bag come with the original dust bag?',         0),
(6,  17,  4, 'Is the Louis Vuitton bag authenticated with a certificate?',  0),
(17,  6,  4, 'Yes it comes with authentication card and original receipt.', 0),
(9,  22, 12, 'Is the Gucci belt still in original packaging?',              0);

-- ── tblReview ─────────────────────────────────────────────────────
CREATE TABLE `tblReview` (
  `review_id`         INT          NOT NULL AUTO_INCREMENT,
  `order_id`          INT          NOT NULL,
  `reviewer_id`       INT          NOT NULL,
  `reviewed_user_id`  INT          NOT NULL,
  `rating`            INT          NOT NULL,
  `review_text`       TEXT         DEFAULT NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  CONSTRAINT `fk_review_order`    FOREIGN KEY (`order_id`)         REFERENCES `tblAorder` (`order_id`),
  CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`)      REFERENCES `tblUser`   (`user_id`),
  CONSTRAINT `fk_review_subject`  FOREIGN KEY (`reviewed_user_id`) REFERENCES `tblUser`   (`user_id`),
  CONSTRAINT `chk_rating`         CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblReview` (order_id, reviewer_id, reviewed_user_id, rating, review_text) VALUES
(1, 1,  15, 5, 'Amazing seller! Item exactly as described, fast shipping. Highly recommend!'),
(2, 2,  17, 5, 'Pieter was fantastic. Sneakers arrived quickly and in perfect condition.'),
(3, 3,  16, 4, 'Good communication, item well packaged. Bag was as described.'),
(4, 5,  15, 5, 'Sarah is a top seller. Dress was even better in person. Will buy again!'),
(7, 9,  22, 5, 'Marcus authenticated the Gucci belt perfectly. Arrived in 2 days!'),
(8, 12, 23, 4, 'Great sneakers, exactly as described. Delivery was a bit slow but worth it.');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END myClothingStore.sql
-- 8 tables | tblUser:30 tblAdmin:1 tblWallet:30 tblListing:30
--           tblListingPhoto:10 tblAorder:8 tblMessage:8 tblReview:6
-- ============================================================
