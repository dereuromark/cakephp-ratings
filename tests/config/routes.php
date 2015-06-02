<?php

use Cake\Routing\Router;

Router::scope('/', function($routes) {
	$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
	$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
});

Router::plugin('Ratings', function ($routes) {
	$routes->connect('/', ['controller' => 'Ratings', 'action' => 'index'], ['routeClass' => 'DashedRoute']);
	$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
	$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
});
