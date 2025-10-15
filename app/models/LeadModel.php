<?php
class LeadModel extends BaseModel {

    // Create a new lead with duplicate detection and auto lead_id if not provided
    public function create($data){
        if(empty($data['lead_id']) && !empty($data['sdr_id'])) {
            $data['lead_id'] = $this->generateLeadId($data['sdr_id']);
        }
        $status = $this->detectDuplicateStatus($data);

        $stmt = $this->pdo->prepare("
        INSERT INTO leads (
            lead_id, name, company, email, phone, linkedin, website, clutch,
            sdr_id, duplicate_status, notes, created_by,
              lead_owner, contact_name, job_title, industry, lead_source,
            tier, lead_status, insta, social_profile, address, description_information,
            whatsapp, next_step, other, status, country, sdr_name
        )
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    
        $stmt->execute([
            $data['lead_id'], $data['name'], $data['company'], $data['email'], $data['phone'],
            $data['linkedin'], $data['website'], $data['clutch'],
            $data['sdr_id'], $status, $data['notes'], $data['created_by'],
            $data['lead_owner'] ?? null,
            $data['contact_name'] ?? null,
            $data['job_title'] ?? null,
            $data['industry'] ?? null,
            $data['lead_source'] ?? null,
            $data['tier'] ?? null,
            $data['lead_status'] ?? null,
            $data['insta'] ?? null,
            $data['social_profile'] ?? null,
            $data['address'] ?? null,
            $data['description_information'] ?? null,
            $data['whatsapp'] ?? null,
            $data['next_step'] ?? null,
            $data['other'] ?? null,
            $data['status'] ?? null,
            $data['country'] ?? null,
            $data['sdr_name'] ?? null
        ]);

        return $this->pdo->lastInsertId();
    }

    // Update existing lead
    public function update($id, $data){
        $status = $this->detectDuplicateStatus($data, $id);
        $stmt = $this->pdo->prepare("
            UPDATE leads SET
                name=?, company=?, email=?, phone=?, linkedin=?, website=?, clutch=?, sdr_id=?, duplicate_status=?, notes=?,
                  lead_owner=?, contact_name=?, job_title=?, industry=?, lead_source=?, tier=?, lead_status=?,
                insta=?, social_profile=?, address=?, description_information=?, whatsapp=?, next_step=?, other=?, status=?, country=?, sdr_name=?,
                updated_at=NOW()
            WHERE id=?
        ");

        return $stmt->execute([
            $data['name'], $data['company'], $data['email'], $data['phone'], $data['linkedin'], $data['website'],
            $data['clutch'], $data['sdr_id'], $status, $data['notes'],
            $data['lead_owner'] ?? null,
            $data['contact_name'] ?? null,
            $data['job_title'] ?? null,
            $data['industry'] ?? null,
            $data['lead_source'] ?? null,
            $data['tier'] ?? null,
            $data['lead_status'] ?? null,
            $data['insta'] ?? null,
            $data['social_profile'] ?? null,
            $data['address'] ?? null,
            $data['description_information'] ?? null,
            $data['whatsapp'] ?? null,
            $data['next_step'] ?? null,
            $data['other'] ?? null,
            $data['status'] ?? null,
            $data['country'] ?? null,
            $data['sdr_name'] ?? null,
            $id
        ]);
    }

    // Delete a lead
    public function delete($id){
        $stmt = $this->pdo->prepare('DELETE FROM leads WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Get a single lead by ID
    public function getById($id){
        $stmt = $this->pdo->prepare('SELECT l.*, u.username as sdr_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.sdr_id WHERE l.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Get all leads (paginated)
    public function all($limit=100, $offset=0){
        $stmt = $this->pdo->prepare('SELECT l.*, u.username as sdr_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.sdr_id ORDER BY l.created_at DESC LIMIT ? OFFSET ?');
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
            $where[] = "(l.email LIKE ? OR l.company LIKE ? OR l.name LIKE ? OR l.website LIKE ? OR l.clutch LIKE ? OR l.linkedin LIKE ? OR l.phone LIKE ? OR l.contact_name LIKE ? OR l.job_title LIKE ?)";
            $like = '%'.$q.'%';
            for($i=0;$i<9;$i++) $params[] = $like;
        }
        // pr( $filters['date_from']);
        // pr( $filters['date_to'],1);
        if(!empty($filters['sdr_id'])){ $where[]='l.sdr_id = ?'; $params[] = $filters['sdr_id']; }
        if(!empty($filters['duplicate_status'])){ $where[]='l.duplicate_status = ?'; $params[] = $filters['duplicate_status']; }
        if(!empty($filters['country'])){ $where[]='l.country = ?'; $params[] = $filters['country']; }
        if(!empty($filters['lead_status'])){ $where[]='l.lead_status = ?'; $params[] = $filters['lead_status']; }
        if(!empty($filters['lead_source'])){ $where[]='l.lead_source = ?'; $params[] = $filters['lead_source']; }
        if(!empty($filters['tier'])){ $where[]='l.tier = ?'; $params[] = $filters['tier']; }
        if(!empty($filters['date_from'])){ $where[]='date(l.created_at) >= ?'; $params[] = $filters['date_from']; }
        if(!empty($filters['date_to'])){ $where[]='date(l.created_at) <= ?'; $params[] = $filters['date_to']; }

        $sql = 'SELECT l.*, u.username as sdr_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.sdr_id';
        if($where) $sql .= ' WHERE '.implode(' AND ',$where);
        $sql .= ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?';
        $params[] = (int)$limit; $params[] = (int)$offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Count rows for pagination with same filters as search
    public function countSearch($q, $filters=[]){
        $where = [];
        $params = [];
        if($q){
            $where[] = "(l.email LIKE ? OR l.company LIKE ? OR l.name LIKE ? OR l.website LIKE ? OR l.clutch LIKE ? OR l.linkedin LIKE ? OR l.phone LIKE ? OR l.contact_name LIKE ? OR l.job_title LIKE ?)";
            $like = '%'.$q.'%';
            for($i=0;$i<9;$i++) $params[] = $like;
        }
        if(!empty($filters['sdr_id'])){ $where[]='l.sdr_id = ?'; $params[] = $filters['sdr_id']; }
        if(!empty($filters['duplicate_status'])){ $where[]='l.duplicate_status = ?'; $params[] = $filters['duplicate_status']; }
        if(!empty($filters['country'])){ $where[]='l.country = ?'; $params[] = $filters['country']; }
        if(!empty($filters['lead_status'])){ $where[]='l.lead_status = ?'; $params[] = $filters['lead_status']; }
        if(!empty($filters['lead_source'])){ $where[]='l.lead_source = ?'; $params[] = $filters['lead_source']; }
        if(!empty($filters['tier'])){ $where[]='l.tier = ?'; $params[] = $filters['tier']; }
        if(!empty($filters['date_from'])){ $where[]='l.created_at >= ?'; $params[] = $filters['date_from']; }
        if(!empty($filters['date_to'])){ $where[]='l.created_at <= ?'; $params[] = $filters['date_to']; }

        $sql = 'SELECT COUNT(*) as cnt FROM leads l';
        if($where) $sql .= ' WHERE '.implode(' AND ',$where);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    // Duplicate detection logic remains the same
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
        if($phone){ $conds[]='REPLACE(phone,"","") LIKE ?'; $params[]=$phone; }
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
        return generateNextSDR($sdr_id);
    }

    // Bulk insert (CSV import)
    public function bulkInsert($rows, $created_by_user_id, $current_user_sdr_id){
        $this->pdo->beginTransaction();
        $leadIds = [];
        try{
            foreach($rows as $r){
                $sdr_id = $r['sdr_id'] ?? $current_user_sdr_id;
                $r['sdr_id'] =  $current_user_sdr_id;
                $r['lead_id'] = $this->generateLeadId($sdr_id);
                $r['created_by'] = $created_by_user_id;
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
        fputcsv($f, [
            'Lead ID','Name','Company','Email','Phone','LinkedIn','Website','Clutch','Lead Owner','Contact Name',
            'Job Title','Industry','Lead Source','Tier','Lead Status','Insta','Social Profile','Address',
            'Description Information','Whatsapp','Next Step','Other','Status','Country','SDR Name','Duplicate Status','Notes'
        ]);
        foreach($leads as $lead){
            fputcsv($f, [
                $lead['lead_id'],$lead['name'],$lead['company'],$lead['email'],$lead['phone'],$lead['linkedin'],$lead['website'],$lead['clutch'],
                $lead['lead_owner'],$lead['contact_name'],$lead['job_title'],$lead['industry'],$lead['lead_source'],$lead['tier'],$lead['lead_status'],
                $lead['insta'],$lead['social_profile'],$lead['address'],$lead['description_information'],$lead['whatsapp'],$lead['next_step'],$lead['other'],
                $lead['status'],$lead['country'],$lead['sdr_name'],$lead['duplicate_status'],$lead['notes']
            ]);
        }
        rewind($f);
        $csv = stream_get_contents($f);
        fclose($f);
        return $csv;
    }

    // Find duplicates for a specific lead
    public function findDuplicates($leadId) {
        $lead = $this->getById($leadId);
        if (!$lead) return [];
        
        $duplicates = [];
        
        // Check for duplicates based on email
        if (!empty($lead['email'])) {
            $stmt = $this->pdo->prepare('
                SELECT l.*, u.username as sdr_name 
                FROM leads l 
                LEFT JOIN users u ON l.sdr_id = u.sdr_id 
                WHERE LOWER(l.email) = LOWER(?) AND l.id != ?
            ');
            $stmt->execute([$lead['email'], $leadId]);
            $emailDups = $stmt->fetchAll();
            foreach ($emailDups as $dup) {
                $duplicates[] = array_merge($dup, ['match_type' => 'email']);
            }
        }

        
        // Check for duplicates based on phone
        if (!empty($lead['phone'])) {
            $normalizedPhone = normalize_phone($lead['phone']);
            // pr( $normalizedPhone ,1);
            $stmt = $this->pdo->prepare('
                SELECT l.*, u.username as sdr_name 
                FROM leads l 
                LEFT JOIN users u ON l.sdr_id = u.sdr_id 
                WHERE REPLACE(l.phone, " ", "") = ? AND l.id != ?
            ');
            $stmt->execute([$normalizedPhone, $leadId]);
            $phoneDups = $stmt->fetchAll();
            foreach ($phoneDups as $dup) {
                $duplicates[] = array_merge($dup, ['match_type' => 'phone']);
            }
        }
        
        // Check for duplicates based on LinkedIn
        if (!empty($lead['linkedin'])) {
            $stmt = $this->pdo->prepare('
                SELECT l.*, u.username as sdr_name 
                FROM leads l 
                LEFT JOIN users u ON l.sdr_id = u.sdr_id 
                WHERE LOWER(l.linkedin) = LOWER(?) AND l.id != ?
            ');
            $stmt->execute([$lead['linkedin'], $leadId]);
            $linkedinDups = $stmt->fetchAll();
            foreach ($linkedinDups as $dup) {
                $duplicates[] = array_merge($dup, ['match_type' => 'linkedin']);
            }
        }
        
        // Check for duplicates based on website
        if (!empty($lead['website'])) {
            $stmt = $this->pdo->prepare('
            SELECT l.*, u.username as sdr_name 
                FROM leads l 
                LEFT JOIN users u ON l.sdr_id = u.sdr_id 
                WHERE LOWER(l.website) = LOWER(?) AND l.id != ?
                ');
                $stmt->execute([$lead['website'], $leadId]);
                $websiteDups = $stmt->fetchAll();
                foreach ($websiteDups as $dup) {
                    $duplicates[] = array_merge($dup, ['match_type' => 'website']);
                }
            }
            
            // Check for duplicates based on Clutch
            if (!empty($lead['clutch'])) {
            $stmt = $this->pdo->prepare('
            SELECT l.*, u.username as sdr_name 
            FROM leads l 
            LEFT JOIN users u ON l.sdr_id = u.id 
                WHERE LOWER(l.clutch) = LOWER(?) AND l.id != ?
                ');
                $stmt->execute([$lead['clutch'], $leadId]);
                $clutchDups = $stmt->fetchAll();
            foreach ($clutchDups as $dup) {
                $duplicates[] = array_merge($dup, ['match_type' => 'clutch']);
            }
        }
        
        // Remove duplicates and return unique results
        $uniqueDuplicates = [];
        $seenIds = [];
        foreach ($duplicates as $dup) {
            if (!in_array($dup['id'], $seenIds)) {
                $uniqueDuplicates[] = $dup;
                $seenIds[] = $dup['id'];
            }
        }
        
        return $uniqueDuplicates;
    }
    
    // Merge duplicate leads
    public function mergeDuplicates($primaryId, $duplicateIds) {
        $this->pdo->beginTransaction();
        try {
            // Get primary lead data
            $primaryLead = $this->getById($primaryId);
            if (!$primaryLead) {
                throw new Exception('Primary lead not found');
            }
            
            // Get duplicate leads data
            $duplicateLeads = [];
            foreach ($duplicateIds as $dupId) {
                $dupLead = $this->getById($dupId);
                if ($dupLead) {
                    $duplicateLeads[] = $dupLead;
                }
            }
            
            // Merge data from duplicates into primary (keep non-empty values)
            $mergedData = [
                'name' => $primaryLead['name'],
                'company' => $primaryLead['company'],
                'email' => $primaryLead['email'],
                'phone' => $primaryLead['phone'],
                'linkedin' => $primaryLead['linkedin'],
                'website' => $primaryLead['website'],
                'clutch' => $primaryLead['clutch'],
                'job_title' => $primaryLead['job_title'],
                'industry' => $primaryLead['industry'],
                'lead_source' => $primaryLead['lead_source'],
                'tier' => $primaryLead['tier'],
                'lead_status' => $primaryLead['lead_status'],
                'insta' => $primaryLead['insta'],
                'social_profile' => $primaryLead['social_profile'],
                'address' => $primaryLead['address'],
                'description_information' => $primaryLead['description_information'],
                'whatsapp' => $primaryLead['whatsapp'],
                'next_step' => $primaryLead['next_step'],
                'other' => $primaryLead['other'],
                'status' => $primaryLead['status'],
                'country' => $primaryLead['country'],
                'notes' => $primaryLead['notes'],
                // 'duplicate_status'=>'unique'
            ];
            
            // Merge data from duplicates
            foreach ($duplicateLeads as $dup) {
                foreach ($mergedData as $key => $value) {
                    if (empty($value) && !empty($dup[$key])) {
                        $mergedData[$key] = $dup[$key];
                    }
                }
                
                // Append notes
                if (!empty($dup['notes'])) {
                    $mergedData['notes'] .= "\n\n--- Merged from Lead ID: {$dup['lead_id']} ---\n" . $dup['notes'];
                }
            }
            
            // Update primary lead with merged data
            $this->update($primaryId, $mergedData);
            
            // Delete duplicate leads
            foreach ($duplicateIds as $dupId) {
                $this->delete($dupId);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Dashboard summary
    public function getSummary(){
        $pdo = $this->pdo;
        return [
            'total' => (int)$pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn(),
            'unique' => (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE duplicate_status='unique'")->fetchColumn(),
            'duplicate' => (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE duplicate_status='duplicate'")->fetchColumn(),
            'incomplete' => (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE duplicate_status='incomplete'")->fetchColumn(),
        ];
    }

    // Filtered summary for dashboard (by sdr_id and/or dates)
    public function getSummaryByFilters($filters = []){
        $where = [];
        $params = [];
        if (!empty($filters['sdr_id'])) { $where[] = 'sdr_id = ?'; $params[] = $filters['sdr_id']; }
        if (!empty($filters['date_from'])) { $where[] = 'date(created_at) >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where[] = 'date(created_at) <= ?'; $params[] = $filters['date_to']; }
        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        // pr('SELECT COUNT(*) FROM leads' . $whereSql,1);
        // pr($params,1);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads' . $whereSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();
        // pr( $total ,1);
        // Helper to safely append extra condition
        $buildWhere = function(string $extra) use ($whereSql){
            if ($whereSql) return $whereSql . ' AND ' . $extra;
            return ' WHERE ' . $extra;
        };
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads" . $buildWhere("duplicate_status='unique'"));
        $stmt->execute($params);
        $unique = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads" . $buildWhere("duplicate_status='duplicate'"));
        $stmt->execute($params);
        $duplicate = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads" . $buildWhere("duplicate_status='incomplete'"));
        $stmt->execute($params);
        $incomplete = (int)$stmt->fetchColumn();

        // Extra cards: counts by lead_source
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads' . $buildWhere("LOWER(lead_source) = 'linkedin'"));
        $stmt->execute($params);
        $linkedin = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads' . $buildWhere("LOWER(lead_source) = 'clutch'"));
        $stmt->execute($params);
        $clutch = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads' . $buildWhere("LOWER(lead_source) = 'gmb'"));
        $stmt->execute($params);
        $gmb = (int)$stmt->fetchColumn();

        return compact('total','unique','duplicate','incomplete','linkedin','clutch','gmb');
    }
}
