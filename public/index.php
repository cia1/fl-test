<?php
require dirname(__DIR__) . '/engine/engine.php';

use engine\engine;
use engine\ErrorController;
use engine\HTTPException;

try {
    Engine::execute($_GET['r'] ?? '');
} catch (HTTPException $e) {
    $controller = new ErrorController();
    $action='action'.$e->getCode();
    $controller->render($controller->$action($e->getMessage()));
}