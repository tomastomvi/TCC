CREATE DATABASE IF NOT EXISTS sistema_orcamento;
USE sistema_orcamento;

CREATE TABLE IF NOT EXISTS orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente VARCHAR(255) NOT NULL,
    servico VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao TEXT,
    status ENUM('pendente', 'aprovado', 'recusado') DEFAULT 'pendente',
    data_criacao DATETIME NOT NULL
);


INSERT INTO orcamentos (cliente, servico, valor, descricao, status, data_criacao) VALUES
('João Silva', 'Desenvolvimento Web', 2500.00, 'Desenvolvimento de site institucional', 'aprovado', NOW()),
('Maria Santos', 'Consultoria TI', 1800.00, 'Consultoria em infraestrutura de TI', 'pendente', NOW()),
('Empresa XYZ', 'Manutenção Sistema', 3200.00, 'Manutenção preventiva do sistema ERP', 'recusado', NOW());