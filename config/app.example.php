<?php

/**
 * Ratings Example Configuration
 *
 * Merge the keys below into your application's config/app.php (or
 * config/app_local.php) — do not replace the whole file, since this snippet
 * only contains this plugin's configuration. When copying entries that
 * reference imported classes, use fully-qualified class names or move the
 * `use` imports to the top of the target file. Customize the values as needed.
 *
 * The `Ratings` namespace is read by Ratings\Model\Table\RatingsTable::initialize().
 */
return [
	'Ratings' => [
		// Override the database table name used by RatingsTable. When empty/not set, the
		// table keeps CakePHP's conventional name ('ratings'). Default: not set.
		'table' => null,

		// Model/class name used for the belongsTo('Users') association on ratings (the
		// owner of a rating). When empty, defaults to 'Users'. Default: not set ('Users').
		'userClass' => 'Users',
	],

	// Global (cross-plugin) convention read by the RatingsInit migration when creating
	// the polymorphic `ratings.foreign_key` column. Must be set BEFORE running migrations
	// on a fresh install. Accepted values: 'integer' (default), 'biginteger', 'uuid',
	// 'binaryuuid'. For the integer variants the column signedness follows the migrations
	// plugin's `Migrations.unsigned_primary_keys` flag (signed when unset).
	'Polymorphic' => [
		'type' => 'integer',
	],
];
