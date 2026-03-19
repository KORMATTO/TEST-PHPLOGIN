============================
REMOTE SERVER SETUP GUIDE
For: SQL + PHP Web Application
============================

# 1. Server Requirements
- OS: Ubuntu 22.04+ or Debian 12+
- Web server: Apache2 or Nginx
- PHP: Version 8.1 or later
- Database: MySQL or MariaDB
- SSH access with sudo privileges

# 2. Update Server
sudo apt update && sudo apt upgrade -y

# 3. Install Dependencies
sudo apt install apache2 php libapache2-mod-php php-mysql mysql-server unzip git -y

# 4. Secure MySQL Installation
sudo mysql_secure_installation

# 5. Create Database and User
sudo mysql -u root -p
> CREATE DATABASE myapp_db;
> CREATE USER 'myapp_user'@'%' IDENTIFIED BY 'StrongPasswordHere';
> GRANT ALL PRIVILEGES ON myapp_db.* TO 'myapp_user'@'%';
> FLUSH PRIVILEGES;
> EXIT;

# 6. Upload Files (PHP + SQL)
Use scp or SFTP to upload:
- PHP project files → /var/www/html/
- SQL dump → /var/www/html/db_init.sql

Example:
scp -r ./myapp/* user@your-server-ip:/var/www/html/

# 7. Import Database
mysql -u myapp_user -p myapp_db < /var/www/html/db_init.sql

# 8. Configure PHP
sudo nano /etc/php/8.1/apache2/php.ini
- Set: display_errors = On
- Set: upload_max_filesize = 64M
- Set: post_max_size = 64M
Save & exit.

# 9. Test PHP
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php

Then, open http://your-server-ip/info.php to confirm PHP is running.

# 10. Restart Services
sudo systemctl restart apache2
sudo systemctl enable apache2 mysql

# 11. Firewall Setup (Optional)
sudo ufw allow 'Apache Full'
sudo ufw allow 3306/tcp
sudo ufw enable

# 12. Verify Setup
- Open your IP/domain in a browser.
- Check database connectivity from PHP.
- Log errors via /var/log/apache2/error.log if needed.

============================
SERVER HOT SETUP COMPLETE
============================
Reports bugs only here!
