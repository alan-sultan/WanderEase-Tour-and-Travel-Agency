// controllers/UserController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/sanitize.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        session_start();
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function handleLogin() {
        session_start();

        $username = sanitizeString($_POST['username'] ?? '');
        $password = sanitizePassword($_POST['password'] ?? '');

        if (!$username || !$password) {
            $_SESSION['error'] = 'Please enter both username and password.';
            header('Location: ?url=user/login');
            exit;
        }

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Optional: Remember Me
            if (isset($_POST['remember_me'])) {
                setcookie('user_id', $user['id'], time() + 30 * 86400, '/', '', true, true);
            }

            $_SESSION['success'] = 'Login successful!';
            header('Location: ?url=home/index');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
            header('Location: ?url=user/login');
            exit;
        }
    }
}
