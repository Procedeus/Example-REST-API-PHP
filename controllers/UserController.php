<?php
// controllers/UserController.php

require_once '../models/User.php';

class UserController {

    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function getAllUsers() {
        $users = $this->userModel->getAll();
        echo json_encode($users);
    }

    public function getUser($id) {
        $user = $this->userModel->get($id);
        echo json_encode($user);
    }

    public function createUser() {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->userModel->create($data);
        echo json_encode(['message' => 'User created']);
    }

    public function updateUser($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['var' => $data]);
        $this->userModel->update($id, $data);
        echo json_encode(['message' => 'User updated']);
    }

    public function deleteUser($id) {
        $this->userModel->delete($id);
        echo json_encode(['message' => 'User deleted']);
    }
}