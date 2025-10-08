<?php
// core/Controller.php
abstract class Controller {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    protected function view($view, $data = []) {
        extract($data);
        include __DIR__ . "/../app/views/{$view}.php";
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
