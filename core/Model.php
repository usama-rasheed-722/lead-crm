<?php
// core/Model.php
abstract class Model {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
}
?>
