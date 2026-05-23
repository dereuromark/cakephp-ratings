<?php

use Cake\Core\Configure;
use Migrations\BaseMigration;

class RatingsInit extends BaseMigration {

	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change(): void {
		// foreign_key (the polymorphic host record's primary key, paired with model)
		// follows the global Polymorphic.type config so apps using UUID primary keys
		// can store matching foreign keys. user_id is a concrete FK to the app's users
		// table. For integer types the signedness follows Migrations.unsigned_primary_keys
		// (signed when unset); only MySQL honors signedness.
		$type = (string)Configure::read('Polymorphic.type', 'integer');
		$signed = !(bool)Configure::read('Migrations.unsigned_primary_keys', false);

		$polymorphicOptions = [
			'default' => null,
			'null' => false,
		];
		if (in_array($type, ['integer', 'biginteger'], true)) {
			$polymorphicOptions['signed'] = $signed;
		}

		$this->table('ratings')
			->addColumn('user_id', 'integer', [
				'default' => null,
				'null' => true,
				'signed' => $signed,
			])
			->addColumn('foreign_key', $type, $polymorphicOptions)
			->addColumn('model', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('value', 'float', [
				'default' => 0,
				'null' => false,
				'precision' => 8,
				'scale' => 4,
			])
			->addColumn('created', 'datetime', [
				'null' => false,
			])
			->addColumn('modified', 'datetime', [
				'null' => false,
			])
			->addIndex(['user_id', 'foreign_key', 'model'], ['unique' => true, 'name' => 'UNIQUE_RATING'])
			->addIndex(['user_id'])
			->addIndex(['foreign_key'])
			->create();
	}

}
