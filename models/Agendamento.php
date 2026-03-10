<?php
class Agendamento {
    private $id;
    private $cliente_id;
    private $servico_id;
    private $prestador_id;
    private $data_hora;
    private $status; // pendente, confirmado, cancelado, concluido
    private $observacoes;
    private $orcamento_id; // opcional, se veio de um orçamento
    private $data_criacao;
    
    public function __construct($cliente_id = null, $servico_id = null, $prestador_id = null, $data_hora = null) {
        $this->cliente_id = $cliente_id;
        $this->servico_id = $servico_id;
        $this->prestador_id = $prestador_id;
        $this->data_hora = $data_hora;
        $this->status = 'pendente';
        $this->data_criacao = date('Y-m-d H:i:s');
    }
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getClienteId() { return $this->cliente_id; }
    public function getServicoId() { return $this->servico_id; }
    public function getPrestadorId() { return $this->prestador_id; }
    public function getDataHora() { return $this->data_hora; }
    public function getStatus() { return $this->status; }
    public function getObservacoes() { return $this->observacoes; }
    public function getOrcamentoId() { return $this->orcamento_id; }
    public function getDataCriacao() { return $this->data_criacao; }
    
    public function setId($id) { $this->id = $id; }
    public function setClienteId($id) { $this->cliente_id = $id; }
    public function setServicoId($id) { $this->servico_id = $id; }
    public function setPrestadorId($id) { $this->prestador_id = $id; }
    public function setDataHora($data_hora) { $this->data_hora = $data_hora; }
    public function setStatus($status) { $this->status = $status; }
    public function setObservacoes($obs) { $this->observacoes = $obs; }
    public function setOrcamentoId($id) { $this->orcamento_id = $id; }
}
?>