<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Agendamento.php';

class AgendamentoDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function listarPorCliente($cliente_id) {
        $sql = "SELECT a.*, s.titulo as servico_titulo, s.preco, 
                       u.nome as prestador_nome, u.foto as prestador_foto
                FROM agendamentos a
                JOIN servicos s ON a.servico_id = s.id
                JOIN usuarios u ON a.prestador_id = u.id
                WHERE a.cliente_id = ?
                ORDER BY a.data_hora DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cliente_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $agendamentos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $agendamentos[] = $row;
        }
        return $agendamentos;
    }
    
    public function listarPorPrestador($prestador_id) {
        $sql = "SELECT a.*, s.titulo as servico_titulo, s.preco,
                       u.nome as cliente_nome, u.telefone as cliente_telefone
                FROM agendamentos a
                JOIN servicos s ON a.servico_id = s.id
                JOIN usuarios u ON a.cliente_id = u.id
                WHERE a.prestador_id = ?
                ORDER BY a.data_hora DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $prestador_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $agendamentos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $agendamentos[] = $row;
        }
        return $agendamentos;
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT a.*, s.titulo as servico_titulo, s.preco, s.duracao,
                       c.nome as cliente_nome, c.telefone as cliente_telefone,
                       p.nome as prestador_nome, p.telefone as prestador_telefone
                FROM agendamentos a
                JOIN servicos s ON a.servico_id = s.id
                JOIN usuarios c ON a.cliente_id = c.id
                JOIN usuarios p ON a.prestador_id = p.id
                WHERE a.id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function verificarDisponibilidade($prestador_id, $data_hora, $duracao) {
        $data_fim = date('Y-m-d H:i:s', strtotime($data_hora . " + $duracao minutes"));
        
        $sql = "SELECT COUNT(*) as total FROM agendamentos 
                WHERE prestador_id = ? 
                AND status IN ('pendente', 'confirmado')
                AND (
                    (data_hora < ? AND DATE_ADD(data_hora, INTERVAL (SELECT duracao FROM servicos WHERE id = servico_id) MINUTE) > ?)
                    OR
                    (data_hora >= ? AND data_hora < ?)
                )";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "issss", $prestador_id, $data_fim, $data_hora, $data_hora, $data_fim);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'] == 0;
    }
    
    public function salvar(Agendamento $agendamento) {
        if ($agendamento->getId()) {
            $sql = "UPDATE agendamentos SET status=?, observacoes=? WHERE id=?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", 
                $agendamento->getStatus(),
                $agendamento->getObservacoes(),
                $agendamento->getId()
            );
        } else {
            $sql = "INSERT INTO agendamentos (cliente_id, servico_id, prestador_id, data_hora, status, observacoes, orcamento_id, data_criacao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiisssi", 
                $agendamento->getClienteId(),
                $agendamento->getServicoId(),
                $agendamento->getPrestadorId(),
                $agendamento->getDataHora(),
                $agendamento->getStatus(),
                $agendamento->getObservacoes(),
                $agendamento->getOrcamentoId()
            );
        }
        return mysqli_stmt_execute($stmt);
    }
}
?>