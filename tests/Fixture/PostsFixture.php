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
 */
class PostsFixture extends TestFixture {

	/**
	 * fields property
	 *
	 * @var array
	 * @access public
	 */
	public array $fields = [
		'id' => ['type' => 'integer'],
		'title' => ['type' => 'string', 'null' => false],
		'rating' => ['type' => 'float', 'null' => false, 'default' => '0', 'length' => '10,2'],
		'rating_sum' => ['type' => 'float', 'null' => false, 'default' => '0', 'length' => '10,2'],
		'rating_count' => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => '10'],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
	];

	/**
	 * records property
	 *
	 * @var array
	 * @access public
	 */
	public array $records = [
		[
			'title' => 'First Post',
			'rating' => 1.0,
			'rating_sum' => 1,
			'rating_count' => 1,
		],
		[
			'title' => 'Second Post',
			'rating' => 3.0,
			'rating_sum' => 3,
			'rating_count' => 1,
		],
		[
			'title' => '3rd Post',
			'rating' => 0.0,
			'rating_sum' => 0,
			'rating_count' => 0,
		],
	];

}
