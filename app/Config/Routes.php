<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'User::login');
$routes->group('clients', function ($routes) {
    $routes->get('(:any)', 'ClientController::getClientsByIdParent/$1', ['filter' => 'authFilter']);
});

$routes->group('inspections', function ($routes) {
    $routes->put('alter_status/(:any)', 'InspectionController::alterStatusInspectionById/$1', ['filter' => 'authFilter']);
    $routes->get('(:any)', 'InspectionController::getInspectionsByClient/$1', ['filter' => 'authFilter']);
});
