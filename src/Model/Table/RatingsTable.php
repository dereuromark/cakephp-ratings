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
use Cake\Validation\Validator;

/**
 * CakePHP Ratings Plugin
 *
 * Rating model
 *
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @method \Ratings\Model\Entity\Rating get($primaryKey, $options = [])
 * @method \Ratings\Model\Entity\Rating newEntity($data = null, array $options = [])
 * @method array<\Ratings\Model\Entity\Rating> newEntities(array $data, array $options = [])
 * @method \Ratings\Model\Entity\Rating|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ratings\Model\Entity\Rating patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Ratings\Model\Entity\Rating> patchEntities($entities, array $data, array $options = [])
 * @method \Ratings\Model\Entity\Rating findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RatingsTable extends Table {

	/**
	 * @param array $config
	 * @return void
	 */
	public function initialize(array $config): void {
		$table = Configure::read('Ratings.table');
		if ($table) {
			$this->setTable($table);
		}

		$userClass = Configure::read('Ratings.userClass');
		if (empty($userClass)) {
			$userClass = 'Users';
		}

		$this->belongsTo('Users', [
				'className' => $userClass, 'foreignKey' => 'user_id',
			],
		);

		$this->addBehavior('Timestamp');
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->scalar('user_id')
			->requirePresence('user_id')
			->notBlank('user_id');

		$validator
			->scalar('model')
			->requirePresence('model')
			->notBlank('model');

		$validator
			->scalar('foreign_key')
			->requirePresence('foreign_key')
			->notBlank('foreign_key');

		$validator
			->scalar('value')
			->requirePresence('value')
			->notBlank('value');

		return $validator;
	}

}
