<?php
// app/controllers/DashboardController.php
class DashboardController extends Controller {
    protected $leadModel;
    protected $userModel;
    protected $noteModel;

    public function __construct() {
        parent::__construct();
        $this->leadModel = new LeadModel();
        $this->userModel = new UserModel();
        $this->noteModel = new NoteModel();
    }

    public function index() {
        $user = auth_user();
        
        // Get summary data
        $summary = $this->leadModel->getSummary();
        
        // Get recent leads (last 10)
        $recentLeads = $this->leadModel->all(10, 0);
        
        // Get recent activity
        $recentActivity = $this->noteModel->getRecentActivity(5);
        
        // Get team performance if user is admin or manager
        $teamPerformance = [];
        if (in_array($user['role'], ['admin', 'manager'])) {
            $teamPerformance = $this->getTeamPerformance();
        }
        
        $this->view('dashboard/home', [
            'summary' => $summary,
            'recentLeads' => $recentLeads,
            'recentActivity' => $recentActivity,
            'teamPerformance' => $teamPerformance
        ]);
    }
    
    private function getTeamPerformance() {
        $stmt = $this->pdo->prepare('
            SELECT 
                u.username as sdr_name,
                COUNT(l.id) as total,
                SUM(CASE WHEN l.duplicate_status = "unique" THEN 1 ELSE 0 END) as unique_count,
                SUM(CASE WHEN l.duplicate_status = "duplicate" THEN 1 ELSE 0 END) as duplicate_count,
                SUM(CASE WHEN l.duplicate_status = "incomplete" THEN 1 ELSE 0 END) as incomplete_count
            FROM users u
            LEFT JOIN leads l ON u.id = l.sdr_id
            WHERE u.role = "sdr"
            GROUP BY u.id, u.username
            ORDER BY total DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>