<?php
// controller/RelatorioController.php
require_once '../model/OrcamentoDAO.php';

class RelatorioController {
    private $dao;
    
    public function __construct() {
        $this->dao = new OrcamentoDAO();
    }
    
    public function geral() {
        $orcamentos = $this->dao->listar();
        
        // SEU CÓDIGO DE CÁLCULO
        $total = 0;
        $aprovados = 0;
        $pendentes = 0;
        $recusados = 0;
        
        foreach ($orcamentos as $o) {
            $total += $o['valor'];
            if ($o['status'] == 'aprovado') $aprovados++;
            if ($o['status'] == 'pendente') $pendentes++;
            if ($o['status'] == 'recusado') $recusados++;
        }
        
        // Agora precisa de uma view
        require_once '../view/relatorio/geral.php';
    }
}
?>