<?php
class OrcamentoController {
    private $dao;
    
    public function __construct() {
        $this->dao = new OrcamentoDAO();
    }
    
    public function listar() {
        $orcamentos = $this->dao->listar();
        
        // Aqui você carregaria uma view para exibir os orçamentos
        echo "<h1>Lista de Orçamentos</h1>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Cliente</th><th>Serviço</th><th>Valor</th><th>Status</th><th>Ações</th></tr>";
        foreach ($orcamentos as $o) {
            echo "<tr>";
            echo "<td>{$o['id']}</td>";
            echo "<td>{$o['cliente']}</td>";
            echo "<td>{$o['servico']}</td>";
            echo "<td>R$ {$o['valor']}</td>";
            echo "<td>{$o['status']}</td>";
            echo "<td>
                    <a href='index.php?url=orcamento/editar/{$o['id']}'>Editar</a> | 
                    <a href='index.php?url=orcamento/excluir/{$o['id']}'>Excluir</a>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<a href='index.php?url=orcamento/criar'>Novo Orçamento</a>";
    }
    
    public function criar() {
        if ($_POST) {
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
            // Exibir formulário de criação
            echo "<h1>Novo Orçamento</h1>";
            echo "<form method='POST'>";
            echo "Cliente: <input type='text' name='cliente' required><br>";
            echo "Serviço: <input type='text' name='servico' required><br>";
            echo "Valor: <input type='number' step='0.01' name='valor' required><br>";
            echo "Descrição: <textarea name='descricao'></textarea><br>";
            echo "<input type='submit' value='Salvar'>";
            echo "</form>";
        }
    }
    
    public function editar($id) {
        if ($_POST) {
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
            
            // Exibir formulário de edição
            echo "<h1>Editar Orçamento</h1>";
            echo "<form method='POST'>";
            echo "Cliente: <input type='text' name='cliente' value='{$orcamento['cliente']}' required><br>";
            echo "Serviço: <input type='text' name='servico' value='{$orcamento['servico']}' required><br>";
            echo "Valor: <input type='number' step='0.01' name='valor' value='{$orcamento['valor']}' required><br>";
            echo "Descrição: <textarea name='descricao'>{$orcamento['descricao']}</textarea><br>";
            echo "Status: 
                  <select name='status'>
                    <option value='pendente' " . ($orcamento['status'] == 'pendente' ? 'selected' : '') . ">Pendente</option>
                    <option value='aprovado' " . ($orcamento['status'] == 'aprovado' ? 'selected' : '') . ">Aprovado</option>
                    <option value='recusado' " . ($orcamento['status'] == 'recusado' ? 'selected' : '') . ">Recusado</option>
                  </select><br>";
            echo "<input type='submit' value='Atualizar'>";
            echo "</form>";
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