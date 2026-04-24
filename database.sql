-- ============================================
-- CRUD DE PRODUTOS - Script SQL
-- Execute este arquivo no MySQL/MariaDB
-- ============================================

-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS crud_produtos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE crud_produtos;

-- Criar tabela de categorias
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Criar tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(200) NOT NULL,
  descricao TEXT,
  preco DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  estoque INT NOT NULL DEFAULT 0,
  categoria_id INT,
  imagem_url VARCHAR(500),
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Inserir categorias de exemplo
INSERT INTO categorias (nome) VALUES
  ('Eletrônicos'),
  ('Roupas'),
  ('Alimentos'),
  ('Livros'),
  ('Brinquedos');

-- Inserir produtos de exemplo
INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id) VALUES
  ('Smartphone Galaxy', 'Celular Android 128GB, tela 6.5"', 1299.90, 15, 1),
  ('Notebook Pro', 'Notebook i5, 8GB RAM, SSD 256GB', 2899.99, 8, 1),
  ('Fone Bluetooth', 'Fone sem fio com cancelamento de ruído', 349.90, 30, 1),
  ('Camiseta Algodão', 'Camiseta 100% algodão, tamanho M', 49.90, 100, 2),
  ('Calça Jeans', 'Calça jeans masculina slim fit', 129.90, 50, 2),
  ('Arroz 5kg', 'Arroz branco tipo 1, saco 5kg', 24.90, 200, 3),
  ('Café Gourmet', 'Café especial torrado e moído 500g', 39.90, 75, 3),
  ('Clean Code', 'Livro de boas práticas de programação', 79.90, 20, 4),
  ('Lego Creator', 'Conjunto Lego 500 peças', 249.90, 12, 5);

-- Criar usuário para a aplicação (ajuste a senha!)
CREATE USER IF NOT EXISTS 'crud_user'@'localhost' IDENTIFIED BY 'SenhaForte@2024';
GRANT ALL PRIVILEGES ON crud_produtos.* TO 'crud_user'@'localhost';
FLUSH PRIVILEGES;

SELECT 'Banco de dados criado com sucesso!' AS status;
