<?php

$router->get('/login', 'LoginController@index', $module);
$router->post('/login', 'LoginController@login', $module);