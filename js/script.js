function showContent(city) {
    // Oculta todos os conteúdos
    var contents = document.querySelectorAll('.content');
    contents.forEach(function(content) {
        content.style.display = 'none';
    });

    // Exibe o conteúdo da cidade selecionada
    document.getElementById(city).style.display = 'block';
}
