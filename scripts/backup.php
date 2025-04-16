// scripts/backup.php
<?php
require __DIR__ . '/../config/database.php';

$backupDir = __DIR__ . '/../backups/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$date = date('Y-m-d_H-i-s');
$clientesFile = $backupDir . "balvedi_clientes_{$date}.sql";
$adminFile = $backupDir . "balvedi_admin_{$date}.sql";

// Configurações do banco de dados
$dbClientes = [
    'host' => $_ENV['DB_CLIENTES_HOST'],
    'user' => $_ENV['DB_CLIENTES_USER'],
    'pass' => $_ENV['DB_CLIENTES_PASS'],
    'name' => $_ENV['DB_CLIENTES_NAME']
];

$dbAdmin = [
    'host' => $_ENV['DB_ADMIN_HOST'],
    'user' => $_ENV['DB_ADMIN_USER'],
    'pass' => $_ENV['DB_ADMIN_PASS'],
    'name' => $_ENV['DB_ADMIN_NAME']
];

function backupDatabase($config, $outputFile) {
    $command = "mysqldump --host={$config['host']} --user={$config['user']} --password={$config['pass']} {$config['name']} > {$outputFile}";
    system($command, $returnVar);
    
    if ($returnVar !== 0) {
        error_log("Erro ao fazer backup do banco de dados {$config['name']}");
        return false;
    }
    
    // Compactar o arquivo
    $zip = new ZipArchive();
    $zipFile = $outputFile . '.zip';
    
    if ($zip->open($zipFile, ZipArchive::CREATE) {
        $zip->addFile($outputFile, basename($outputFile));
        $zip->close();
        unlink($outputFile);
        return true;
    }
    
    return false;
}

// Executar backups
$clientesSuccess = backupDatabase($dbClientes, $clientesFile);
$adminSuccess = backupDatabase($dbAdmin, $adminFile);

// Manter apenas os últimos 7 backups
$files = glob($backupDir . '*.zip');
if (count($files) > 7) {
    usort($files, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    for ($i = 0; $i < count($files) - 7; $i++) {
        unlink($files[$i]);
    }
}

// Registrar resultado
$logMessage = sprintf(
    "[%s] Backup realizado - Clientes: %s, Admin: %s\n",
    date('Y-m-d H:i:s'),
    $clientesSuccess ? 'SUCESSO' : 'FALHA',
    $adminSuccess ? 'SUCESSO' : 'FALHA'
);

file_put_contents($backupDir . 'backup.log', $logMessage, FILE_APPEND);
