# Rameshwar Traditional Wear Rental System

A complete PHP-based web application for renting traditional Indian wear including Sherwanis, Kurtas, Blazers, and Indo-Western outfits.

## Features

### User Features
- **User Registration & Login**: Secure user authentication system
- **Product Browsing**: Dynamic product catalog with advanced filtering
- **Smart Filtering**: Filter by category, price range, size, and color
- **Product Details**: Modal preview with image carousel
- **Booking System**: Complete rental booking with date selection
- **Payment Options**: Cash on Delivery (COD) and UPI payment
- **Booking Management**: View booking history and status
- **User Profile**: Manage personal information and password
- **Feedback System**: Leave reviews and ratings after rental

### Admin Features
- **Admin Dashboard**: Overview of system statistics
- **Product Management**: Add, edit, and delete products
- **Category Management**: Manage product categories
- **Booking Management**: View and manage all bookings
- **User Management**: View and manage registered users
- **Inventory Tracking**: Monitor stock levels and low stock alerts
- **Payment Tracking**: Monitor payment status and transactions
- **Feedback Management**: View and respond to customer feedback

### Technical Features
- **Responsive Design**: Works on all devices (mobile, tablet, desktop)
- **AJAX Powered**: Dynamic filtering without page reloads
- **Secure**: PDO prepared statements, input validation, XSS protection
- **Modern UI**: Bootstrap 5 with custom styling
- **Database Driven**: MySQL with proper relationships and indexing

## Installation Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8.0+)
- Web browser
- Text editor (optional)

### Step 1: Download and Extract
1. Download the `rtwrs_web` folder
2. Extract/copy the folder to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\rtwrs_web\
   ```

### Step 2: Database Setup
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
4. Create a new database named `rtwrs`
5. Import the database schema:
   - Click on the `rtwrs` database
   - Go to "Import" tab
   - Select the file: `rtwrs_web/database/rtwrs_database.sql`
   - Click "Go" to import

### Step 3: Database Configuration
1. Open `rtwrs_web/config/db.php`
2. Update database credentials if needed:
   ```php
   $host = 'localhost';
   $db   = 'rtwrs';
   $user = 'root';        // Your MySQL username
   $pass = '';            // Your MySQL password (usually empty for XAMPP)
   ```

### Step 4: File Permissions (Linux/Mac only)
Make the uploads directory writable:
```bash
chmod 755 rtwrs_web/uploads/
```

### Step 5: Access the Application
- **User Website**: `http://localhost/rtwrs_web/user/index.php`
- **Admin Panel**: `http://localhost/rtwrs_web/admin/login.php`

## Default Login Credentials

### Admin Access
- **URL**: `http://localhost/rtwrs_web/admin/login.php`
- **Email**: `admin@rameshwar.com`
- **Password**: `password`

### Sample User Accounts
- **Email**: `john@example.com` | **Password**: `password`
- **Email**: `jane@example.com` | **Password**: `password`
- **Email**: `raj@example.com` | **Password**: `password`

## Project Structure

```
rtwrs_web/
├── admin/                  # Admin panel pages
│   ├── includes/          # Admin header/footer
│   ├── dashboard.php      # Admin dashboard
│   ├── login.php          # Admin login
│   └── manage-*.php       # Management pages
├── api/                   # Backend API handlers
│   ├── filter-handler.php # Product filtering
│   ├── rent-handler.php   # Booking processing
│   └── logout.php         # Logout handler
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Product images
├── config/               # Configuration files
│   └── db.php            # Database connection
├── database/             # Database schema
│   └── rtwrs_database.sql # Complete database structure
├── includes/             # Shared components
│   ├── header.php        # Common header
│   └── footer.php        # Common footer
├── uploads/              # File upload directory
└── user/                 # User-facing pages
    ├── index.php         # Homepage
    ├── products.php      # Product catalog
    ├── rent.php          # Booking form
    ├── login.php         # User login
    ├── register.php      # User registration
    ├── profile.php       # User profile
    └── my-bookings.php   # Booking history
```

## Adding Product Images

1. Add your product images to the `assets/images/` folder
2. Update the database `products` table with correct image paths
3. Use comma-separated values for multiple images per product

Example:
```sql
UPDATE products SET image_url = 'assets/images/sherwani1.jpg,assets/images/sherwani1_2.jpg' WHERE id = 1;
```

## Customization

### Styling
- Edit `assets/css/style.css` for main styling
- Edit `assets/css/admin.css` for admin panel styling
- Change colors in CSS variables at the top of `style.css`

### Branding
- Replace logo in header files
- Update company name and colors
- Modify hero section text in `user/index.php`

### Features
- Add new product categories in the database
- Extend booking form with additional fields
- Add new payment methods
- Implement email notifications

## Security Notes

- Change default admin password after installation
- Use strong passwords for all accounts
- Enable HTTPS in production
- Regular database backups
- Keep PHP and MySQL updated

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL service is running
   - Verify database credentials in `config/db.php`
   - Ensure `rtwrs` database exists

2. **Images Not Loading**
   - Check file paths in database
   - Ensure images exist in `assets/images/`
   - Verify file permissions

3. **AJAX Filters Not Working**
   - Check browser console for JavaScript errors
   - Ensure all JS files are loaded correctly
   - Verify API endpoints are accessible

4. **Login Issues**
   - Clear browser cache and cookies
   - Check session configuration
   - Verify user exists in database

### Getting Help

If you encounter issues:
1. Check the browser console for errors
2. Check PHP error logs
3. Verify database structure matches the provided schema
4. Ensure all files are properly uploaded

## Database Schema

The application uses the following main tables:
- `categories` - Product categories
- `products` - Rental items
- `users` - Customer accounts
- `bookings` - Rental bookings
- `payments` - Payment records
- `feedback` - Customer reviews
- `admin_notifications` - System notifications

## License

This project is provided as-is for educational and commercial use.

---

**Developed for Rameshwar Traditional Wear**
Version 1.0 | 2024
