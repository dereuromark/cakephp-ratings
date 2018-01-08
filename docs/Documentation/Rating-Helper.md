Rating Helper
=============

Use the helper in your views to generate links that mark a model record as favorite

```php
echo $this->Rating->display([
	'item' => $post['id'],
	'type' => 'radio',
	'stars' => 5,
	'value' => $item['rating'],
	'createForm' => [
		'url' => [
			$this->passedArgs, 
			'rate' => $item['id'],
			'redirect' => true
		]
	]
]);
```

This generated form will be compatible with jQuery UI Stars. This jQuery plugin needs to be manually included in your webroot folder.

Here is the JS sample code that will stylize the form:

```js
$('#ratingform').stars({
	split: 2,
	cancelShow: false,
	callback: function(ui, type, value) {
		ui.$form.submit();
	}
});
```

AJAX support
------------

If the URL ends with ".json" extension then the response contains a JSON object instead of page redirect.

The JSON object contains the following structure:

```json
{
	"status": "success",
	"data": {
		"message": "Result message"
	}
}
```

There is a sample JSON layout included in the ratings plugin, but views need to be implement for each rate action.

Helper methods
--------------

* **display():** Displays a bunch of rating links wrapped into a list element of your choice. Using `createForm` option you can use this to allow rating.
* **ratingImage():** Displays pure HTML stars (non JS) for easy readonly use.
* **bar($value, $total, $options):** Bar rating.
* **starForm($options, $urlHtmlAttributes):** Displays a star form. Used by display().
