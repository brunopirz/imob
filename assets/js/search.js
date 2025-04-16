// assets/js/search.js
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    
    if (searchInput && searchResults) {
        let debounceTimer;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            
            const query = this.value.trim();
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.classList.add('hidden');
                return;
            }
            
            debounceTimer = setTimeout(() => {
                fetch('/api/search?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            let html = '';
                            data.forEach(imovel => {
                                html += `
                                <a href="/imovel/${imovel.id}" class="block p-2 hover:bg-[#F5F5DC] border-b border-gray-100">
                                    <div class="flex items-center">
                                        <img src="${imovel.thumbnail}" alt="${imovel.titulo}" class="w-12 h-12 object-cover rounded">
                                        <div class="ml-3">
                                            <h4 class="font-medium">${imovel.titulo}</h4>
                                            <p class="text-sm text-gray-600">${imovel.cidade}/${imovel.estado} - R$ ${imovel.preco.toLocaleString('pt-BR')}</p>
                                        </div>
                                    </div>
                                </a>
                                `;
                            });
                            searchResults.innerHTML = html;
                            searchResults.classList.remove('hidden');
                        } else {
                            searchResults.innerHTML = '<p class="p-2 text-gray-500">Nenhum imóvel encontrado</p>';
                            searchResults.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Erro na busca:', error);
                    });
            }, 300);
        });
        
        // Fechar resultados ao clicar fora
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }
});
