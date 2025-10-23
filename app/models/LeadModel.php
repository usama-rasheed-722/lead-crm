<?php
class LeadModel extends Model {

    // Create a new lead with duplicate detection and auto lead_id if not provided
    public function create($data){
        if(empty($data['lead_id']) && !empty($data['sdr_id'])) {
            $data['lead_id'] = $this->generateLeadId($data['sdr_id']);
        }
        $status = $this->detectDuplicateStatus($data);
        
        // Set default status if not provided
        if (empty($data['status_id'])) {
            $statusModel = new StatusModel();
            $defaultStatus = $statusModel->getDefaultStatus();
            $data['status_id'] = $defaultStatus ? $defaultStatus['id'] : null;
        }

        $stmt = $this->pdo->prepare("
        INSERT INTO leads (
            lead_id, name, company, email, phone, linkedin, website, clutch,
            sdr_id, duplicate_status, notes, created_by,
              lead_owner, contact_name, job_title, industry, lead_source_id,
            tier, lead_status, insta, social_profile, address, description_information,
            whatsapp, next_step, other, status, status_id, country, sdr_name
        )
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    
        $stmt->execute([
            $data['lead_id'], $data['name'], $data['company'], $data['email'], $data['phone'],
            $data['linkedin'], $data['website'], $data['clutch'],
            $data['sdr_id'], $status, $data['notes'], $data['created_by'],
            $data['lead_owner'] ?? null,
            $data['contact_name'] ?? null,
            $data['job_title'] ?? null,
            $data['industry'] ?? null, 
            $data['lead_source_id'] ?? null,
            $data['tier'] ?? null,
            $data['lead_status'] ?? null,
            $data['insta'] ?? null,
            $data['social_profile'] ?? null,
            $data['address'] ?? null,
            $data['description_information'] ?? null,
            $data['whatsapp'] ?? null,
            $data['next_step'] ?? null,
            $data['other'] ?? null,
            $data['status'] ?? 'New Lead',
            $data['status_id'] ?? null,
            $data['country'] ?? null,
            $data['sdr_name'] ?? null
        ]);

        $leadId = $this->pdo->lastInsertId();
        
        // Log initial status if it's not the default status
        $statusModel = new StatusModel();
        $defaultStatus = $statusModel->getDefaultStatus();
        if ($data['status_id'] && $data['status_id'] !== $defaultStatus['id']) {
            $customFieldsData = $data['custom_fields_data'] ?? null;
            $statusName = $statusModel->getById($data['status_id'])['name'] ?? null;
            $this->logStatusChange($leadId, null, $statusName, $data['created_by'], $customFieldsData);
        }

        return $leadId;
    }

    // Update existing lead
    public function update($id, $data){
        $status = $this->detectDuplicateStatus($data, $id);
        
        // Get current lead data to check for status changes
        $currentLead = $this->getById($id);
        $oldStatusId = $currentLead['status_id'] ?? null;
        $newStatusId = $data['status_id'] ?? null;
        
        $stmt = $this->pdo->prepare("
            UPDATE leads SET
                name=?, company=?, email=?, phone=?, linkedin=?, website=?, clutch=?, sdr_id=?, duplicate_status=?, notes=?,
lead_owner=?, contact_name=?, job_title=?, industry=?, lead_source_id=?, tier=?, lead_status=?,
                insta=?, social_profile=?, address=?, description_information=?, whatsapp=?, next_step=?, other=?, status=?, status_id=?, country=?, sdr_name=?,
                updated_at=NOW()
            WHERE id=?
        ");

        $result = $stmt->execute([
            $data['name'], $data['company'], $data['email'], $data['phone'], $data['linkedin'], $data['website'],
            $data['clutch'], $data['sdr_id'], $status, $data['notes'],
            $data['lead_owner'] ?? null,
            $data['contact_name'] ?? null,
            $data['job_title'] ?? null,
            $data['industry'] ?? null,
            $data['lead_source_id'] ?? null,
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
            $data['status_id'] ?? null,
            $data['country'] ?? null,
            $data['sdr_name'] ?? null,
            $id
        ]);
        
        // Log status change if status was updated
        if ($result && $oldStatusId !== $newStatusId && !empty($newStatusId)) {
            $changedBy = $data['changed_by'] ?? $currentLead['created_by'];
            $customFieldsData = $data['custom_fields_data'] ?? null;
            $statusModel = new StatusModel();
            $oldStatusName = $oldStatusId ? $statusModel->getById($oldStatusId)['name'] ?? null : null;
            $newStatusName = $statusModel->getById($newStatusId)['name'] ?? null;
            $this->logStatusChange($id, $oldStatusName, $newStatusName, $changedBy, $customFieldsData);
        }
        
        return $result;
    }

    // Delete a lead
    public function delete($id){
        $stmt = $this->pdo->prepare('DELETE FROM leads WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Get a single lead by ID
    public function getById($id){
        $stmt = $this->pdo->prepare('SELECT l.*, u.username as sdr_name, ls.name as lead_source_name, s.name as status_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.sdr_id LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id LEFT JOIN status s ON l.status_id = s.id WHERE l.id = ?');
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
            $where[] = "(l.email LIKE ? OR l.company LIKE ? OR l.name LIKE ? OR l.website LIKE ? OR l.clutch LIKE ? OR l.linkedin LIKE ? OR l.phone LIKE ? OR l.contact_name LIKE ? OR l.job_title LIKE ? OR l.industry LIKE ? OR ls.name LIKE ? OR l.tier LIKE ? OR l.lead_status LIKE ? OR l.address LIKE ? OR l.country LIKE ? OR l.whatsapp LIKE ? OR l.next_step LIKE ? OR l.other LIKE ? OR l.notes LIKE ?)";
            $like = '%'.$q.'%';
            for($i=0;$i<19;$i++) $params[] = $like;
        }
        
        // Field-specific search
        if(!empty($filters['field_type']) && !empty($filters['field_value'])){
            $fieldType = $filters['field_type'];
            $fieldValue = $filters['field_value'];
            
            // Get available fields to validate the field type
            $availableFields = $this->getAvailableFields();
            $validFields = array_column($availableFields, 'value');
            
            if(in_array($fieldType, $validFields)){
                $where[] = "l.{$fieldType} LIKE ?";
                $params[] = '%' . $fieldValue . '%';
            }
        }
        // pr( $filters['date_from']);
        // pr( $filters['date_to'],1);
        if(!empty($filters['sdr_id'])){ $where[]='l.sdr_id = ?'; $params[] = $filters['sdr_id']; }
        if(!empty($filters['duplicate_status'])){ $where[]='l.duplicate_status = ?'; $params[] = $filters['duplicate_status']; }
        if(!empty($filters['country'])){ $where[]='l.country = ?'; $params[] = $filters['country']; }
        if(!empty($filters['lead_status'])){ $where[]='l.lead_status = ?'; $params[] = $filters['lead_status']; }
        if(!empty($filters['lead_source_id'])){ $where[]='l.lead_source_id = ?'; $params[] = $filters['lead_source_id']; }
        if(!empty($filters['status_id'])){ $where[]='l.status_id = ?'; $params[] = $filters['status_id']; }
        if(!empty($filters['tier'])){ $where[]='l.tier = ?'; $params[] = $filters['tier']; }
        if(!empty($filters['date_from'])){ $where[]='date(l.created_at) >= ?'; $params[] = $filters['date_from']; }
        if(!empty($filters['date_to'])){ $where[]='date(l.created_at) <= ?'; $params[] = $filters['date_to']; }

        $sql = 'SELECT l.*, u.username as sdr_name, ls.name as lead_source_name, s.name as status_name FROM leads l LEFT JOIN users u ON l.sdr_id = u.sdr_id LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id LEFT JOIN status s ON l.status_id = s.id';
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
            $where[] = "(l.email LIKE ? OR l.company LIKE ? OR l.name LIKE ? OR l.website LIKE ? OR l.clutch LIKE ? OR l.linkedin LIKE ? OR l.phone LIKE ? OR l.contact_name LIKE ? OR l.job_title LIKE ? OR l.industry LIKE ? OR ls.name LIKE ? OR l.tier LIKE ? OR l.lead_status LIKE ? OR l.address LIKE ? OR l.country LIKE ? OR l.whatsapp LIKE ? OR l.next_step LIKE ? OR l.other LIKE ? OR l.notes LIKE ?)";
            $like = '%'.$q.'%';
            for($i=0;$i<19;$i++) $params[] = $like;
        }
        
        // Field-specific search
        if(!empty($filters['field_type']) && !empty($filters['field_value'])){
            $fieldType = $filters['field_type'];
            $fieldValue = $filters['field_value'];
            
            // Get available fields to validate the field type
            $availableFields = $this->getAvailableFields();
            $validFields = array_column($availableFields, 'value');
            
            if(in_array($fieldType, $validFields)){
                $where[] = "l.{$fieldType} LIKE ?";
                $params[] = '%' . $fieldValue . '%';
            }
        }
        if(!empty($filters['sdr_id'])){ $where[]='l.sdr_id = ?'; $params[] = $filters['sdr_id']; }
        if(!empty($filters['duplicate_status'])){ $where[]='l.duplicate_status = ?'; $params[] = $filters['duplicate_status']; }
        if(!empty($filters['country'])){ $where[]='l.country = ?'; $params[] = $filters['country']; }
        if(!empty($filters['lead_status'])){ $where[]='l.lead_status = ?'; $params[] = $filters['lead_status']; }
        if(!empty($filters['lead_source_id'])){ $where[]='l.lead_source_id = ?'; $params[] = $filters['lead_source_id']; }
        if(!empty($filters['tier'])){ $where[]='l.tier = ?'; $params[] = $filters['tier']; }
        if(!empty($filters['status_id'])){ $where[]='l.status_id = ?'; $params[] = $filters['status_id']; }
        if(!empty($filters['date_from'])){ $where[]='l.created_at >= ?'; $params[] = $filters['date_from']; }
        if(!empty($filters['date_to'])){ $where[]='l.created_at <= ?'; $params[] = $filters['date_to']; }

        $sql = 'SELECT COUNT(*) as cnt FROM leads l LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id LEFT JOIN status s ON l.status_id = s.id';
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

    // Bulk insert (CSV import) - Optimized for performance
    public function bulkInsert($rows, $created_by_user_id, $current_user_sdr_id){
        if (empty($rows)) {
            return 0;
        }
        
        $this->pdo->beginTransaction();
        
        try{
            // Preload reference data efficiently (fetch once only)
            $leadSourceModel = new LeadSourceModel();
            $leadSources = $leadSourceModel->getActive();
            $leadSourceMap = [];
            foreach ($leadSources as $source) {
                $leadSourceMap[strtolower(trim($source['name']))] = $source['id'];
            }
            
            $statusModel = new StatusModel();
            $statuses = $statusModel->all();
            $statusMap = [];
            foreach ($statuses as $status) {
                $statusName = strtolower(trim($status['name']));
                $statusMap[$statusName] = $status['id'];
                
                // Add fuzzy matching for common variations
                if (strpos($statusName, 'follow up') !== false) {
                    // Handle "Follow up in 3 days" vs "Follow up in 3 day"
                    $fuzzyName = str_replace('days', 'day', $statusName);
                    if ($fuzzyName !== $statusName) {
                        $statusMap[$fuzzyName] = $status['id'];
                    }
                    $fuzzyName = str_replace('day', 'days', $statusName);
                    if ($fuzzyName !== $statusName) {
                        $statusMap[$fuzzyName] = $status['id'];
                    }
                }
            }
            
            $defaultStatus = $statusModel->getDefaultStatus();
            $defaultStatusId = $defaultStatus ? $defaultStatus['id'] : null;
            
            // Prepare batch insert data
            $insertData = [];
            $leadIds = [];
            
            // Get the highest existing lead ID for this SDR to avoid conflicts
            $stmt = $this->pdo->prepare('
                SELECT lead_id 
                FROM leads 
                WHERE lead_id LIKE ? 
                ORDER BY id DESC 
                LIMIT 1
            ');
            $pattern = "SDR{$current_user_sdr_id}-%";
            $stmt->execute([$pattern]);
            $latestLead = $stmt->fetch();
            
            $nextNumber = 1;
            if ($latestLead && !empty($latestLead['lead_id'])) {
                if (preg_match('/SDR' . $current_user_sdr_id . '-(\d+)/', $latestLead['lead_id'], $matches)) {
                    $lastNumber = (int) $matches[1];
                    $nextNumber = $lastNumber + 1;
                }
            }
            
            foreach($rows as $r){
                $sdr_id = $r['sdr_id'] ?? $current_user_sdr_id;
                
                // Generate unique lead ID for this batch
                $formattedNumber = str_pad($nextNumber, 10, '0', STR_PAD_LEFT);
                $lead_id = "SDR{$sdr_id}-{$formattedNumber}";
                $nextNumber++; // Increment for next lead in the batch
                
                $duplicate_status = $this->detectDuplicateStatus($r);
                
                // Handle lead source mapping - strict mode: fail if no match found
                $lead_source_id = null;
                if (isset($r['lead_source_id']) && !empty($r['lead_source_id'])) {
                    $lead_source_id = $r['lead_source_id'];
                } else if (isset($r['lead_source']) && !empty($r['lead_source'])) {
                    $leadSourceName = strtolower(trim($r['lead_source']));
                    if (isset($leadSourceMap[$leadSourceName])) {
                        $lead_source_id = $leadSourceMap[$leadSourceName];
                    } else {
                        // If lead source is provided but no match found, throw error
                        throw new Exception("Lead source '{$r['lead_source']}' not found in system. Please check your CSV data.");
                    }
                }
                
                // Handle status mapping - strict mode: fail if no match found
                $status_id = null;
                if (isset($r['status']) && !empty($r['status'])) {
                    $statusName = strtolower(trim($r['status']));
                    if (isset($statusMap[$statusName])) {
                        $status_id = $statusMap[$statusName];
                    } else {
                        // If status is provided but no match found, throw error
                        throw new Exception("Status '{$r['status']}' not found in system. Please check your CSV data.");
                    }
                } else {
                    // Use default status only if no status provided
                    $status_id = $defaultStatusId;
                }
                
                // Prepare data for batch insert
                $insertData[] = [
                    $lead_id,
                    $r['name'] ?? null,
                    $r['company'] ?? null,
                    $r['email'] ?? null,
                    $r['phone'] ?? null,
                    $r['linkedin'] ?? null,
                    $r['website'] ?? null,
                    $r['clutch'] ?? null,
                    $current_user_sdr_id,
                    $duplicate_status,
                    $r['notes'] ?? null,
                    $created_by_user_id,
                    $r['lead_owner'] ?? null,
                    $r['contact_name'] ?? null,
                    $r['job_title'] ?? null,
                    $r['industry'] ?? null,
                    $lead_source_id,
                    $r['tier'] ?? null,
                    $r['lead_status'] ?? null,
                    $r['insta'] ?? null,
                    $r['social_profile'] ?? null,
                    $r['address'] ?? null,
                    $r['description_information'] ?? null,
                    $r['whatsapp'] ?? null,
                    $r['next_step'] ?? null,
                    $r['other'] ?? null,
                    $status_id,
                    $r['country'] ?? null,
                    $r['sdr_name'] ?? null
                ];
            }
            
            // Perform batch insert
            if (!empty($insertData)) {
                $placeholders = str_repeat('(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?),', count($insertData) - 1) . '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
                
                $sql = "INSERT INTO leads (
                    lead_id, name, company, email, phone, linkedin, website, clutch,
                    sdr_id, duplicate_status, notes, created_by,
                    lead_owner, contact_name, job_title, industry, lead_source_id,
                    tier, lead_status, insta, social_profile, address, description_information,
                    whatsapp, next_step, other, status_id, country, sdr_name
                ) VALUES {$placeholders}";
                
                $stmt = $this->pdo->prepare($sql);
                
                // Flatten the array for execute
                $flatData = [];
                foreach ($insertData as $row) {
                    $flatData = array_merge($flatData, $row);
                }
                
                $stmt->execute($flatData);
                
                // Get inserted IDs
                $firstId = $this->pdo->lastInsertId();
                $insertedCount = count($insertData);
                for ($i = 0; $i < $insertedCount; $i++) {
                    $leadIds[] = $firstId + $i;
                }
            }
            
            $this->pdo->commit();
            return count($leadIds);
            
        } catch(Exception $e){
            $this->pdo->rollBack();
            throw $e;
        }
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
                $lead['lead_owner'],$lead['contact_name'],$lead['job_title'],$lead['industry'],$lead['lead_source_name'],$lead['tier'],$lead['lead_status'],
                $lead['insta'],$lead['social_profile'],$lead['address'],$lead['description_information'],$lead['whatsapp'],$lead['next_step'],$lead['other'],
                $lead['status_name'],$lead['country'],$lead['sdr_name'],$lead['duplicate_status'],$lead['notes']
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
    public function mergeDuplicates($primaryId, $duplicateIds, $assignSdrId = null) {
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
            
            // Ensure SDR assignment: if provided, assign to merging SDR; else keep existing
            if ($assignSdrId !== null && $assignSdrId !== '') {
                $mergedData['sdr_id'] = $assignSdrId;
            } elseif (empty($primaryLead['sdr_id'])) {
                // If primary has no SDR and no assign provided, default to created_by if it looks like an SDR id
                if (!empty($primaryLead['created_by'])) {
                    $mergedData['sdr_id'] = $primaryLead['created_by'];
                }
            }

            // Update primary lead with merged data
            $this->update($primaryId, $mergedData);
            
            // Delete duplicate leads
            foreach ($duplicateIds as $dupId) {
                $this->delete($dupId);
            }
            
            // Check if primary lead has 'duplicate' tag and if no duplicates remain
            if ($primaryLead['duplicate_status'] === 'duplicate') {
                $remainingDuplicates = $this->findDuplicates($primaryId);
                if (empty($remainingDuplicates)) {
                    // No duplicates remain, remove the duplicate tag
                    $stmt = $this->pdo->prepare("UPDATE leads SET duplicate_status = 'unique' WHERE id = ?");
                    $stmt->execute([$primaryId]);
                }
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

        // Dynamic lead source counts - get all lead sources and their counts
        $leadSourceModel = new LeadSourceModel();
        $leadSources = $leadSourceModel->all();
        $leadSourceCounts = [];
        
        foreach ($leadSources as $source) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads' . $buildWhere("lead_source_id = ?"));
            $stmt->execute(array_merge($params, [$source['id']]));
            $leadSourceCounts[strtolower(str_replace(' ', '_', $source['name']))] = (int)$stmt->fetchColumn();
        }

        return compact('total','unique','duplicate','incomplete') + $leadSourceCounts;
    }

    // Bulk update status for multiple leads
    public function bulkUpdateStatus($leadIds, $newStatusId, $changedBy) {
        if (empty($leadIds) || empty($newStatusId)) {
            return false;
        }
        
        $this->pdo->beginTransaction();
        try {
            $placeholders = str_repeat('?,', count($leadIds) - 1) . '?';
            
            // Get current statuses for logging
            $stmt = $this->pdo->prepare("SELECT id, status_id FROM leads WHERE id IN ($placeholders)");
            $stmt->execute($leadIds);
            $currentLeads = $stmt->fetchAll();
            
            // Get status names for logging
            $statusModel = new StatusModel();
            $newStatusName = $statusModel->getById($newStatusId)['name'] ?? null;
            
            // Update statuses
            $stmt = $this->pdo->prepare("UPDATE leads SET status_id = ?, updated_at = NOW() WHERE id IN ($placeholders)");
            $params = array_merge([$newStatusId], $leadIds);
            $stmt->execute($params);
            
            // Log status changes
            foreach ($currentLeads as $lead) {
                if ($lead['status_id'] != $newStatusId) {
                    $oldStatusName = $lead['status_id'] ? $statusModel->getById($lead['status_id'])['name'] ?? null : null;
                    $this->logStatusChange($lead['id'], $oldStatusName, $newStatusName, $changedBy);
                }
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Update status for a single lead with custom fields
    public function updateStatusWithCustomFields($leadId, $newStatusId, $changedBy, $customFieldsData = null) {
        if (empty($leadId) || empty($newStatusId)) {
            return false;
        }
        
        $this->pdo->beginTransaction();
        try {
            // Get current lead data
            $currentLead = $this->getById($leadId);
            if (!$currentLead) {
                throw new Exception('Lead not found');
            }
            
            $oldStatusId = $currentLead['status_id'];
            
            // Get status names for logging
            $statusModel = new StatusModel();
            $oldStatusName = $oldStatusId ? $statusModel->getById($oldStatusId)['name'] ?? null : null;
            $newStatusName = $statusModel->getById($newStatusId)['name'] ?? null;
            
            // Update status
            $stmt = $this->pdo->prepare("UPDATE leads SET status_id = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatusId, $leadId]);
            
            // Log status change with custom fields data
            if ($oldStatusId != $newStatusId) {
                $this->logStatusChange($leadId, $oldStatusName, $newStatusName, $changedBy, $customFieldsData);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Bulk update status with custom fields (single transaction, single query approach)
    public function bulkUpdateStatusWithCustomFields($leadIds, $newStatusId, $changedBy, $customFieldsData = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Get status names for logging
            $statusModel = new StatusModel();
            $newStatus = $statusModel->getById($newStatusId);
            if (!$newStatus) {
                throw new Exception('Invalid status ID');
            }
            $newStatusName = $newStatus['name'];
            $customFieldsJson = !empty($customFieldsData) ? json_encode($customFieldsData) : null;
            
            // Get current status for each lead
            $placeholders = str_repeat('?,', count($leadIds) - 1) . '?';
            $stmt = $this->pdo->prepare("SELECT id, status_id FROM leads WHERE id IN ($placeholders)");
            $stmt->execute($leadIds);
            $currentLeads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Prepare status history data for bulk insert
            $statusHistoryData = [];
            $updateLeadIds = [];
            
            foreach ($currentLeads as $lead) {
                $oldStatusId = $lead['status_id'];
                $oldStatusName = $oldStatusId ? $statusModel->getById($oldStatusId)['name'] ?? null : null;
                
                // Only update if status is different
                if ($oldStatusId != $newStatusId) {
                    $updateLeadIds[] = $lead['id'];
                    $statusHistoryData[] = [
                        'lead_id' => $lead['id'],
                        'old_status' => $oldStatusName,
                        'new_status' => $newStatusName,
                        'changed_by' => $changedBy,
                        'custom_fields_data' => $customFieldsJson
                    ];
                }
            }
            
            if (empty($updateLeadIds)) {
                $this->pdo->commit();
                return true; // No changes needed
            }
            
            // Bulk update leads status
            $updatePlaceholders = str_repeat('?,', count($updateLeadIds) - 1) . '?';
            $updateStmt = $this->pdo->prepare("UPDATE leads SET status_id = ?, updated_at = NOW() WHERE id IN ($updatePlaceholders)");
            $updateParams = array_merge([$newStatusId], $updateLeadIds);
            $updateStmt->execute($updateParams);
            
            // Bulk insert status history (single query)
            if (!empty($statusHistoryData)) {
                $historyPlaceholders = [];
                $historyParams = [];
                
                foreach ($statusHistoryData as $data) {
                    $uniqueId = uniqid(); // Generate unique placeholder IDs
                    $historyPlaceholders[] = "(:lead_id_$uniqueId, :old_status_$uniqueId, :new_status_$uniqueId, :changed_by_$uniqueId, :custom_fields_data_$uniqueId)";
                    $historyParams[":lead_id_$uniqueId"] = $data['lead_id'];
                    $historyParams[":old_status_$uniqueId"] = $data['old_status'];
                    $historyParams[":new_status_$uniqueId"] = $data['new_status'];
                    $historyParams[":changed_by_$uniqueId"] = $data['changed_by'];
                    $historyParams[":custom_fields_data_$uniqueId"] = $data['custom_fields_data'];
                }
                
                $historySql = "INSERT INTO contact_status_history (lead_id, old_status, new_status, changed_by, custom_fields_data) VALUES " . implode(', ', $historyPlaceholders);
                $historyStmt = $this->pdo->prepare($historySql);
                $historyStmt->execute($historyParams);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Bulk update tier and lead_status
    public function bulkUpdateTierAndStatus($leadIds, $tier = '', $leadStatus = '') {
        try {
            $this->pdo->beginTransaction();
            
            $updateFields = [];
            $params = [];
            
            if (!empty($tier)) {
                $updateFields[] = 'tier = ?';
                $params[] = $tier;
            }
            
            if (!empty($leadStatus)) {
                $updateFields[] = 'lead_status = ?';
                $params[] = $leadStatus;
            }
            
            if (!empty($updateFields)) {
                $updateFields[] = 'updated_at = NOW()';
                $params = array_merge($params, $leadIds);
                
                $placeholders = str_repeat('?,', count($leadIds) - 1) . '?';
                $sql = "UPDATE leads SET " . implode(', ', $updateFields) . " WHERE id IN ($placeholders)";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Log status change
    private function logStatusChange($leadId, $oldStatus, $newStatus, $changedBy, $customFieldsData = null) {
        $stmt = $this->pdo->prepare('
            INSERT INTO contact_status_history (lead_id, old_status, new_status, changed_by, custom_fields_data) 
            VALUES (?, ?, ?, ?, ?)
        ');
        $customFieldsJson = $customFieldsData ? json_encode($customFieldsData) : null;
        return $stmt->execute([$leadId, $oldStatus, $newStatus, $changedBy, $customFieldsJson]);
    }

    // Get leads with specific columns for the new leads page
    public function getLeadsForManagement($limit = 100, $offset = 0, $filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['sdr_id'])) {
            $where[] = 'l.sdr_id = ?';
            $params[] = $filters['sdr_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'l.status = ?';
            $params[] = $filters['status'];
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "
            SELECT l.id, l.lead_id, l.company, l.name as contact_name, l.website, l.linkedin, l.clutch, l.status
            FROM leads l 
            {$whereClause}
            ORDER BY l.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Count leads for management page
    public function countLeadsForManagement($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['sdr_id'])) {
            $where[] = 'sdr_id = ?';
            $params[] = $filters['sdr_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) FROM leads {$whereClause}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Get available fields from leads table for dynamic field search
    public function getAvailableFields() {
        $stmt = $this->pdo->prepare("DESCRIBE leads");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        $fields = [];
        foreach ($columns as $column) {
            $fieldName = $column['Field'];
            // Skip system fields and IDs
            if (!in_array($fieldName, ['id', 'created_at', 'updated_at', 'created_by', 'sdr_id'])) {
                $displayName = ucwords(str_replace('_', ' ', $fieldName));
                $fields[] = [
                    'value' => $fieldName,
                    'label' => $displayName
                ];
            }
        }
        
        return $fields;
    }

    // Get status history for a lead
    public function getStatusHistory($leadId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                h.*,
                u.full_name,
                u.username
            FROM contact_status_history h
            LEFT JOIN users u ON h.changed_by = u.id
            WHERE h.lead_id = ?
            ORDER BY h.changed_at DESC
        ");
        $stmt->execute([$leadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
