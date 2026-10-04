#!/usr/bin/env bash
# ==============================================================================
# Examination Duty Portal - Turnkey Setup Script for GCP Always Free VM (Ubuntu)
# ==============================================================================
set -e

# Configuration Variables
DB_NAME="examination_portal"
DB_USER="exam_user"
DB_PASS="ExamPass@2026_Secure"
APP_DIR="/var/www/examination-portal"

echo "--------------------------------------------------------"
echo ">>> [1/7] Updating system packages..."
echo "--------------------------------------------------------"
export DEBIAN_FRONTEND=noninteractive
sudo apt-get update -y && sudo apt-get upgrade -y
sudo apt-get install -y curl wget git unzip zip software-properties-common ufw

echo "--------------------------------------------------------"
echo ">>> [2/7] Configuring 2GB Swap (Essential for 1GB RAM VM)..."
echo "--------------------------------------------------------"
if [ ! -f /swapfile ]; then
    sudo fallocate -l 2G /swapfile
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
    echo "vm.swappiness=10" | sudo tee -a /etc/sysctl.conf
    echo "Swap created and enabled successfully."
else
    echo "Swapfile already exists. Skipping."
fi

echo "--------------------------------------------------------"
echo ">>> [3/7] Installing PHP 8.3 & Required Extensions..."
echo "--------------------------------------------------------"
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update -y
sudo apt-get install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl \
    php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl

# Adjust PHP memory and upload limits for Excel/PDF exports
sudo sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/upload_max_filesize = .*/upload_max_filesize = 32M/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/post_max_size = .*/post_max_size = 32M/' /etc/php/8.3/fpm/php.ini
sudo systemctl restart php8.3-fpm

echo "--------------------------------------------------------"
echo ">>> [4/7] Installing Composer..."
echo "--------------------------------------------------------"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

echo "--------------------------------------------------------"
echo ">>> [5/7] Installing & Configuring MariaDB/MySQL..."
echo "--------------------------------------------------------"
sudo apt-get install -y mariadb-server
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Create Database and User
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mariadb -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mariadb -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
sudo mariadb -e "FLUSH PRIVILEGES;"
echo "Database '${DB_NAME}' and user '${DB_USER}' configured."

echo "--------------------------------------------------------"
echo ">>> [6/7] Installing & Configuring Nginx..."
echo "--------------------------------------------------------"
sudo apt-get install -y nginx

# Setup Nginx Virtual Host
sudo tee /etc/nginx/sites-available/examination-portal > /dev/null <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name _;
    root /var/www/examination-portal/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;

    client_max_body_size 32M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -sf /etc/nginx/sites-available/examination-portal /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx

echo "--------------------------------------------------------"
echo ">>> [7/7] Preparing Web Directory..."
echo "--------------------------------------------------------"
sudo mkdir -p ${APP_DIR}
sudo chown -R $USER:www-data ${APP_DIR}

echo ""
echo "========================================================"
echo ">>> VM Server Environment Ready!"
echo "========================================================"
echo "Next Steps:"
echo "1. Upload or git-clone your project into: ${APP_DIR}"
echo "2. Copy .env.example to .env and configure DB credentials:"
echo "   DB_DATABASE=${DB_NAME}"
echo "   DB_USERNAME=${DB_USER}"
echo "   DB_PASSWORD=${DB_PASS}"
echo "3. Run deployment commands:"
echo "   cd ${APP_DIR}"
echo "   composer install --no-dev --optimize-autoloader"
echo "   php artisan key:generate --force"
echo "   php artisan migrate --seed --force"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo "   sudo chown -R www-data:www-data storage bootstrap/cache"
echo "   sudo chmod -R 775 storage bootstrap/cache"
echo "========================================================"
