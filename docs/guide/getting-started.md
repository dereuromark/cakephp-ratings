# Getting started

Complete [installation](./installation.md), including the database setup. This example uses your existing Posts pages and an authentication setup that places an identity on the request, such as [CakePHP Authentication](https://book.cakephp.org/authentication/3/en/).

In `PostsController`, preserve your existing initialization code:

```php
public function initialize(): void {
    parent::initialize();
    $this->loadComponent('Ratings.Rating', ['actions' => ['view']]);
}

public function beforeFilter(\Cake\Event\EventInterface $event): void {
    parent::beforeFilter($event);
    if ($this->getRequest()->is('post') && $this->getRequest()->getParam('action') === 'view') {
        $value = $this->getRequest()->getData('rating');
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int)$value < 1 || (int)$value > 5) {
            throw new \Cake\Http\Exception\BadRequestException('Choose a rating from 1 to 5.');
        }
    }
}

public function view(string $id): void {
    $posts = $this->fetchTable('Posts');
    $post = $posts->get($id);
    $identity = $this->getRequest()->getAttribute('identity');
    $userId = $identity ? $identity->get('id') : null;
    $behavior = $posts->getBehavior('Ratable');
    $isRated = $userId ? $behavior->hasRated($post->id, $userId) : false;
    $this->set(compact('post', 'userId', 'isRated'));
}
```

The component attaches the behavior and loads the helper during startup. It processes a submitted rating before the action reads the post.

In `templates/Posts/view.php`:

```php
<h1><?= h($post->title) ?></h1>
<p>Average: <?= h($post->rating) ?>; votes: <?= h($post->rating_count) ?></p>
<?php if (!$userId): ?>
    <p>Sign in to rate this post.</p>
<?php elseif ($isRated): ?>
    <p>You have already rated this post.</p>
<?php else: ?>
    <?= $this->Rating->control(['item' => $post->id]) ?>
<?php endif; ?>
```

Keep the normal Flash helper output in your application layout:

```php
<?= $this->Flash->render() ?>
```

Open an existing `/posts/view/1` page with a user who does not own that post. Select a value and press Rate. The form posts to the same action, the component saves the vote and redirects back, and the average and vote count update. Repeating the vote is rejected unless you enable updates.

No JavaScript or icon font is required for this example. The `beforeFilter()` check enforces whole-star values from 1 to 5 before the component processes the vote. The plugin does not provide sign-in routes or permissions for your posts. Keep your application's authentication, authorization and CSRF protection in place. If the post has a `user_id` owner field, self-voting is blocked by default.
