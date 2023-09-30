<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'User::login');
$routes->get('/getActiveClients', 'ClientController::getActiveClients',['filter' => 'authFilter']);

$routes->group('inspections', function ($routes) {
    $routes->put('alter_status/(:any)', 'InspectionController::alterStatusInspectionById/$1', ['filter' => 'authFilter']);
    $routes->get('(:any)', 'InspectionController::getInspectionsByClient/$1', ['filter' => 'authFilter']);
});
