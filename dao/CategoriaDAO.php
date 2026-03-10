<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Categoria.php';

class CategoriaDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function listar() {
        $sql = "SELECT * FROM categorias ORDER BY nome";
        $result = mysqli_query($this->conn, $sql);
        $categorias = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categorias[] = $row;
        }
        return $categorias;
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM categorias WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function salvar(Categoria $categoria) {
        if ($categoria->getId()) {
            $sql = "UPDATE categorias SET nome=?, icone=?, descricao=? WHERE id=?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", 
                $categoria->getNome(),
                $categoria->getIcone(),
                $categoria->getDescricao(),
                $categoria->getId()
            );
        } else {
            $sql = "INSERT INTO categorias (nome, icone, descricao) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", 
                $categoria->getNome(),
                $categoria->getIcone(),
                $categoria->getDescricao()
            );
        }
        return mysqli_stmt_execute($stmt);
    }
    
    public function excluir($id) {
        $sql = "DELETE FROM categorias WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }
}
?>