<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group("api/v1", function ($routes) {
  $routes->post("register", "Register::index");
  $routes->post("login", "Login::index");
  $routes->get("users", "User::index", ['filter' => 'authFilter']);
  $routes->get("users/(:num)", "User::get/$1", ['filter' => 'authFilter']);
});
