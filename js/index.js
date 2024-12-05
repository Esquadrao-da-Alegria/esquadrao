function enviarFormulario() {
    const nome = document.getElementById('nome').value;
    const email = document.getElementById('email').value;
    const mensagem = document.getElementById('mensagem').value;
    const accessKey = document.getElementById('accessKey').value;

    
    const dados = {
        name: nome,
        email: email,
        message: mensagem,
        accessKey: accessKey,
    };

    fetch('https://api.staticforms.xyz/submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados),
    })
    .then((response) => {
        if (response.ok) {
            alert('Mensagem enviada com sucesso!');
            document.getElementById('meuFormulario').reset(); 
            setTimeout(() => location.reload(), 500);
        } else {
            throw new Error('Erro ao enviar a mensagem.');
        }
    })
    .catch((error) => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao enviar a mensagem. Tente novamente mais tarde.');
    });
}

//Para fazer scroll mais suáve
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});