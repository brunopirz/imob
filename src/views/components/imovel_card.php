// src/views/components/imovel_card.php
function renderImovelCard($imovel) {
    $firstImage = isset($imovel['imagens'][0]) ? $imovel['imagens'][0]['caminho'] : '/assets/images/default-house.jpg';
    $precoFormatado = 'R$ ' . number_format($imovel['preco'], 2, ',', '.');
    
    return <<<HTML
    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
        <div class="relative h-48 overflow-hidden">
            <img src="{$firstImage}" alt="{$imovel['titulo']}" 
                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            {$imovel['destaque'] ? '<span class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-bold">Destaque</span>' : ''}
        </div>
        <div class="p-4">
            <h3 class="text-lg font-bold mb-2">{$imovel['titulo']}</h3>
            <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt mr-1"></i> {$imovel['cidade']}/{$imovel['estado']}</p>
            <div class="flex justify-between items-center mb-3">
                <span class="text-sm text-gray-500">
                    <i class="fas fa-bed mr-1"></i> {$imovel['quartos']} quartos
                </span>
                <span class="text-sm text-gray-500">
                    <i class="fas fa-bath mr-1"></i> {$imovel['banheiros']} banheiros
                </span>
                <span class="text-sm text-gray-500">
                    <i class="fas fa-ruler-combined mr-1"></i> {$imovel['area']} m²
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[#D2B48C] font-bold">{$precoFormatado}</span>
                <a href="/imovel/{$imovel['id']}" class="bg-[#D2B48C] hover:bg-[#C4A575] text-white px-3 py-1 rounded text-sm transition-colors duration-300">
                    Ver detalhes
                </a>
            </div>
        </div>
    </div>
HTML;
}
