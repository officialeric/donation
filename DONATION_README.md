# Orphanage Donation Platform

A web-based donation platform that allows visitors to browse orphanages and make donations to support children in need. The platform features a public interface for donors and an admin panel for managing orphanages.

## Features

### For Visitors/Donors:
- Browse all orphanages without registration
- View detailed information about each orphanage
- Register an account to make donations
- Secure donation processing
- Donation history tracking

### For Administrators:
- Manage orphanages (add, edit, delete)
- View donation reports
- User management
- Dashboard with statistics

## Installation

### Prerequisites:
- XAMPP (or similar local server environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions:

1. **Clone/Download the project** to your XAMPP htdocs folder:
   ```
   c:\xampp\htdocs\donation\
   ```

2. **Import the database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `donation`
   - Import the `donation_schema.sql` file

3. **Configure database connection:**
   - Open `dist/includes/connection.php`
   - Update database credentials if needed (default: root user, no password)

4. **Start XAMPP services:**
   - Start Apache and MySQL services

5. **Access the application:**
   - Visit: http://localhost/donation/

### Default Admin Account:
- **Email:** admin@donation.com
- **Password:** password

## Usage

### For Visitors:
1. Visit the homepage to see all available orphanages
2. Click "View Details" to learn more about an orphanage
3. Click "Register to Donate" to create an account
4. After registration, login and make donations

### For Administrators:
1. Login with admin credentials
2. Access the admin dashboard
3. Manage orphanages through the admin panel
4. View donation reports and statistics

## File Structure

```
donation/
├── index.php              # Public homepage with orphanage listings
├── login.php              # User login page
├── register.php           # User registration page
├── orphanage-details.php  # Orphanage detail view
├── make-donation.php      # Donation form
├── donation-success.php   # Donation confirmation
├── donation_schema.sql    # Database schema
├── dist/
│   ├── includes/
│   │   ├── connection.php     # Database connection
│   │   ├── auth.php          # Authentication handling
│   │   ├── process-donation.php # Donation processing
│   │   └── process-orphanage.php # Orphanage management
│   └── pages/
│       ├── admin/            # Admin panel pages
│       └── donor/            # Donor dashboard pages
└── README.md
```

## Security Features

- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- Session management for user authentication
- Role-based access control (admin/donor)
- Input validation and sanitization

## Technologies Used

- **Backend:** PHP, MySQL
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **UI Framework:** AdminLTE 4
- **Icons:** Bootstrap Icons

## Database Schema

The platform uses the following main tables:
- `users` - User accounts (donors and admins)
- `orphanages` - Orphanage information
- `donations` - Donation records

## Donation Flow

1. **Public Access:** Visitors can browse orphanages without registration
2. **Registration Required:** To donate, users must register an account
3. **Login Redirect:** After registration, users are redirected to complete their donation
4. **Payment Processing:** Donations are processed and recorded in the database
5. **Confirmation:** Users receive confirmation of successful donations

## Admin Features

- **Orphanage Management:** Add, edit, and delete orphanages
- **User Management:** View and manage user accounts
- **Donation Reports:** Track all donations and generate reports
- **Dashboard:** Overview of platform statistics

## License

This project is open source and available under the [MIT License](LICENSE).
