# Enable HTTPS for Localhost Development

## Option 1: XAMPP SSL Setup (Easiest)

### Step 1: Enable SSL Module in XAMPP
1. Open XAMPP Control Panel
2. Click "Config" next to Apache
3. Select "httpd.conf"
4. Find this line and uncomment it (remove the #):
   ```
   #Include conf/extra/httpd-ssl.conf
   ```
   Change to:
   ```
   Include conf/extra/httpd-ssl.conf
   ```

5. Find this line and uncomment it:
   ```
   #LoadModule ssl_module modules/mod_ssl.so
   ```
   Change to:
   ```
   LoadModule ssl_module modules/mod_ssl.so
   ```

### Step 2: Configure SSL
1. Open `C:\xampp\apache\conf\extra\httpd-ssl.conf`
2. Find the VirtualHost section and update it:
   ```apache
   <VirtualHost _default_:443>
   DocumentRoot "C:/xampp/htdocs"
   ServerName localhost:443
   SSLEngine on
   SSLCertificateFile "conf/ssl.crt/server.crt"
   SSLCertificateKeyFile "conf/ssl.key/server.key"
   </VirtualHost>
   ```

### Step 3: Generate SSL Certificate
1. Open Command Prompt as Administrator
2. Navigate to XAMPP Apache bin directory:
   ```cmd
   cd C:\xampp\apache\bin
   ```
3. Generate certificate:
   ```cmd
   openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ..\conf\ssl.key\server.key -out ..\conf\ssl.crt\server.crt
   ```
4. Fill in the certificate details (use "localhost" for Common Name)

### Step 4: Restart Apache
1. Stop Apache in XAMPP
2. Start Apache again
3. You should now be able to access: https://localhost/donation/

## Option 2: Simple Development Solution (Recommended)

For development purposes, you can simply:

1. **Ignore the "not secure" warning** - it's normal for localhost HTTP
2. **Use HTTP for development** - PayPal sandbox works fine with HTTP
3. **Use HTTPS only in production** - when you deploy to a real server

## Option 3: Disable HTTPS Enforcement for Development

Update your payment configuration to allow HTTP in development:

```php
// In dist/includes/payment_config.php
const SECURITY_CONFIG = [
    'require_https' => false, // Set to false for development
    // ... other settings
];
```

## Browser Security Override

If you want to test with HTTPS but get certificate warnings:

1. **Chrome/Edge**: Click "Advanced" → "Proceed to localhost (unsafe)"
2. **Firefox**: Click "Advanced" → "Accept the Risk and Continue"

## Production HTTPS

For production deployment:
1. **Get SSL certificate** from Let's Encrypt (free) or your hosting provider
2. **Configure your web server** (Apache/Nginx) with the certificate
3. **Set require_https = true** in your configuration
4. **Test all payment flows** with real HTTPS

## Quick Fix for Current Issue

For now, to continue testing:

1. **Use HTTP** (http://localhost/donation/make-donation.php)
2. **Ignore the "not secure" warning** - it's normal for development
3. **PayPal will work fine** with HTTP in sandbox mode

The "not secure" warning doesn't affect functionality - it's just a browser warning about HTTP vs HTTPS.
