// config/database.php
class Database {
    private static $instances = [];
    
    private function __construct() {}
    
    public static function getInstance($type = 'clientes') {
        if (!isset(self::$instances[$type])) {
            $config = [
                'host' => $_ENV['DB_' . strtoupper($type) . '_HOST'],
                'dbname' => $_ENV['DB_' . strtoupper($type) . '_NAME'],
                'user' => $_ENV['DB_' . strtoupper($type) . '_USER'],
                'pass' => $_ENV['DB_' . strtoupper($type) . '_PASS'],
                'charset' => 'utf8mb4'
            ];
            
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                self::$instances[$type] = new PDO($dsn, $config['user'], $config['pass'], $options);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection error");
            }
        }
        
        return self::$instances[$type];
    }
}
