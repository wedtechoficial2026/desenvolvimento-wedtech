<?php
// auth.php - Gerenciamento de Sessão e Autenticação

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function is_logged_in(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function get_logged_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function attempt_login(string $email, string $senha): array {
    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
    if (empty($email) || empty($senha)) {
        return ['success' => false, 'message' => 'Preencha o e-mail e a senha.'];
    }

    try {
        $db = getDb();
        $stmt = $db->prepare("SELECT id, nome, email, senha, cargo FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            // Regenera ID da sessão para prevenir session fixation
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id'    => $user['id'],
                'nome'  => $user['nome'],
                'email' => $user['email'],
                'cargo' => $user['cargo'],
            ];

            return ['success' => true, 'user' => $_SESSION['user']];
        }

        return ['success' => false, 'message' => 'E-mail ou senha incorretos.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro interno ao autenticar: ' . $e->getMessage()];
    }
}

function register_user(string $nome, string $email, string $senha): array {
    $nome  = trim(preg_replace('/\s+/', ' ', $nome));
    $email = trim($email);

    $letras = preg_match_all('/./u', $nome);
    if ($letras < 2 || $letras > 80) {
        return ['success' => false, 'message' => 'Informe seu nome (2 a 80 letras).'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Informe um e-mail válido.'];
    }
    if (strlen($senha) < 6) {
        return ['success' => false, 'message' => 'A senha precisa ter pelo menos 6 caracteres.'];
    }

    try {
        $db = getDb();
        $stmt = $db->prepare("SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn()) {
            return ['success' => false, 'message' => 'Já existe uma conta com este e-mail. Use a aba Entrar.', 'code' => 409];
        }

        $stmt = $db->prepare("INSERT INTO users (nome, email, senha, cargo) VALUES (:nome, :email, :senha, 'Lojista')");
        $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':senha' => password_hash($senha, PASSWORD_DEFAULT),
        ]);

        // Conta criada: já entra no painel
        return attempt_login($email, $senha);
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro interno ao criar a conta: ' . $e->getMessage()];
    }
}

function logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
