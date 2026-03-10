<?php
class Categoria {
    private $id;
    private $nome;
    private $icone;
    private $descricao;
    
    public function __construct($nome = '', $icone = '') {
        $this->nome = $nome;
        $this->icone = $icone;
    }
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getIcone() { return $this->icone; }
    public function getDescricao() { return $this->descricao; }
    
    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setIcone($icone) { $this->icone = $icone; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
}
?>