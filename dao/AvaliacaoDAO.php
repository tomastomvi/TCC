<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Avaliacao.php';

class AvaliacaoDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function listarPorPrestador($prestador_id) {
        $sql = "SELECT a.*, u.nome as cliente_nome, u.foto as cliente_foto,
                       s.titulo as servico_titulo
                FROM avaliacoes a
                JOIN usuarios u ON a.cliente_id = u.id
                JOIN agendamentos ag ON a.agendamento_id = ag.id
                JOIN servicos s ON ag.servico_id = s.id
                WHERE a.prestador_id = ?
                ORDER BY a.data DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $prestador_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $avaliacoes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $avaliacoes[] = $row;
        }
        return $avaliacoes;
    }
    
    public function buscarPorAgendamento($agendamento_id) {
        $sql = "SELECT * FROM avaliacoes WHERE agendamento_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $agendamento_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function salvar(Avaliacao $avaliacao) {
        $sql = "INSERT INTO avaliacoes (agendamento_id, cliente_id, prestador_id, nota, comentario, data) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiids", 
            $avaliacao->getAgendamentoId(),
            $avaliacao->getClienteId(),
            $avaliacao->getPrestadorId(),
            $avaliacao->getNota(),
            $avaliacao->getComentario()
        );
        return mysqli_stmt_execute($stmt);
    }
}
?>