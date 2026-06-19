================================================================================
                        PASTIMES - South Africa's Fashion Marketplace
================================================================================

PROJECT OVERVIEW
--------------------------------------------------------------------------------
Pastimes is a pre-owned fashion marketplace where users can buy and sell 
clothing, accessories, and verified luxury items. The platform includes:
- Shopping cart with checkout
- User registration and login
- Seller request system
- Admin management panel
- Messaging between users
- Product listings with image upload

TECHNICAL REQUIREMENTS
--------------------------------------------------------------------------------
- XAMPP / WAMP / MAMP (Apache + MySQL + PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- GD Library (for image processing)
- Web browser (Chrome, Firefox, Edge)

DATABASE SETUP
--------------------------------------------------------------------------------
Option 1: Run the setup script (Recommended)
1. Open your browser and go to: http://localhost/pastimes/loadClothingStore.php
2. The script will create the database and seed it with sample data
3. Wait for the "All tables created and seeded successfully" message

Option 2: Manual import
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named: ClothingStore
3. Click the "Import" tab
4. Select the file: myClothingStore.sql
5. Click "Go" to import

DEFAULT LOGIN CREDENTIALS
--------------------------------------------------------------------------------
┌─────────────┬──────────────────────────┬──────────────────┐
│ Role        │ Email                    │ Password         │
├─────────────┼──────────────────────────┼──────────────────┤
│ Admin       │ admin@pastimes.co.za     │ adminpass        │
│ Seller      │ s.nkosi@gmail.com        │ seller456        │
│ Buyer       │ j.doe@abc.co.za          │ password123      │
└─────────────┴──────────────────────────┴──────────────────┘

FEATURES IMPLEMENTED
--------------------------------------------------------------------------------
✅ User registration with email verification
✅ User login/logout with session management
✅ Shopping cart (add, remove, update quantities)
✅ Checkout process with delivery details
✅ Product browsing with search and filters
✅ Seller request system (apply to become a seller)
✅ Admin approval/rejection of seller requests
✅ Admin user management (add/edit/delete users)
✅ Admin product listing management (add/edit/delete listings)
✅ Admin order management (update order status)
✅ Messaging system (buyer-seller, admin messages)
✅ Contact form with database storage
✅ Image upload for product listings (with thumbnails)
✅ Responsive design for mobile and desktop
✅ Visually appealing UI with gold/dark theme

FILE STRUCTURE
--------------------------------------------------------------------------------
pastimes/
├── admin/                      # Admin panel files
│   ├── dashboard.php           # Admin dashboard with user management
│   ├── listings.php            # Admin product listing management
│   ├── messages.php            # Admin message center
│   ├── orders.php              # Admin order management
│   ├── seller_requests.php     # Admin seller request management
│   └── login.php               # Admin login page
├── css/
│   └── style.css               # Main stylesheet
├── includes/
│   ├── cart.php                # Shopping cart functions
│   ├── DBConn.php              # Database connection
│   ├── footer.php              # Site footer
│   ├── header.php              # Site header with navigation
│   └── session_check.php       # Session management functions
├── uploads/                    # Product images
│   └── listings/
│       ├── full/               # Original uploaded images
│       └── thumbnails/         # Resized thumbnails
├── index.php                   # Homepage
├── browse.php                  # Product browsing with filters
├── cart.php                    # Shopping cart page
├── checkout.php                # Checkout process
├── contact.php                 # Contact form with database storage
├── dashboard.php               # User dashboard
├── login.php                   # User login
├── logout.php                  # Logout
├── message-seller.php          # Message a seller
├── my-listings.php             # Seller's listings
├── orders.php                  # My orders
├── profile.php                 # User profile
├── register.php                # User registration
├── seller_request.php          # Apply to become a seller
├── sell.php                    # List an item for sale
├── wishlist.php                # Wishlist
├── myClothingStore.sql         # Database schema and seed data
├── loadClothingStore.php       # Automated database setup script
├── userData.txt                # Sample user data
└── README.txt                  # This file

HOW TO USE THE APPLICATION
--------------------------------------------------------------------------------
For Buyers:
1. Register or login to your account
2. Browse products on the homepage or browse page
3. Search or filter by category, brand, or price
4. Add items to your cart
5. Go to cart to review and update quantities
6. Proceed to checkout and enter delivery details
7. Place your order

For Sellers:
1. Login to your account
2. Go to "Become a Seller" in the sidebar
3. Fill in shop details and submit request
4. Wait for admin approval
5. Once approved, go to "Sell" to list items
6. Manage your listings in "My Listings"

For Admins:
1. Login using admin credentials
2. Go to Admin Dashboard
3. Manage users (add/edit/delete)
4. Manage listings (add/edit/delete)
5. Review seller requests (approve/reject)
6. View and reply to messages
7. Manage orders (update status)

TROUBLESHOOTING
--------------------------------------------------------------------------------
Issue: "Image upload not working"
Solution: Enable GD extension in php.ini (uncomment extension=gd)

Issue: "Foreign key constraint error"
Solution: Run the SQL in myClothingStore.sql to reset the database

Issue: "Permission denied for uploads/"
Solution: Set permissions to 755 (Linux/Mac) or Full Control (Windows)

Issue: "Database connection failed"
Solution: Check your MySQL is running and credentials in DBConn.php

CONTACT
--------------------------------------------------------------------------------
For support or queries:
Email: support@pastimes.co.za

================================================================================
                        END OF README
================================================================================