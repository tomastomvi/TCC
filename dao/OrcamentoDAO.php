<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Orcamento.php';

class OrcamentoDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function listarPorCliente($cliente_id) {
        $sql = "SELECT o.*, u.nome as prestador_nome, u.foto as prestador_foto,
                       s.titulo as servico_titulo
                FROM orcamentos o
                LEFT JOIN usuarios u ON o.prestador_id = u.id
                LEFT JOIN servicos s ON o.servico_id = s.id
                WHERE o.cliente_id = ?
                ORDER BY o.data_solicitacao DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cliente_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $orcamentos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $orcamentos[] = $row;
        }
        return $orcamentos;
    }
    
    public function listarPorPrestador($prestador_id) {
        $sql = "SELECT o.*, u.nome as cliente_nome, u.telefone as cliente_telefone,
                       s.titulo as servico_titulo
                FROM orcamentos o
                LEFT JOIN usuarios u ON o.cliente_id = u.id
                LEFT JOIN servicos s ON o.servico_id = s.id
                WHERE o.prestador_id = ?
                ORDER BY o.data_solicitacao DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $prestador_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $orcamentos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $orcamentos[] = $row;
        }
        return $orcamentos;
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT o.*, 
                       c.nome as cliente_nome, c.telefone as cliente_telefone,
                       p.nome as prestador_nome, p.telefone as prestador_telefone,
                       s.titulo as servico_titulo
                FROM orcamentos o
                LEFT JOIN usuarios c ON o.cliente_id = c.id
                LEFT JOIN usuarios p ON o.prestador_id = p.id
                LEFT JOIN servicos s ON o.servico_id = s.id
                WHERE o.id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function salvar(Orcamento $orcamento) {
        if ($orcamento->getId()) {
            $sql = "UPDATE orcamentos SET valor_proposto=?, prazo_dias=?, status=?, observacoes_prestador=?, data_resposta=NOW() WHERE id=?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "dissi", 
                $orcamento->getValorProposto(),
                $orcamento->getPrazoDias(),
                $orcamento->getStatus(),
                $orcamento->getObservacoesPrestador(),
                $orcamento->getId()
            );
        } else {
            $sql = "INSERT INTO orcamentos (cliente_id, prestador_id, servico_id, descricao, fotos, status, data_solicitacao) 
                    VALUES (?, ?, ?, ?, ?, 'pendente', NOW())";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiiss", 
                $orcamento->getClienteId(),
                $orcamento->getPrestadorId(),
                $orcamento->getServicoId(),
                $orcamento->getDescricao(),
                $orcamento->getFotos()
            );
        }
        return mysqli_stmt_execute($stmt);
    }


    // Adicione estes métodos à classe OrcamentoDAO

public function contarPorCliente($cliente_id, $limite = null) {
    $sql = "SELECT COUNT(*) as total FROM orcamentos WHERE cliente_id = ?";
    $stmt = mysqli_prepare($this->conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cliente_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

public function listarPendentesPorPrestador($prestador_id, $limite = 10) {
    $sql = "SELECT o.*, u.nome as cliente_nome, u.telefone as cliente_telefone,
                   s.titulo as servico_titulo
            FROM orcamentos o
            LEFT JOIN usuarios u ON o.cliente_id = u.id
            LEFT JOIN servicos s ON o.servico_id = s.id
            WHERE o.prestador_id = ? AND o.status = 'pendente'
            ORDER BY o.data_solicitacao DESC
            LIMIT ?";
    $stmt = mysqli_prepare($this->conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $prestador_id, $limite);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $orcamentos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orcamentos[] = $row;
    }
    return $orcamentos;
}

public function contarPendentesPorPrestador($prestador_id) {
    $sql = "SELECT COUNT(*) as total FROM orcamentos WHERE prestador_id = ? AND status = 'pendente'";
    $stmt = mysqli_prepare($this->conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $prestador_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

public function contarTodos() {
    $sql = "SELECT COUNT(*) as total FROM orcamentos";
    $result = mysqli_query($this->conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
}


?>