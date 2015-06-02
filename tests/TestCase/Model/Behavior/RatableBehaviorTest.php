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
namespace Ratings\Test\TestCase\Model\Behavior;

use App\Model\Model;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * CakePHP Ratings Plugin
 *
 * Ratable behavior tests
 *
 * @package 	ratings
 * @subpackage 	ratings.tests.cases.behaviors
 */
class RatableBehaviorTest extends TestCase {

/**
 * Holds the instance of the model
 *
 * @var mixed
 */
	public $Article = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.ratings.ratings',
		'plugin.ratings.articles',
		'plugin.ratings.posts',
		'plugin.ratings.users'
	);

/**
 * startTest
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Article = TableRegistry::get('Articles');
		$this->Post = TableRegistry::get('Posts');

		//$this->loadFixtures('Rating');
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Article);
		unset($this->Post);
		TableRegistry::clear();
	}

/**
 * Testing calculation of the rating
 *
 * @return void
 */
	public function testCalculateRating() {
		$this->Article->addBehavior('Ratings.Ratable', array());
		$result = $this->Article->calculateRating(1);
		$this->assertEquals($result['rating'], '1.0000');

		$result = $this->Article->calculateRating(1, false);
		$this->assertEquals($result, '1.0000');

		$result = $this->Article->calculateRating(1, 'title');
		$this->assertEquals($result['title'], '1.0000');

		$result = $this->Article->calculateRating(2);
		$this->assertEquals($result['rating'], '0');

		$data = array(
			'Rating' => array(
				'foreign_key' => '1',
				'model' => 'Article',
				'user_id' => '3',
				'value' => 2.5000));
		$rating = $this->Article->Rating->newEntity($data);
		$this->Article->Rating->save($rating);
		$result = $this->Article->calculateRating(1);
		$this->assertEquals($result['rating'], '1.75000000');

		$this->expectException('InvalidArgumentException');
		$this->Article->calculateRating(1, true, 'pow');
	}

/**
 * Testing update of the rating
 *
 * @return void
 */
	public function testIncrementRating() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->incrementRating(1, 1);
		$this->assertEquals($result['rating'], '1.0000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 2);
	}

	public function testIncrementRatingCalc() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->incrementRating(1, 1, false);
		$this->assertEquals($result, '1.0000');
	}

	public function testIncrementRatingOtherField() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->incrementRating(1, 1, 'title');
		$this->assertEquals($result['title'], '1.0000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 2);
	}

	public function testIncrementRatingCalc2() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->incrementRating(2, 1);
		$this->assertEquals($result['rating'], '2');
	}

	public function testIncrementRatingNewRating() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$data = array(
			'Rating' => array(
				'foreign_key' => '1',
				'model' => 'Post',
				'user_id' => '3',
				'value' => 2.5000));
		$rating = $this->Article->Rating->newEntity($data);
		$this->Article->Rating->save($rating);
		$result = $this->Post->incrementRating(1, 2.5);
		$this->assertEquals($result['rating'], '1.75000000');

		$this->expectException('InvalidArgumentException');
		$this->Post->incrementRating(1, 1, true, 'pow');
	}




	public function testDecrementRating() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->decrementRating(1, 1);
		$this->assertEquals($result['rating'], '0.0000');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

	public function testDecrementRatingCalc() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->decrementRating(1, 1, false);
		$this->assertEquals($result, '0.0000');
	}

	public function testDecrementRatingOtherField() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->decrementRating(1, 1, 'title');
		$this->assertEquals($result['title'], '0.0000');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

	public function testDecrementRatingCalc2() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$result = $this->Post->decrementRating(2, 1);
		$this->assertEquals($result['rating'], '0');
	}

	public function testDecrementRatingNewRating() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$data = array(
			'Rating' => array(
				'foreign_key' => '1',
				'model' => 'Post',
				'user_id' => '3',
				'value' => 2.5000));
		$rating = $this->Article->Rating->newEntity($data);
		$this->Article->Rating->save($rating);
		$result = $this->Post->incrementRating(1, 2.5);
		$this->assertEquals($result['rating'], '1.75000000');

		$result = $this->Post->decrementRating(1, 2.5);
		$this->assertEquals($result['rating'], '1.50000000');

		$this->expectException('InvalidArgumentException');
		$this->Post->decrementRating(1, 1, true, 'pow');
	}

/**
 * testSaveRating
 *
 * @return void
 */
	public function testSaveRating() {
		$this->Article->addBehavior('Ratings.Ratable', array());
		$userId = '2'; // floriank
		$result = $this->Article->saveRating(1, $userId, 4);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '2.5000');

		$userId = '1'; // phpnut
		$this->assertFalse($this->Article->saveRating(1, $userId, 4));
	}

/**
 * testSaveRating
 *
 * @return void
 */
	public function testSaveRatingWithAdditionalFields() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$userId = '2'; // floriank
		$result = $this->Post->saveRating(1, $userId, 4);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '2.5000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 5);

		$userId = '1'; // phpnut
		$this->assertFalse($this->Post->saveRating(1, $userId, 4));
	}

/**
 * testSaveRating
 *
 * @return void
 */
	public function testSaveUpdatedRating() {
		$this->Post->addBehavior('Ratings.Ratable', array(
			'update' => true));
		$userId = '1'; // phpnut
		$result = $this->Post->saveRating(1, $userId, 3);

		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '3');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 3);
	}

	public function testSaveUpdatedRatingForNewRating() {
		$this->Post->addBehavior('Ratings.Ratable', array(
			'update' => true));
		$userId = '1'; // phpnut
		$result = $this->Post->saveRating(3, $userId, 5);

		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '5');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 5);
	}

/**
 * testSaveRating
 *
 * @return void
 */
	public function testRemoveRating() {
		$this->Article->addBehavior('Ratings.Ratable', array());
		$userId = '2'; // floriank
		$result = $this->Article->saveRating(1, $userId, 4);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '2.5000');

		$result = $this->Article->removeRating(1, $userId);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '1.0000');

		$userId = '1'; // phpnut
		$this->assertFalse($this->Article->saveRating(1, $userId, 4));
	}

/**
 * testSaveRating
 *
 * @return void
 */
	public function testRemoveRatingWithAdditionalFields() {
		$this->Post->addBehavior('Ratings.Ratable', array());
		$userId = '2'; // floriank
		$result = $this->Post->saveRating(1, $userId, 4);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '2.5000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 5);

		$result = $this->Post->removeRating(1, $userId);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '1.0000');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 1);

		$userId = '5'; // somebody
		$this->assertFalse($this->Post->removeRating(1, $userId));
	}

/**
 * testSaveRating
 *
 * @return void
 */
	public function testRemoveUpdatedRating() {
		$this->Post->addBehavior('Ratings.Ratable', array(
			'update' => true));
		$userId = '1'; // phpnut
		$result = $this->Post->saveRating(1, $userId, 3);

		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '3');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 3);

		$result = $this->Post->removeRating(1, $userId);

		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '0');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

	public function testRemoveUpdatedRatingForNewRating() {
		$this->Post->addBehavior('Ratings.Ratable', array(
			'update' => true));
		$userId = '1'; // phpnut
		$result = $this->Post->saveRating(3, $userId, 5);

		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '5');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 5);

		$result = $this->Post->removeRating(3, $userId);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['rating'], '0');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

/**
 * Testings Ratable::isRatedBy()
 *
 */
	public function testIsRatedBy() {
		$this->Article->addBehavior('Ratings.Ratable', array());
		$userId = '1'; // phpnut
		$foreignKey = 1;
		$result = $this->Article->isRatedBy($foreignKey, $userId);
		$this->assertEquals(array('Rating' => array(
			'id' => '1',
			'user_id' => '1',
			'foreign_key' => '1',
			'model' => 'Article',
			'value' => '1.0000',
			'created' => '2009-01-01 12:12:12',
			'modified' => '2009-01-01 12:12:12',
		)), $result);

		$userId = '1'; // phpnut
		$foreignKey = array(1, 2);
		$result = $this->Article->isRatedBy($foreignKey, $userId);
		$this->assertEquals($result, array(1));
	}

/**
 * Testings Ratable::rate()
 *
 */
	public function testRate() {
		$this->Article->addBehavior('Ratings.Ratable', array());
		$userId = '3'; // phpnut
		$foreignKey = 1;
		$result = $this->Article->rate($foreignKey, $userId, 'up');
		$this->assertTrue($result);

		$this->expectException('RuntimeException');
		$this->Article->rate($foreignKey, $userId, 'up');

		$this->expectException('OutOfBoundsException');
		$this->Article->rate('does-not-exist', $userId, 'up');

		$this->expectException('OutOfBoundsException');
		$this->Article->rate($foreignKey, $userId, 'invalid-rating');

		$this->expectException('LogicException');
		$this->Article->rate($foreignKey, 0, 'up');
	}

/**
 * Testings Ratable::cacheRatingStatistics()
 *
 */
	public function testCacheRatingStatistics() {
		$this->Article->addBehavior('Ratings.Ratable', array());
		$this->Article->saveRating(1, 4, 3);

		$data = array(
			'type' => 'saveRating',
			'foreignKey' => 1,
			'userId' => 4,
			'value' => 3,
			'update' => false,
			'oldRating' => false,
			'result' => array(
				'Article' => array(
					'rating' => 2.00000000,
					'id' => 1)));

		$result = $this->Article->cacheRatingStatistics($data);
		$this->assertTrue(!empty($result));

		$this->Article->recursive = -1;
		$result = $this->Article->read(null, 1);
		$this->assertEquals($result['rating_3'], 1);
	}

	public function testCacheRatingStatisticsForRemove() {
		$this->Article->addBehavior('Ratings.Ratable', array());
		$this->Article->saveRating(1, 4, 3);

		$oldRating = $this->Article->Rating->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'Rating.model' => 'Article',
				'Rating.foreign_key' => 1,
				'Rating.user_id' => 4)))->first();
		$result = $this->Article->removeRating(1, 4);

		$data = array(
			'type' => 'saveRating',
			'foreignKey' => 1,
			'userId' => 4,
			'value' => 3,
			'update' => false,
			'oldRating' => false,
			'result' => array(
				'Article' => array(
					'rating' => 2.00000000,
					'id' => 1)));

		$result = $this->Article->cacheRatingStatistics($data);

		$data = array(
			'type' => 'removeRating',
			'foreignKey' => 1,
			'userId' => 4,
			'value' => 3,
			'update' => false,
			'oldRating' => $oldRating,
			'result' => array(
				'Article' => array(
					'rating' => 1.00000000,
					'id' => 1)));

		$result = $this->Article->cacheRatingStatistics($data);
		$this->assertTrue(!empty($result));

		$this->Article->recursive = -1;
		$result = $this->Article->read(null, 1);
		$this->assertEquals($result['rating_3'], 0);
	}
}
