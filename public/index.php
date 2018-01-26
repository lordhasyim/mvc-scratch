<?php

/**
 * Front Controller
 * PHP v 5.4
 */
//echo 'Requested URL= "' . $_SERVER['QUERY_STRING'] . '"'; 


require '../Core/Router.php';

$router = new Router();

//Add the routes
$router->add('',['controller' => 'Home', 'action' => 'index']);
$router->add('posts',['controller' => 'Posts', 'action' => 'index']);
$router->add('posts/new',['controller' => 'Posts', 'action' => 'new']);

echo '<pre>';
var_dump($router->getRoutes());
echo '</pre>';


