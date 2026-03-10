<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Servico.php';

class ServicoDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function listar($filtros = []) {
        $sql = "SELECT s.*, u.nome as prestador_nome, u.foto as prestador_foto, u.avaliacao_media,
                       c.nome as categoria_nome, c.icone as categoria_icone
                FROM servicos s
                JOIN usuarios u ON s.prestador_id = u.id
                JOIN categorias c ON s.categoria_id = c.id
                WHERE s.ativo = 1";
        
        $params = [];
        $types = "";
        
        if (isset($filtros['categoria_id'])) {
            $sql .= " AND s.categoria_id = ?";
            $params[] = $filtros['categoria_id'];
            $types .= "i";
        }
        
        if (isset($filtros['prestador_id'])) {
            $sql .= " AND s.prestador_id = ?";
            $params[] = $filtros['prestador_id'];
            $types .= "i";
        }
        
        if (isset($filtros['busca'])) {
            $sql .= " AND (s.titulo LIKE ? OR s.descricao LIKE ?)";
            $busca = "%{$filtros['busca']}%";
            $params[] = $busca;
            $params[] = $busca;
            $types .= "ss";
        }
        
        $sql .= " ORDER BY s.data_cadastro DESC";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $servicos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $servicos[] = $row;
        }
        return $servicos;
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT s.*, u.nome as prestador_nome, u.foto as prestador_foto, u.avaliacao_media,
                       u.telefone as prestador_telefone, u.email as prestador_email,
                       c.nome as categoria_nome
                FROM servicos s
                JOIN usuarios u ON s.prestador_id = u.id
                JOIN categorias c ON s.categoria_id = c.id
                WHERE s.id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function salvar(Servico $servico) {
        if ($servico->getId()) {
            $sql = "UPDATE servicos SET categoria_id=?, titulo=?, descricao=?, preco=?, duracao=?, fotos=?, ativo=? WHERE id=?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "issdissi", 
                $servico->getCategoriaId(),
                $servico->getTitulo(),
                $servico->getDescricao(),
                $servico->getPreco(),
                $servico->getDuracao(),
                $servico->getFotos(),
                $servico->getAtivo(),
                $servico->getId()
            );
        } else {
            $sql = "INSERT INTO servicos (prestador_id, categoria_id, titulo, descricao, preco, duracao, fotos, ativo, data_cadastro) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "iissdiss", 
                $servico->getPrestadorId(),
                $servico->getCategoriaId(),
                $servico->getTitulo(),
                $servico->getDescricao(),
                $servico->getPreco(),
                $servico->getDuracao(),
                $servico->getFotos(),
                $servico->getAtivo()
            );
        }
        return mysqli_stmt_execute($stmt);
    }
    
    public function excluir($id) {
        $sql = "DELETE FROM servicos WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }
}
?>