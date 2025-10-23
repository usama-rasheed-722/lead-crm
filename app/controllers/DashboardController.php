<?php
// app/controllers/DashboardController.php
class DashboardController extends Controller {
    protected $leadModel;
    protected $userModel;
    protected $noteModel;
    protected $quotaModel;

    public function __construct() {
        parent::__construct();
        $this->leadModel = new LeadModel();
        $this->userModel = new UserModel();
        $this->noteModel = new NoteModel();
        $this->quotaModel = new QuotaModel();
    }

    public function index() {
        $user = auth_user();
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $selectedSdrId = '';
        if (in_array($user['role'], ['admin','manager'])) {
            $selectedSdrId = $_GET['sdr_id'] ?? '';
        }
        $filters = [];
        if ($dateFrom) { $filters['date_from'] = $dateFrom; }
        if ($dateTo) { $filters['date_to'] = $dateTo; }
        if ($user['role'] === 'sdr') { $filters['sdr_id'] = $user['sdr_id'] ?? $user['id']; }
        if ($selectedSdrId !== '') { $filters['sdr_id'] = $selectedSdrId; }

        // Personalized summary
        $summary = $this->leadModel->getSummaryByFilters($filters);

        // Recent leads scoped
        $recentLeads = $this->leadModel->search('', $filters, 10, 0);

        // Recent activity scoped
        $recentActivity = $this->noteModel->getRecentActivity(5, $filters);
        
        // Team performance for admin/manager with optional date filters
        $teamPerformance = [];
        if (in_array($user['role'], ['admin', 'manager'])) {
            $teamPerformance = $this->getTeamPerformance($dateFrom, $dateTo);
        }
        
        // User quota information
        $userQuotas = [];
        if ($user['role'] === 'sdr') {
            $userQuotas = $this->quotaModel->getQuotaDetailsWithUsage($user['id']);
        }
        
        $this->view('dashboard/home', [
            'summary' => $summary,
            'recentLeads' => $recentLeads,
            'recentActivity' => $recentActivity,
            'teamPerformance' => $teamPerformance,
            'userQuotas' => $userQuotas,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'users' => $this->userModel->getSDRs(),
            'selected_sdr_id' => $selectedSdrId
        ]);
    }
    
    private function getTeamPerformance($dateFrom = '', $dateTo = '') {
        $where = 'u.role = "sdr"';
        $params = [];
        if ($dateFrom) { $where .= ' AND date(l.created_at) >= ?'; $params[] = $dateFrom; }
        if ($dateTo) { $where .= ' AND date(l.created_at) <= ?'; $params[] = $dateTo; }
        $sql = '
            SELECT 
                u.username as sdr_name,
                COALESCE(COUNT(l.id),0) as total,
                COALESCE(SUM(CASE WHEN l.duplicate_status = "unique" THEN 1 ELSE 0 END),0) as unique_count,
                COALESCE(SUM(CASE WHEN l.duplicate_status = "duplicate" THEN 1 ELSE 0 END),0) as duplicate_count,
                COALESCE(SUM(CASE WHEN l.duplicate_status = "incomplete" THEN 1 ELSE 0 END),0) as incomplete_count
            FROM users u
            LEFT JOIN leads l ON u.sdr_id = l.sdr_id
            WHERE ' . $where . '
            GROUP BY u.id, u.username
            ORDER BY total DESC
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>