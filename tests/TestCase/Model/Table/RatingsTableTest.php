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

class RatingsTableTest extends TestCase {

	/**
	 * @var \Ratings\Model\Table\RatingsTable
	 */
	public $Ratings;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Ratings.Users',
		'plugin.Ratings.Ratings',
		'plugin.Ratings.Articles',
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

	/**
	 * testRatingInstance
	 *
	 * @return void
	 */
	public function testSave() {
		$data = [
			'model' => 'Foo',
			'foreign_key' => 1,
			'user_id' => 1,
			'value' => 2.0,
		];
		$rating = $this->Ratings->newEntity($data);
		$result = $this->Ratings->save($rating);
		$this->assertTrue((bool)$result, print_r($rating->errors(), true));
	}

}
