// src/views/admin/dashboard.php
AuthService::checkAuth();

$db = Database::getInstance('admin');
$imoveisDb = Database::getInstance('clientes');

// Contar imóveis por status
$imoveisStatus = $imoveisDb->query("
    SELECT status, COUNT(*) as total 
    FROM imoveis 
    GROUP BY status
")->fetchAll();

// Últimos leads
$leads = $imoveisDb->query("
    SELECT l.*, i.titulo as imovel_titulo 
    FROM leads l
    LEFT JOIN imoveis i ON l.imovel_id = i.id
    ORDER BY l.data_envio DESC
    LIMIT 5
")->fetchAll();

$content = <<<HTML
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Painel Administrativo</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Imóveis Cadastrados</h3>
            <div class="flex justify-between items-center">
                <span class="text-3xl font-bold">{$totalImoveis}</span>
                <i class="fas fa-home text-4xl text-[#D2B48C]"></i>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Leads Recebidos</h3>
            <div class="flex justify-between items-center">
                <span class="text-3xl font-bold">{$totalLeads}</span>
                <i class="fas fa-envelope text-4xl text-[#D2B48C]"></i>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Status dos Imóveis</h3>
            <div class="space-y-2">
HTML;

foreach ($imoveisStatus as $status) {
    $percent = round(($status['total'] / $totalImoveis) * 100);
    $content .= <<<HTML
    <div>
        <div class="flex justify-between text-sm mb-1">
            <span>{$status['status']}</span>
            <span>{$status['total']} ({$percent}%)</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-[#D2B48C] h-2 rounded-full" style="width: {$percent}%"></div>
        </div>
    </div>
HTML;
}

$content .= <<<HTML
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Últimos Leads</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imóvel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
HTML;

foreach ($leads as $lead) {
    $imovelRef = $lead['imovel_titulo'] ?: 'Geral';
    $data = date('d/m/Y H:i', strtotime($lead['data_envio']));
    
    $content .= <<<HTML
    <tr>
        <td class="px-6 py-4 whitespace-nowrap">{$lead['nome']}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div>{$lead['email']}</div>
            <div class="text-sm text-gray-500">{$lead['telefone']}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">{$imovelRef}</td>
        <td class="px-6 py-4 whitespace-nowrap">{$data}</td>
    </tr>
HTML;
}

$content .= <<<HTML
                </tbody>
            </table>
        </div>
    </div>
</div>
HTML;

include '../layouts/admin.php';
