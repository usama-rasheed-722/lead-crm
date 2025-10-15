<?php
// app/models/NoteModel.php
class NoteModel extends BaseModel {
    
    // Add a note to a lead
    public function add($leadId, $userId, $type, $content) {
        $stmt = $this->pdo->prepare('INSERT INTO lead_notes (lead_id, user_id, type, content) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$leadId, $userId, $type, $content]);
    }
    
    // Get all notes for a lead
    public function getByLeadId($leadId) {
        $stmt = $this->pdo->prepare('
            SELECT ln.*, u.username, u.full_name 
            FROM lead_notes ln 
            LEFT JOIN users u ON ln.user_id = u.id 
            WHERE ln.lead_id = ? 
            ORDER BY ln.created_at DESC
        ');
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }
    
    // Delete a note
    public function delete($noteId, $userId) {
        // Check if user owns the note or is admin
        $stmt = $this->pdo->prepare('SELECT user_id FROM lead_notes WHERE id = ?');
        $stmt->execute([$noteId]);
        $note = $stmt->fetch();
        
        if (!$note) {
            return false;
        }
        
        $user = auth_user();
        if ($note['user_id'] != $userId && $user['role'] !== 'admin') {
            return false;
        }
        
        $stmt = $this->pdo->prepare('DELETE FROM lead_notes WHERE id = ?');
        return $stmt->execute([$noteId]);
    }
    
    // Get recent activity for dashboard
    public function getRecentActivity($limit = 10, $filters = []) {
        $where = [];
        $params = [];
        if (!empty($filters['sdr_id'])) { $where[] = 'l.sdr_id = ?'; $params[] = $filters['sdr_id']; }
        if (!empty($filters['date_from'])) { $where[] = 'date(ln.created_at) >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where[] = 'date(ln.created_at) <= ?'; $params[] = $filters['date_to']; }
        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        $sql = '
            SELECT ln.*, u.username, u.full_name, l.lead_id, l.name as lead_name, l.company
            FROM lead_notes ln 
            LEFT JOIN users u ON ln.user_id = u.id 
            LEFT JOIN leads l ON ln.lead_id = l.id' . $whereSql . '
            ORDER BY ln.created_at DESC 
            LIMIT ?
        ';
        $stmt = $this->pdo->prepare($sql);
        $params[] = (int)$limit;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
