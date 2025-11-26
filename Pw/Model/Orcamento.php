<?php
class Orcamento {
    private $id;
    private $cliente;
    private $servico;
    private $valor;
    private $data;
    private $status;
    private $descricao;
    
    public function __construct($cliente = '', $servico = '', $valor = 0, $descricao = '') {
        $this->cliente = $cliente;
        $this->servico = $servico;
        $this->valor = $valor;
        $this->descricao = $descricao;
        $this->data = date('Y-m-d H:i:s');
        $this->status = 'pendente';
    }
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getCliente() { return $this->cliente; }
    public function getServico() { return $this->servico; }
    public function getValor() { return $this->valor; }
    public function getData() { return $this->data; }
    public function getStatus() { return $this->status; }
    public function getDescricao() { return $this->descricao; }
    
    public function setId($id) { $this->id = $id; }
    public function setCliente($cliente) { $this->cliente = $cliente; }
    public function setServico($servico) { $this->servico = $servico; }
    public function setValor($valor) { $this->valor = $valor; }
    public function setData($data) { $this->data = $data; }
    public function setStatus($status) { $this->status = $status; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
}
?>