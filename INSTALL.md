# EMP Installation Guide

## Quick Fix for "your-domain.com/install" Redirect Issue

If you're getting redirected to `http://ww17.your-domain.com/install`, follow these steps:

### Step 1: Database Configuration
```bash
cp application/config/database.php.example application/config/database.php
```

Edit `application/config/database.php` with your database credentials:
```php
$db['default'] = array(
    'hostname' => 'localhost',
    'username' => 'your_db_username',
    'password' => 'your_db_password',
    'database' => 'your_db_name',
    // ... other settings
);
```

### Step 2: Import Database
```bash
mysql -u your_username -p your_database_name < sql/emp.sql
```

### Step 3: System Configuration
```bash
cp application/config/config.php.example application/config/config.php
```

Edit `application/config/config.php`:
1. Set your base URL (or leave auto-detect)
2. Generate encryption keys:
   - `$config['encryption_key']` - 32 character string
   - `$config['jwt_key']` - secure random string
3. **IMPORTANT**: Set `$config['installed'] = TRUE;`

### Step 4: Set Permissions
```bash
chmod -R 755 uploads/ application/logs/
```

### Step 5: Access System
- Visit your domain
- Default admin login will be in the database
- Complete any remaining setup

## Why This Happens

The system checks `$config['installed']` in `application/core/MY_Controller.php`. If it's `FALSE` or missing, it redirects to the install page. Setting it to `TRUE` after proper database setup fixes this issue.

## Default Admin Access

Check the `login_credential` table in your database for default admin credentials, or create a new admin user through the database.