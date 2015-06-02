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
	public $validate = array();


	public function initialize(array $config) {
		$userClass = Configure::read('App.userClass');
		if (empty($userClass)) {
			$userClass = 'Users';
		}

		$this->belongsTo('Users', array(
				'className' => $userClass, 'foreignKey' => 'user_id'
			)
		);



	}

	public function buildValidator() {
		$rules = array(
			'notBlank' => array(
				'required' => true,
				'rule' => 'notBlank'));

		$this->validate = array(
			'user_id' => array(
				'required' => $rules['notEmpty']),
			'model' => array(
				'required' => $rules['notEmpty']),
			'foreign_key' => array(
				'required' => $rules['notEmpty']),
			'value' => array(
				'required' => $rules['notEmpty']));
	}

}
