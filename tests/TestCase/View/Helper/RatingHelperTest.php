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

namespace Ratings\Test\TestCase\View\Helper;

use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Exception;
use Ratings\View\Helper\RatingHelper;

class RatingHelperTest extends TestCase {

	/**
	 * @var \Ratings\View\Helper\RatingHelper
	 */
	protected $Rating;

	/**
	 * @var \Cake\Http\ServerRequest
	 */
	protected $request;

	/**
	 * @var \Cake\View\View
	 */
	protected $View;

	/**
	 * (non-PHPdoc)
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->request = new ServerRequest();
		$this->Controller = new Controller();
		$this->View = new View($this->request);
		$this->Rating = new RatingHelper($this->View);

		Router::reload();
		Router::scope('/', function (RouteBuilder $routes) {
			$routes->fallbacks();
		});
	}

	/**
	 * Test percentage method
	 *
	 * @return void
	 */
	public function testPercentage() {
		$this->assertSame(40.0, $this->Rating->percentage(2, 5));
		$this->assertSame(0, $this->Rating->percentage(0, 0));
		$this->assertSame(100.0, $this->Rating->percentage(6, 6));
	}

	/**
	 * @return void
	 */
	public function testRound() {
		$this->assertSame(3.25, $this->Rating->round(3.31, 4));
		$this->assertSame(5, $this->Rating->round(5.31, 4, 1, 5));
		$this->assertSame(1, $this->Rating->round(0.76, 4, 1, 5));
	}

	/**
	 * Test bar method
	 *
	 * @return void
	 */
	public function testBar() {
		$result = $this->Rating->bar(1, 2);
		$expected = '<div class="bar-rating"><div style="width: 50%" class="inner"><span>1</span></div></div>';
		$this->assertEquals($expected, $result);

		$result = $this->Rating->bar(1.2, 2);
		$expected = '<div class="bar-rating"><div style="width: 60%" class="inner"><span>1,2</span></div></div>';
		$this->assertEquals($expected, $result);

		$result = $this->Rating->bar(1, 4, ['innerHtml' => '<span>%percentage%</span>']);
		$expected = '<div class="bar-rating"><div style="width: 25%" class="inner"><span>25</span></div></div>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testRatingImage() {
		ini_set('intl.default_locale', 'de_DE');

		$result = $this->Rating->ratingImage(3.25);

		$expected = '<div title="3,5 of 5 stars" class="ratingStars clearfix"><i class="fa fa-fw fa-star"></i><i class="fa fa-fw fa-star"></i><i class="fa fa-fw fa-star"></i><i class="fa fa-fw fa-star-half-o"></i><i class="fa fa-fw fa-star-o"></i></div>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testControl() {
		$options = [
			'item' => 3,
			'type' => 'radio',
			'stars' => 5,
			'js' => true,
			'createForm' => ['url' => ['controller' => 'MyController', 'action' => 'myAction', '?' => ['rate' => 3, 'redirect' => true]]],
		];
		$attributes = ['legend' => false];

		$result = $this->Rating->control($options, $attributes);
		$this->assertTextContains('<form method="post" accept-charset="utf-8" action="/my-controller/my-action?rate=3&amp;redirect=1">', $result);
		$this->assertTextContains('<input type="hidden" name="rate" value="3"/>', $result);
		$this->assertTextContains('<select name="rating" id="', $result);
	}

	/**
	 * Tests control() method exception
	 *
	 * @return void
	 */
	public function testControlException() {
		$this->expectException(Exception::class);

		$this->Rating->control([]);
	}

	/**
	 * Test display method
	 *
	 * @deprecated
	 *
	 * @return void
	 */
	public function _testDisplay() {
		$options = [
			'item' => '42',
			'url' => ['controller' => 'Articles', 'action' => 'rate'],
			'stars' => 5];
		$result = $this->Rating->display($options);
		$expected =
		'<ul class="rating rating-0">' .
			'<li class="star1"><a href="/articles/rate?rate=42&amp;rating=1&amp;redirect=1">1</a></li>' .
			'<li class="star2"><a href="/articles/rate?rate=42&amp;rating=2&amp;redirect=1">2</a></li>' .
			'<li class="star3"><a href="/articles/rate?rate=42&amp;rating=3&amp;redirect=1">3</a></li>' .
			'<li class="star4"><a href="/articles/rate?rate=42&amp;rating=4&amp;redirect=1">4</a></li>' .
			'<li class="star5"><a href="/articles/rate?rate=42&amp;rating=5&amp;redirect=1">5</a></li>' .
		'</ul>';
		$this->assertEquals($expected, $result);

		$options = array_merge($options, [
			'type' => 'ol',
			'redirect' => false,
			'value' => '2.25',
			'stars' => '1']);
		$result = $this->Rating->display($options);
		$expected =
		'<ol class="rating rating-2">' .
			'<li class="star1"><a href="/articles/rate?rate=42&amp;rating=1">1</a></li>' .
		'</ol>';
		$this->assertEquals($expected, $result);

		$options = array_merge($options, [
			'type' => 'div']);
		$result = $this->Rating->display($options);
		$expected =
		'<ul class="rating rating-2">' .
			'<li class="star1"><a href="/articles/rate?rate=42&amp;rating=1">1</a></li>' .
		'</ul>';
		$this->assertEquals($expected, $result);

		$options = [
			'item' => '42',
			'type' => 'radio',
			'url' => ['controller' => 'Articles', 'action' => 'rate'],
			'stars' => 2];
		$result = $this->Rating->display($options);

		$expected = '<div class="input radio"><input type="radio" name="data[rating]" id="Rating1" value="1" /><label for="Rating1">1</label><input type="radio" name="data[rating]" id="Rating2" value="2" /><label for="Rating2">2</label></div>';
		//$this->assertEquals($expected, $result);

		$options = [
			'item' => '42',
			'type' => 'radio',
			'url' => ['controller' => 'Articles', 'action' => 'rate'],
			'stars' => 2];
		$result = $this->Rating->display($options);

		$expected = '<div class="input radio"><input type="radio" name="data[rating]" id="Rating1" value="1" /><label for="Rating1">1</label><input type="radio" name="data[rating]" id="Rating2" value="2" /><label for="Rating2">2</label></div>';
		//$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testImage() {
		$result = $this->Rating->image(3.11);
		$expected = '<div data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" title="3 of 5 stars" class="rating-container rating-fa"><div class="rating-stars" data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" style="width: 60%"></div></div>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		unset($this->Rating);
	}

}
