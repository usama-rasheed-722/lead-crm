<?php
// app/models/UserModel.php
class UserModel extends Model {
    
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
        // Enforce unique sdr_id at application layer
        if (!empty($data['sdr_id'])) {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE sdr_id = ? LIMIT 1');
            $stmt->execute([$data['sdr_id']]);
            $existing = $stmt->fetch();
            if ($existing) {
                throw new Exception('SDR ID already in use by another user');
            }
        }
        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password, full_name, role, sdr_id) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['full_name'],
            $data['role'],
            $data['sdr_id'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        // Enforce unique sdr_id at application layer
        if (!empty($data['sdr_id'])) {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE sdr_id = ? AND id != ? LIMIT 1');
            $stmt->execute([$data['sdr_id'], $id]);
            $existing = $stmt->fetch();
            if ($existing) {
                throw new Exception('SDR ID already in use by another user');
            }
        }
        $stmt = $this->pdo->prepare('UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, sdr_id = ? WHERE id = ?');
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['full_name'],
            $data['role'],
            $data['sdr_id'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    public function getSDRs() {
        $stmt = $this->pdo->prepare('SELECT id, username, full_name, sdr_id FROM users WHERE role = "sdr" ORDER BY full_name');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}