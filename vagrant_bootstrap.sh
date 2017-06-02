#!/bin/bash
# now using centos 7
echo "TLS_REQCERT never" >> /etc/openldap/ldap.conf
#yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm https://www.softwarecollections.org/en/scls/rhscl/httpd24/epel-7-x86_64/download/rhscl-httpd24-epel-7-x86_64.noarch.rpm https://www.softwarecollections.org/en/scls/rhscl/rh-php56/epel-7-x86_64/download/rhscl-rh-php56-epel-7-x86_64.noarch.rpm
yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm 
yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum-config-manager --enable remi-php71

#yum -y install mariadb-server mariadb rh-php56 rh-php56-php-mysqlnd rh-php56-php-mbstring rh-php56-php httpd24-httpd vim rh-php56-php-ldap wget
yum -y install mariadb-server mariadb vim wget httpd kernel-devel kernel-headers kernel-uek-devel
yum -y install php71 php71-php-json php71-php-ldap php71-php-mbstring php71-php-pdo php71-php php71-php-mysqlnd php71-php-xml php71-php-pecl-zip
systemctl enable httpd.service
systemctl enable mariadb.service
systemctl disable firewalld
systemctl stop firewalld
#sudo cp /vagrant/projects.conf /opt/rh/httpd24/root/etc/httpd/conf.d/
sudo cp /vagrant/projects.conf /etc/httpd/conf.d/
#curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#chmod +x /usr/local/bin/composer
#chown vagrant /var/www/html
#sudo sed -ie 's/^#NameVirtualHost/NameVirtualHost/' /etc/httpd/conf/httpd.conf
#cp /vagrant/tmt.conf /etc/httpd/conf.d/tmt.conf
#cp -f /vagrant/php.ini /etc/
systemctl start mariadb.service
#systemctl start httpd24-httpd.service
systemctl start httpd.service
mysqladmin -u root password 'Iayixoch8puth6we'
mysql -u root -p'Iayixoch8puth6we' -e 'CREATE DATABASE IF NOT EXISTS projects2;'
mysql -u root -p'Iayixoch8puth6we' -e 'grant all privileges on projects2.* to `projects2`@`localhost` identified by "iiqu5iquogeu4ohN";'

