# Installation

Check [composer.json](https://github.com/dereuromark/cakephp-ratings/blob/master/composer.json) for supported PHP and CakePHP versions. This branch supports CakePHP 5.

```sh
composer require dereuromark/cakephp-ratings
```

In your existing `Application::bootstrap()`, after the parent call:

```php
$this->addPlugin('Ratings');
```

## Create the ratings table

The bundled migration creates `ratings`. If your application does not already use Migrations:

```sh
composer require cakephp/migrations
```

Load `Migrations` in your application's bootstrap if it is not already loaded:

```php
$this->addPlugin('Migrations');
```

Run:

```sh
bin/cake migrations migrate -p Ratings
```

The table stores `user_id`, `foreign_key`, `model`, `value` and timestamps. Its unique index permits one rating per user, record and model.

The migration uses an integer `user_id`. `Polymorphic.type` changes only `foreign_key`, and must be configured before the initial migration. For UUID user IDs, custom table names or a different schema, create an application migration instead of running the bundled one. `Ratings.table` changes runtime table lookup; it does not rename the bundled migration's table.

## Rated records

The [first example](./getting-started.md) assumes an existing `posts` table and route, plus users who can sign in. A `rating` float column stores the calculated result. Optional `rating_sum` float and `rating_count` integer columns enable cached totals. Initialize all three to zero and keep them out of user-submitted mass assignment; the behavior writes its calculated fields explicitly.

If these fields do not exist, add an application migration such as `config/Migrations/20260101000000_AddRatingCacheToPosts.php`:

```php
<?php
use Migrations\BaseMigration;

class AddRatingCacheToPosts extends BaseMigration {
    public function change(): void {
        $this->table('posts')
            ->addColumn('rating', 'float', ['default' => 0, 'null' => false])
            ->addColumn('rating_sum', 'float', ['default' => 0, 'null' => false])
            ->addColumn('rating_count', 'integer', ['default' => 0, 'null' => false])
            ->update();
    }
}
```

Use a filename timestamp later than your existing migrations, then run `bin/cake migrations migrate`.
