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

namespace Ratings\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component\AuthComponent;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use TestApp\Controller\ArticlesController;

class RatingComponentTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected $fixtures = [
		'core.Sessions',
		'plugin.Ratings.Ratings',
		'plugin.Ratings.Articles',
		'plugin.Ratings.Users',
	];

	/**
	 * Controller using the tested component
	 *
	 * @var \TestApp\Controller\ArticlesController
	 */
	protected $Controller;

	/**
	 * Mock AuthComponent object
	 *
	 * @var \Cake\Controller\Component\AuthComponent
	 */
	protected $AuthComponent;

	/**
	 * startTest method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->session = new Session();

		$this->Controller = new ArticlesController(new ServerRequest());
		$this->Controller->setEventManager(new EventManager());

		$this->Collection = $this->getMockBuilder(ComponentRegistry::class)->setConstructorArgs([$this->Controller])->getMock();
		$this->AuthComponent = $this->getMockBuilder(AuthComponent::class)->setMethods(['user'])->disableOriginalConstructor()->getMock();

		Router::reload();
	}

	/**
	 * endTest method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->Controller->getRequest()->getSession()->destroy();
		unset($this->Controller);
		//TableRegistry::clear();
	}

	/**
	 * testInitialize
	 *
	 * @return void
	 */
	public function testInitialize() {
		$this->_initControllerAndRatings();
		$this->assertEquals(['Html' => null, 'Form' => null, 'Ratings.Rating'], $this->Controller->helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
	}

	/**
	 * testInitializeWithParamsForBehavior
	 *
	 * @return void
	 */
	public function testInitializeWithParamsForBehavior() {
		$this->Controller->components = [
			'Ratings.Rating' => [
				'update' => true],
			'Auth'];

		$this->_initControllerAndRatings([]);
		$this->assertEquals([
			'Html' => null, 'Form' => null, 'Ratings.Rating'], $this->Controller->helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
		$this->assertTrue($this->Controller->Articles->behaviors()->Ratable->config('update'), 'Ratable behavior should be updatable.');
	}

	/**
	 * testInitializeWithParamsForComponent
	 *
	 * @return void
	 */
	public function testInitializeWithParamsForComponent() {
		$this->Controller->components = [
			'Ratings.Rating' => [
				'actions' => ['show']],
			'Auth'];

		$this->_initControllerAndRatings(['action' => 'show']);
		$this->assertEquals(['Html' => null, 'Form' => null, 'Ratings.Rating'], $this->Controller->helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
		$this->assertEquals(['show'], $this->Controller->Rating->getConfig('actions'));
	}

	/**
	 * Get with data in URL completely.
	 * Invalid get is not accepted. Value must be posted as payload or at least as part of the query.
	 *
	 * @return void
	 */
	public function testStartup() {
		$this->Controller->getRequest()->getSession()->write('Flash', null);

		$params = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'pass' => [],
			'?' => [
				'rating' => '5',
				'rate' => '2',
				'redirect' => true,
			],
		];
		$expectedRedirect = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test'];
/*
		$this->Controller->getRequest()->getSession()->expectAt(0, 'setFlash', array('Your rate was successfull.', 'default', array(), 'success'));
		$this->Controller->getRequest()->getSession()->expectAt(1, 'setFlash', array('You have already rated.', 'default', array(), 'error'));
		$this->Controller->getRequest()->getSession()->expectAt(2, 'setFlash', array('Invalid rate.', 'default', array(), 'error'));
*/
		$this->Controller->getRequest()->getSession()->write('Flash', null);
		ServerRequest::addDetector('post', function() { return true;
});
		$result = $this->_initControllerAndRatings($params);
		$url = $result->getHeaderLine('Location');
		$this->assertEquals(Router::url($expectedRedirect), $url);

		$sessionFlash = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$expectedFlash = [
			[
				'message' => __d('ratings', 'Not logged in'),
				'key' => 'flash',
				'element' => 'Flash/error',
				'params' => [],
			],
		];
		$this->assertSame($expectedFlash, $sessionFlash);

		$this->Controller->getRequest()->getSession()->write('Flash', null);
		$options = [
			'userId' => 1,
		];
		$result = $this->_initControllerAndRatings($params, $options);
		$url = $result->getHeaderLine('Location');
		$this->assertEquals(Router::url($expectedRedirect), $url);

		$sessionFlash = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$expectedFlash = [
			[
				'message' => __d('ratings', 'Your rate was successful.'),
				'key' => 'flash',
				'element' => 'Flash/success',
				'params' => [],
			],
		];
		$this->assertSame($expectedFlash, $sessionFlash);

		$this->Controller->getRequest()->getSession()->write('Flash', null);
		$params['?']['rate'] = '1';
		$result = $this->_initControllerAndRatings($params);

		$this->assertEquals(Router::url($expectedRedirect), $url);

//		$this->Controller->getRequest()->getSession()->write('Message', null);
		$params['?']['rate'] = 'invalid-record!';
		$result = $this->_initControllerAndRatings($params);
		$this->assertEquals(Router::url($expectedRedirect), $url);
	}

	/**
	 * testStartupAcceptPost
	 *
	 * @return void
	 */
	public function testStartupAcceptPost() {
		$this->Controller->getRequest()->getSession()->write('Auth.User.id', 1);
		/*
		$this->AuthComponent
			->expects($this->any())
			->method('user')
			->with('id')
			->will($this->returnValue(1));
		*/

		$params = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'pass' => [],
			'?' => [
				'rate' => '2',
				'redirect' => true,
			],
		];
		$expectedRedirect = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
		];
		$request = $this->Controller->getRequest()->withData(['rating' => 2]);
		$this->Controller->setRequest($request);

		ServerRequest::addDetector('post', function() {
			return true;
		});

		/** @var \Cake\Http\Response $result */
		$result = $this->_initControllerAndRatings($params);
		$url = $result->getHeaderLine('Location');
		$this->assertEquals(Router::url($expectedRedirect), $url);
	}

	/**
	 * testBuildUrl
	 *
	 * @return void
	 */
	public function testBuildUrl() {
		$params = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'pass' => [],
			'?' => [
				'foo' => 'bar',
				//'rating' => 'test',
				'rate' => '5',
				'redirect' => true,
			],
		];
		$this->_initControllerAndRatings($params);

		$result = $this->Controller->Rating->buildUrl();
		$expected = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'?' => [
				'foo' => 'bar',
			],
		];
		$this->assertEquals($expected, $result);
	}

	/**
	 * Convenience method for testing: Initializes the controller and the Ratings component
	 *
	 * @param array $params Controller params
	 * @param array $options
	 * @return \Cake\Http\Response|null
	 */
	protected function _initControllerAndRatings(array $params = [], array $options = []) {
		$_default = ['?' => [], 'pass' => []];
		$this->Controller->getRequest()->params = array_merge($_default, $params);
		/*
		if ($this->Controller->getRequest()->params['?'])) {
			$this->Controller->getRequest()->query = $this->Controller->getRequest()->params['?'];
		}
		*/

		$this->Controller->components()->unload('Rating');

		$defaultOptions = isset($this->Controller->components['Ratings.Rating']) ? $this->Controller->components['Ratings.Rating'] : [];
		$this->Controller->loadComponent('Ratings.Rating', $options + $defaultOptions);
		$event = new Event('startup', $this->Controller);

		return $this->Controller->Rating->startup($event);
	}

}
