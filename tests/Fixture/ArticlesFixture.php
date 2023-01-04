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
 * Article fixture
 */
class ArticlesFixture extends TestFixture {

	/**
	 * fields property
	 *
	 * @var array
	 * @access public
	 */
	public array $fields = [
		'id' => ['type' => 'integer'],
		'user_id' => ['type' => 'integer', 'null' => false, 'length' => 10],
		'title' => ['type' => 'string', 'null' => false],
		'rating' => ['type' => 'float', 'null' => false, 'default' => '0', 'length' => '10,2'],
		'rating_1' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 5],
		'rating_2' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 5],
		'rating_3' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 5],
		'rating_4' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 5],
		'rating_5' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 5],
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
			'id' => 1,
			'user_id' => 0,
			'title' => 'First Article',
			'rating' => 1.0000,
			'rating_1' => 0,
			'rating_2' => 0,
			'rating_3' => 0,
			'rating_4' => 0,
			'rating_5' => 0],
		[
			'id' => 2,
			'user_id' => 0,
			'title' => 'First Article',
			'rating' => 3.0000,
			'rating_1' => 0,
			'rating_2' => 0,
			'rating_3' => 0,
			'rating_4' => 0,
			'rating_5' => 0]];

}
