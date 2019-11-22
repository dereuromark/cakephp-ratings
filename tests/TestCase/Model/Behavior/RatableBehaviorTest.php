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

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakePHP Ratings Plugin
 *
 * Ratable behavior tests
 */
class RatableBehaviorTest extends TestCase {

	/**
	 * Holds the instance of the model
	 *
	 * @var \Cake\ORM\Table|\Ratings\Model\Behavior\RatableBehavior
	 */
	public $Articles;

	/**
	 * @var \Cake\ORM\Table|\Ratings\Model\Behavior\RatableBehavior
	 */
	protected $Posts;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Ratings.Ratings',
		'plugin.Ratings.Articles',
		'plugin.Ratings.Posts',
		'plugin.Ratings.Users',
	];

	/**
	 * startTest
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Articles = TableRegistry::get('Articles');
		$this->Posts = TableRegistry::get('Posts');
	}

	/**
	 * endTest
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Articles);
		unset($this->Posts);
		TableRegistry::clear();
	}

	/**
	 * Testing calculation of the rating
	 *
	 * @return void
	 */
	public function testCalculateRating() {
		$this->Articles->addBehavior('Ratings.Ratable', []);
		$result = $this->Articles->calculateRating(1);
		$this->assertEquals('1.0000', $result['rating']);

		$result = $this->Articles->calculateRating(1, false);
		$this->assertEquals('1.0000', $result);

		$result = $this->Articles->calculateRating(1, 'title');
		$this->assertEquals($result['title'], '1.0000');

		$result = $this->Articles->calculateRating(2);
		$this->assertEquals($result['rating'], '0');

		$data = [
			'foreign_key' => 1,
			'model' => 'Articles',
			'user_id' => '3',
			'value' => 2.5000];
		$rating = $this->Articles->Ratings->newEntity($data);
		$this->Articles->Ratings->save($rating);
		$result = $this->Articles->calculateRating(1);
		$this->assertEquals('1.75000000', $result['rating']);
	}

	/**
	 * Testing calculation of the rating
	 *
	 * @expectedException \BadMethodCallException
	 * @return void
	 */
	public function testCalculateRatingException() {
		$this->Articles->calculateRating(1, true, 'pow');
	}

	/**
	 * Testing update of the rating
	 *
	 * @return void
	 */
	public function testIncrementRating() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->incrementRating(1, 1)->toArray();
		$this->assertEquals($result['rating'], '1.0000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 2);
	}

	/**
	 * @return void
	 */
	public function testIncrementRatingCalc() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->incrementRating(1, 1, false);
		$this->assertEquals($result, '1.0000');
	}

	/**
	 * @return void
	 */
	public function testIncrementRatingOtherField() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->incrementRating(1, 1, 'title')->toArray();
		$this->assertEquals($result['title'], '1.0000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 2);
	}

	/**
	 * @return void
	 */
	public function testIncrementRatingCalc2() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->incrementRating(2, 1)->toArray();
		$this->assertEquals($result['rating'], '2');
	}

	/**
	 * @return void
	 */
	public function testIncrementRatingNewRating() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$data = [
			'foreign_key' => '1',
			'model' => 'Posts',
			'user_id' => '3',
			'value' => 2.5000];
		$rating = $this->Posts->Ratings->newEntity($data);
		$this->Posts->Ratings->save($rating);
		$result = $this->Posts->incrementRating(1, 2.5)->toArray();
		$this->assertEquals($result['rating'], '1.75000000');

		//$this->expectException('InvalidArgumentException');
		//$this->Posts->incrementRating(1, 1, true, 'pow');
	}

	/**
	 * @return void
	 */
	public function testDecrementRating() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->decrementRating(1, 1);
		$this->assertEquals($result['rating'], '0.0000');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

	/**
	 * @return void
	 */
	public function testDecrementRatingCalc() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->decrementRating(1, 1, false);
		$this->assertEquals('0.0', $result);
	}

	/**
	 * @return void
	 */
	public function testDecrementRatingOtherField() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->decrementRating(1, 1, 'title');
		$this->assertEquals($result['title'], '0.0000');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

	/**
	 * @return void
	 */
	public function testDecrementRatingCalc2() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$result = $this->Posts->decrementRating(2, 1);
		$this->assertEquals($result['rating'], '0');
	}

	/**
	 * @return void
	 */
	public function testDecrementRatingNewRating() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$data = [
			'foreign_key' => 1,
			'model' => 'Posts',
			'user_id' => '3',
			'value' => 3.5,
		];
		$rating = $this->Posts->Ratings->newEntity($data);
		$this->Posts->Ratings->saveOrFail($rating);

		// The current record has one record votes with value 1 (sum 1)
		$result = $this->Posts->incrementRating(1, 2.5);
		$this->assertEquals('1.75', $result['rating']);
		$this->assertEquals('3.5', $result['rating_sum']);

		$result = $this->Posts->decrementRating(1, 2.5);
		$this->assertEquals('1.0', $result['rating']);
		$this->assertEquals('1.0', $result['rating_sum']);

		$result = $this->Posts->incrementRating(1, 3);
		$this->assertEquals('2.0', $result['rating']);
		$this->assertEquals('4.0', $result['rating_sum']);
	}

	/**
	 * @expectedException \BadMethodCallException
	 * @return void
	 */
	public function testDecrementRatingException() {
		$this->Posts->decrementRating(1, 1, true, 'pow');
	}

	/**
	 * testSaveRating
	 *
	 * @return void
	 */
	public function testSaveRating() {
		$this->Articles->addBehavior('Ratings.Ratable', []);
		$userId = '2'; // floriank
		$result = $this->Articles->saveRating(1, $userId, 4)->toArray();

		$this->assertEquals($result['rating'], '2.5000');

		$userId = '1'; // phpnut
		$this->assertFalse($this->Articles->saveRating(1, $userId, 4));
	}

	/**
	 * testSaveRating
	 *
	 * @return void
	 */
	public function testSaveRatingWithAdditionalFields() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$userId = '2'; // floriank
		$result = $this->Posts->saveRating(1, $userId, 4)->toArray();

		$this->assertEquals($result['rating'], '2.5000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 5);

		$userId = '1'; // phpnut
		$this->assertFalse($this->Posts->saveRating(1, $userId, 4));
	}

	/**
	 * testSaveRating
	 *
	 * @return void
	 */
	public function testSaveUpdatedRating() {
		$this->Posts->addBehavior('Ratings.Ratable', [
			'update' => true]);
		$userId = '1'; // phpnut
		$result = $this->Posts->saveRating(1, $userId, 3)->toArray();

		$this->assertEquals($result['rating'], '3');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 3);
	}

	/**
	 * @return void
	 */
	public function testSaveUpdatedRatingForNewRating() {
		$this->Posts->addBehavior('Ratings.Ratable', [
			'update' => true]);
		$userId = '1'; // phpnut
		$result = $this->Posts->saveRating(3, $userId, 5)->toArray();

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
		$this->Articles->addBehavior('Ratings.Ratable', []);
		$userId = '2'; // floriank
		$result = $this->Articles->saveRating(1, $userId, 4)->toArray();

		$this->assertEquals($result['rating'], '2.5000');

		$result = $this->Articles->removeRating(1, $userId)->toArray();

		$this->assertEquals($result['rating'], '1.0000');

		$userId = '1'; // phpnut
		$this->assertFalse($this->Articles->saveRating(1, $userId, 4));
	}

	/**
	 * testSaveRating
	 *
	 * @return void
	 */
	public function testRemoveRatingWithAdditionalFields() {
		$this->Posts->addBehavior('Ratings.Ratable', []);
		$userId = '2'; // floriank
		$result = $this->Posts->saveRating(1, $userId, 4)->toArray();

		$this->assertEquals($result['rating'], '2.5000');
		$this->assertEquals($result['rating_count'], 2);
		$this->assertEquals($result['rating_sum'], 5);

		$result = $this->Posts->removeRating(1, $userId)->toArray();

		$this->assertEquals($result['rating'], '1.0000');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 1);

		$userId = '5'; // somebody
		$this->assertFalse($this->Posts->removeRating(1, $userId));
	}

	/**
	 * testSaveRating
	 *
	 * @return void
	 */
	public function testRemoveUpdatedRating() {
		$this->Posts->addBehavior('Ratings.Ratable', [
			'update' => true]);
		$userId = '1'; // phpnut
		$result = $this->Posts->saveRating(1, $userId, 3)->toArray();

		$this->assertEquals($result['rating'], '3');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 3);

		$result = $this->Posts->removeRating(1, $userId)->toArray();

		$this->assertEquals($result['rating'], '0');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

	/**
	 * @return void
	 */
	public function testRemoveUpdatedRatingForNewRating() {
		$this->Posts->addBehavior('Ratings.Ratable', [
			'update' => true]);
		$userId = '1'; // phpnut
		$result = $this->Posts->saveRating(3, $userId, 5)->toArray();

		$this->assertEquals($result['rating'], '5');
		$this->assertEquals($result['rating_count'], 1);
		$this->assertEquals($result['rating_sum'], 5);

		$result = $this->Posts->removeRating(3, $userId)->toArray();

		$this->assertEquals($result['rating'], '0');
		$this->assertEquals($result['rating_count'], 0);
		$this->assertEquals($result['rating_sum'], 0);
	}

	/**
	 * Testings Ratable::isRatedBy()
	 *
	 * @return void
	 */
	public function testIsRatedBy() {
		$this->Articles->addBehavior('Ratings.Ratable', []);
		$userId = 1; // phpnut
		$foreignKey = 1;
		$result = $this->Articles->isRatedBy($foreignKey, $userId)->count();

		$this->assertSame(1, $result);

		$userId = 1; // phpnut
		$foreignKey = 3;
		$result = $this->Articles->isRatedBy($foreignKey, $userId)->count();
		$this->assertSame(0, $result);
	}

	/**
	 * Testings Ratable::rate()
	 *
	 * @return void
	 */
	public function testRate() {
		$this->Articles->addBehavior('Ratings.Ratable', []);
		$userId = '3'; // phpnut
		$foreignKey = 1;
		$result = $this->Articles->rate($foreignKey, $userId, 'up');
		$this->assertTrue($result);

		//$this->expectException('RuntimeException');
		//$this->Articles->rate($foreignKey, $userId, 'up');

		//$this->expectException('OutOfBoundsException');
		//$this->Articles->rate('does-not-exist', $userId, 'up');

		//$this->expectException('OutOfBoundsException');
		//$this->Articles->rate($foreignKey, $userId, 'invalid-rating');

		//$this->expectException('LogicException');
		//$this->Articles->rate($foreignKey, 0, 'up');
	}

	/**
	 * Testings Ratable::cacheRatingStatistics()
	 *
	 * @return void
	 */
	public function testCacheRatingStatistics() {
		$this->Articles->addBehavior('Ratings.Ratable', []);
		$this->Articles->saveRating(1, 4, 3);

		$data = [
			'type' => 'saveRating',
			'foreignKey' => 1,
			'userId' => 4,
			'value' => 3,
			'update' => false,
			'oldRating' => false,
			'result' => [
				'rating' => 2.00000000,
				'id' => 1]];

		$result = $this->Articles->cacheRatingStatistics($data);
		$this->assertTrue(!empty($result));

		$this->Articles->recursive = -1;
		$result = $this->Articles->get(1)->toArray();
		$this->assertEquals($result['rating_3'], 1);
	}

	/**
	 * @return void
	 */
	public function testCacheRatingStatisticsForRemove() {
		$this->Articles->addBehavior('Ratings.Ratable', []);
		$this->Articles->saveRating(1, 4, 3);

		$oldRating = $this->Articles->Ratings->find('all', [
			'recursive' => -1,
			'conditions' => [
				'Ratings.model' => 'Articles',
				'Ratings.foreign_key' => 1,
				'Ratings.user_id' => 4]])->first();
		$result = $this->Articles->removeRating(1, 4);

		$data = [
			'type' => 'saveRating',
			'foreignKey' => 1,
			'userId' => 4,
			'value' => 3,
			'update' => false,
			'oldRating' => false,
			'result' => [
				'rating' => 2.00000000,
				'id' => 1]];

		$result = $this->Articles->cacheRatingStatistics($data);

		$data = [
			'type' => 'removeRating',
			'foreignKey' => 1,
			'userId' => 4,
			'value' => 3,
			'update' => false,
			'oldRating' => $oldRating,
			'result' => [
				'rating' => 1.00000000,
				'id' => 1]];

		$result = $this->Articles->cacheRatingStatistics($data);
		$this->assertTrue(!empty($result));

		$this->Articles->recursive = -1;
		$result = $this->Articles->get(1)->toArray();
		$this->assertEquals($result['rating_3'], 0);
	}

}
