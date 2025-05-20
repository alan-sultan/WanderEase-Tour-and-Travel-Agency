<?php

// Define Routes using the Router instance
$router->get('home/index', ['HomeController', 'index']);
$router->get('about', ['AboutController', 'index']);
$router->get('contact', ['ContactController', 'index']);
