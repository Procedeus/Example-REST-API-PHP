<?php

// Carregar o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carregar as variáveis do arquivo .env (caminho correto)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // __DIR__ . '/..' para acessar a raiz do projeto
$dotenv->load();

require_once '../config/database.php';
require_once '../routes/api.php';

handleRequest($pdo);