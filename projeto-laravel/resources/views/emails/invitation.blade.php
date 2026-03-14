<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Convite para se registrar - Esquadrão da Alegria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Esquadrão da Alegria</h1>
        <p>Seu convite chegou!</p>
    </div>
    
    <div class="content">
        <h2>Olá!</h2>
        
        <p>Você foi convidado para se registrar na plataforma do <strong>Esquadrão da Alegria</strong>!</p>
        
        <p>Para completar seu registro e começar a fazer a diferença na vida de muitas pessoas, clique no botão abaixo:</p>
        
        <div style="text-align: center;">
            <a href="{{ route('register.with-token', ['token' => $invitation->invitation_token]) }}" class="button">
            Registrar-se Agora
            </a>
        </div>
        
        <p><strong>Importante:</strong></p>
        <ul>
            <li>Este link é único e pessoal</li>
            <li>Você deve usar o email: <strong>{{ $invitation->email }}</strong></li>
            <li>O link expira em 7 dias</li>
        </ul>
        
        <p>Se você não solicitou este convite, pode ignorar este email.</p>
        
        <p>Obrigado por fazer parte da nossa missão de levar alegria e esperança!</p>
        
        <p>Um bj e um qj,<br>
        <strong>Equipe Esquadrão da Alegria</strong></p>
    </div>
    
    <div class="footer">
        <p>Este é um email automático, por favor não responda.</p>
        <p>Esquadrão da Alegria</p>
    </div>
</body>
</html>
