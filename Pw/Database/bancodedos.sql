CREATE DATABASE IF NOT EXISTS sistema_orcamento;
USE sistema_orcamento;

CREATE TABLE orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pendente'
);

INSERT INTO orcamentos (cliente, descricao, valor, status) VALUES
('João Silva', 'Site institucional', 2500.00, 'aprovado'),
('Maria Santos', 'Consultoria marketing', 1800.00, 'pendente');