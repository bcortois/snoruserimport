#!/usr/bin/env bash

# Use single quotes instead of double quotes to make it work with special-character passwords
PASSWORD='vagrant'
PROJECTFOLDER='snoruserimport'

# create project folder
sudo mkdir "/var/www/html/${PROJECTFOLDER}"

# update / upgrade
sudo apt-get update
sudo apt-get -y upgrade

# install apache 2.5 and php 5.5
sudo apt-get install -y apache2
sudo apt-get install -y php5

# install mysql and give password to installer
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $PASSWORD"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $PASSWORD"
sudo apt-get -y install mysql-server
sudo apt-get install php5-mysql

# install phpmyadmin and give password(s) to installer
# for simplicity I'm using the same password for mysql and phpmyadmin
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $PASSWORD"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password $PASSWORD"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $PASSWORD"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
sudo apt-get -y install phpmyadmin

# setup hosts file
VHOST=$(cat <<EOF
<VirtualHost *:80>
    DocumentRoot "/var/www/html/${PROJECTFOLDER}"
    <Directory "/var/www/html/${PROJECTFOLDER}">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF
)
echo "${VHOST}" > /etc/apache2/sites-available/000-default.conf

# enable mod_rewrite
sudo a2enmod rewrite

# restart apache
service apache2 restart

# install git
sudo apt-get -y install git

# install Composer
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# installeer project dependencies met behulp van composer
composer install -d "/var/www/html/${PROJECTFOLDER}"

# installatie van de ldap module voor php, bron: https://stackoverflow.com/questions/42379108/enable-ldap-module-in-laravel-homestead
sudo apt-get install php5-ldap

# installatie van de cURL module voor php.
sudo apt-get install php5-curl

# install xdebug
# juiste manier? deze post bevat een cli installatie in het script:
# https://stackoverflow.com/questions/40641526/how-to-automatically-enable-php-extensions-in-homestead-on-vagrant-up
sudo apt-get --assume-yes install php-pear
sudo apt-get --assume-yes install php5-dev
sudo pecl install xdebug

# edit php.ini to enable remote xdebugging
sudo cat <<EOT >> /etc/php5/apache2/php.ini
[xdebug]
zend_extension=/usr/lib/php5/20121212/xdebug.so
xdebug.remote_enable=1
xdebug.remote_host=10.0.2.2
xdebug.remote_port=9000
EOT

# restart apache
sudo service apache2 restart
