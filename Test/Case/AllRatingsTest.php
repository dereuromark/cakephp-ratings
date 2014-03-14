<?php

class AllRatingsTest extends PHPUnit_Framework_TestSuite {

/**
 * suite method, defines tests for this suite.
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Ratings plugin Tests');
		$suite->addTestDirectoryRecursive(App::pluginPath('Ratings') . 'Test' . DS . 'Case' . DS);

		return $suite;
	}
}
