# Ratable behavior

For use outside the component, attach the behavior in the rated table's existing `initialize()` method:

```php
$this->addBehavior('Ratings.Ratable', ['update' => false]);
```

## Save, query and remove

```php
$behavior = $posts->getBehavior('Ratable');
$result = $behavior->saveRating($postId, $userId, 4);
$alreadyRated = $posts->hasRated($postId, $userId);
$previous = $posts->isRatedBy($postId, $userId)->first();
$removed = $behavior->removeRating($postId, $userId);
```

`isRatedBy()` returns a query, not an entity. Call `first()` to retrieve a vote. `hasRated()` returns a boolean.

`saveRating()` and `removeRating()` return the saved rated-record entity when the calculated result is stored, a numeric result when it is not stored, or false when no change succeeds. Set `update` to true to replace a user's previous vote. `saveRating()` wraps its writes in a database transaction; it does not provide a general guarantee that concurrent updates to cached totals cannot race.

The behavior's default owner field is `user_id`. Matching owners cannot vote on their own record. Set `userField` to null to disable this guard deliberately. Direct behavior calls do not authenticate users or check application permissions.

## Average and sum

The default calculation is `average`. Select `sum` for vote totals:

```php
$this->addBehavior('Ratings.Ratable', ['calculation' => 'sum']);
```

With both configured summary and counter fields present, saves update cached totals. Otherwise the behavior recalculates from the ratings table. `calculateRating($id, false)` returns the aggregate without storing it. A string second argument stores it in that field.

`incrementRating()` and `decrementRating()` only adjust calculated fields; they do not create or remove vote rows. Prefer `saveRating()` and `removeRating()` for ordinary voting.

## Up and down votes

```php
$posts->rate($postId, $userId, 'up', [
    'values' => ['up' => 1, 'down' => -1],
]);
```

`rate()` returns true on success and throws for an unknown symbolic value, missing record, self-vote or unsuccessful save. Its `userField` option controls its own precheck; the behavior's configured owner guard also applies during `saveRating()`.

The default schema and validation do not impose a five-star maximum. Validate your application's accepted range when calling the behavior directly or accepting submitted values.

## Callbacks and statistics

Table methods `beforeRate(array $data)` and `afterRate(array $data)` receive the operation type (`saveRating` or `removeRating`), record key, user ID and update information. The after callback also receives the result and previous vote. Removal callbacks do not receive a new `value`; read `oldRating->value`.

Call `cacheRatingStatistics($data)` from `afterRate()` when your table contains histogram columns such as `rating_1`, `rating_2` or `rating_neg1`. `getNewRating()` returns the last aggregate recorded by the calculation or increment path.
