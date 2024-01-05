<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'User::login');
$routes->get('/validate_jwt', 'SystemController::validateJwt', ['filter' => 'authFilter']);
$routes->group('clients', function ($routes) {
    $routes->get('getLogoInspectable/(:any)', 'ClientController::getLogosInspectables/$1');
    $routes->get('/', 'ClientController::getClients', ['filter' => 'authFilter']);
});

$routes->group('inspections', function ($routes) {
    $routes->post('getInspectableList', 'InspectionController::getInspecTableList', ['filter' => 'authFilter']);
    $routes->put('alter_status', 'InspectionController::updateInspectionStatusById', ['filter' => 'authFilter']);
    $routes->post('set_is_closed', 'InspectionController::setIsClosedInspectable', ['filter' => 'authFilter']);
    $routes->post('set_is_closed_sector', 'InspectionController::setIsClosedSectors', ['filter' => 'authFilter']);
    $routes->post('register_maintenance', 'InspectionController::registerMaintenance', ['filter' => 'authFilter']);
    $routes->post('get_maintenance', 'InspectionController::getMaintenance', ['filter' => 'authFilter']);
    $routes->get('getSectorsByIdInspection/(:any)', 'InspectionController::getSectorsByIdInspection/$1', ['filter' => 'authFilter']);
    $routes->get('(:any)', 'InspectionController::getInspectionsByClientId/$1', ['filter' => 'authFilter']);
});
