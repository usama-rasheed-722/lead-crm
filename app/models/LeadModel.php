<?php
class LeadModel extends BaseModel {

    // Create a new lead with duplicate detection and auto lead_id if not provided
    public function create($data){
        if(empty($data['lead_id']) && !empty($data['sdr_id'])) {
            $data['lead_id'] = $this->generateLeadId($data['sdr_id']);
        }
        $status = $this->detectDuplicateStatus($data);
        $stmt = $this->pdo->prepare("INSERT INTO leads (lead_id,name,company,email,phone,linkedin,website,clutch,sdr_id,duplicate_status,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lead_id'],$data['name'],$data['company'],$data['email'],$data['phone'],$data['linkedin'],$data['website'],$data['clutch'],$data['sdr_id'],$status,$data['notes'],$data['created_by']
        ]);
        return $this->pdo->lastInsertId();
    }

    // Update existing lead
    public function update($id,$data){
        $status = $this->detectDuplicateStatus($data,$id);
        $stmt = $this->pdo->prepare("UPDATE leads SET name=?,company=?,email=?,phone=?,linkedin=?,website=?,clutch=?,sdr_id=?,duplicate_status=?,notes=?,updated_at=NOW() WHERE id=?");
        return $stmt->execute([$data['name'],$data['company'],$data['email'],$data['phone'],$data['linkedin'],$data['website'],$data['clutch'],$data['sdr_id'],$status,$data['notes'],$id]);
    }

    // Delete a lead
    public function delete($id){
        $stmt = $this->pdo->prepare('DELETE FROM leads WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Get a single lead by ID
    public function getById($id){
        $stmt = $this->pdo->prepare('SELECT l.*, u.username as sdr_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.id WHERE l.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Get all leads (paginated)
    public function all($limit=100, $offset=0){
        $stmt = $this->pdo->prepare('SELECT l.*, u.username as sdr_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.id ORDER BY l.created_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1,(int)$limit,PDO::PARAM_INT);
        $stmt->bindValue(2,(int)$offset,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Search leads by any field
    public function search($q, $filters=[],$limit=100,$offset=0){
        $where = [];
        $params = [];
        if($q){
            $where[] = "(l.email LIKE ? OR l.company LIKE ? OR l.name LIKE ? OR l.website LIKE ? OR l.clutch LIKE ? OR l.linkedin LIKE ? OR l.phone LIKE ? )";
            $like = '%'.$q.'%';
            for($i=0;$i<7;$i++) $params[] = $like;
        }
        if(!empty($filters['sdr_id'])){ $where[]='l.sdr_id = ?'; $params[] = $filters['sdr_id']; }
        if(!empty($filters['duplicate_status'])){ $where[]='l.duplicate_status = ?'; $params[] = $filters['duplicate_status']; }
        if(!empty($filters['date_from'])){ $where[]='l.created_at >= ?'; $params[] = $filters['date_from']; }
        if(!empty($filters['date_to'])){ $where[]='l.created_at <= ?'; $params[] = $filters['date_to']; }

        $sql = 'SELECT l.*, u.username as sdr_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.id';
        if($where) $sql .= ' WHERE '.implode(' AND ',$where);
        $sql .= ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?';
        $params[] = (int)$limit; $params[] = (int)$offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Detect duplicate status
    public function detectDuplicateStatus($data, $excludeId = null){
        $email = $data['email'] ? normalize($data['email']) : null;
        $phone = $data['phone'] ? normalize_phone($data['phone']) : null;
        $linkedin = $data['linkedin'] ? normalize($data['linkedin']) : null;
        $website = $data['website'] ? normalize($data['website']) : null;
        $clutch = $data['clutch'] ? normalize($data['clutch']) : null;

        if(!$email && !$phone && !$linkedin && !$website && !$clutch) return 'incomplete';

        $conds = [];
        $params = [];
        if($email){ $conds[]='LOWER(email)=?'; $params[]=$email; }
        if($phone){ $conds[]='REPLACE(phone,\'\',\'\') LIKE ?'; $params[]="%".$phone."%"; }
        if($linkedin){ $conds[]='LOWER(linkedin)=?'; $params[]=$linkedin; }
        if($website){ $conds[]='LOWER(website)=?'; $params[]=$website; }
        if($clutch){ $conds[]='LOWER(clutch)=?'; $params[]=$clutch; }

        if(!$conds) return 'incomplete';

        $sql = 'SELECT id FROM leads WHERE ('.implode(' OR ',$conds).')';
        if($excludeId) { $sql .= ' AND id != '.(int)$excludeId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? 'duplicate' : 'unique';
    }

    // Generate Lead ID in format SDR{ID}-00001
    public function generateLeadId($sdr_id){
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as c FROM leads WHERE sdr_id = ?');
        $stmt->execute([$sdr_id]);
        $c = (int)$stmt->fetchColumn();
        $seq = $c + 1;
        return 'SDR'.$sdr_id.'-'.str_pad($seq,5,'0',STR_PAD_LEFT);
    }

    // Bulk insert (CSV import)
    public function bulkInsert($rows, $created_by){
        $this->pdo->beginTransaction();
        $leadIds = [];
        try{
            foreach($rows as $r){
                $sdr_id = $r['sdr_id'] ?? $created_by;
                $r['lead_id'] = $this->generateLeadId($sdr_id);
                $r['created_by'] = $created_by;
                $id = $this->create($r);
                $leadIds[] = $id;
            }
            $this->pdo->commit();
        } catch(Exception $e){
            $this->pdo->rollBack();
            throw $e;
        }
        return $leadIds;
    }

    // Export all leads to CSV (returns CSV string)
    public function exportCsv($filters = []){
        $leads = $this->search('', $filters, 10000, 0);
        $f = fopen('php://temp', 'r+');
        fputcsv($f, ['Lead ID','Name','Company','Email','Phone','LinkedIn','Website','Clutch','SDR Name','Duplicate Status','Notes']);
        foreach($leads as $lead){
            fputcsv($f, [$lead['lead_id'],$lead['name'],$lead['company'],$lead['email'],$lead['phone'],$lead['linkedin'],$lead['website'],$lead['clutch'],$lead['sdr_name'],$lead['duplicate_status'],$lead['notes']]);
        }
        rewind($f);
        $csv = stream_get_contents($f);
        fclose($f);
        return $csv;
    }

    // Get summary counts for dashboard
    public function getSummary(){
        $pdo = $this->pdo;
        return [
            'total' => (int)$pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn(),
            'unique' => (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE duplicate_status='unique'")->fetchColumn(),
            'duplicate' => (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE duplicate_status='duplicate'")->fetchColumn(),
            'incomplete' => (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE duplicate_status='incomplete'")->fetchColumn(),
        ];
    }
}

