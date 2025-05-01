<?php

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Router;
use Cake\TestSuite\Fixture\SchemaLoader;
use Ratings\RatingsPlugin;
use Shim\Filesystem\Folder;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('WINDOWS')) {
	if (DS === '\\' || substr(PHP_OS, 0, 3) === 'WIN') {
		define('WINDOWS', true);
	} else {
		define('WINDOWS', false);
	}
}

define('ROOT', dirname(__DIR__));
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('APP', sys_get_temp_dir());
define('APP_DIR', 'src');
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);

define('WWW_ROOT', ROOT . DS . 'webroot' . DS);
define('CONFIG', dirname(__FILE__) . DS . 'config' . DS);

ini_set('intl.default_locale', 'de-DE');

require dirname(__DIR__) . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';
require CAKE_CORE_INCLUDE_PATH . '/src/functions.php';

Configure::write('App', [
		'namespace' => 'App',
		'encoding' => 'UTF-8']);
Configure::write('debug', true);

mb_internal_encoding('UTF-8');

$Tmp = new Folder(TMP);
$Tmp->create(TMP . 'cache/models', 0770);
$Tmp->create(TMP . 'cache/persistent', 0770);
$Tmp->create(TMP . 'cache/views', 0770);

$cache = [
	'default' => [
		'engine' => 'File',
		'path' => CACHE,
	],
	'_cake_translations_' => [
		'className' => 'File',
		'prefix' => 'myapp_cake_translations_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds',
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'myapp_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds',
	],
];
Cache::setConfig($cache);

Router::defaultRouteClass(DashedRoute::class);

Plugin::getCollection()->add(new RatingsPlugin());

// Ensure default test connection is defined
if (!getenv('DB_URL')) {
	putenv('DB_URL=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', [
	'url' => getenv('DB_URL') ?: null,
	'timezone' => 'UTC',
	'quoteIdentifiers' => true,
	'cacheMetadata' => true,
]);

if (env('FIXTURE_SCHEMA_METADATA')) {
	$loader = new SchemaLoader();
	$loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}

class_alias(\TestApp\Model\Entity\User::class, 'App\Model\Entity\User');
class_alias(\TestApp\Model\Table\UsersTable::class, 'App\Model\Table\UsersTable');
