-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS servicehub;
USE servicehub;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo ENUM('cliente', 'prestador', 'admin') DEFAULT 'cliente',
    endereco TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    foto VARCHAR(255),
    descricao TEXT,
    avaliacao_media DECIMAL(3,2) DEFAULT 0,
    data_cadastro DATETIME DEFAULT NOW()
);

-- Tabela de categorias
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    icone VARCHAR(50),
    descricao TEXT
);

-- Tabela de serviços
CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prestador_id INT NOT NULL,
    categoria_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    duracao INT DEFAULT 60, -- minutos
    fotos TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro DATETIME DEFAULT NOW(),
    FOREIGN KEY (prestador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servico_id INT NOT NULL,
    prestador_id INT NOT NULL,
    data_hora DATETIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado', 'concluido') DEFAULT 'pendente',
    observacoes TEXT,
    orcamento_id INT NULL,
    data_criacao DATETIME DEFAULT NOW(),
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (prestador_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de orçamentos
CREATE TABLE orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    prestador_id INT NOT NULL,
    servico_id INT NULL,
    descricao TEXT NOT NULL,
    valor_proposto DECIMAL(10,2),
    prazo_dias INT,
    status ENUM('pendente', 'respondido', 'aprovado', 'recusado', 'cancelado') DEFAULT 'pendente',
    data_solicitacao DATETIME DEFAULT NOW(),
    data_resposta DATETIME,
    observacoes_prestador TEXT,
    fotos TEXT,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (prestador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL
);

-- Tabela de avaliações
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT UNIQUE NOT NULL,
    cliente_id INT NOT NULL,
    prestador_id INT NOT NULL,
    nota INT CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    data DATETIME DEFAULT NOW(),
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (prestador_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de disponibilidade (horários de trabalho dos prestadores)
CREATE TABLE disponibilidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prestador_id INT NOT NULL,
    dia_semana TINYINT, -- 0=domingo, 1=segunda, ..., 6=sábado
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    FOREIGN KEY (prestador_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Índices para performance
CREATE INDEX idx_agendamentos_data ON agendamentos(data_hora);
CREATE INDEX idx_agendamentos_cliente ON agendamentos(cliente_id);
CREATE INDEX idx_agendamentos_prestador ON agendamentos(prestador_id);
CREATE INDEX idx_servicos_categoria ON servicos(categoria_id);
CREATE INDEX idx_servicos_prestador ON servicos(prestador_id);
CREATE INDEX idx_orcamentos_cliente ON orcamentos(cliente_id);
CREATE INDEX idx_orcamentos_prestador ON orcamentos(prestador_id);
CREATE INDEX idx_avaliacoes_prestador ON avaliacoes(prestador_id);

-- Dados iniciais (categorias de exemplo)
INSERT INTO categorias (nome, icone, descricao) VALUES
('Reformas', 'hammer', 'Serviços de reforma e construção'),
('Limpeza', 'broom', 'Serviços de limpeza residencial e comercial'),
('Eletricista', 'bolt', 'Instalações e reparos elétricos'),
('Encanador', 'wrench', 'Serviços de encanamento e hidráulica'),
('Jardinagem', 'leaf', 'Cuidados com jardins e plantas'),
('Pintura', 'paint-brush', 'Pintura de paredes e superfícies'),
('Informática', 'computer', 'Suporte técnico e manutenção de computadores'),
('Aulas', 'book', 'Aulas particulares e reforço escolar');

-- Usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@servicehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');