# Installation

To install the plugin, composer it as `"dereuromark/ratings":"dev-master"`.

Then, include the following line in your `config/bootstrap.php` to load the plugin in your application.

```php
Plugin::load('Ratings');
```

## Database Setup

The recommended way to install and maintain the database is using the [Migrations](https://github.com/cakephp/migrations) plugin.

To set up the **Ratings** plugin tables run this command:

```
cake Migrations migrate -p Ratings
```

Alternately you can copy-and-paste the SQL commands from the migration files.

## Database Table Name

To customize the table name, you can use the Configure key `Ratings.table`:
```php
Configure::write('Ratings.table', 'prefixed_special_ratings');
```
