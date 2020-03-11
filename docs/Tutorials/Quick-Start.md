# Quick Start

For this example we assume that we want to rate "posts". We will cover here only the *very* basics of getting the plugin to work.

## Setup
Make sure that you application is using the auth component, the plugin won't work properly without the auth component.
Your actions you want to rate on should be non-public - or you need to hide rating until the person is logged in.

## Model
You have already created the ratings table.

Ideally, you have the following fields in your posts table:
- "rating" (float, default 0.0)
- "rating_sum" (int 10, optional but recommended)
- "rating_count" (int 10, optional but recommended)

If you allow smaller ratings then integer 1...x, you might want to set rating_sum to float, as well.

## Controller
Your controller could look like this:
```php
class PostsController extends AppController {

	public function initialize(): void {
        parent::initialize();

        $this->loadComponent('Ratings.Rating', ['actions' => ['view']);
	}

	/**
	 * @param string|int|null $id
	 * @return void
	 */
	public function view($id = null) {
		$post = $this->Posts->get($id);

		$this->set('post', $post);
		$this->set('isRated', $this->Posts->isRatedBy($id, $this->Auth->user('id')));
	}

}
```

All you have to do is to add the Rating component to your controllers component array.
This will already make ratings work and load the behavior for the controllers current `$modelClass` and also load the helper.

This line

```php
$this->set('hasRated', $this->Posts->hasRated($id, $this->Auth->user('id')));
```

is not required but shows you how you can check if the current record was already rated for the current logged-in user.

## Template

### Rating
In your ```view.php``` add this.

```php
if (!$isRated) {
	echo $this->Rating->control([
		'item' => $post->id,
		'js' => true,
	]);
} else {
	echo __('You have already rated.');
	echo $this->Rating->display($isRated['value']);
}
```

The RatingHelper::display() method needs two options, the `item` to rate and the target `url`.
The `item` is the id of the record you want to rate. The `url` will take by default the current URL but you'll have to additional parameters to it.
In our case we want to go back to the view we're currently on so we need to pass the post record id here as well.

JS can be included to generate a nice star rating widget to click on instead of just an input, radio or dropdown. Use `'js' => true` for this.
Also don't forget to include the assets.

### Read Only
If you only want to display the results, a HTML only (non JS) font-svg solution is recommended:
```php
echo $this->Rating->display($post->rating) . ' <nobr>(' . $post->rating_count . ' votes)</nobr>';
```
