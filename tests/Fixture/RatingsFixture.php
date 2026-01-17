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
 * Rating fixture
 */
class RatingsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public array $fields = [
		'id' => ['type' => 'integer', 'null' => false],
		'user_id' => ['type' => 'integer', 'null' => true, 'default' => null],
		'foreign_key' => ['type' => 'integer', 'null' => false, 'default' => null],
		'model' => ['type' => 'string', 'null' => false, 'default' => null],
		'value' => ['type' => 'float', 'null' => false, 'default' => '0'],
		'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => false, 'default' => null],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']], 'UNIQUE_RATING' => ['type' => 'unique', 'columns' => ['user_id', 'foreign_key', 'model']]],
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public array $records = [
		[
			'user_id' => 1,
			'foreign_key' => 1, // first article
			'model' => 'Articles',
			'value' => 1,
			'created' => '2009-01-01 12:12:12',
			'modified' => '2009-01-01 12:12:12',
		],
		[
			'user_id' => 1,
			'foreign_key' => 1, // first post
			'model' => 'Posts',
			'value' => 1,
			'created' => '2009-01-01 12:12:12',
			'modified' => '2009-01-01 12:12:12',
		],
		[
			'user_id' => 1,
			'foreign_key' => 2, // second post
			'model' => 'Posts',
			'value' => 3,
			'created' => '2009-01-01 12:12:12',
			'modified' => '2009-01-01 12:12:12',
		],
	];

}
