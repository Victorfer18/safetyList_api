<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'User::login');

$routes->group('inspections', function ($routes) {
    $routes->get('(:any)', 'InspectionController::getInspectionsByClient/$1', ['filter' => 'authFilter']);
});
