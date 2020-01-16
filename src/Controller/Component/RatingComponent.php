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
use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
class RatingComponent extends Component {

	/**
	 * @var array
	 */
	public $components = ['RequestHandler', 'Flash'];

	/**
	 * @var \Cake\Controller\Controller
	 */
	protected $Controller;

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'actions' => [], // Empty: all
		'modelName' => null, // Empty: auto-detect
		'params' => ['rate' => null, 'rating' => null, 'redirect' => true],
		'userId' => null,
		'userIdField' => 'id',
	];

	/**
	 * Callback
	 *
	 * @param \Cake\Event\Event $event
	 * @return \Cake\Http\Response|array|null
	 */
	public function startup(Event $event) {
		/** @var \Cake\Controller\Controller $controller */
		$controller = $event->getSubject();
		$this->Controller = $controller;

		$actions = $this->getConfig('actions');
		if ($actions) {
			$action = $this->getController()->getRequest()->getParam('action') ?: '';
			if (!in_array($action, $actions, true)) {
				return null;
			}
		}

		$isJson = ($this->getController()->getRequest()->getParam('_ext') && $this->getController()->getRequest()->getParam('_ext') === 'json');
		$request = $this->getController()->getRequest()->withParam('isJson', $isJson);
		$this->Controller->setRequest($request);

		$modelName = $this->getConfig('modelName');
		if (empty($modelName)) {
			$modelName = $this->Controller->modelClass;
		}
		list(, $modelName) = pluginSplit($modelName);
		$this->setConfig('modelName', $modelName);
		if (!$this->Controller->{$modelName}->behaviors()->has('Ratable')) {
			$this->Controller->{$modelName}->behaviors()->load('Ratings.Ratable', $this->_config);
		}
		$this->Controller->helpers[] = 'Ratings.Rating';

		if (!$this->getController()->getRequest()->is('post')) {
			return null;
		}

		$params = $this->getController()->getRequest()->getData() + $this->getController()->getRequest()->getQuery() + $this->_config['params'];
		if (!method_exists($this->Controller, 'rate')) { // Should be $this->Controller->{$modelName} ?
			if (isset($params['rate']) && isset($params['rating'])) {
				$userId = $this->getConfig('userId') ?: $this->Controller->Auth->user($this->getConfig('userIdField'));
				return $this->rate($params['rate'], $params['rating'], $userId, $params['redirect']);
			}
		}

		return null;
	}

	/**
	 * Adds as user rating for a model record
	 *
	 * @param string $rate the model record id
	 * @param string $rating
	 * @param string|int $user
	 * @param bool|string|array $redirect boolean to redirect to same url or string or array to use it for Router::url()
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
		} elseif ($Controller->{$this->getConfig('modelName')}->findById($rate)) {
			/** @var \Ratings\Model\Behavior\RatableBehavior $Model */
			$Model = $Controller->{$this->getConfig('modelName')};
			$newRating = $Model->saveRating($rate, $user, $rating);
			if ($newRating) {
				$rating = round($newRating->newRating);
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
	 * @return array
	 */
	public function buildUrl() {
		$params = [
			'plugin' => $this->getController()->getRequest()->getParam('plugin'),
			'controller' => $this->getController()->getRequest()->getParam('controller'),
			'action' => $this->getController()->getRequest()->getParam('action'),
		];
		$params = array_merge($params, $this->getController()->getRequest()->getParam('pass'));

		$ratingParams = array_keys($this->_config['params']);
		foreach ($this->getController()->getRequest()->getQuery() as $name => $value) {
			if (!in_array($name, $ratingParams, true)) {
				$params['?'][$name] = $value;
			}
		}
		return $params;
	}

	/**
	 * Overload Redirect.  Many actions are invoked via Xhr, most of these
	 * require a list of current favorites to be returned.
	 *
	 * @param array|string $url
	 * @param string|null $status
	 * @return \Cake\Http\Response|null
	 */
	public function redirect($url, $status = null) {
		if (!empty($this->Controller->viewVars['authMessage']) && $this->getController()->getRequest()->getParam('isJson')) {
			$this->RequestHandler->renderAs($this->Controller, 'json');
			$this->Controller->set('message', $this->Controller->viewVars['authMessage']);
			$this->Controller->set('status', 'error');
			$this->response->body($this->Controller->render('rate'));
			return $this->response;
		}

		if (!empty($this->Controller->viewVars['authMessage'])) {
			$this->Flash->error($this->Controller->viewVars['authMessage']);
		}
		if ($this->getController()->getRequest()->getParam('isAjax') || $this->getController()->getRequest()->getParam('isJson')) {
			$this->Controller->setAction('rated', $this->getController()->getRequest()->params['named']['rate']);
			return $this->Controller->render('rated');
		}
		if (isset($this->Controller->viewVars['status']) && isset($this->Controller->viewVars['message'])) {
			$method = $this->Controller->viewVars['status'];
			$this->Flash->$method($this->Controller->viewVars['message']);
		}

		return $this->Controller->redirect($url, $status);
	}

}
