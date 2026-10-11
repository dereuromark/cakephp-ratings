<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Ratings\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\ORM\Exception\MissingTableClassException;
use Cake\View\JsonView;
use LogicException;
use ReflectionClass;

/**
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
class RatingComponent extends Component {

	/**
	 * @var array<int|string, array<string, mixed>|string>
	 */
	protected array $components = ['Flash'];

	/**
	 * @var \Cake\Controller\Controller
	 */
	protected $Controller;

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'actions' => [], // Empty: all
		'modelName' => null, // Empty: auto-detect
		'params' => ['rate' => null, 'rating' => null, 'redirect' => true],
		'userId' => null,
		'userIdField' => 'id',
		'allowFallbackClass' => true, // For model detection
	];

	/**
	 * Callback
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function startup(EventInterface $event): void {
		/** @var \Cake\Controller\Controller $controller */
		$controller = $event->getSubject();
		$this->Controller = $controller;

		$actions = $this->getConfig('actions');
		if ($actions) {
			$action = $this->Controller->getRequest()->getParam('action') ?: '';
			if (!in_array($action, $actions, true)) {
				return;
			}
		}

		$isJson = ($this->Controller->getRequest()->getParam('_ext') && $this->Controller->getRequest()->getParam('_ext') === 'json');
		$request = $this->Controller->getRequest()->withParam('isJson', $isJson);
		$this->Controller->setRequest($request);

		$modelName = $this->getConfig('modelName');
		if (!$modelName) {
			// We need the original field, as $this->getController()->fetchTable()->getAlias() only contains aliased version
			/** @phpstan-ignore-next-line */
			$modelName = $this->invokeProperty($this->Controller, 'modelClass') ?: $this->invokeProperty($this->Controller, 'defaultTable');
		}
		$this->setConfig('modelName', $modelName);

		try {
			$model = $this->Controller->getTableLocator()->get($modelName, ['allowFallbackClass' => $this->getConfig('allowFallbackClass')]);
		} catch (MissingTableClassException) {
			$model = null;
		}
		if ($model && !$model->behaviors()->has('Ratable')) {
			$model->behaviors()->load('Ratable', ['className' => 'Ratings.Ratable'] + $this->_config);
		}
		$this->Controller->viewBuilder()->addHelper('Ratings.Rating');

		if (!$this->Controller->getRequest()->is('post')) {
			return;
		}

		$requestParams = (array)$this->Controller->getRequest()->getData() + (array)$this->Controller->getRequest()->getQuery();
		$params = $requestParams + (array)$this->_config['params'];
		if (array_key_exists('redirect', $requestParams)) {
			$redirect = filter_var($requestParams['redirect'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			if ($redirect === null) {
				throw new BadRequestException('The redirect request parameter must be boolean.');
			}
			$params['redirect'] = $redirect;
		}
		if (method_exists($this->Controller, 'rate')) { // Should be $this->Controller->{$modelName} ?
			return;
		}

		if (!isset($params['rate']) || !isset($params['rating'])) {
			return;
		}

		$userId = $this->resolveUserId();

		$rate = $this->rate($params['rate'], (float)$params['rating'], $userId, $params['redirect']);
		if ($rate) {
			$event->setResult($rate);
		}
	}

	/**
	 * Resolves the active user id from configured override, the request identity
	 * (Authentication plugin), or a legacy AuthUser/Auth component if attached.
	 *
	 * Returns null when no user can be resolved - callers must handle this.
	 *
	 * @return string|int|null
	 */
	protected function resolveUserId(): string|int|null {
		$userId = $this->getConfig('userId');
		if ($userId) {
			return $userId;
		}

		$identity = $this->Controller->getRequest()->getAttribute('identity');
		if ($identity) {
			return $identity->get($this->getConfig('userIdField'));
		}

		if ($this->Controller->components()->has('AuthUser')) {
			return $this->Controller->AuthUser->id();
		}

		if ($this->Controller->components()->has('Auth')) {
			return $this->Controller->Auth->user($this->getConfig('userIdField'));
		}

		return null;
	}

	/**
	 * Gets protected/private property of a class.
	 *
	 * So
	 *   $this->invokeProperty($object, '_foo');
	 * is equal to
	 *   $object->_foo
	 * (assuming the property was directly publicly accessible)
	 *
	 * @param object $object Instantiated object that we want the property off.
	 * @param string $name Property name to fetch.
	 *
	 * @return mixed Property value.
	 */
	protected function invokeProperty(&$object, string $name) {
		$reflection = new ReflectionClass(get_class($object));
		if (!$reflection->hasProperty($name)) {
			return null;
		}
		$property = $reflection->getProperty($name);

		return $property->getValue($object);
	}

	/**
	 * Adds as user rating for a model record
	 *
	 * @param string|int $rate the model record id
	 * @param float|int $rating
	 * @param string|int|null $user
	 * @param array<mixed>|string|bool $redirect boolean to redirect to same url or string or array to use it for Router::url()
	 * @return \Cake\Http\Response|null
	 */
	public function rate($rate, $rating, $user, $redirect = false) {
		$Controller = $this->Controller;
		$table = $Controller->getTableLocator()->get($this->getConfig('modelName'));
		$primaryKey = $table->getPrimaryKey();
		if (!is_string($primaryKey)) {
			throw new LogicException('Ratings require a single-column primary key.');
		}

		if (!$rating) {
			$message = __d('ratings', 'No rating selected');
			$status = 'error';
		} elseif (!$user) {
			$message = __d('ratings', 'Not logged in');
			$status = 'error';
		} elseif ($table->find()->where([$primaryKey => $rate])->first()) {
			/** @var \Ratings\Model\Behavior\RatableBehavior $behavior */
			$behavior = $table->getBehavior('Ratable');
			try {
				$newRating = $behavior->saveRating($rate, $user, $rating);
			} catch (LogicException $e) {
				$result = ['status' => 'error', 'message' => $e->getMessage(), 'rating' => $rating];
				$this->Controller->set($result);
				if ($redirect) {
					if (is_numeric($redirect)) {
						$redirect = (bool)$redirect;
					}
					if ($redirect === true) {
						return $this->redirect($this->buildUrl());
					}

					return $this->redirect($redirect);
				}

				return null;
			}
			if ($newRating !== false) {
				$resultField = $behavior->getConfig('saveToField');
				$resultField = is_string($resultField) ? $resultField : $behavior->getConfig('field');
				$rating = round((float)($newRating instanceof EntityInterface
					? $newRating->get($resultField)
					: $newRating));
				$message = __d('ratings', 'Your rate was successful.');
				$status = 'success';
			} else {
				$message = __d('ratings', 'You have already rated.');
				$status = 'error';
			}
		} else {
			$message = __d('ratings', 'Invalid rate.');
			$status = 'error';
		}
		$result = compact('status', 'message', 'rating');
		$this->Controller->set($result);
		if ($redirect) {
			if (is_numeric($redirect)) {
				$redirect = (bool)$redirect;
			}
			if ($redirect === true) {
				return $this->redirect($this->buildUrl());
			}

			return $this->redirect($redirect);
		}

		return null;
	}

	/**
	 * Clean url from rating parameters
	 *
	 * @return array<mixed>
	 */
	public function buildUrl() {
		$params = [
			'plugin' => $this->Controller->getRequest()->getParam('plugin'),
			'controller' => $this->Controller->getRequest()->getParam('controller'),
			'action' => $this->Controller->getRequest()->getParam('action'),
		];
		$params = array_merge($params, $this->Controller->getRequest()->getParam('pass'));

		$ratingParams = array_keys($this->_config['params']);
		foreach ((array)$this->Controller->getRequest()->getQuery() as $name => $value) {
			if (!in_array($name, $ratingParams, true)) {
				$params['?'][$name] = $value;
			}
		}

		return $params;
	}

	/**
	 * Overload Redirect. Many actions are invoked via Xhr, most of these
	 * require a list of current favorites to be returned.
	 *
	 * @param array<mixed>|string $url
	 * @param int $status
	 * @return \Cake\Http\Response|null
	 */
	public function redirect($url, int $status = 302): ?Response {
		if ($this->Controller->viewBuilder()->getVar('authMessage') && $this->Controller->getRequest()->getParam('isJson')) {
			$this->Controller->set('message', $this->Controller->viewBuilder()->getVar('authMessage'));
			$this->Controller->set('status', 'error');

			return $this->renderJson();
		}

		if ($this->Controller->viewBuilder()->getVar('authMessage')) {
			$this->Flash->error($this->Controller->viewBuilder()->getVar('authMessage'));
		}
		if ($this->Controller->getRequest()->getParam('isJson')) {
			return $this->renderJson();
		}
		if ($this->Controller->getRequest()->getParam('isAjax')) {
			return $this->Controller->render('rated');
		}
		if ($this->Controller->viewBuilder()->getVar('status') !== null && $this->Controller->viewBuilder()->getVar('message') !== null) {
			$method = $this->Controller->viewBuilder()->getVar('status');
			$this->Flash->$method($this->Controller->viewBuilder()->getVar('message'));
		}

		return $this->Controller->redirect($url, $status ?: 302);
	}

	/**
	 * @return \Cake\Http\Response
	 */
	protected function renderJson(): Response {
		$this->Controller->viewBuilder()
			->setClassName(JsonView::class)
			->setOption('serialize', ['status', 'message', 'rating']);

		return $this->Controller->render();
	}

}
