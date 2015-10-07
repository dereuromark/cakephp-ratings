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
namespace Ratings\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\Table;

/**
 * CakePHP Ratings Plugin
 *
 * Rating model
 *
 * @package 	ratings
 * @subpackage 	ratings.models
 */
class RatingsTable extends Table {

	/**
	 * Validation rules
	 *
	 * @var array $validate
	 */
	public $validate = [];

	public function initialize(array $config) {
		$table = Configure::read('Ratings.table');
		if ($table) {
			$this->table($table);
		}

		$userClass = Configure::read('Ratings.userClass');
		if (empty($userClass)) {
			$userClass = 'Users';
		}

		$this->belongsTo('Users', [
				'className' => $userClass, 'foreignKey' => 'user_id'
			]
		);

		$this->addBehavior('Timestamp');
	}

	public function buildValidator() {
		$rules = [
			'notBlank' => [
				'required' => true,
				'rule' => 'notBlank']];

		$this->validate = [
			'user_id' => [
				'required' => $rules['notEmpty']],
			'model' => [
				'required' => $rules['notEmpty']],
			'foreign_key' => [
				'required' => $rules['notEmpty']],
			'value' => [
				'required' => $rules['notEmpty']]];
	}

}
