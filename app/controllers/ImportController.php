<?php
// app/controllers/ImportController.php
class ImportController extends Controller {
    protected $leadModel;
    protected $statusModel;
    protected $leadSourceModel;
    
    public function __construct() {
        parent::__construct();
        $this->leadModel = new LeadModel();
        $this->statusModel = new StatusModel();
        $this->leadSourceModel = new LeadSourceModel();
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
            
            // Validate data before import
            $this->validateImportData($data);
            
            $importedCount = $this->leadModel->bulkInsert($data, $user['id'], $user['sdr_id'] ?? $user['id']);
            
            $this->redirect('index.php?action=import&success=' . urlencode("Successfully imported {$importedCount} leads"));
        } catch (Exception $e) {
            $this->redirect('index.php?action=import&error=' . urlencode('Import failed: ' . $e->getMessage()));
        }
    }
    
    // Validate import data
    private function validateImportData($data) {
        if (empty($data)) {
            throw new Exception('No data found in the uploaded file.');
        }
        
        // Get all valid statuses and lead sources
        $statuses = $this->statusModel->all();
        $leadSources = $this->leadSourceModel->getActive();
        
        $validStatusNames = array_column($statuses, 'name');
        $validLeadSourceNames = array_column($leadSources, 'name');
        
        $errors = [];
        
        foreach ($data as $index => $row) {
            $rowNumber = $index + 1;
            
            // Validate status
            if (isset($row['status']) && !empty($row['status'])) {
                if (!in_array($row['status'], $validStatusNames)) {
                    $errors[] = "Row {$rowNumber}: Invalid status '{$row['status']}'. Valid statuses are: " . implode(', ', $validStatusNames);
                }
            }
            
            // Validate lead source
            if (isset($row['lead_source']) && !empty($row['lead_source'])) {
                if (!in_array($row['lead_source'], $validLeadSourceNames)) {
                    $errors[] = "Row {$rowNumber}: Invalid lead source '{$row['lead_source']}'. Valid lead sources are: " . implode(', ', $validLeadSourceNames);
                }
            }
        }
        
        if (!empty($errors)) {
            $errorMessage = "Validation failed:\n" . implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $errorMessage .= "\n... and " . (count($errors) - 10) . " more errors.";
            }
            throw new Exception($errorMessage);
        }
    }
    
    // Export leads to CSV
    public function exportCsv() {
        $user = auth_user();
        $filters = [];
        
        // Apply role-based filtering
        if ($user['role'] === 'sdr') {
            $filters['sdr_id'] = $user['sdr_id'] ?? $user['id'];
        }
        // Carry over table filters from query
        if (!empty($_GET['duplicate_status'])) { $filters['duplicate_status'] = $_GET['duplicate_status']; }
        if (!empty($_GET['date_from'])) { $filters['date_from'] = $_GET['date_from']; }
        if (!empty($_GET['date_to'])) { $filters['date_to'] = $_GET['date_to']; }
        if (!empty($_GET['lead_source_id'])) { $filters['lead_source_id'] = $_GET['lead_source_id']; }
        if (!empty($_GET['status_id'])) { $filters['status_id'] = $_GET['status_id']; }
        if (!empty($_GET['sdr_id'])) { $filters['sdr_id'] = $_GET['sdr_id']; }
        if (!empty($_GET['search'])) { $search = $_GET['search']; } else { $search = ''; }
        
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
            $filters['sdr_id'] = $user['sdr_id'] ?? $user['id'];
        }
        // Carry over table filters from query
        if (!empty($_GET['duplicate_status'])) { $filters['duplicate_status'] = $_GET['duplicate_status']; }
        if (!empty($_GET['date_from'])) { $filters['date_from'] = $_GET['date_from']; }
        if (!empty($_GET['date_to'])) { $filters['date_to'] = $_GET['date_to']; }
        if (!empty($_GET['lead_source_id'])) { $filters['lead_source_id'] = $_GET['lead_source_id']; }
        if (!empty($_GET['status_id'])) { $filters['status_id'] = $_GET['status_id']; }
        if (!empty($_GET['sdr_id'])) { $filters['sdr_id'] = $_GET['sdr_id']; }
        if (!empty($_GET['search'])) { $search = $_GET['search']; } else { $search = ''; }
        
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
    
    // Parse CSV file with robust handling (delimiter, BOM, empty cells)
    private function parseCsv($filePath) {
        $data = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception('Could not open file');
        }

        // Read first line raw to detect BOM and delimiter
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new Exception('Empty file');
        }
        // Remove UTF-8 BOM if present
        if (substr($firstLine, 0, 3) === "\xEF\xBB\xBF") {
            $firstLine = substr($firstLine, 3);
        }

        // Detect delimiter by choosing the one with most fields
        $candidateDelimiters = [',', '\t', ';', '|'];
        $bestDelimiter = ',';
        $maxFields = 0;
        foreach ($candidateDelimiters as $delim) {
            $parsed = str_getcsv($firstLine, $delim);
            if (count($parsed) > $maxFields) {
                $maxFields = count($parsed);
                $bestDelimiter = $delim;
            }
        }

        // Headers from first line using detected delimiter
        $headers = array_map('trim', str_getcsv($firstLine, $bestDelimiter));
        if (empty($headers)) {
            fclose($handle);
            throw new Exception('Invalid CSV header');
        }
        // Normalize headers (lowercase)
        $headers = array_map('strtolower', $headers);

        // Read remaining lines using fgetcsv with the detected delimiter
        while (($row = fgetcsv($handle, 0, $bestDelimiter)) !== false) {
            // Align row length to headers
            if (count($row) < count($headers)) {
                $row = array_merge($row, array_fill(0, count($headers) - count($row), ''));
            } elseif (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }
            // Check if row is effectively empty
            $nonEmpty = array_filter($row, function($v){ return trim((string)$v) !== ''; });
            if (empty($nonEmpty)) { continue; }
            $data[] = array_combine($headers, $row);
        }

        fclose($handle);
        return $data;
    }
    
    // Validate and format data for import
    private function validateAndFormatData($data) {
        $formatted = [];
        
        foreach ($data as $row) {
            $lead = [
                'lead_id' => trim($row['lead id'] ?? $row['lead_id'] ?? ''),
                'name' => trim($row['contact name'] ?? $row['name'] ?? ''),
                'company' => trim($row['company'] ?? ''),
                'email' => trim($row['email'] ?? ''),
                'phone' => trim($row['phone'] ?? ''),
                'linkedin' => trim($row['linkedin'] ?? ''),
                'website' => trim($row['website'] ?? ''),
                'clutch' => trim($row['clutch link'] ?? $row['clutch'] ?? ''),
                'job_title' => trim($row['job title'] ?? $row['job_title'] ?? ''),
                'industry' => trim($row['industry'] ?? ''),
                'lead_source' => trim($row['lead source'] ?? $row['lead_source'] ?? ''),
                'tier' => trim($row['tier'] ?? ''),
                'lead_status' => trim($row['lead status'] ?? $row['lead_status'] ?? ''),
                'insta' => trim($row['insta'] ?? $row['instagram'] ?? ''),
                'social_profile' => trim($row['social profile'] ?? $row['social_profile'] ?? ''),
                'address' => trim($row['address'] ?? ''),
                'description_information' => trim($row['description information'] ?? $row['description_information'] ?? ''),
                'whatsapp' => trim($row['whatsapp'] ?? ''),
                'next_step' => trim($row['next step'] ?? $row['next_step'] ?? ''),
                'other' => trim($row['other'] ?? ''),
                'status' => trim($row['status'] ?? ''),
                'country' => trim($row['country'] ?? ''),
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