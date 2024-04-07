<?php

use Phinx\Migration\AbstractMigration;

class RatingsInit extends AbstractMigration {

	/**
	 * Change Method.
	 *
	 * More information on this method is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-change-method
	 *
	 * Uncomment this method if you would like to use it.
	 *
	public function change()
	{
	}
	*/

	/**
	 * Migrate Up.
	 *
	 * @return void
	 */
	public function up() {
		$content = <<<SQL
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NOT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` float(8,4) NOT NULL DEFAULT '0.0000',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_RATING` (`user_id`,`foreign_key`,`model`),
  KEY `user_id` (`user_id`),
  KEY `foreign_key` (`foreign_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SQL;
		$this->query($content);
	}

	/**
	 * Migrate Down.
	 *
	 * @return void
	 */
	public function down() {
	}

}
