CREATE DATABASE IF NOT EXISTS sistema_orcamento;
USE sistema_orcamento;

CREATE TABLE IF NOT EXISTS orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente VARCHAR(255) NOT NULL,
    servico VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao TEXT,
    status ENUM('pendente', 'aprovado', 'recusado') DEFAULT 'pendente',
    data_criacao DATETIME NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);