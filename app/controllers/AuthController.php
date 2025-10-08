<?php
// app/controllers/AuthController.php
class AuthController {
    protected $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // Show login form & handle login request
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $user = $this->userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ];
                header('Location: index.php?action=dashboard');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
        include __DIR__ . '/../views/auth/login.php';
    }

    // Handle user logout
    public function logout() {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }

    // Optional: register new users (for Admin only)
    public function register() {
        require_role(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'full_name' => trim($_POST['full_name'] ?? ''),
                'role' => $_POST['role'] ?? 'sdr',
                'password' => password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT)
            ];

            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('INSERT INTO users (username,email,password,full_name,role) VALUES (?,?,?,?,?)');
            $stmt->execute([$data['username'],$data['email'],$data['password'],$data['full_name'],$data['role']]);

            header('Location: index.php?action=users');
            exit;
        }

        include __DIR__ . '/../views/auth/register.php';
    }
}
?>
