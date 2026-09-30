<?php
// login.php - Tela de Login do WedTech
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: painel.php');
    exit;
}

$erro = '';
$sucesso = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'desconectado') {
    $sucesso = 'Você foi desconectado com sucesso.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $res = attempt_login($email, $senha);
    if ($res['success']) {
        header('Location: painel.php');
        exit;
    } else {
        $erro = $res['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · WedTech</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            position: relative;
        }

        .login-card {
            width: 100%;
            max-width: 25.5rem;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            padding: 2.25rem 2rem;
            box-shadow: 0 1.5rem 3rem rgba(0, 0, 0, 0.45);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-logo {
            width: 4rem;
            height: 4rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--acc), var(--acc2));
            padding: 0.25rem;
            display: grid;
            place-items: center;
            margin-bottom: 1rem;
            box-shadow: 0 0.5rem 1.5rem color-mix(in srgb, var(--acc) 30%, transparent);
        }

        .login-logo img {
            width: 100%;
            height: 100%;
            border-radius: 0.8rem;
            object-fit: contain;
            padding: 0.3rem;
            background: #fff;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--txt);
            margin-bottom: 0.35rem;
        }

        .login-subtitle {
            font-size: 0.88rem;
            color: var(--mut);
        }

        .form-group {
            margin-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--txt);
            margin: 0;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper input {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 0.85rem;
            font-size: 0.95rem;
            border-radius: 0.65rem;
            border: 1px solid var(--line);
            background: var(--card);
            color: var(--txt);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-wrapper input:focus {
            border-color: var(--acc);
            outline: none;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--acc) 25%, transparent);
        }

        .toggle-password {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: var(--mut);
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            border-radius: 0.35rem;
        }

        .toggle-password:hover {
            color: var(--txt);
        }

        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.65rem;
            background: linear-gradient(90deg, var(--acc2), var(--acc));
            color: #ffffff;
            border: none;
            cursor: pointer;
            box-shadow: 0 0.5rem 1.25rem color-mix(in srgb, var(--acc) 35%, transparent);
            transition: transform 0.15s ease, filter 0.2s ease;
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert {
            padding: 0.75rem 0.9rem;
            border-radius: 0.6rem;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-error {
            background: color-mix(in srgb, var(--bad) 15%, var(--panel));
            border: 1px solid var(--bad);
            color: #ff8b8b;
        }

        .alert-success {
            background: color-mix(in srgb, var(--ok) 15%, var(--panel));
            border: 1px solid var(--ok);
            color: #6ee7b7;
        }

        .btn-exemplo {
            width: 100%;
            margin-top: 0.75rem;
            padding: 0.6rem;
            font: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--txt);
            background: none;
            border: 1px dashed var(--line);
            border-radius: 99px;
            cursor: pointer;
        }

        .btn-exemplo:hover {
            border-color: var(--acc);
        }

        .login-rodape {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--mut);
        }

        .login-rodape a {
            color: var(--acc);
            font-weight: 600;
            text-decoration: none;
        }

        .login-rodape a:hover {
            text-decoration: underline;
        }

        .theme-float {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
        }
    </style>
</head>
<body data-theme="dark">
    <button class="ic theme-float" id="themeToggle" aria-label="Alternar tema" onclick="toggleTheme()">☀</button>

    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="logo-wedtech.png" alt="WedTech">
                </div>
                <h1 class="login-title">Acessar WedTech</h1>
                <p class="login-subtitle">Gestão Inteligente de Estoque e Vendas</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-error" role="alert">
                    <span>⚠️</span>
                    <span><?= htmlspecialchars($erro) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-success" role="alert">
                    <span>✓</span>
                    <span><?= htmlspecialchars($sucesso) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="on">
                <div class="form-group">
                    <label for="email">E-mail corporativo</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" required placeholder="seu@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="input-wrapper">
                        <input type="password" id="senha" name="senha" required placeholder="••••••••">
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Mostrar ou ocultar senha">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Entrar no Sistema</button>
                <button type="button" class="btn-exemplo" onclick="preencherExemplo()">✨ Preencher exemplo</button>
            </form>

            <p class="login-rodape">
                Não tem conta? <a href="index.html#criar-conta">Criar conta grátis</a>
                <span aria-hidden="true">·</span>
                <a href="index.html">← Voltar ao site</a>
            </p>
        </div>
    </div>

    <script>
        // Tema persistente
        const savedTheme = localStorage.getItem('wedtech_theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        const toggleBtn = document.getElementById('themeToggle');
        toggleBtn.textContent = savedTheme === 'dark' ? '☀' : '☾';

        function toggleTheme() {
            const current = document.body.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', next);
            localStorage.setItem('wedtech_theme', next);
            toggleBtn.textContent = next === 'dark' ? '☀' : '☾';
        }

        // Alternar visualização de senha
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('senha');
        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            togglePassword.textContent = isPassword ? '🔒' : '👁';
        });

        // Preenche a conta de demonstração
        function preencherExemplo() {
            document.getElementById('email').value = 'admin@wedtech.com';
            document.getElementById('senha').value = 'admin123';
            document.querySelector('.btn-submit').focus();
        }
    </script>
</body>
</html>
