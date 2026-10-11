# Helper and assets

The component loads `Ratings.Rating` on matching actions. For display-only pages, load the helper in `AppView::initialize()`:

```php
$this->loadHelper('Ratings.Rating');
```

## A normal form

```php
<?= $this->Rating->control([
    'item' => $post->id,
    'stars' => 5,
    'type' => 'select',
    'url' => ['controller' => 'Posts', 'action' => 'view', $post->id],
]) ?>
```

`item` is required. `type` accepts `select` or `radio`; `stars` sets the positive integer maximum. The default submits to the current action and uses a post/redirect/get flow. `createForm` can contain FormHelper options. To embed the control in an existing form, provide your own hidden `rate` field and disable its generated form with an empty `url` and `createForm` false.

`control()` does not emit a read-only rating. `display($value)` does, using Font Awesome 4 star classes by default. Include matching CSS in your application, or use plain text for the average. The legacy `ui` display mode requires jQuery UI star styles and `image()` is deprecated.

## Optional star widget

`js => true` appends initialization for [bootstrap-star-rating](https://plugins.krajee.com/star-rating). Your application must provide jQuery, the widget's JavaScript and CSS, and Font Awesome 4 styles for the plugin's configured icons. Consult the [widget installation](https://plugins.krajee.com/star-rating#installation) for its dependencies.

Load these assets before the layout's script block, and include that block:

```php
<?= $this->fetch('script') ?>
```

The widget enhances the rating input; the Rate button still submits the normal form. Its generated initialization uses whole-star steps. For a different widget, keep `js` false and initialize your chosen library yourself.

## Other output

`bar($value, $total)` renders a percentage bar; supply application CSS for its classes. `percentage()` returns an integer percentage, and `round()` rounds within a configured minimum and maximum using a positive number of steps. The bar method also accepts a custom element and passes `value`, `percentage` and `total` to it.
