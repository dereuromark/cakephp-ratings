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
use Cake\Routing\Router;
use Cake\Event\Event;

/**
 * Ratings component
 *
 */
class RatingsComponent extends Component {

/**
 * Components that are required
 *
 * @var array $components
 */
	public $components = array('RequestHandler', 'Flash');

	protected $_defaultConfig = [
		'enabled' => true,
		'actions' => [], // Empty: all
		'modelName' => null, // Empty: auto-detect
		'params' => array('rate' => true, 'rating' => true, 'redirect' => true),
		'userId' => 'id', // or bool
		''
	];

/**
 * Callback
 *
 * @param object Controller object
 */
	public function initialize(array $config) {
		parent::initialize($config);
	}

/**
 * Callback
 *
 * @param object Controller object
 * @return void
 */
	public function beforeFilter(Event $event) {
		$this->Controller = $event->subject();

		if (!$this->config('enabled')) {
			return;
		}

		if ($actions = $this->config('actions')) {
			$action = !empty($this->Controller->request->params['action']) ? $this->Controller->request->params['action'] : '';
			if (!in_array($action, $actions)) {
				return;
			}
		}

		$this->Controller->request->params['isJson'] = (isset($this->Controller->request->params['url']['_ext']) && $this->Controller->request->params['url']['_ext'] === 'json');
		$modelName = $this->config('modelName');
		if (empty($modelName)) {
			$modelName = $this->Controller->modelClass;
		}
		list(, $modelName) = pluginSplit($modelName);
		$this->config('modelName', $modelName);
		if (!$this->Controller->{$modelName}->behaviors()->has('Ratable')) {
			$this->Controller->{$modelName}->behaviors()->load('Ratings.Ratable', $this->_config);
		}
		$this->Controller->helpers[] = 'Ratings.Rating';

		$message = '';
		$rating = null;
		$params = $this->request->query;

		if (empty($params['rating']) && !empty($this->request->data['rating'])) {
			$params['rating'] = $this->request->data['rating'];
		}

		if (!method_exists($this->Controller, 'rate')) {
			if (isset($params['rate']) && isset($params['rating'])) {
				$userId = !is_string($this->config('userId')) ? $this->config('userId') : $this->Controller->Auth->user($this->config('userId'));
				return $this->rate($params['rate'], $params['rating'], $userId, !empty($params['redirect']));
			}
		}
	}

/**
 * Adds as user rating for a model record
 *
 * @param string $rate the model record id
 * @param string $rating
 * @param mixed $redirect boolean to redirect to same url or string or array to use it for Router::url()
 */
	public function rate($rate, $rating, $user, $redirect = false) {
		$Controller = $this->Controller;
		//$Controller->{$this->config('modelName')}->id = $rate;
		if (!$user) {
			$message = __d('ratings', 'Not logged in');
			$status = 'error';
		} elseif ($Controller->{$this->config('modelName')}->findById($rate)) {
			if ($newRating = $Controller->{$this->config('modelName')}->saveRating($rate, $user, $rating)) {
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
		if (!empty($redirect)) {
			if ($redirect === true) {
				return $this->redirect($this->buildUrl());
			}
			return $this->redirect($redirect);
		}
		return $result;
	}

/**
 * Clean url from rating parameters
 *
 * @return array
 */
	public function buildUrl() {
		$params = array('plugin' => $this->Controller->request->params['plugin'], 'controller' => $this->Controller->request->params['controller'], 'action' => $this->Controller->request->params['action']);
		$params = array_merge($params, $this->Controller->request->params['pass']);
		foreach ($this->Controller->request->query as $name => $value) {
			if (!isset($this->_config['params'][$name])) {
				$params['?'][$name] = $value;
			}
		}
		return $params;
	}

/**
 * Overload Redirect.  Many actions are invoked via Xhr, most of these
 * require a list of current favorites to be returned.
 *
 * @param string $url
 * @param string|null $code
 * @return void|Response
 */
	public function redirect($url, $status = null) {
		if (!empty($this->Controller->viewVars['authMessage']) && !empty($this->Controller->request->params['isJson'])) {
			$this->RequestHandler->renderAs($this->Controller, 'json');
			$this->set('message', $this->Controller->viewVars['authMessage']);
			$this->set('status', 'error');
			echo $this->Controller->render('rate');
			$this->_stop();
		} elseif (!empty($this->viewVars['authMessage'])) {
			$this->Flash->error($this->viewVars['authMessage']);
		}
		if (!empty($this->Controller->request->params['isAjax']) || !empty($this->Controller->request->params['isJson'])) {
			$this->Controller->setAction('rated', $this->Controller->request->params['named']['rate']);
			return $this->Controller->render('rated');
		}
		if (isset($this->Controller->viewVars['status']) && isset($this->Controller->viewVars['message'])) {
			$this->Flash->success($this->Controller->viewVars['message']);
		}

		return $this->Controller->redirect($url, $status);
	}

}
