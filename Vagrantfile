# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = '2'

@script = <<SCRIPT
# Install dependencies

add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install -y apache2 php5.6 php5.6-curl php5.6-sqlite php5.6-xdebug curl php5.6-curl zip unzip
apt remove -y php7.1-cli

# Configure Apache
echo "<VirtualHost *:8080>
	DocumentRoot /var/www/web
	AllowEncodedSlashes On

	<Directory /var/www/web>
        Options +FollowSymLinks -MultiViews
        DirectoryIndex index.php index.html
        Order allow,deny
        Allow from all
        AllowOverride All
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]
	</Directory>


	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
<VirtualHost *:8081>
	DocumentRoot /var/www/web
	AllowEncodedSlashes On

	<Directory /var/www/web>
        Options +FollowSymLinks -MultiViews
        DirectoryIndex index.php index.html
        Order allow,deny
        Allow from all
        AllowOverride All
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]
	</Directory>


	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

a2enmod rewrite
echo "xdebug.remote_enable=on" >> /etc/php/5.6/apache2/conf.d/xdebug.ini
echo "xdebug.remote_host=192.168.34.1" >> /etc/php/5.6/apache2/conf.d/xdebug.ini
sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

echo Listen 8081 >> /etc/apache2/ports.conf
service apache2 restart

cd /var/www/
cp data/parameters.json.dist data/parameters.json
sed -i 's?"grant"?"http://localhost:8081/lockdin/token"?g' data/parameters.json
sed -i 's?"access"?"http://localhost:8081/lockdin/resource"?g' data/parameters.json


if [ -e /usr/local/bin/composer ]; then
    /usr/local/bin/composer self-update
else
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

cd /var/www/
composer install

# Reset home directory of vagrant user
if ! grep -q "cd /var/www" /home/vagrant/.profile; then
    echo "cd /var/www" >> /home/vagrant/.profile
fi


SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = 'bento/ubuntu-16.04'
  config.vm.network "private_network", ip: "192.168.34.2"
  config.vm.network "forwarded_port", guest: 8080, host: 8080
  config.vm.network "forwarded_port", guest: 8081, host: 8081
  config.vm.synced_folder '.', '/var/www'
  config.vm.provision 'shell', inline: @script

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1024"]
    vb.customize ["modifyvm", :id, "--name", "oauth2-demo-php"]
  end
end
