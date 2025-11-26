<?php
class RelatorioController {
    private $dao;
    
    public function __construct() {
        $this->dao = new OrcamentoDAO();
    }
    
    public function geral() {
        $orcamentos = $this->dao->listar();
        
        // Simulação de dados para relatório
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
        
        echo "<h1>Relatório Geral</h1>";
        echo "<p>Total de Orçamentos: " . count($orcamentos) . "</p>";
        echo "<p>Valor Total: R$ " . number_format($total, 2, ',', '.') . "</p>";
        echo "<p>Aprovados: $aprovados</p>";
        echo "<p>Pendentes: $pendentes</p>";
        echo "<p>Recusados: $recusados</p>";
    }
}
?>