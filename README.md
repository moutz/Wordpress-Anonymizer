# Wordpress anonymize users datas and Woocommerce informations

This is a PHP script to anonymize datas for Wordpress and Woocommerce.
It uses Faker PHP to erase sensitives datas with datas that looks like real datas, useful to respect GDPR on your test/staging environment.

Based on this two scripts :

- https://www.businessbloomer.com/woocommerce-anonymize-users-orders/

- https://gist.github.com/trainingspark/d3fdf638dcdcdbd65c1618e6bc6fe946

## Usage

Pull the repo at the root of your Wordpress install, then go in the folder and :

```
composer install
```

then

```
php anonymize-datas.php
```
