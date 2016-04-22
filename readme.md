# UUD User Verification App

## Installation:

### Nginx/MariaDB/PHP Stack

If you already have a LAMP setup or some other similar stack it should work on anything that Laravel will run on.

* Install Nginx:

```shell
curl -sL https://raw.githubusercontent.com/MelonSmasher/NginxInstaller/master/nginx-latest-install.sh | bash -
```

* Install MariaDB/MySLQ:

```shell
# Install prerequisite
apt-get install python-software-properties -y;
# Get apt key for new repo
apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xcbcb082a1bb943db;
# Add the MariaDB repo to our lists
echo 'deb http://mirrors.syringanetworks.net/mariadb/repo/10.1/debian jessie main' > /etc/apt/sources.list.d/mariadb.list;
# Refresh your apt cache
apt-get update;
# Download MariaDB and install
apt-get install mariadb-client mariadb-server -y;
# Secure mariadb
mysql_secure_installation;
```
* Install PHP & PHP-FPM:

 ```shell
 apt-get update && apt-get upgrade -y;
 apt-get install php5-cli php5 php5-fpm php5-mysql php5-mcrypt -y;
 ```

* Install PHP Composer:

 ```shell
 curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
 ```

* Configure Nginx & PHP-FPM:

 ```shell
 vi /etc/nginx/nginx.conf
 ```

 Change:

 ```shell
 #user  nobody;
 ```

 to:

 ```shell
 user  nginx;
 ```

 Find the document root section:

 ```shell
 location / {
    root   html;
    index  index.html index.htm;
 }
 ```

 Make it look like this:

 ```shell
 location / {
    root   /usr/share/nginx/html;
    index  index.php index.html index.htm;
 }
 ```


 Find this PHP section:

 ```shell
 #location ~ \.php$ {
 #    root           html;
 #    fastcgi_pass   127.0.0.1:9000;
 #    fastcgi_index  index.php;
 #    fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
 #    include        fastcgi_params;
 #}
 ```

 Make it look like this:

 ```shell
 location ~ \.php$ {
    include             fastcgi_params;
    fastcgi_param       SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param       PATH_INFO $fastcgi_path_info;
    fastcgi_pass        unix:/var/run/php5-fpm.sock;
 }
 ```

 ### Configure PHP:

 In the php.ini file change `;cgi.fix_pathinfo=1` to `cgi.fix_pathinfo=o`.

 ```shell
 sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php5/fpm/php.ini
 ```

 Change the user and group that php5-fpm runs as from `www-data` to `nginx`:

 ```shell
 sed -i 's/www-data/nginx/g' /etc/php5/fpm/pool.d/www.conf
 ```


### Application Install

* Provision MySQL

```shell
mysql -u root -p;
```

```mysql
CREATE DATABASE 'user_verification';
CREATE USER 'uv_user'@'localhost' IDENTIFIED BY 'STRONG-PASSWORD';
GRANT ALL ON 'user_verification'.* TO 'uv_user'@'localhost';
FLUSH PRIVILEGES;
```
* Do the following as the Nginx user:

```shell
su nginx;
bash;
```

```shell
cd /usr/share/nginx/html;
git clone --recursive https://gitlab.sage.edu/UniversalUserData/UserVerificationClient.git uv;
cd uv;
```

* Install Framework and Dependencies:

```shell
composer install --no-dev --no-scripts --prefer-dist;
php artisan key:generate
```

* Configure your `.env` file:

```shell
vi .env;
# Fill out the MySQL settings and Mail settings
# Set your environtment to production
```

* Configure the UUD Client:

```shell
cp config/uud.example.php config/uud.php;
vi config/uud.php;
# Fill out the api server and this application's api key.
```

* Migrate and seed the database:

```shell
php artisan migrate --force --seed;
```

* This creates a default admin account:

email: admin@doamin.tld

password: Cascade

You'll want to change both the email first and the password second from the profile page.