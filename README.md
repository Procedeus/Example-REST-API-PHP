.htaccess: é um arquivo usado para realizado configuração do Apache, permitindo definir regras em um diretório e em todos os seus subdiretórios.
```php
//RewriteEngine On ativa o mecanismo de reescrita de URLs do Apache;
RewriteEngine On
//^(.*)$ -> Expressão regular que corresponde a qualquer URL solicitada.
//public/index.php -> Destino para onde todas as requisições serão redirecionadas.
//[QSA,L] -> Flags que indicam:
//QSA -> Anexa os parâmetros URL original para a URL de destino.
//L -> Informa que é a última regra a ser processada;
RewriteRule ^(.*)$ public/index.php [QSA,L]
```

Public: É utilizada como ponto de entrada para as requisições.
```php

// Carrega o autoloader do Composer;
require_once __DIR__ . '/../vendor/autoload.php';

//Carrega as variáveis do arquivo .env (caminho correto)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // __DIR__ . '/..' para acessar a raiz do projeto
$dotenv->load();

//Inclui a Config do banco de dados, explicado abaixo;
require_once '../config/database.php';
//Inclui as correspondencias da URL, explicado abaixo;
require_once '../routes/api.php';
//Chama a função das rotas da API, explicado abaixo;
handleRequest($pdo);
```
Routes: A rota é uma correspondência entre uma URL solicitada e uma ação específica no código da aplicação, por exemplo, "url.com/rota1/".
```php
//Inclui a classe UserController, responsável pelas entradas de informações interage com o Model e determina a resposta adequada;
require_once '../controllers/UserController.php';

//Função Responsável por tratar as rotas da API;
function handleRequest($pdo) {

//$_SERVER['REQUEST_METHOD'] determina qual é o tipo de requisição;
    $method = $_SERVER['REQUEST_METHOD'];
//$_SERVER['REQUEST_URI'] informa a URI completa da requisição;
    $url = $_SERVER['REQUEST_URI'];

    $userController = new UserController($pdo);

//preg_match('/\/api\/users\/(\d+)/', $url, $matches): verifica se a URL corresponde ao padrão /api/users/{id};
//\/api\/users\/: a string literal /api/users/;
//(\d+): um ou mais dígitos numéricos;
    if (preg_match('/\/api\/users\/(\d+)/', $url, $matches)) {
//Atribui o valor capturado (o ID do usuário) à variável $id;
        $id = $matches[1];

//Verifica qual o tipo de requisição e chama a função correspondente a requisição;
        if ($method == 'GET') {
            $userController->getUser($id);
        } elseif ($method == 'PUT') {
            $userController->updateUser($id);
        } elseif ($method == 'DELETE') {
            $userController->deleteUser($id);
        }
    }
//preg_match('/\/api\/users\/(\d+)/', $url, $matches): verifica se a URL corresponde ao padrão /api/users/ sem o ID;
        elseif (preg_match('/\/api\/users/', $url)) {
//Verifica qual o tipo de requisição e chama a função correspondente a requisição;
        if ($method == 'GET') {
            $userController->getAllUsers();
        } elseif ($method == 'POST') {
            $userController->createUser();
        }
    }
}
```
Config: Responsável por armazenar as informações necessárias para a conexão com o banco de dados. Em seguida, utiliza-se o PDO (PHP Data Objects) para estabelecer a conexão, configurando-o para lançar exceções em caso de erros.
```php
//Criação eas credenciais de acesso ao banco de dados;
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
//Criação de uma conexão com o banco de dados utilizando PDO(PHP Data Objects);
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
//O método setAttribute do PDO (PHP Data Objects) é utilizado para definir atributos específicos de uma conexão com o banco de dados.
//PDO::ATTR_ERRMODE: Este atributo define o modo de tratamento de erros do PDO;
//PDO::ERRMODE_EXCEPTION: Lança uma exceção PDOException quando ocorre um erro.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
// Tratamento da exceção, exibindo o erro que ocorreu;
    die("Connection Error: " . $e->getMessage());

}
```

Models: É responsável por gerenciar e manipular os dados, incluindo operações como criação, leitura(um ou todos), atualização e exclusão (CRUD).
```php
//Criação de uma Classe User;
class User {
//Criação de um atributo privado PDO, que será a conexão com o banco de dados;
    private $pdo;

//__construct é utilizado para inicializar o objeto, configurando suas propriedades e preparando-o para uso.
    public function __construct($pdo) {
//Instanciando o PDO a classe User;
        $this->pdo = $pdo;
    }
//Função lista todos os usuários cadastrados do banco de dados;
    public function getAll() {
//A instrução query itera um conjunto de resultados retornado pela instrução SELECT.
        $stmt = $this->pdo->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
//Função retorna um user pelo ID cadastrado no banco de dados;
    public function get($id) {
//A instrução prepare é utilizado para preparar uma instrução SQL para execução, permitindo a utilização de parâmetros nomeados ou posicionais.
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
//execute uma instrução SQL preparada, passando um array associativo que vincula o parâmetro :id ao valor da variável $id;
        $stmt->execute(['id' => $id]);
//método fetch é chamado com o parâmetro PDO::FETCH_ASSOC, indicando que o resultado da consulta deve ser retornado como um array associativo, onde as chaves são os nomes das colunas da tabela.
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

Controllers: Atua como intermediário entre o Model e a View. O UserController recebe as entradas do usuário, processa essas informações interagindo com o Model e determina a resposta adequada, que pode envolver a execução de ações específicas.
```php
require_once '../models/User.php';
//Criação de uma Classe UserController;
class UserController {

    private $userModel;
//Inicializa o userModel, instanciando um novo User, recebendo como parametro o PDO;
    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }
//A função chama a função de getAll criada em models e exibe o que foi retornado;
    public function getAllUsers() {
        $users = $this->userModel->getAll();
        echo json_encode($users);
    }
//A função chama a função de get recebendo o ID como parametro, criada em models e exibe o que foi retornado;
    public function getUser($id) {
        $user = $this->userModel->get($id);
        echo json_encode($user);
    }
//Função cria um novo usuário e exibe uma mensagem de sucesso;
    public function createUser() {
//
        $data = json_decode(file_get_contents('php://input'), true);
        $this->userModel->create($data);
        echo json_encode(['message' => 'User created']);
    }

    public function updateUser($id) {
//json_decode(..., true) converte a string JSON obtida na etapa anterior em um array associativo PHP;
//file_get_contents('php://input') lê o conteúdo bruto do corpo da requisição HTTP;
        $data = json_decode(file_get_contents('php://input'), true);
//json_encode(['var' => $data]) converte o array associativo ['var' => $data] em uma string JSON e exibe o resultado.
        echo json_encode(['var' => $data]);
//O metodo update, atualiza o usuário identificando pelo ID, com as informações de $data;
        $this->userModel->update($id, $data);
        echo json_encode(['message' => 'User updated']);
    }
}
```
