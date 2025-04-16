// src/services/AuthService.php
class AuthService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance('admin');
    }
    
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM administradores WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['senha_hash'])) {
            if (password_needs_rehash($user['senha_hash'], PASSWORD_DEFAULT)) {
                $this->updatePasswordHash($user['id'], $password);
            }
            
            // Registrar token CSRF na sessão
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nivel'] = $user['nivel_acesso'];
            
            $this->registrarLogin($user['id']);
            return true;
        }
        
        return false;
    }
    
    private function updatePasswordHash($userId, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE administradores SET senha_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
    }
    
    private function registrarLogin($userId) {
        $stmt = $this->db->prepare("UPDATE administradores SET ultimo_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        
        $logStmt = $this->db->prepare("INSERT INTO logs_acesso (admin_id, acao, ip, user_agent) VALUES (?, ?, ?, ?)");
        $logStmt->execute([
            $userId,
            'login',
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    public static function checkAuth() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login.php');
            exit;
        }
    }
    
    public static function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
