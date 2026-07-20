<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
//Authentification
$routes->get('/login', 'Client::login');
$routes->post('/login', 'Auth::loginAuth');

//Client
$routes->get('/solde', 'Client::solde');
$routes->get('/depot', 'Client::depot');
$routes->post('/depot', 'Transaction::faireDepot');
$routes->get('/retrait', 'Client::retrait');
$routes->post('/retrait', 'Transaction::faireRetrait');
$routes->get('/transfert', 'Client::transfert');
$routes->post('/transfert', 'Transaction::faireTransfert');