<?php
// db.php - Conexão e Inicialização do Banco de Dados SQLite

function getDb(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbFile = __DIR__ . '/database.sqlite';
    $isNew = !file_exists($dbFile);

    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Chaves estrangeiras e journal mode para desempenho e integridade
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA journal_mode = WAL;');

        if ($isNew || filesize($dbFile) === 0) {
            initDatabase($pdo);
        }

        return $pdo;
    } catch (PDOException $e) {
        die('Erro na conexão com o banco de dados: ' . htmlspecialchars($e->getMessage()));
    }
}

function initDatabase(PDO $pdo): void {
    // 1. Tabela de Usuários
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            senha TEXT NOT NULL,
            cargo TEXT NOT NULL DEFAULT 'Administrador',
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 2. Tabela de Configurações
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            chave TEXT PRIMARY KEY,
            valor TEXT NOT NULL
        );
    ");

    // 3. Tabela de Produtos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS produtos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            sku TEXT NOT NULL,
            qtd INTEGER NOT NULL DEFAULT 0,
            min INTEGER NOT NULL DEFAULT 0,
            preco REAL NOT NULL DEFAULT 0.0,
            vendas_dia REAL NOT NULL DEFAULT 0.0,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 4. Tabela de Pedidos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cliente TEXT NOT NULL,
            total REAL NOT NULL DEFAULT 0.0,
            status TEXT NOT NULL DEFAULT 'aberto',
            data TEXT NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 5. Tabela de Itens de Pedidos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedido_itens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            produto_id INTEGER,
            nome TEXT NOT NULL,
            qtd INTEGER NOT NULL,
            preco REAL NOT NULL,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
        );
    ");

    // 6. Tabela de Notas Fiscais
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            num TEXT NOT NULL UNIQUE,
            pedido_id INTEGER,
            data TEXT NOT NULL,
            cliente TEXT NOT NULL,
            total REAL NOT NULL,
            icms REAL NOT NULL,
            chave TEXT NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Seed de Usuário Padrão
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insertUser = $pdo->prepare("
            INSERT INTO users (nome, email, senha, cargo)
            VALUES (:nome, :email, :senha, :cargo)
        ");

        // Usuário admin padrão
        $insertUser->execute([
            ':nome'  => 'Matheus',
            ':email' => 'admin@wedtech.com',
            ':senha' => password_hash('admin123', PASSWORD_DEFAULT),
            ':cargo' => 'Administrador',
        ]);

        // Usuário demonstrativo adicional
        $insertUser->execute([
            ':nome'  => 'Matheus Silva',
            ':email' => 'voce@suaempresa.com.br',
            ':senha' => password_hash('admin123', PASSWORD_DEFAULT),
            ':cargo' => 'Gerente de Estoque',
        ]);
    }

    // Seed de Produtos Padrão
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insertProd = $pdo->prepare("
            INSERT INTO produtos (nome, sku, qtd, min, preco, vendas_dia)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insertProd->execute(['Camiseta Básica', 'CAM-001', 42, 20, 59.90, 3.2]);
        $insertProd->execute(['Calça Jeans', 'CAL-002', 9, 15, 139.90, 1.8]);
        $insertProd->execute(['Tênis Urbano', 'TEN-003', 6, 10, 249.90, 1.1]);
        $insertProd->execute(['Boné', 'BON-004', 80, 15, 39.90, 0.6]);
    }
}
