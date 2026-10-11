# Rating component

The component handles rating POSTs on selected actions. It reads `rate`, `rating` and `redirect` from the body, then the query string, then configured defaults. GET requests do not save votes.

```php
$this->loadComponent('Ratings.Rating', [
    'actions' => ['view'],
    'modelName' => 'Posts',
    'update' => true,
]);
```

An empty `actions` array enables every action. `modelName` selects the controller table; when omitted it is detected from the controller. Behavior options are passed through when the component first attaches `Ratable`. A behavior already attached to the table keeps its own configuration.

User IDs resolve from `userId`, the request identity's configured `userIdField` (default `id`), then attached legacy AuthUser or Auth components. A plain `Auth.User` session entry alone is not an identity. The old Auth component is not required.

The component saves no vote for an unresolved user. It checks for the rated record and reports failures through its response variables or Flash. It does not impose a five-star range on submitted data; enforce your allowed values in application validation.

A controller method named `rate()` disables automatic POST processing so the application can handle it explicitly. The component's own `rate($recordId, $value, $userId, $redirect)` method remains callable.

## Redirects and responses

The default `redirect` is true: the component removes rating query parameters and redirects to the current action. False leaves response variables (`status`, `message`, `rating`) for your action. Request-supplied `redirect` values must be boolean, including form strings such as `1` or `0`; URL values are rejected. Set `params.redirect` in component configuration to select a trusted URL or route array. Direct application calls to `rate()` may also pass an explicit target.

For a `.json` route, enable extension parsing in your own routes. When the redirect flag is enabled, the component returns those variables using CakePHP's JsonView, without requiring a template. The JSON shape is `{status, message, rating}`; application errors use HTTP 200 with `status: error`. On success, `rating` is the rounded aggregate; on error it is the submitted value. With redirect disabled, your action handles those variables. A custom `isAjax` route parameter instead renders your application's `rated` template. The helper's optional star widget does not submit automatically or enable AJAX.

## Validate submitted values

For a five-star form, check the submitted value before component startup. Add this to the controller's existing `beforeFilter()` logic:

```php
if ($this->getRequest()->is('post')) {
    $value = $this->getRequest()->getData('rating');
    if (!is_scalar($value) || !is_numeric($value)
        || !is_finite((float)$value) || (float)$value < 1 || (float)$value > 5
    ) {
        throw new \Cake\Http\Exception\BadRequestException('Choose a rating from 1 to 5.');
    }
}
```

Keep the parent `beforeFilter()` call and limit this check to your rating action if the controller handles other POST forms. Application authorization must also decide which records the user may rate.
