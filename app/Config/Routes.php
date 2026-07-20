<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'OperateurController::index');

// Espace Opérateur (Version 1)
$routes->group('operateur', function ($routes) {
    $routes->get('/', 'OperateurController::index');
    $routes->get('selectionner', 'OperateurController::selectionner');
    $routes->post('selectionner', 'OperateurController::selectionner');
    $routes->get('prefixe', 'OperateurController::prefixe');
    $routes->post('prefixe/ajouter', 'OperateurController::prefixeAjouter');
    $routes->get('prefixe/supprimer/(:num)', 'OperateurController::prefixeSupprimer/$1');
    $routes->get('types', 'OperateurController::types');
    $routes->post('type/ajouter', 'OperateurController::typeAjouter');
    $routes->get('type/supprimer/(:num)', 'OperateurController::typeSupprimer/$1');
    $routes->get('montants/(:num)', 'OperateurController::montants/$1');
    $routes->post('montant/ajouter', 'OperateurController::montantAjouter');
    $routes->post('montant/modifier/(:num)', 'OperateurController::montantModifier/$1');
    $routes->get('montant/supprimer/(:num)', 'OperateurController::montantSupprimer/$1');
    $routes->get('gains', 'OperateurController::gains');
    $routes->get('montants-envoyes', 'OperateurController::montantsEnvoyes');
    $routes->get('comptes', 'OperateurController::comptes');
    $routes->get('client/ajouter', 'OperateurController::clientAjouter');
    $routes->post('client/store', 'OperateurController::clientStore');
    $routes->get('client/modifier/(:num)', 'OperateurController::clientModifier/$1');
    $routes->post('client/update/(:num)', 'OperateurController::clientUpdate/$1');
    $routes->get('client/supprimer/(:num)', 'OperateurController::clientSupprimer/$1');
    $routes->get('client/detail/(:num)', 'OperateurController::clientDetail/$1');
    $routes->get('commissions', 'OperateurController::commissions');
    $routes->post('commission/ajouter', 'OperateurController::commissionAjouter');
    $routes->post('commission/modifier/(:num)', 'OperateurController::commissionModifier/$1');
    $routes->get('commission/supprimer/(:num)', 'OperateurController::commissionSupprimer/$1');
});
//Authentification
$routes->get('/login', 'Client::login');
$routes->post('/login', 'Auth::loginAuth');

//Client
$routes->get('/solde', 'Client::solde');
$routes->get('/historique', 'Client::historique');
$routes->get('/depot', 'Client::depot');
$routes->post('/depot', 'Transaction::faireDepot');
$routes->get('/retrait', 'Client::retrait');
$routes->post('/retrait', 'Transaction::faireRetrait');
$routes->get('/transfert', 'Client::transfert');
$routes->post('/transfert', 'Transaction::faireTransfert');
$routes->post('/transfert-multiple', 'Transaction::faireTransfertMultiple');
$routes->get('/logout', 'Client::logout');
