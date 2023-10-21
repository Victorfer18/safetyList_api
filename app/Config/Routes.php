<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'User::login');
$routes->group('clients', function ($routes) {
    $routes->get('(:any)', 'ClientController::getClientsByIdParent/$1', ['filter' => 'authFilter']);
    $routes->get('(:any)', 'ClientController::getClientsByIdParent/$1');
});

$routes->group('inspections', function ($routes) {
    $routes->get('getInspectableList/(:num)', 'InspectionController::getInspectableList/$1', ['filter' => 'authFilter']);
    $routes->put('alter_status/(:any)', 'InspectionController::updateInspectionStatusById/$1', ['filter' => 'authFilter']);
    $routes->post('save_is_closed', 'InspectionController::saveInspectableIsClosed', ['filter' => 'authFilter']);
    $routes->get('(:any)', 'InspectionController::getInspectionsByClientIdAndStatus/$1', ['filter' => 'authFilter']);
});
