<?php

require_once '../controllers/UserController.php';

function handleRequest($pdo) {
    $method = $_SERVER['REQUEST_METHOD'];
    $url = $_SERVER['REQUEST_URI'];

    $userController = new UserController($pdo);

    if (preg_match('/\/api\/users\/(\d+)/', $url, $matches)) {
        $id = $matches[1];

        if ($method == 'GET') {
            $userController->getUser($id);
        } elseif ($method == 'PUT') {
            $userController->updateUser($id);
        } elseif ($method == 'DELETE') {
            $userController->deleteUser($id);
        }
    }
    elseif (preg_match('/\/api\/users/', $url)) {
        if ($method == 'GET') {
            $userController->getAllUsers();
        } elseif ($method == 'POST') {
            $userController->createUser();
        }
    }
}