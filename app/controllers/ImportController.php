<?php
// app/controllers/ImportController.php
class ImportController extends Controller {
    protected $leadModel;
    
    public function __construct() {
        parent::__construct();
        $this->leadModel = new LeadModel();
    }
    
    // Show import page
    public function index() {
        $this->view('leads/import');
    }
    
    // Handle file upload and import
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=import');
        }
        
        $user = auth_user();
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('index.php?action=import&error=' . urlencode('Please select a valid CSV file'));
        }
        
        $file = $_FILES['csv_file'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, ['csv', 'xlsx', 'xls'])) {
            $this->redirect('index.php?action=import&error=' . urlencode('Please upload a CSV or Excel file'));
        }
        
        try {
            $data = $this->parseFile($file['tmp_name'], $fileExtension);
            $importedCount = $this->leadModel->bulkInsert($data, $user['id']);
            
            $this->redirect('index.php?action=import&success=' . urlencode("Successfully imported {$importedCount} leads"));
        } catch (Exception $e) {
            $this->redirect('index.php?action=import&error=' . urlencode('Import failed: ' . $e->getMessage()));
        }
    }
    
    // Export leads to CSV
    public function exportCsv() {
        $user = auth_user();
        $filters = [];
        
        // Apply role-based filtering
        if ($user['role'] === 'sdr') {
            $filters['sdr_id'] = $user['id'];
        }
        
        $csv = $this->leadModel->exportCsv($filters);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit;
    }
    
    // Export leads to Excel (simplified - just CSV with .xlsx extension)
    public function exportExcel() {
        $user = auth_user();
        $filters = [];
        
        // Apply role-based filtering
        if ($user['role'] === 'sdr') {
            $filters['sdr_id'] = $user['id'];
        }
        
        $csv = $this->leadModel->exportCsv($filters);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="leads_' . date('Y-m-d') . '.xlsx"');
        echo $csv;
        exit;
    }
    
    // Parse uploaded file
    private function parseFile($filePath, $extension) {
        $data = [];
        
        if ($extension === 'csv') {
            $data = $this->parseCsv($filePath);
        } else {
            // For Excel files, we'll use a simple CSV approach
            // In a production environment, you'd use PhpSpreadsheet library
            $data = $this->parseCsv($filePath);
        }
        
        return $this->validateAndFormatData($data);
    }
    
    // Parse CSV file
    private function parseCsv($filePath) {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new Exception('Could not open file');
        }
        
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new Exception('Invalid CSV format');
        }
        
        // Normalize headers
        $headers = array_map('strtolower', array_map('trim', $headers));
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        fclose($handle);
        return $data;
    }
    
    // Validate and format data for import
    private function validateAndFormatData($data) {
        $formatted = [];
        
        foreach ($data as $row) {
            $lead = [
                'name' => trim($row['name'] ?? ''),
                'company' => trim($row['company'] ?? ''),
                'email' => trim($row['email'] ?? ''),
                'phone' => trim($row['phone'] ?? ''),
                'linkedin' => trim($row['linkedin'] ?? ''),
                'website' => trim($row['website'] ?? ''),
                'clutch' => trim($row['clutch'] ?? ''),
                'notes' => trim($row['notes'] ?? ''),
                'sdr_id' => null // Will be set by the model
            ];
            
            // Skip empty rows
            if (empty(array_filter($lead, function($value) {
                return !empty(trim($value));
            }))) {
                continue;
            }
            
            $formatted[] = $lead;
        }
        
        return $formatted;
    }
}
?>