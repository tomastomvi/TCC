<?php
class OrcamentoDAO {
    private $pdo;
    
    public function __construct() {
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function listar() {
        $stmt = $this->pdo->prepare("SELECT * FROM orcamentos ORDER BY data_criacao DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM orcamentos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function salvar(Orcamento $orcamento) {
        if ($orcamento->getId()) {
            // Atualizar
            $stmt = $this->pdo->prepare("UPDATE orcamentos SET cliente = ?, servico = ?, valor = ?, descricao = ?, status = ? WHERE id = ?");
            return $stmt->execute([
                $orcamento->getCliente(),
                $orcamento->getServico(),
                $orcamento->getValor(),
                $orcamento->getDescricao(),
                $orcamento->getStatus(),
                $orcamento->getId()
            ]);
        } else {
            // Inserir
            $stmt = $this->pdo->prepare("INSERT INTO orcamentos (cliente, servico, valor, descricao, status, data_criacao) VALUES (?, ?, ?, ?, ?, NOW())");
            return $stmt->execute([
                $orcamento->getCliente(),
                $orcamento->getServico(),
                $orcamento->getValor(),
                $orcamento->getDescricao(),
                $orcamento->getStatus()
            ]);
        }
    }
    
    public function excluir($id) {
        $stmt = $this->pdo->prepare("DELETE FROM orcamentos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>