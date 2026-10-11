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

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use TestApp\Controller\ArticlesController;

class RatingComponentTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
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
	 * startTest method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Controller = new ArticlesController(new ServerRequest());
		$this->Controller->setEventManager(new EventManager());

		$builder = Router::createRouteBuilder('/');
		$builder->setRouteClass(DashedRoute::class);
		$builder->scope('/', function (RouteBuilder $routes): void {
			$routes->fallbacks();
		});
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
		TableRegistry::getTableLocator()->clear();
	}

	/**
	 * testInitialize
	 *
	 * @return void
	 */
	public function testInitialize() {
		$this->_initControllerAndRatings();
		$helpers = $this->Controller->viewBuilder()->getHelpers();
		$this->assertArrayHasKey('Rating', $helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
	}

	/**
	 * testInitializeWithParamsForBehavior
	 *
	 * @return void
	 */
	public function testInitializeWithParamsForBehavior() {
		$this->Controller->components()->unload('Rating');
		$this->Controller->loadComponent('Ratings.Rating', [
			'update' => true,
		]);

		$this->_initControllerAndRatings([]);
		$helpers = $this->Controller->viewBuilder()->getHelpers();
		$this->assertArrayHasKey('Rating', $helpers);
		$this->assertTrue($this->Controller->Articles->behaviors()->has('Ratable'), 'Ratable behavior should attached.');
		$this->assertTrue($this->Controller->Articles->behaviors()->Ratable->getConfig('update'), 'Ratable behavior should be updatable.');
	}

	/**
	 * testInitializeWithParamsForComponent
	 *
	 * @return void
	 */
	public function testInitializeWithParamsForComponent() {
		$this->Controller->components()->unload('Rating');
		//$this->Controller->loadComponent('Auth');
		$this->Controller->loadComponent('Ratings.Rating', [
			'actions' => ['show'],
		]);

		$this->_initControllerAndRatings(['action' => 'show']);
		$helpers = $this->Controller->viewBuilder()->getHelpers();
		$this->assertArrayHasKey('Rating', $helpers, print_r($helpers, true));
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
		$this->Controller->getRequest()->getSession()->delete('Flash');

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
			'action' => 'test',
		];
		/*
		$this->Controller->getRequest()->getSession()->expectAt(0, 'setFlash', array('Your rate was successfull.', 'default', array(), 'success'));
		$this->Controller->getRequest()->getSession()->expectAt(1, 'setFlash', array('You have already rated.', 'default', array(), 'error'));
		$this->Controller->getRequest()->getSession()->expectAt(2, 'setFlash', array('Invalid rate.', 'default', array(), 'error'));
		*/
		$this->Controller->getRequest()->getSession()->delete('Flash');
		ServerRequest::addDetector('post', function() {
			return true;
		});
		$result = $this->_initControllerAndRatings($params);
		$url = $result->getHeaderLine('Location');
		$this->assertEquals(Router::url($expectedRedirect), $url);

		$sessionFlash = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$expectedFlash = [
			[
				'message' => __d('ratings', 'Not logged in'),
				'key' => 'flash',
				'element' => 'flash/error',
				'params' => [],
			],
		];
		$this->assertSame($expectedFlash, $sessionFlash);

		$this->Controller->getRequest()->getSession()->delete('Flash');
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
				'element' => 'flash/success',
				'params' => [],
			],
		];
		$this->assertSame($expectedFlash, $sessionFlash);

		//$this->Controller->getRequest()->getSession()->write('Flash', null);
		//$params['?']['rate'] = '1';
		//$result = $this->_initControllerAndRatings($params);

		$this->assertEquals(Router::url($expectedRedirect), $url);
	}

	/**
	 * Get with data in URL completely.
	 * Invalid get is not accepted. Value must be posted as payload or at least as part of the query.
	 *
	 * @return void
	 */
	public function testStartupInvalid() {
		$options = [
			'userId' => 1,
		];

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

		$params['?']['rate'] = 'invalid-record!';

		$this->expectException(InvalidArgumentException::class);

		$this->_initControllerAndRatings($params, $options);
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
		$request = $this->Controller->getRequest()->withData('rating', 2);
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
	 * @param array<string, mixed> $options
	 * @return \Cake\Http\Response
	 */
	protected function _initControllerAndRatings(array $params = [], array $options = []) {
		//$_default = ['?' => [], 'pass' => []];
		//$this->Controller->getRequest()->params = array_merge($_default, $params);
		/*
		if ($this->Controller->getRequest()->params['?'])) {
			$this->Controller->getRequest()->query = $this->Controller->getRequest()->params['?'];
		}
		*/
		$request = $this->Controller->getRequest();
		foreach ($params as $key => $param) {
			$request = $request->withParam($key, $param);
		}
		if (isset($params['?'])) {
			$request = $request->withQueryParams($params['?']);
		}

		$this->Controller->setRequest($request);

		$defaultOptions = $this->Controller->components()->get('Rating')->getConfig();
		$this->Controller->components()->unload('Rating');

		$options['className'] = 'Ratings.Rating';
		$this->Controller->loadComponent('Rating', $options + $defaultOptions);
		$event = new Event('startup', $this->Controller);

		return $this->Controller->Rating->startup($event) ?: $this->Controller->getResponse();
	}

	public function testJsonRedirectSerializesRatingWithoutATemplate(): void {
		$this->_initControllerAndRatings(['controller' => 'Articles', 'action' => 'view', 'pass' => [1]]);
		$this->Controller->setRequest($this->Controller->getRequest()->withParam('isJson', true));
		$this->Controller->set(['status' => 'success', 'message' => 'Rated', 'rating' => 4]);
		$response = $this->Controller->Rating->redirect('/articles/view/1');
		$this->assertSame('application/json', $response->getType());
		$data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
		$this->assertSame(['status' => 'success', 'message' => 'Rated', 'rating' => 4], $data);
	}

	public function testJsonAuthMessageProducesAnErrorResponse(): void {
		$this->_initControllerAndRatings(['controller' => 'Articles', 'action' => 'view', 'pass' => [1]]);
		$this->Controller->setRequest($this->Controller->getRequest()->withParam('isJson', true));
		$this->Controller->set('authMessage', 'Sign in first');
		$response = $this->Controller->Rating->redirect('/articles/view/1');
		$data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
		$this->assertSame('error', $data['status']);
		$this->assertSame('Sign in first', $data['message']);
	}

	public function testStartupPreservesControllerHelpers(): void {
		$this->Controller->viewBuilder()->addHelper('Text');
		$this->_initControllerAndRatings(['controller' => 'Articles', 'action' => 'view', 'pass' => [1]]);
		$this->assertArrayHasKey('Text', $this->Controller->viewBuilder()->getHelpers());
		$this->assertArrayHasKey('Rating', $this->Controller->viewBuilder()->getHelpers());
	}

	public function testNumericSavedAggregateIsASuccess(): void {
		$this->_initControllerAndRatings(['controller' => 'Articles', 'action' => 'view', 'pass' => [1]], ['saveToField' => false]);
		$this->Controller->Rating->rate(1, 4, 2);
		$this->assertSame('success', $this->Controller->viewBuilder()->getVar('status'));
		$this->assertSame(3.0, $this->Controller->viewBuilder()->getVar('rating'));
	}

	public function testRequestCannotSupplyAnExternalRedirect(): void {
		$this->Controller->setRequest($this->Controller->getRequest()->withMethod('POST'));
		$this->expectException(BadRequestException::class);
		$this->_initControllerAndRatings([
			'controller' => 'Articles',
			'action' => 'view',
			'pass' => [1],
			'?' => ['rate' => 1, 'rating' => 4, 'redirect' => 'https://example.org/'],
		], ['userId' => 2]);
	}

	public function testConfiguredRedirectTargetStillWorks(): void {
		$this->Controller->setRequest($this->Controller->getRequest()->withMethod('POST'));
		$this->_initControllerAndRatings([
			'controller' => 'Articles',
			'action' => 'view',
			'pass' => [1],
			'?' => ['rate' => 1, 'rating' => 4, 'redirect' => '1'],
		], ['userId' => 2, 'params' => ['redirect' => '/ratings/thanks']]);
		$this->assertSame('/ratings/thanks', $this->Controller->getResponse()->getHeaderLine('Location'));
	}

	public function testComponentUsesConfiguredResultField(): void {
		$this->_initControllerAndRatings(['controller' => 'Articles', 'action' => 'view', 'pass' => [1]], ['saveToField' => 'title']);
		$this->Controller->Rating->rate(1, 4, 2);
		$this->assertSame('success', $this->Controller->viewBuilder()->getVar('status'));
		$this->assertSame(3.0, $this->Controller->viewBuilder()->getVar('rating'));
	}

	public function testComponentUsesTheTablePrimaryKey(): void {
		$this->Controller->getTableLocator()->get('Articles')->setPrimaryKey('user_id');
		$this->_initControllerAndRatings(['controller' => 'Articles', 'action' => 'view', 'pass' => [5]]);
		$this->Controller->Rating->rate(5, 4, 2);
		$this->assertSame('success', $this->Controller->viewBuilder()->getVar('status'));
		$this->assertSame(4.0, $this->Controller->viewBuilder()->getVar('rating'));
	}

	public function testUnrelatedPostDoesNotValidateTheRedirect(): void {
		$this->Controller->setRequest($this->Controller->getRequest()->withMethod('POST'));
		$this->_initControllerAndRatings([
			'controller' => 'Articles',
			'action' => 'view',
			'pass' => [1],
			'?' => ['redirect' => '/posts/view/1'],
		]);
		$this->assertSame(200, $this->Controller->getResponse()->getStatusCode());
	}

	public function testAnonymousRateDoesNotRequireTableResolution(): void {
		$this->Controller->components()->unload('Rating');
		$this->Controller->loadComponent('Ratings.Rating');
		$this->Controller->Rating->startup(new Event('startup', $this->Controller));
		$this->Controller->Rating->setConfig('modelName', null);
		$this->Controller->Rating->rate(1, 4, null);
		$this->assertSame('Not logged in', $this->Controller->viewBuilder()->getVar('message'));
	}

}
