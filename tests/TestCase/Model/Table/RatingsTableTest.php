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
namespace Ratings\Test\TestCase\Model;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakePHP Ratings Plugin
 *
 * Rating model tests
 *
 * @package 	ratings
 * @subpackage 	ratings.tests.cases.models
 */
class RatingsTableTest extends TestCase {

	/**
	 * Rating Model
	 *
	 * @var Rating
	 */
	public $Ratings = null;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.ratings.users',
		'plugin.ratings.ratings',
		'plugin.ratings.articles'
	];

	/**
	 * Start Test callback
	 *
	 * @param string $method
	 * @return void
	 */
	public function setUp() {
		Configure::delete('Ratings');
		parent::setUp();

		$this->Ratings = TableRegistry::get('Ratings.Ratings');
	}

	/**
	 * testRatingInstance
	 *
	 * @return void
	 */
	public function testRatingInstance() {
		$this->assertInstanceOf('Ratings\Model\Table\RatingsTable', $this->Ratings);
	}
}
