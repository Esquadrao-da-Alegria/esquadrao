let activeItem = null;

function showContent(city, element) {
    // Se um item ativo já existir e for diferente do item clicado, reseta a seta e oculta o conteúdo
    if (activeItem && activeItem !== element) {
        activeItem.querySelector('.arrow').textContent = '▲';
    }

    // Exibe o conteúdo da cidade selecionada
    let contents = document.querySelectorAll('.content');
    contents.forEach(function(content) {
        content.style.display = 'none';
    });
    document.getElementById(city).style.display = 'block';

    // Define o novo item ativo, altera a seta para ▼ e mantém a seta assim
    element.querySelector('.arrow').innerHTML = '▼';

    // Atualiza o item ativo
    activeItem = element;
}