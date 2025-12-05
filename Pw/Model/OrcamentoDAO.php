<?php
// OrcamentoDAO.php (AJUSTADO PARA mysqli)
require_once 'config.php';

class OrcamentoDAO {
    private $conn;
    
    public function __construct() {
        global $conexão; // Usa sua variável do config.php
        $this->conn = $conexão;
    }
    
    public function listar() {
        $sql = "SELECT * FROM orcamentos ORDER BY data_criacao DESC";
        $result = mysqli_query($this->conn, $sql);
        
        $orcamentos = [];
        while($row = mysqli_fetch_assoc($result)) {
            $orcamentos[] = $row;
        }
        return $orcamentos;
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM orcamentos WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }
    
    public function salvar(Orcamento $orcamento) {
        if ($orcamento->getId()) {
            // UPDATE
            $sql = "UPDATE orcamentos SET cliente = ?, servico = ?, valor = ?, descricao = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssdssi", 
                $orcamento->getCliente(),
                $orcamento->getServico(),
                $orcamento->getValor(),
                $orcamento->getDescricao(),
                $orcamento->getStatus(),
                $orcamento->getId()
            );
        } else {
            // INSERT
            $sql = "INSERT INTO orcamentos (cliente, servico, valor, descricao, status, data_criacao) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssdss", 
                $orcamento->getCliente(),
                $orcamento->getServico(),
                $orcamento->getValor(),
                $orcamento->getDescricao(),
                $orcamento->getStatus()
            );
        }
        
        return mysqli_stmt_execute($stmt);
    }
    
    public function excluir($id) {
        $sql = "DELETE FROM orcamentos WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        return mysqli_stmt_execute($stmt);
    }
}
?>