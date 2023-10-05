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
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Exception\MissingTableClassException;
use ReflectionClass;

/**
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
class RatingComponent extends Component {

	/**
	 * @var array<mixed>
	 */
	protected array $components = ['RequestHandler', 'Flash'];

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
	];

	/**
	 * Callback
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @return \Cake\Http\Response|null
	 */
	public function startup(EventInterface $event) {
		/** @var \Cake\Controller\Controller $controller */
		$controller = $event->getSubject();
		$this->Controller = $controller;

		$actions = $this->getConfig('actions');
		if ($actions) {
			$action = $this->Controller->getRequest()->getParam('action') ?: '';
			if (!in_array($action, $actions, true)) {
				return null;
			}
		}

		$isJson = ($this->Controller->getRequest()->getParam('_ext') && $this->Controller->getRequest()->getParam('_ext') === 'json');
		$request = $this->Controller->getRequest()->withParam('isJson', $isJson);
		$this->Controller->setRequest($request);

		$modelName = $this->getConfig('modelName');
		if (!$modelName) {
			$modelName = $this->invokeProperty($this->Controller, 'modelClass') ?: $this->invokeProperty($this->Controller, 'defaultTable');
		}
		$this->setConfig('modelName', $modelName);

		try {
			$model = $this->Controller->getTableLocator()->get($modelName, ['allowFallbackClass' => false]);
		} catch (MissingTableClassException) {
			$model = null;
		}
		if ($model && !$model->behaviors()->has('Ratable')) {
			$model->behaviors()->load('Ratings.Ratable', $this->_config);
		}
		$this->Controller->viewBuilder()->setHelpers(['Ratings.Rating']);

		if (!$this->Controller->getRequest()->is('post')) {
			return null;
		}

		$params = (array)$this->Controller->getRequest()->getData() + (array)$this->Controller->getRequest()->getQuery() + (array)$this->_config['params'];
		if (!method_exists($this->Controller, 'rate')) { // Should be $this->Controller->{$modelName} ?
			if (isset($params['rate']) && isset($params['rating'])) {
				$userId = $this->getConfig('userId') ?: null;
				if (!$userId && isset($this->Controller->Auth)) {
					$userId = $this->Controller->Auth->user($this->getConfig('userIdField'));
				}

				return $this->rate($params['rate'], (float)$params['rating'], $userId, $params['redirect']);
			}
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
		$property = $reflection->getProperty($name);
		$property->setAccessible(true);

		return $property->getValue($object);
	}

	/**
	 * Adds as user rating for a model record
	 *
	 * @param string|int $rate the model record id
	 * @param float|int $rating
	 * @param string|int $user
	 * @param array<mixed>|string|bool $redirect boolean to redirect to same url or string or array to use it for Router::url()
	 * @return \Cake\Http\Response|null
	 */
	public function rate($rate, $rating, $user, $redirect = false) {
		$Controller = $this->Controller;

		if (!$rating) {
			$message = __d('ratings', 'No rating selected');
			$status = 'error';
		} elseif (!$user) {
			$message = __d('ratings', 'Not logged in');
			$status = 'error';
		} elseif ($Controller->getTableLocator()->get($this->getConfig('modelName'))->find()->where(['id' => $rate])->first()) {
			/** @var \Ratings\Model\Behavior\RatableBehavior $Model */
			$Model = $Controller->getTableLocator()->get($this->getConfig('modelName'));
			$newRating = $Model->saveRating($rate, $user, $rating);
			if ($newRating) {
				$rating = round($newRating->rating);
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
			//FIXME
			//$this->RequestHandler->renderAs($this->Controller, 'json');
			$this->Controller->set('message', $this->Controller->viewBuilder()->getVar('authMessage'));
			$this->Controller->set('status', 'error');

			$response = $this->Controller->getResponse()->withStringBody($this->Controller->render('rate'));

			return $response;
		}

		if ($this->Controller->viewBuilder()->getVar('authMessage')) {
			$this->Flash->error($this->Controller->viewBuilder()->getVar('authMessage'));
		}
		if ($this->Controller->getRequest()->getParam('isAjax') || $this->Controller->getRequest()->getParam('isJson')) {
			//FIXME
			//$this->Controller->setAction('rated', $this->Controller->getRequest()->getData('rate'));

			return $this->Controller->render('rated');
		}
		if ($this->Controller->viewBuilder()->getVar('status') !== null && $this->Controller->viewBuilder()->getVar('message') !== null) {
			$method = $this->Controller->viewBuilder()->getVar('status');
			$this->Flash->$method($this->Controller->viewBuilder()->getVar('message'));
		}

		return $this->Controller->redirect($url, $status ?: 302);
	}

}
