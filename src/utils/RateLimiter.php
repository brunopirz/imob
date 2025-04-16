// src/utils/RateLimiter.php
class RateLimiter {
    private $storagePath;
    private $maxAttempts;
    private $timeWindow;
    
    public function __construct($storagePath, $maxAttempts = 5, $timeWindow = 3600) {
        $this->storagePath = rtrim($storagePath, '/') . '/';
        $this->maxAttempts = $maxAttempts;
        $this->timeWindow = $timeWindow;
        
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }
    
    public function check($identifier) {
        $ipHash = hash('sha256', $identifier);
        $filePath = $this->storagePath . $ipHash . '.json';
        
        $data = [];
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
        }
        
        $now = time();
        $windowStart = $now - $this->timeWindow;
        
        // Filtrar tentativas dentro da janela de tempo
        $attempts = array_filter($data['attempts'] ?? [], function($timestamp) use ($windowStart) {
            return $timestamp >= $windowStart;
        });
        
        // Contar tentativas recentes
        $count = count($attempts);
        
        if ($count >= $this->maxAttempts) {
            return false; // Limite excedido
        }
        
        // Registrar nova tentativa
        $attempts[] = $now;
        $data['attempts'] = $attempts;
        file_put_contents($filePath, json_encode($data));
        
        return $this->maxAttempts - $count;
    }
    
    public function cleanup() {
        $files = glob($this->storagePath . '*.json');
        $now = time();
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            $windowStart = $now - $this->timeWindow;
            
            $attempts = array_filter($data['attempts'] ?? [], function($timestamp) use ($windowStart) {
                return $timestamp >= $windowStart;
            });
            
            if (empty($attempts)) {
                unlink($file);
            } else {
                $data['attempts'] = $attempts;
                file_put_contents($file, json_encode($data));
            }
        }
    }
}

// Uso no formulário de contato
$rateLimiter = new RateLimiter(__DIR__ . '/../storage/rate_limit');
$remainingAttempts = $rateLimiter->check($_SERVER['REMOTE_ADDR'] . '_contact');

if ($remainingAttempts === false) {
    http_response_code(429);
    die("Muitas tentativas de envio. Por favor, tente novamente mais tarde.");
}
