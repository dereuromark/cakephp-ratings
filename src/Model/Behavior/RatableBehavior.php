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

namespace Ratings\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query\SelectQuery;
use Exception;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
use RuntimeException;

/**
 * @property \Cake\ORM\Table&\Ratings\Model\Behavior\RatableBehavior $_table
 */
class RatableBehavior extends Behavior {

	/**
	 * Default settings
	 *
	 * modelClass - must be set in the case of a plugin model to make the behavior work with plugin models like 'Plugin.Model'
	 * rateClass - name of the rate class model
	 * foreignKey - foreign key field
	 * saveToField - boolean, true if the calculated result should be saved in the rated model
	 * field - name of the field that is updated with the calculated rating
	 * fieldSummary - optional cache field that will store summary of all ratings that allow to implement quick rating calculation
	 * fieldCounter - optional cache field that will store count of all ratings that allow to implement quick rating calculation
	 * calculation - 'average' or 'sum', default is average
	 * update - boolean flag, that define permission to rerate(change previous rating)
	 * modelValidate - validate the model before save, default is false
	 * modelCallbacks - run model callbacks when the rating is saved to the model, default is false
	 * countRates - counter cache
	 *
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'modelClass' => null,
		'rateClass' => 'Ratings.Ratings',
		'foreignKey' => 'foreign_key',
		'field' => 'rating',
		'fieldSummary' => 'rating_sum',
		'fieldCounter' => 'rating_count',
		'calculation' => 'average',
		'saveToField' => true,
		'countRates' => false,
		'update' => false,
		'modelValidate' => false,
		'modelCallbacks' => false,
	];

	/**
	 * Rating modes
	 *
	 * @var array<string, string>
	 */
	protected array $modes = [
		'average' => 'avg',
		'sum' => 'sum',
	];

	/**
	 * @var \Ratings\Model\Entity\Rating|null
	 */
	protected $oldRating;

	/**
	 * Setup
	 *
	 * @param array<string, mixed> $config Config
	 * @return void
	 */
	public function initialize(array $config): void {
		if (empty($this->_config['modelClass'])) {
			$this->_config['modelClass'] = $this->_table->getAlias();
		}

		$this->_table->hasMany('Ratings', [
				'className' => $this->_config['rateClass'],
				'foreignKey' => $this->_config['foreignKey'],
				'unique' => true,
				'dependent' => true,
		]);

		$this->_table->Ratings->belongsTo(
			$this->_config['modelClass'],
			[
				'className' => $this->_config['modelClass'],
				'foreignKey' => 'foreign_key',
				'counterCache' => $this->_config['countRates'],
			],
		);
	}

	/**
	 * Saves a new rating
	 *
	 * @param array<mixed>|string|int $foreignKey
	 * @param string|int $userId
	 * @param float|int $value
	 * @throws \Exception
	 * @return \Ratings\Model\Entity\Rating|float|false Boolean or calculated sum
	 */
	public function saveRating($foreignKey, $userId, $value) {
		if (is_array($foreignKey)) {
			throw new Exception('Array not supported for $foreignKey here');
		}
		if (!$foreignKey) {
			throw new Exception('Empty $foreignKey is not allowed for saveRating()');
		}

		$type = 'saveRating';
		$update = $this->_config['update'];
		$this->beforeRateCallback(compact('foreignKey', 'userId', 'value', 'update', 'type'));
		/** @var \Ratings\Model\Entity\Rating|null $oldRating */
		$oldRating = $this->isRatedBy($foreignKey, $userId)->first();

		if (!$oldRating || $this->_config['update']) {
			$data = [];

			$data['foreign_key'] = $foreignKey;
			$data['model'] = $this->_table->getAlias();
			$data['user_id'] = $userId;
			$data['value'] = $value;
			if ($update) {
				$update = true;
				$this->oldRating = $oldRating;
				if ($oldRating) {
					$this->_table->Ratings->deleteAll([
						'Ratings.model' => $this->_table->getAlias(),
						'Ratings.foreign_key' => $foreignKey,
						'Ratings.user_id' => $userId,
					]);
				}
			} else {
				$oldRating = null;
				$update = false;
			}

			$rating = $this->_table->Ratings->newEntity($data);
			if ($this->_table->Ratings->save($rating)) {
				$fieldCounterType = $this->_table->hasField($this->_config['fieldCounter']);
				$fieldSummaryType = $this->_table->hasField($this->_config['fieldSummary']);
				if ($fieldCounterType && $fieldSummaryType) {
					$result = $this->incrementRating($foreignKey, $value, $this->_config['saveToField'], $this->_config['calculation'], $update);
				} else {
					$result = $this->calculateRating($foreignKey, $this->_config['saveToField'], $this->_config['calculation']);
				}
				$this->afterRateCallback(compact('foreignKey', 'userId', 'value', 'result', 'update', 'oldRating', 'type'));

				return $result;
			}
		}

		return false;
	}

	/**
	 * Remove exists rating
	 *
	 * @param array<mixed>|string|int $foreignKey
	 * @param string|int $userId
	 * @throws \Exception
	 * @return \Ratings\Model\Entity\Rating|float|bool Boolean or calculated sum
	 */
	public function removeRating($foreignKey, $userId) {
		if (is_array($foreignKey)) {
			throw new Exception('Array not supported for $foreignKey here');
		}
		if (!$foreignKey) {
			throw new Exception('Empty $foreignKey is not allowed for removeRating()');
		}

		$type = 'removeRating';
		$update = $this->_config['update'];
		$this->beforeRateCallback(compact('foreignKey', 'userId', 'update', 'type'));
		/** @var \Ratings\Model\Entity\Rating|null $oldRating */
		$oldRating = $this->isRatedBy($foreignKey, $userId)->first();
		if (!$oldRating) {
			return false;
		}

		$this->oldRating = $oldRating;

		$this->_table->Ratings->deleteAll([
			'Ratings.model' => $this->_table->getAlias(),
			'Ratings.foreign_key' => $foreignKey,
			'Ratings.user_id' => $userId,
		]);

		$fieldCounterType = $this->_table->hasField($this->_config['fieldCounter']);
		$fieldSummaryType = $this->_table->hasField($this->_config['fieldSummary']);
		if ($fieldCounterType && $fieldSummaryType) {
			$result = $this->decrementRating($foreignKey, $oldRating['value'], $this->_config['saveToField'], $this->_config['calculation']);
		} else {
			$result = $this->calculateRating($foreignKey, $this->_config['saveToField'], $this->_config['calculation']);
		}
		$this->afterRateCallback(compact('foreignKey', 'userId', 'result', 'update', 'oldRating', 'type'));

		return $result;
	}

	/**
	 * Increments/decrements the rating
	 *
	 * See also Ratable::calculateRating() and decide which one suits your needs better
	 *
	 * @see Ratable::calculateRating()
	 *
	 * @param string|int $id Foreign key
	 * @param int $value Value of new rating
	 * @param mixed $saveToField boolean or field name
	 * @param string $mode type of calculation
	 * @throws \InvalidArgumentException
	 * @return float|bool Boolean or calculated sum
	 */
	public function decrementRating($id, $value, $saveToField = true, $mode = 'average') {
		if (!array_key_exists($mode, $this->modes)) {
			throw new InvalidArgumentException('Invalid rating mode ' . $mode);
		}

		$rating = $this->_table->find('all', [
			'conditions' => [
				$this->_table->getAlias() . '.' . $this->_table->getPrimaryKey() => $id],
		])->first();

		$fieldSummary = $this->_config['fieldSummary'];
		$fieldCounter = $this->_config['fieldCounter'];

		$ratingSumNew = $rating[$fieldSummary] - $value;
		$ratingCountNew = $rating[$fieldCounter] - 1;

		if ($mode === 'average') {
			if ($ratingCountNew === 0) {
				$ratingSum = 0;
			} else {
				$ratingSum = $ratingSumNew / $ratingCountNew;
			}
		} else {
			$ratingSum = $ratingSumNew;
		}

		if ($saveToField || is_string($saveToField)) {
			$save = [];
			if (is_string($saveToField)) {
				$save[$saveToField] = $ratingSum;
			} else {
				$save[$this->_config['field']] = $ratingSum;
			}
			$save[$fieldSummary] = $ratingSumNew;
			$save[$fieldCounter] = $ratingCountNew;

			$rating = $this->_table->patchEntity($rating, $save, ['validate' => $this->_config['modelValidate']]);

			return $this->_table->save($rating, [
				'callbacks' => $this->_config['modelCallbacks']]);
		}

		return $ratingSum;
	}

	/**
	 * Increments/decrements the rating
	 *
	 * See also Ratable::calculateRating() and decide which one suits your needs better
	 *
	 * @see Ratable::calculateRating()
	 *
	 * @param string|int $id foreignKey
	 * @param float|int $value of new rating
	 * @param mixed $saveToField boolean or fieldname
	 * @param string $mode type of calculation
	 * @param bool $update
	 * @throws \InvalidArgumentException
	 * @return \Ratings\Model\Entity\Rating|float|false Boolean or calculated sum
	 */
	public function incrementRating($id, $value, $saveToField = true, $mode = 'average', $update = false) {
		if (!array_key_exists($mode, $this->modes)) {
			throw new InvalidArgumentException('Invalid rating mode ' . $mode);
		}

		$data = $this->_table->find('all', [
			'conditions' => [
				$this->_table->getAlias() . '.' . $this->_table->getPrimaryKey() => $id],
		])->first();

		$fieldSummary = $this->_config['fieldSummary'];
		$fieldCounter = $this->_config['fieldCounter'];

 		if ($update && $this->oldRating !== null) {
			$ratingSumNew = $data[$fieldSummary] - $this->oldRating['value'] + $value;
			$ratingCountNew = $data[$fieldCounter];
		} else {
			$ratingSumNew = $data[$fieldSummary] + $value;
			$ratingCountNew = $data[$fieldCounter] + 1;
		}

		if ($mode === 'average') {
			$rating = $ratingSumNew / $ratingCountNew;
		} else {
			$rating = $ratingSumNew;
		}
		$this->_table->newRating = $rating;

		if ($saveToField || is_string($saveToField)) {
			$save = [];
			if (is_string($saveToField)) {
				$save[$saveToField] = $rating;
			} else {
				$save[$this->_config['field']] = $rating;
			}
			$save[$fieldSummary] = $ratingSumNew;
			$save[$fieldCounter] = $ratingCountNew;
			$r = $this->_table->patchEntity($data, $save, ['validate' => $this->_config['modelValidate']]);

			return $this->_table->save($r, [
				'callbacks' => $this->_config['modelCallbacks']]);
		}

		return $rating;
	}

	/**
	 * Calculates the rating
	 *
	 * This method does always a calculation of the the values based on SQL AVG()
	 * and SUM(). Please note that this is relatively slow compared to incrementing
	 * the values, see Ratable::incrementRating()
	 *
	 * @param array<mixed>|string|int $foreignKey
	 * @param string|bool $saveToField boolean or field name
	 * @param string $mode type of calculation
	 * @throws \Exception
	 * @return \Ratings\Model\Entity\Rating|float|false Boolean or calculated sum
	 */
	public function calculateRating($foreignKey, $saveToField = true, $mode = 'average') {
		if (!array_key_exists($mode, $this->modes)) {
			throw new InvalidArgumentException('Invalid rating mode ' . $mode);
		}
		if (is_array($foreignKey)) {
			throw new Exception('Array not supported for $foreignKey here');
		}

		$mode = $this->modes[$mode];
		$options = [
			'contain' => [$this->_table->getAlias()],
			'fields' => function ($query) use ($mode) {
				/** @var \Cake\Database\Query $query */
				$rating = $query->newExpr()->add($mode . '(value)');

				return [
					'rating' => $rating,
				];
			},
			'conditions' => [
				'Ratings.foreign_key' => $foreignKey,
				'Ratings.model' => $this->_table->getAlias(),
			],
		];

		$result = $this->_table->Ratings->find('all', $options);
		if ($result) {
			$result = $result->toArray();
		}

		if (empty($result[0]['rating'])) {
			$result[0]['rating'] = 0;
		}

		$this->_table->newRating = $result[0]['rating'];
		if (!$saveToField) {
			return $result[0]['rating'];
		}

		if (!is_string($saveToField)) {
			$saveToField = $this->_config['field'];
		}

		if (!$this->_table->hasField($saveToField)) {
			return $result[0]['rating'];
		}

		$data = [
			$this->_table->getPrimaryKey() => $foreignKey,
			$saveToField => $result[0]['rating'],
		];

		$rating = $this->_table->find()->where([$this->_table->getPrimaryKey() => $foreignKey])->firstOrFail();

		$rating = $this->_table->patchEntity($rating, $data, ['validate' => $this->_config['modelValidate']]);

		/** @var \Ratings\Model\Entity\Rating $rating */
		$rating = $this->_table->saveOrFail($rating, [
			'callbacks' => $this->_config['modelCallbacks'],
		]);

		return $rating;
	}

	/**
	 * Method to check if an entry is rated by a certain user
	 *
	 * @param string|int $foreignKey Foreign key as uuid or int
	 * @param string|int $userId
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function isRatedBy($foreignKey, $userId): SelectQuery {
		$entry = $this->_table->Ratings->find('all', [
			'conditions' => [
				'Ratings.foreign_key' => $foreignKey,
				'Ratings.user_id' => $userId,
				'Ratings.model' => $this->_table->getAlias(),
			],
		]);

		return $entry;
	}

	/**
	 * @param string|int $foreignKey Foreign key as uuid or int
	 * @param string|int $userId
	 *
	 * @return bool
	 */
	public function hasRated($foreignKey, $userId) {
		return $this->isRatedBy($foreignKey, $userId)->count() > 0;
	}

	/**
	 * afterRate callback to the model
	 *
	 * @param array<string, mixed> $data
	 * @return void
	 */
	public function afterRateCallback($data = []) {
		if (method_exists($this->_table, 'afterRate')) {
			$this->_table->afterRate($data);
		}
	}

	/**
	 * beforeRate callback to the model
	 *
	 * @param array<string, mixed> $data
	 * @return void
	 */
	public function beforeRateCallback(array $data = []) {
		if (method_exists($this->_table, 'beforeRate')) {
			$this->_table->beforeRate($data);
		}
	}

	/**
	 * More intelligent version of saveRating - checks record existence and ratings
	 *
	 * @param array<mixed>|string|int $foreignKey Integer or string uuid
	 * @param string|int $userId User id
	 * @param mixed $rating Integer or string rating
	 * @param array<string, mixed> $options
	 * @throws \Exception
	 * @return bool
	 */
	public function rate($foreignKey, $userId, $rating, array $options = []) {
		if (is_array($foreignKey)) {
			throw new Exception('Array not supported for $foreignKey here');
		}

		$options = array_merge([
			'userField' => 'user_id',
			'find' => [
				'contain' => [],
				'conditions' => [
					$this->_table->getAlias() . '.' . $this->_table->getPrimaryKey() => $foreignKey]],
			'values' => [
				'up' => 1, 'down' => -1,
			],
		], $options);

		if (!array_key_exists($rating, $options['values'])) {
			throw new OutOfBoundsException(__d('ratings', 'Invalid Rating'));
		}

		$record = $this->_table->find('all', $options['find'])->first();

		if (empty($record)) {
			throw new OutOfBoundsException(__d('ratings', 'Invalid Record'));
		}

		if ($options['userField'] && $this->_table->hasField($options['userField'])) {
			if ($record[$options['userField']] == $userId) {
				//$this->_table->data = $record;
				throw new LogicException(__d('ratings', 'You can not vote on your own records'));
			}
		}

		if ($this->_table->saveRating($foreignKey, $userId, $options['values'][$rating])) {
			//$this->_table->data = $record;
			return true;
		}

		throw new RuntimeException(__d('ratings', 'You have already rated this record'));
	}

	/**
	 * Caches the sum of the different ratings for each of them
	 *
	 * For example a rating of 1 will increase the value in the field "rating_1" by 1,
	 * a rating of 2 will increase "rating_2" by one...
	 *
	 * @param array<string, mixed> $data Data passed to afterRate() or similar structure
	 * @throws \Exception
	 * @return bool True on success
	 */
	public function cacheRatingStatistics(array $data = []) {
		if (empty($data['result'])) {
			return false;
		}

		if ($data['type'] === 'removeRating') {
			$data['value'] = $data['oldRating']['value'];
		}

		if (!$this->_table->hasField($this->_fieldName(round($data['value'], 0)))) {
			return false;
		}

		if (is_array($data['foreignKey'])) {
			throw new Exception('Array not supported for $foreignKey here');
		}

		/** @var \Ratings\Model\Entity\Rating $rating */
		$rating = $this->_table->find('all', [
			'conditions' => [
				$this->_table->getAlias() . '.' . $this->_table->getPrimaryKey() => $data['foreignKey']],
		])->firstOrFail();

		if (($data['update'] || $data['type'] === 'removeRating') && !empty($data['oldRating'])) {
			$oldId = round($data['oldRating']['value']);
			$rating[$this->_fieldName($oldId)] -= 1;
		}

		if ($data['type'] === 'saveRating') {
			$newId = round($data['value']);
			$rating[$this->_fieldName($newId)] += 1;
		}

		return $this->_table->save($rating, [
			'callbacks' => $this->_config['modelCallbacks'],
		]);
	}

	/**
	 * Return field name for cache value
	 *
	 * @param float|int $value
	 * @param string $prefix
	 * @return string
	 */
	protected function _fieldName($value, $prefix = 'rating_') {
		$postfix = $value;
		if ($value < 0) {
			$postfix = 'neg' . abs($value);
		}

		return $prefix . $postfix;
	}

}
