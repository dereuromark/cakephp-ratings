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
use Cake\Network\Request;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\Helper\FormHelper;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Ratings\View\Helper\RatingHelper;

/**
 * CakePHP Ratings Plugin
 *
 * Rating helper tests
 *
 * @package 	ratings
 * @subpackage 	ratings.tests.cases.helpers
 */
class RatingHelperTest extends TestCase {

	/**
	 * Helper being tested
	 *
	 * @var RatingHelper
	 */
	public $Rating;

	/**
	 * (non-PHPdoc)
	 * @see cake/tests/lib/TestCase#startTest($method)
	 */
	public function setUp() {
		parent::setUp();
		$this->request = new Request();
		$this->Controller = new Controller();
		$this->View = new View($this->request);
		$this->Rating = new RatingHelper($this->View);
		//$this->Rating->Form = new FormHelper($this->View);
		//$this->Rating->Html = new HtmlHelper($this->View);
		//$this->Rating->Form->Html = $this->Rating->Html;
		//$this->Rating->Form->params['action'] = 'add';

		//ClassRegistry::addObject('view', $this->View);
		Router::reload();
	}

	/**
	 * Test percentage method
	 *
	 * @return void
	 */
	public function testPercentage() {
		$this->assertEquals('40', $this->Rating->percentage(2, 5));
		$this->assertEquals('0', $this->Rating->percentage(0, 0));
		$this->assertEquals('100', $this->Rating->percentage(6, 6));
	}

	/**
	 * Test bar method
	 *
	 * @return void
	 */
	public function testBar() {
		$result = $this->Rating->bar(1, 2);
		$expected = '<div class="barRating"><div style="width: 50%" class="inner"><span>1</span></div></div>';
		$this->assertEquals($expected, $result);

		$result = $this->Rating->bar(1, 4, ['innerHtml' => '<span>%percentage%</span>']);
		$expected = '<div class="barRating"><div style="width: 25%" class="inner"><span>25</span></div></div>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * Test display method exception
	 *
	 * @return void
	 * @expectedException \Exception
	 */
	public function testDisplayException() {
		$this->Rating->display();
	}

	/**
	 * Test display method
	 *
	 * @return void
	 */
	public function testDisplay() {
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
	 * (non-PHPdoc)
	 * @see cake/tests/lib/TestCase#endTest($method)
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Rating);
		//TableRegistry::flush();
	}
}
