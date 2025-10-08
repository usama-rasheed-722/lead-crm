<?php
// app/models/BaseModel.php
class BaseModel {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
}