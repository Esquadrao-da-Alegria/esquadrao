// Pegue o elemento do hamburger menu
var hamburger = document.querySelector('.hamburger-menu');
var nav = document.querySelector('.main-nav');

// Adicione um evento de clique ao hamburger para alternar a visibilidade do menu
hamburger.addEventListener('click', function() {
    nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
});