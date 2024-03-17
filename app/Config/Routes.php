<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group("api/v1", function ($routes) {
  $routes->post("register", "Register::index");
  $routes->post("login", "Login::index");
  
  
  $routes->group("users", function ($routes) {
    $routes->get("/", "User::index", ['filter' => 'authFilter']);
    $routes->get("(:num)", "User::get/$1", ['filter' => 'authFilter']);
    $routes->post("change-password", "User::changePassword", ['filter' => 'authFilter']);
  });

  $routes->group("stories", function ($routes) {
    $routes->get("/", "Story::index", ['filter' => 'authFilter']);
    $routes->get("self", "Story::self", ['filter' => 'authFilter']);
    $routes->post("/", "Story::create", ['filter' => 'authFilter']);
    $routes->put("(:num)", "Story::update/$1", ['filter' => 'authFilter']);
    $routes->delete("(:num)", "Story::remove/$1", ['filter' => 'authFilter']);
  });

  $routes->group("friends", function ($routes) {
    $routes->get("/", "Friend::index", ['filter' => 'authFilter']);
    $routes->get("requests", "Friend::requests", ['filter' => 'authFilter']);
    $routes->post("(:num)", "Friend::add/$1", ['filter' => 'authFilter']);
    $routes->put("(:num)", "Friend::update/$1", ['filter' => 'authFilter']);
    $routes->delete("(:num)", "Friend::remove/$1", ['filter' => 'authFilter']);
  });
});
