<?php
// app/models/UserModel.php
class UserModel extends BaseModel {
    
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function all() {
        $stmt = $this->pdo->prepare('SELECT * FROM users ORDER BY full_name');
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['full_name'],
            $data['role']
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare('UPDATE users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?');
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['full_name'],
            $data['role'],
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    public function getSDRs() {
        $stmt = $this->pdo->prepare('SELECT id, username, full_name FROM users WHERE role = "sdr" ORDER BY full_name');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}