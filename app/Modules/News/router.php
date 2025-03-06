<?php

$router->get('/news', 'NewsController@index', $module);
$router->get('/news/{id}', 'NewsController@view_news_by_id', $module);
