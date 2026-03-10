<?php
class Usuario {
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $telefone;
    private $tipo; // 'cliente', 'prestador', 'admin'
    private $endereco;
    private $latitude;
    private $longitude;
    private $foto;
    private $descricao; // para prestadores
    private $avaliacao_media;
    private $data_cadastro;
    
    public function __construct($nome = '', $email = '', $senha = '', $tipo = 'cliente') {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->tipo = $tipo;
        $this->data_cadastro = date('Y-m-d H:i:s');
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getEmail() { return $this->email; }
    public function getSenha() { return $this->senha; }
    public function getTelefone() { return $this->telefone; }
    public function getTipo() { return $this->tipo; }
    public function getEndereco() { return $this->endereco; }
    public function getLatitude() { return $this->latitude; }
    public function getLongitude() { return $this->longitude; }
    public function getFoto() { return $this->foto; }
    public function getDescricao() { return $this->descricao; }
    public function getAvaliacaoMedia() { return $this->avaliacao_media; }
    public function getDataCadastro() { return $this->data_cadastro; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setEmail($email) { $this->email = $email; }
    public function setSenha($senha) { $this->senha = password_hash($senha, PASSWORD_DEFAULT); }
    public function setTelefone($telefone) { $this->telefone = $telefone; }
    public function setTipo($tipo) { $this->tipo = $tipo; }
    public function setEndereco($endereco) { $this->endereco = $endereco; }
    public function setLatitude($latitude) { $this->latitude = $latitude; }
    public function setLongitude($longitude) { $this->longitude = $longitude; }
    public function setFoto($foto) { $this->foto = $foto; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setAvaliacaoMedia($media) { $this->avaliacao_media = $media; }
}
?>