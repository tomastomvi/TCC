<?php
class Avaliacao {
    private $id;
    private $agendamento_id;
    private $cliente_id;
    private $prestador_id;
    private $nota;
    private $comentario;
    private $data;
    
    public function __construct($agendamento_id = null, $cliente_id = null, $prestador_id = null, $nota = 5) {
        $this->agendamento_id = $agendamento_id;
        $this->cliente_id = $cliente_id;
        $this->prestador_id = $prestador_id;
        $this->nota = $nota;
        $this->data = date('Y-m-d H:i:s');
    }
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getAgendamentoId() { return $this->agendamento_id; }
    public function getClienteId() { return $this->cliente_id; }
    public function getPrestadorId() { return $this->prestador_id; }
    public function getNota() { return $this->nota; }
    public function getComentario() { return $this->comentario; }
    public function getData() { return $this->data; }
    
    public function setId($id) { $this->id = $id; }
    public function setAgendamentoId($id) { $this->agendamento_id = $id; }
    public function setClienteId($id) { $this->cliente_id = $id; }
    public function setPrestadorId($id) { $this->prestador_id = $id; }
    public function setNota($nota) { $this->nota = $nota; }
    public function setComentario($comentario) { $this->comentario = $comentario; }
}
?>