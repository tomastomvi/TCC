<?php
class Orcamento {
    private $id;
    private $cliente_id;
    private $prestador_id;
    private $servico_id;
    private $descricao;
    private $valor_proposto;
    private $prazo_dias;
    private $status; // pendente, respondido, aprovado, recusado, cancelado
    private $data_solicitacao;
    private $data_resposta;
    private $observacoes_prestador;
    private $fotos;
    
    public function __construct($cliente_id = null, $prestador_id = null, $descricao = '') {
        $this->cliente_id = $cliente_id;
        $this->prestador_id = $prestador_id;
        $this->descricao = $descricao;
        $this->status = 'pendente';
        $this->data_solicitacao = date('Y-m-d H:i:s');
    }
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getClienteId() { return $this->cliente_id; }
    public function getPrestadorId() { return $this->prestador_id; }
    public function getServicoId() { return $this->servico_id; }
    public function getDescricao() { return $this->descricao; }
    public function getValorProposto() { return $this->valor_proposto; }
    public function getPrazoDias() { return $this->prazo_dias; }
    public function getStatus() { return $this->status; }
    public function getDataSolicitacao() { return $this->data_solicitacao; }
    public function getDataResposta() { return $this->data_resposta; }
    public function getObservacoesPrestador() { return $this->observacoes_prestador; }
    public function getFotos() { return $this->fotos; }
    
    public function setId($id) { $this->id = $id; }
    public function setClienteId($id) { $this->cliente_id = $id; }
    public function setPrestadorId($id) { $this->prestador_id = $id; }
    public function setServicoId($id) { $this->servico_id = $id; }
    public function setDescricao($desc) { $this->descricao = $desc; }
    public function setValorProposto($valor) { $this->valor_proposto = $valor; }
    public function setPrazoDias($prazo) { $this->prazo_dias = $prazo; }
    public function setStatus($status) { $this->status = $status; }
    public function setDataResposta($data) { $this->data_resposta = $data; }
    public function setObservacoesPrestador($obs) { $this->observacoes_prestador = $obs; }
    public function setFotos($fotos) { $this->fotos = $fotos; }
}
?>