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

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Session;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Test ArticlesTestController
 */
class ArticlesTestController extends Controller {

	/**
	 * Models used
	 *
	 * @var string
	 */
	public $modelClass = 'Articles';

	/**
	 * Helpers used
	 *
	 * @var array
	 */
	public $helpers = ['Html', 'Form'];

	/**
	 * Components used
	 *
	 * @var array
	 */
	public $components = ['Ratings.Rating', 'Auth', 'Flash'];

	/**
	 * test method
	 *
	 * @return void
	 */
	public function test() {
		return;
	}

	/**
	 * Overloaded redirect
	 *
	 * @param string $url
	 * @param string|null $status
	 * @param string $exit
	 * @return void
	 */
	public function redirect($url, $status = null) {
		$this->redirect = $url;
	}

}

class RatingComponentTest extends TestCase {

	/**
	 * Controller using the tested component
	 *
	 * @var \Cake\Controller\Controller
	 */
	public $Controller;

	/**
	 * Mock AuthComponent object
	 *
	 * @var MockAuthComponent
	 */
	public $AuthComponent;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'core.sessions',
		'plugin.ratings.ratings',
		'plugin.ratings.articles',
		'plugin.ratings.users'
	];

	/**
	 * startTest method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->session = new Session();

		$this->session->write('foo', 'bar');
		$this->session->delete('foo');

		$this->Controller = new ArticlesTestController(new Request());
		//$this->Controller->constructClasses();

		//$this->Collection = $this->getMock('ComponentRegistry');

		/*
		if (!class_exists('MockAuthComponent')) {
 			$this->getMock('AuthComponent', array('user'), array($this->Collection), "MockAuthComponent");
		}

		$this->AuthComponent = new MockAuthComponent($this->Collection);
		$this->AuthComponent->enabled = true;
		$this->Controller->Auth = $this->AuthComponent;
		*/
	}

	/**
	 * endTest method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		$this->session->destroy();
		unset($this->Controller);
		TableRegistry::clear();
	}

	/**
	 * testInitialize
	 *
	 * @return void
	 */
	public function testInitialize() {
		$this->_initControllerAndRatings([], false);
		$this->assertEquals(['Html' => null, 'Form' => null, 'Ratings.Rating'], $this->Controller->helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
		//$this->assertEquals('Articles', $this->Controller->Ratings->modelName);
	}

	/**
	 * testInitializeWithParamsForBehavior
	 *
	 * @return void
	 */
	public function testInitializeWithParamsForBehavior() {
		$this->Controller->components = [
			'Ratings.Ratings' => [
				'update' => true],
			'Auth'];

		$this->_initControllerAndRatings([]);
		$this->assertEquals([
			'Html' => null, 'Form' => null, 'Ratings.Rating'], $this->Controller->helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
		$this->assertTrue($this->Controller->Articles->behaviors()->Ratable->config('update'), 'Ratable behavior should be updatable.');
		//$this->assertEquals('Articles', $this->Controller->Ratings->modelName);
	}

	/**
	 * testInitializeWithParamsForComponent
	 *
	 * @return void
	 */
	public function testInitializeWithParamsForComponent() {
		$this->Controller->components = [
			'Ratings.Ratings' => [
				'actions' => ['show']],
			'Auth'];

		$this->_initControllerAndRatings(['action' => 'show']);
		$this->assertEquals(['Html' => null, 'Form' => null, 'Ratings.Rating'], $this->Controller->helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
		$this->assertEquals(['show'], $this->Controller->Ratings->config('actions'));
		//$this->assertEquals('Articles', $this->Controller->Ratings->config('modelName'));
	}

	/**
	 * testStartup
	 *
	 * @return void
	 */
	public function testStartup() {
		/*
		$this->AuthComponent
			->expects($this->any())
			->method('user')
			->with('id')
			->will($this->returnValue(array('1')));
		*/

		$params = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'pass' => [],
			'?' => [
				'rating' => '5',
				'rate' => '2',
				'redirect' => true]];
		$expectedRedirect = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test'];
/*
		$this->Controller->Session->expectCallCount('setFlash', 3);

		$this->Controller->Session->expectAt(0, 'setFlash', array('Your rate was successfull.', 'default', array(), 'success'));
		$this->Controller->Session->expectAt(1, 'setFlash', array('You have already rated.', 'default', array(), 'error'));
		$this->Controller->Session->expectAt(2, 'setFlash', array('Invalid rate.', 'default', array(), 'error'));
*/
//		$this->Controller->Session->write('Message', null);
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);

//		$this->Controller->Session->write('Message', null);
		$params['?']['rate'] = '1';
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);

//		$this->Controller->Session->write('Message', null);
		$params['?']['rate'] = 'invalid-record!';
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);
	}

	/**
	 * testStartupAcceptPost
	 *
	 * @return void
	 */
	public function testStartupAcceptPost() {
		$this->session->write('Auth.User.id', 1);
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
				'redirect' => true]];
		$expectedRedirect = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test'];
		$this->Controller->request->data = ['rating' => 2];

		//$this->Controller->Session->write('Message', null);

		//$this->Controller->Session->expects($this->any())->method('setFlash');
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);
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
				'rating' => 'test',
				'rate' => '5',
				'redirect' => true]];
		$this->_initControllerAndRatings($params);

		$result = $this->Controller->Ratings->buildUrl();
		$expected = [
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'?' => ['foo' => 'bar']
		];
		$this->assertEquals($expected, $result);
	}

	/**
	 * Convenience method for testing: Initializes the controller and the Ratings component
	 *
	 * @param array $params Controller params
	 * @return void
	 */
	protected function _initControllerAndRatings($params = []) {
		$_default = ['?' => [], 'pass' => []];
		$this->Controller->request->params = array_merge($_default, $params);
		if (!empty($this->Controller->request->params['?'])) {
			$this->Controller->request->query = $this->Controller->request->params['?'];
		}

		$this->Controller->components()->unload('Ratings');

		$options = isset($this->Controller->components['Ratings.Ratings']) ? $this->Controller->components['Ratings.Ratings'] : [];
		$this->Controller->loadComponent('Ratings.Ratings', $options);
		$event = new Event('beforeFilter', $this->Controller);
		$this->Controller->Ratings->beforeFilter($event);
		//$this->Controller->Components->trigger('initialize', array(&$this->Controller));
		//$this->Controller->Auth = $this->AuthComponent;
		//$this->Controller->Ratings->beforeFilter($this->Controller);
	}

}
