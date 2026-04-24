<?php
// ============================================
// api/produtos.php - API REST para produtos
// ============================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$acao   = isset($_GET['acao']) ? $_GET['acao'] : null;

// Listar categorias
if ($acao === 'categorias') {
    $pdo  = getDB();
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome");
    echo json_encode($stmt->fetchAll());
    exit();
}

switch ($method) {

    // ---- LISTAR / BUSCAR ----
    case 'GET':
        $pdo = getDB();
        if ($id) {
            $stmt = $pdo->prepare(
                "SELECT p.*, c.nome AS categoria_nome
                 FROM produtos p
                 LEFT JOIN categorias c ON p.categoria_id = c.id
                 WHERE p.id = ?"
            );
            $stmt->execute([$id]);
            $produto = $stmt->fetch();
            if (!$produto) {
                http_response_code(404);
                echo json_encode(['erro' => 'Produto não encontrado.']);
            } else {
                echo json_encode($produto);
            }
        } else {
            $busca     = isset($_GET['busca']) ? '%' . $_GET['busca'] . '%' : '%';
            $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

            $sql = "SELECT p.*, c.nome AS categoria_nome
                    FROM produtos p
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    WHERE (p.nome LIKE ? OR p.descricao LIKE ?)";
            $params = [$busca, $busca];

            if ($categoria > 0) {
                $sql .= " AND p.categoria_id = ?";
                $params[] = $categoria;
            }
            $sql .= " ORDER BY p.criado_em DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        }
        break;

    // ---- CRIAR ----
    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados || empty($dados['nome']) || !isset($dados['preco'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e preço são obrigatórios.']);
            break;
        }
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id, imagem_url, ativo)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            htmlspecialchars($dados['nome']),
            htmlspecialchars($dados['descricao'] ?? ''),
            (float)$dados['preco'],
            (int)($dados['estoque'] ?? 0),
            !empty($dados['categoria_id']) ? (int)$dados['categoria_id'] : null,
            htmlspecialchars($dados['imagem_url'] ?? ''),
            isset($dados['ativo']) ? (int)$dados['ativo'] : 1,
        ]);
        http_response_code(201);
        echo json_encode(['mensagem' => 'Produto criado com sucesso!', 'id' => $pdo->lastInsertId()]);
        break;

    // ---- ATUALIZAR ----
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID do produto é obrigatório.']);
            break;
        }
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados || empty($dados['nome']) || !isset($dados['preco'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e preço são obrigatórios.']);
            break;
        }
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "UPDATE produtos
             SET nome=?, descricao=?, preco=?, estoque=?, categoria_id=?, imagem_url=?, ativo=?
             WHERE id=?"
        );
        $stmt->execute([
            htmlspecialchars($dados['nome']),
            htmlspecialchars($dados['descricao'] ?? ''),
            (float)$dados['preco'],
            (int)($dados['estoque'] ?? 0),
            !empty($dados['categoria_id']) ? (int)$dados['categoria_id'] : null,
            htmlspecialchars($dados['imagem_url'] ?? ''),
            isset($dados['ativo']) ? (int)$dados['ativo'] : 1,
            $id,
        ]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['erro' => 'Produto não encontrado.']);
        } else {
            echo json_encode(['mensagem' => 'Produto atualizado com sucesso!']);
        }
        break;

    // ---- DELETAR ----
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID do produto é obrigatório.']);
            break;
        }
        $pdo  = getDB();
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['erro' => 'Produto não encontrado.']);
        } else {
            echo json_encode(['mensagem' => 'Produto excluído com sucesso!']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido.']);
}
