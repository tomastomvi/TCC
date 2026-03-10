<?php
class Servico {
    private $id;
    private $prestador_id;
    private $categoria_id;
    private $titulo;
    private $descricao;
    private $preco;
    private $duracao; // em minutos
    private $fotos;
    private $ativo;
    private $data_cadastro;
    
    public function __construct($prestador_id = null, $categoria_id = null, $titulo = '', $preco = 0) {
        $this->prestador_id = $prestador_id;
        $this->categoria_id = $categoria_id;
        $this->titulo = $titulo;
        $this->preco = $preco;
        $this->ativo = 1;
        $this->data_cadastro = date('Y-m-d H:i:s');
    }
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getPrestadorId() { return $this->prestador_id; }
    public function getCategoriaId() { return $this->categoria_id; }
    public function getTitulo() { return $this->titulo; }
    public function getDescricao() { return $this->descricao; }
    public function getPreco() { return $this->preco; }
    public function getDuracao() { return $this->duracao; }
    public function getFotos() { return $this->fotos; }
    public function getAtivo() { return $this->ativo; }
    public function getDataCadastro() { return $this->data_cadastro; }
    
    public function setId($id) { $this->id = $id; }
    public function setPrestadorId($id) { $this->prestador_id = $id; }
    public function setCategoriaId($id) { $this->categoria_id = $id; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setPreco($preco) { $this->preco = $preco; }
    public function setDuracao($duracao) { $this->duracao = $duracao; }
    public function setFotos($fotos) { $this->fotos = $fotos; }
    public function setAtivo($ativo) { $this->ativo = $ativo; }
}
?>