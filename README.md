# 🔒 Secrets Vault

A secure PHP web application for storing encrypted sensitive information with user authentication.

## Features

- **Secure Authentication**: Complete login/registration system with password hashing
- **End-to-End Encryption**: All secrets are encrypted with AES-256-CBC before storage
- **CRUD Operations**: Add, view, edit, and delete your encrypted secrets
- **Modern UI**: Clean, responsive interface built with Tailwind CSS
- **Security-First Design**:
  - Protection against SQL injection, XSS, and CSRF attacks
  - Prepared statements (PDO) for all database operations
  - Secure session management
  - Content Security Policy implementation

## Requirements

- PHP 7.4+ (PHP 8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache web server with mod_rewrite enabled
- OpenSSL PHP extension

## Installation

1. **Clone the repository**
   ```
   git clone https://github.com/K-Lakshan/Secrets-Vault.git
   cd Secrets-Vault
   ```

2. **Set up the database**
   - Create a new MySQL database
   - Import the database schema from `database/schema.sql`
   ```
   mysql -u username -p your_database_name < database/schema.sql
   ```

3. **Configure the application**
   - Update the database credentials and site key ```config.php```
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'secrets_vault');
   define('DB_USER', 'your_username'); 
   define('DB_PASS', 'your_password');
   define('SITE_KEY', 'your_random_secure_key'); // Generate a secure random string
   ```

5. **Configure your web server**
   - Ensure the document root points to the application directory
   - Make sure mod_rewrite is enabled and .htaccess is working

6. **Access the application**
   - Navigate to `http://your-domain.com/` in your web browser
   - Register a new account and start storing secrets!

## Security Recommendations

- Always use HTTPS in production environments
- Regularly update your PHP version and dependencies
- Consider implementing:
  - Two-factor authentication
  - IP-based restrictions for sensitive operations
  - Login attempt rate limiting
  - Regular database backups (encrypted)

## How It Works

1. **User Authentication**:
   - User registers with username, email, and password
   - Password is securely hashed using PHP's `password_hash()` function
   - A unique encryption key is generated for each user

2. **Encryption Process**:
   - Each user has their own encryption key stored encrypted in the database
   - When a secret is saved, it's encrypted using the user's key with a unique IV
   - The encrypted content and IV are stored separately in the database

3. **Decryption Process**:
   - When viewing a secret, the system retrieves the encrypted content and IV
   - The content is decrypted using the user's key and displayed only to them

## Project Structure

```
secrets-vault/
├── config.php             # Database & application configuration
├── index.php              # Entry point redirecting to login/dashboard
├── login.php              # User authentication
├── register.php           # New user registration
├── dashboard.php          # Main secret listing page
├── add_secret.php         # Create new secrets
├── view_secret.php        # View decrypted secret
├── edit_secret.php        # Modify existing secrets
├── delete_secret.php      # Remove secrets
├── logout.php             # End user session
└── .htaccess              # Apache security configurations
```

## Customization

- **Styling**: The application uses Tailwind CSS via CDN. You can customize the appearance by modifying the class names or installing Tailwind locally.
- **Additional Fields**: You can extend the secrets table to include additional fields like categories, tags, or expiration dates.
- **Encryption**: The default encryption is AES-256-CBC, but you can update the `encrypt_data()` and `decrypt_data()` functions to use different algorithms if needed.


## Acknowledgments

- Built with [Tailwind CSS](https://tailwindcss.com/)
- Encryption powered by PHP's OpenSSL functions

