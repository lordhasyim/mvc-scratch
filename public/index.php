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

//matcg the requested route
$url = $_SERVER['QUERY_STRING'];
if ($router->match($url)) {
    echo '<pre>';
    var_dump($router->getParams());
    echo '</pre>';
} else {
    echo "not found route for {$url}";
}


