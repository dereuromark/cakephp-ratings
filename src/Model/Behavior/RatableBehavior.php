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
use Cake\Utility\Hash;

/**
 * CakePHP Ratings Plugin
 *
 * Ratable behavior
 *
 * @package 	ratings
 * @subpackage 	ratings.models.behaviors
 */
class RatableBehavior extends Behavior {

/**
 * Settings array
 *
 * @var array
 */
	public $settings = array();

/**
 * Default settings
 *
 * modelClass		- must be set in the case of a plugin model to make the behavior work with plugin models like 'Plugin.Model'
 * rateClass		- name of the rate class model
 * foreignKey		- foreign key field
 * saveToField		- boolean, true if the calculated result should be saved in the rated model
 * field 			- name of the field that is updated with the calculated rating
 * fieldSummary		- optional cache field that will store summary of all ratings that allow to implement quick rating calculation
 * fieldCounter		- optional cache field that will store count of all ratings that allow to implement quick rating calculation
 * calculation		- 'average' or 'sum', default is average
 * update			- boolean flag, that define permission to rerate(change previous rating)
 * modelValidate	- validate the model before save, default is false
 * modelCallbacks	- run model callbacks when the rating is saved to the model, default is false
 * allowedValues	- @todo
 *
 * @var array
 */
	protected $_defaultConfig = array(
		'modelClass' => null,
		'rateClass' => 'Ratings.Rating',
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
		'allowedValues' => array()
	);

/**
 * Rating modes
 *
 * @var array
 */
	public $modes = array(
		'average' => 'avg',
		'sum' => 'sum');

/**
 * Setup
 *
 * @param array $settings
 * @return void
 */
	public function initialize(array $config) {
		if (empty($this->_config['modelClass'])) {
			$this->_config['modelClass'] = $this->_table->alias();
		}

		$this->_table->hasMany('Rating'
			 , array(
				'className' => $this->_config['rateClass'],
				'foreignKey' => $this->_config['foreignKey'],
				'unique' => true,
				'conditions' => '',
				'fields' => '',
				'dependent' => true
			)
		);

		$this->_table->belongsTo($this->_config['modelClass'],
  			array(
				'className' => $this->_config['modelClass'],
				'foreignKey' => 'foreign_key',
				'counterCache' => $this->_config['countRates']
			)
		);
	}

/**
 * Saves a new rating
 *
 * @param string $foreignKey
 * @param string $userId
 * @param numeric $value
 * @return mixed boolean or calculated sum
 */
	public function saveRating($foreignKey = null, $userId = null, $value = 0) {
		$type = 'saveRating';
		$this->beforeRateCallback(compact('foreignKey', 'userId', 'value', 'update', 'type'));
		$oldRating = $this->isRatedBy($foreignKey, $userId);
		if (!$oldRating || $this->_config['update'] == true) {
			$data['Rating']['foreign_key'] = $foreignKey;
			$data['Rating']['model'] = $this->_table->alias();
			$data['Rating']['user_id'] = $userId;
			$data['Rating']['value'] = $value;
			if ($this->_config['update'] == true) {
				$update = true;
				$this->oldRating = $oldRating;
				if (!empty($oldRating)) {
					if (is_array($foreignKey)) {
						$oldRating = $this->oldRating = $this->_table->Rating->find('all', array(
							//'recursive' => -1,
							'conditions' => array(
								'Rating.model' => $this->_table->alias(),
								'Rating.foreign_key' => $foreignKey,
								'Rating.user_id' => $userId
							)
						))->first();
					}

					$this->_table->Rating->deleteAll(array(
						'Rating.model' => $this->_table->alias(),
						'Rating.foreign_key' => $foreignKey,
						'Rating.user_id' => $userId
					), false, false);
				}
			} else {
				$oldRating = null;
				$update = false;
			}

			$this->_table->Rating->create();
			if ($this->_table->Rating->save($data)) {
				$fieldCounterType = $this->_table->getColumnType($this->_config['fieldCounter']);
				$fieldSummaryType = $this->_table->getColumnType($this->_config['fieldSummary']);
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

 * @param string $foreignKey
 * @param string $userId
 * @param numeric $value
 * @return mixed boolean or calculated sum
 */
	public function removeRating($foreignKey = null, $userId = null) {
		$type = 'removeRating';
		$this->beforeRateCallback(compact('foreignKey', 'userId', 'update', 'type'));
		$oldRating = $this->isRatedBy($foreignKey, $userId);
		if ($oldRating) {
			$data['Rating']['foreign_key'] = $foreignKey;
			$data['Rating']['model'] = $this->_table->alias();
			$data['Rating']['user_id'] = $userId;
			$update = true;
			$this->oldRating = $oldRating;
			if (is_array($foreignKey)) {
				$oldRating = $this->oldRating = $this->_table->Rating->find('all', array(
					//'recursive' => -1,
					'conditions' => array(
						'Rating.model' => $this->_table->alias(),
						'Rating.foreign_key' => $foreignKey,
						'Rating.user_id' => $userId
					)
				))->first();
			}

			$this->_table->Rating->deleteAll(array(
				'Rating.model' => $this->_table->alias(),
				'Rating.foreign_key' => $foreignKey,
				'Rating.user_id' => $userId
			), false, false);

			$fieldCounterType = $this->_table->getColumnType($this->_config['fieldCounter']);
			$fieldSummaryType = $this->_table->getColumnType($this->_config['fieldSummary']);
			if ($fieldCounterType && $fieldSummaryType) {
				$result = $this->decrementRating($foreignKey, $oldRating['Rating']['value'], $this->_config['saveToField'], $this->_config['calculation'], $update);
			} else {
				$result = $this->calculateRating($foreignKey, $this->_config['saveToField'], $this->_config['calculation']);
			}
			$this->afterRateCallback(compact('foreignKey', 'userId', 'result', 'update', 'oldRating', 'type'));
			return $result;
		}
		return false;
	}

/**
 * Increments/decrements the rating
 *
 * See also Ratable::calculateRating() and decide which one suits your needs better
 *
 * @see Ratable::calculateRating()

 * @param string $foreignKey
 * @param integer $value of new rating
 * @param mixed $saveToField boolean or fieldname
 * @param string $mode type of calculation
 * @return mixed boolean or calculated sum
 */
	public function decrementRating($foreignKey = null, $oldRating, $saveToField = true, $mode = 'average', $update = false) {
		if (!in_array($mode, array_keys($this->modes))) {
			throw new InvalidArgumentException(sprintf(__d('ratings', 'Invalid rating mode %s.'), $mode));
		}

		$data = $this->_table->find('all', array(
			'conditions' => array(
				$this->_table->alias() . '.' . $this->_table->primaryKey() => $foreignKey),
			//'recursive' => -1
		))->first();

		$fieldSummary = $this->_config['fieldSummary'];
		$fieldCounter = $this->_config['fieldCounter'];

		$ratingSumNew = $data[$fieldSummary] - $oldRating;
		$ratingCountNew = $data[$fieldCounter] - 1;

		if ($mode === 'average') {
			if ($ratingCountNew == 0) {
				$rating = 0;
			} else {
				$rating = $ratingSumNew / $ratingCountNew;
			}
		} else {
			$rating = $ratingSumNew;
		}

		if ($saveToField || is_string($saveToField)) {
			$save = array();
			if (is_string($saveToField)) {
				$save[$saveToField] = $rating;
			} else {
				$save[$this->_config['field']] = $rating;
			}
			$save[$fieldSummary] = $ratingSumNew;
			$save[$fieldCounter] = $ratingCountNew;
			$save[$this->_table->primaryKey()] = $foreignKey;

			return $this->_table->save($save, array(
				'validate' => $this->_config['modelValidate'],
				'callbacks' => $this->_config['modelCallbacks']));
		}
		return $rating;
	}

/**
 * Increments/decrements the rating
 *
 * See also Ratable::calculateRating() and decide which one suits your needs better
 *
 * @see Ratable::calculateRating()

 * @param string $foreignKey
 * @param integer $value of new rating
 * @param mixed $saveToField boolean or fieldname
 * @param string $mode type of calculation
 * @return mixed boolean or calculated sum
 */
	public function incrementRating($foreignKey = null, $value, $saveToField = true, $mode = 'average', $update = false) {
		if (!in_array($mode, array_keys($this->modes))) {
			throw new InvalidArgumentException(sprintf(__d('ratings', 'Invalid rating mode %s.'), $mode));
		}

		$data = $this->_table->find('all', array(
			'conditions' => array(
				$this->_table->alias() . '.' . $this->_table->primaryKey() => $foreignKey),
			//'recursive' => -1
		))->first();

		$fieldSummary = $this->_config['fieldSummary'];
		$fieldCounter = $this->_config['fieldCounter'];

 		if ($update == true && !empty($this->oldRating)) {
			$ratingSumNew = $data[$fieldSummary] - $this->oldRating['Rating']['value'] + $value;
			$ratingCountNew = $data[$fieldCounter];
		} else {
			$ratingSumNew = $data[$fieldSummary] + $value;
			$ratingCountNew = $data[$fieldCounter] + 1;
		}

		if ($mode == 'average') {
			$rating = $ratingSumNew / $ratingCountNew;
		} else {
			$rating = $ratingSumNew;
		}
		$this->_table->newRating = $rating;

		if ($saveToField || is_string($saveToField)) {
			$save = array();
			if (is_string($saveToField)) {
				$save[$saveToField] = $rating;
			} else {
				$save[$this->_config['field']] = $rating;
			}
			$save[$fieldSummary] = $ratingSumNew;
			$save[$fieldCounter] = $ratingCountNew;
			$save[$this->_table->primaryKey()] = $foreignKey;

			return $this->_table->save($save, array(
				'validate' => $this->_config['modelValidate'],
				'callbacks' => $this->_config['modelCallbacks']));
		}
		return $rating;
	}

/**
 * Calculates the rating
 *
 * This method does always a calculation of the the values based on SQL AVG()
 * and SUM(). Please note that this is relativly slow compared to incrementing
 * the values, see Ratable::incrementRating()
 *

 * @param string $foreignKey
 * @param mixed $saveToField boolean or fieldname
 * @param string $mode type of calculation
 * @return mixed boolean or calculated sum
 */
	public function calculateRating($foreignKey = null, $saveToField = true, $mode = 'average') {
		if (!in_array($mode, array_keys($this->modes))) {
			throw new InvalidArgumentException(sprintf(__d('ratings', 'Invalid rating mode %s.'), $mode));
		}

		$result = $this->_table->Rating->find('all', array(
			'contain' => array($this->_table->alias()),
			'fields' => array(
				$this->modes[$mode] . '(Rating.value) AS rating'),
			'conditions' => array(
				'Rating.foreign_key' => $foreignKey,
				'Rating.model' => $this->_table->alias()
			)
		));

		if (empty($result[0][0]['rating'])) {
			$result[0][0]['rating'] = 0;
		}

		$this->_table->newRating = $result[0][0]['rating'];
		if (!$saveToField) {
			return $result[0][0]['rating'];
		}

		if (!is_string($saveToField)) {
			$saveToField = $this->_config['field'];
		}

		if (!$this->_table->hasField($saveToField)) {
			return $result[0][0]['rating'];
		}

		$data = array(
			$this->_table->primaryKey() => $foreignKey,
			$saveToField => $result[0][0]['rating'],
		);

		return $this->_table->save($data, array(
			'validate' => $this->_config['modelValidate'],
			'callbacks' => $this->_config['modelCallbacks']
		));
	}

/**
 * Method to check if an entry is rated by a certain user
 *

 * @param mixed Single foreign key as uuid or int or array of foreign keys
 * @param mixed Boolean true or false if a single foreign key was supplied else an array of already voted keys
 * @return mixed Array of related foreignKeys when querying for multiple entries, entry or false otherwise
 */
	public function isRatedBy($foreignKey = null, $userId = null) {
		$findMethod = 'first';
		if (is_array($foreignKey)) {
			$findMethod = 'all';
		}

		$entry = $this->_table->Rating->find($findMethod, array(
			//'recursive' => -1,
			'conditions' => array(
				'Rating.foreign_key' => $foreignKey,
				'Rating.user_id' => $userId,
				'Rating.model' => $this->_table->alias()
			)
		));

		if ($findMethod == 'all') {
			return Hash::extract($entry, '{n}.Rating.foreign_key');
		}

		if (empty($entry)) {
			return false;
		}

		return $entry;
	}

/**
 * afterRate callback to the model
 *

 * @param array
 * @return void
 */
	public function afterRateCallback($data = array()) {
		if (method_exists($this->_table, 'afterRate')) {
			$this->_table->afterRate($data);
		}
	}

/**
 * beforeRate callback to the model
 *

 * @param array
 * @return void
 */
	public function beforeRateCallback($data = array()) {
		if (method_exists($this->_table, 'beforeRate')) {
			$this->_table->beforeRate($data);
		}
	}

/**
 * More intelligent version of saveRating - checks record existance and ratings
 *

 * @param string model primary key / id
 * @param mixed user id integer or string uuid
 * @param mixed integer or string rating
 * @param array options
 * @param return boolean True on success
 */
	public function rate($foreignKey = null, $userId = null, $rating = null, $options = array()) {
		$options = array_merge(array(
			'userField' => 'user_id',
			'find' => array(
				'contain' => array(),
				'conditions' => array(
					$this->_table->alias() . '.' . $this->_table->primaryKey() => $foreignKey)),
			'values' => array(
				'up' => 1, 'down' => -1
			)
		), $options);

		if (!in_array($rating, array_keys($options['values']))) {
			throw new OutOfBoundsException(__d('ratings', 'Invalid Rating'));
		}

		$record = $this->_table->find('all', $options['find'])->first();

		if (empty($record)) {
			throw new OutOfBoundsException(__d('ratings', 'Invalid Record'));
		}

		if ($options['userField'] !== false && $this->_table->getColumnType($options['userField'])) {
			if ($record[$options['userField']] == $userId) {
				$this->_table->data = $record;
				throw new LogicException(__d('ratings', 'You can not vote on your own records'));
			}
		}

		if ($this->_table->saveRating($foreignKey, $userId, $options['values'][$rating])) {
			$this->_table->data = $record;
			return true;
		} else {
			throw new RuntimeException(__d('ratings', 'You have already rated this record'));
		}
	}

/**
 * Caches the sum of the different ratings for each of them
 *
 * For example a rating of 1 will increase the value in the field "rating_1" by 1,
 * a rating of 2 will increase "rating_2" by one...
 *
 * @param object Model
 * @param array Data passed to afterRate() or similar structure
 * @return boolean True on success
 */
	public function cacheRatingStatistics($data = array()) {
		extract($data);
		if ($result) {
			if ($type === 'removeRating') {
				$value = $oldRating['Rating']['value'];
			}

			if ($this->_table->getColumnType($this->_fieldName(round($value, 0)))) {
				$data = $this->_table->find('all', array(
					'conditions' => array(
						$this->_table->alias() . '.' . $this->_table->primaryKey() => $foreignKey),
					//'recursive' => -1
				))->first();

				if (($update === true || $type === 'removeRating') && !empty($oldRating['Rating'])) {
					$oldId = round($oldRating['Rating']['value']);
					$data[$this->_fieldName($oldId)] -= 1;
				}

				if ($type === 'saveRating') {
					$newId = round($value);
					$data[$this->_fieldName($newId)] += 1;
				}

				return $this->_table->save($data, array(
					'validate' => $this->_config['modelValidate'],
					'callbacks' => $this->_config['modelCallbacks']
				));
			}
		}
	}

/**
 * Return field name for cache value
 *
 * @param string $value
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
