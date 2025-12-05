<?php
// controller/OrcamentoController.php
require_once '../model/Orcamento.php';
require_once '../model/OrcamentoDAO.php';

class OrcamentoController {
    private $dao;
    
    public function __construct() {
        $this->dao = new OrcamentoDAO();
    }
    
    public function listar() {
        $orcamentos = $this->dao->listar();
        require_once '../view/orcamento/listar.php';
    }
    
    public function criar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // SEU CÓDIGO DE SALVAR
            $orcamento = new Orcamento(
                $_POST['cliente'],
                $_POST['servico'],
                $_POST['valor'],
                $_POST['descricao']
            );
            
            if ($this->dao->salvar($orcamento)) {
                header("Location: index.php?url=orcamento/listar");
            } else {
                echo "Erro ao salvar orçamento";
            }
        } else {
            // Exibir formulário
            require_once '../view/orcamento/criar.php';
        }
    }
    
    public function editar($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // SEU CÓDIGO DE ATUALIZAR
            $orcamento = new Orcamento(
                $_POST['cliente'],
                $_POST['servico'],
                $_POST['valor'],
                $_POST['descricao']
            );
            $orcamento->setId($id);
            $orcamento->setStatus($_POST['status']);
            
            if ($this->dao->salvar($orcamento)) {
                header("Location: index.php?url=orcamento/listar");
            } else {
                echo "Erro ao atualizar orçamento";
            }
        } else {
            $orcamento = $this->dao->buscarPorId($id);
            require_once '../view/orcamento/editar.php';
        }
    }
    
    public function excluir($id) {
        if ($this->dao->excluir($id)) {
            header("Location: index.php?url=orcamento/listar");
        } else {
            echo "Erro ao excluir orçamento";
        }
    }
}
?>