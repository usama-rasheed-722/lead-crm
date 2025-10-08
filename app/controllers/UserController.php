<?php
// app/controllers/UserController.php
class UserController extends Controller {
    protected $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        require_role(['admin']); // Only admins can manage users
    }
    
    // List all users
    public function index() {
        $users = $this->userModel->all();
        $this->view('user/index', ['users' => $users]);
    }
    
    // Show create user form
    public function create() {
        $this->view('user/form', ['user' => null, 'action' => 'create']);
    }
    
    // Store new user
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=user_add');
        }
        
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role' => $_POST['role'] ?? 'sdr',
            'password' => password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT)
        ];
        
        // Validate required fields
        if (empty($data['username']) || empty($data['email']) || empty($_POST['password'])) {
            $this->view('user/form', [
                'user' => $data,
                'action' => 'create',
                'error' => 'Please fill in all required fields'
            ]);
            return;
        }
        
        try {
            $this->userModel->create($data);
            $this->redirect('index.php?action=users&success=' . urlencode('User created successfully'));
        } catch (Exception $e) {
            $this->view('user/form', [
                'user' => $data,
                'action' => 'create',
                'error' => 'Failed to create user: ' . $e->getMessage()
            ]);
        }
    }
    
    // Show edit user form
    public function edit($id) {
        if (!$id) {
            $this->redirect('index.php?action=users');
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->redirect('index.php?action=users');
        }
        
        $this->view('user/form', ['user' => $user, 'action' => 'edit']);
    }
    
    // Update user
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redirect('index.php?action=users');
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->redirect('index.php?action=users');
        }
        
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role' => $_POST['role'] ?? 'sdr'
        ];
        
        // Validate required fields
        if (empty($data['username']) || empty($data['email'])) {
            $this->view('user/form', [
                'user' => array_merge($user, $data),
                'action' => 'edit',
                'error' => 'Please fill in all required fields'
            ]);
            return;
        }
        
        try {
            $this->userModel->update($id, $data);
            $this->redirect('index.php?action=users&success=' . urlencode('User updated successfully'));
        } catch (Exception $e) {
            $this->view('user/form', [
                'user' => array_merge($user, $data),
                'action' => 'edit',
                'error' => 'Failed to update user: ' . $e->getMessage()
            ]);
        }
    }
    
    // Delete user
    public function delete($id) {
        if (!$id) {
            $this->redirect('index.php?action=users');
        }
        
        try {
            $this->userModel->delete($id);
            $this->redirect('index.php?action=users&success=' . urlencode('User deleted successfully'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=users&error=' . urlencode('Failed to delete user'));
        }
    }
}
?>