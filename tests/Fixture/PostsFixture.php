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
namespace Ratings\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CakePHP Ratings Plugin
 *
 * Post fixture
 *
 * @package 	ratings
 * @subpackage 	ratings.tests.fixtures
 */
class PostsFixture extends TestFixture {

	/**
	 * fields property
	 *
	 * @var array
	 * @access public
	 */
	public $fields = [
		'id' => ['type' => 'integer'],
		'title' => ['type' => 'string', 'null' => false],
		'rating' => ['type' => 'float', 'null' => false, 'default' => '0', 'length' => '10,2'],
		'rating_sum' => ['type' => 'float', 'null' => false, 'default' => '0', 'length' => '10,2'],
		'rating_count' => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => '10'],
		'integer_rating' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 5],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
	];

	/**
	 * records property
	 *
	 * @var array
	 * @access public
	 */
	public $records = [
		[
			'id' => 1,
			'title' => 'First Post',
			'rating' => 1.0,
			'rating_sum' => 1,
			'rating_count' => 1,
			'integer_rating' => 1],
		[
			'id' => 2,
			'title' => 'Second Post',
			'rating' => 3.0,
			'rating_sum' => 3,
			'rating_count' => 1,
			'integer_rating' => -3],
		[
			'id' => 3,
			'title' => '3rd Post',
			'rating' => 0.0,
			'rating_sum' => 0,
			'rating_count' => 0,
			'integer_rating' => 0],
	];

}
