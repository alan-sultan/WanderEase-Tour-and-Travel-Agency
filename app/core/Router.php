<?php

$url = $_GET['url'] ?? 'home/index';
$urlParts = explode('/', $url);

$controllerName = ucfirst($urlParts[0]) . 'Controller';
$method = $urlParts[1] ?? 'index';

$controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    
    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        echo "Method $method not found";
    }
} else {
    echo "Controller $controllerName not found";
}
