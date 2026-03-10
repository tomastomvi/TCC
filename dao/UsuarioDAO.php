<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/geolocation.php';

class UsuarioDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT id, nome, email, telefone, tipo, endereco, latitude, longitude, foto, descricao, avaliacao_media, data_cadastro 
                FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Lista prestadores com filtros avançados
     */
    public function listarPrestadores($categoria_id = null, $latitude = null, $longitude = null, $raio = 10, $busca = null, $limite = 50, $offset = 0) {
        // Query base
        $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.foto, u.descricao, u.avaliacao_media, 
                       u.endereco, u.latitude, u.longitude, u.data_cadastro";
        
        // Se tem localização, calcular distância
        if ($latitude && $longitude) {
            $sql .= ", (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                    sin(radians(latitude)))) AS distancia";
        }
        
        $sql .= " FROM usuarios u WHERE u.tipo = 'prestador'";
        
        $params = [];
        $types = "";
        
        // Adicionar parâmetros de localização no início se existirem
        if ($latitude && $longitude) {
            $params[] = $latitude;
            $params[] = $longitude;
            $params[] = $latitude;
            $types .= "ddd";
        }
        
        // Filtrar por categoria (verifica se o prestador tem serviços na categoria)
        if ($categoria_id) {
            $sql .= " AND u.id IN (SELECT DISTINCT prestador_id FROM servicos WHERE categoria_id = ? AND ativo = 1)";
            $params[] = $categoria_id;
            $types .= "i";
        }
        
        // Busca por nome ou descrição
        if ($busca) {
            $sql .= " AND (u.nome LIKE ? OR u.descricao LIKE ?)";
            $busca_param = "%{$busca}%";
            $params[] = $busca_param;
            $params[] = $busca_param;
            $types .= "ss";
        }
        
        // Se tem localização, filtrar por raio
        if ($latitude && $longitude) {
            $sql .= " HAVING distancia < ?";
            $params[] = $raio;
            $types .= "d";
            
            $sql .= " ORDER BY distancia";
        } else {
            $sql .= " ORDER BY u.avaliacao_media DESC, u.data_cadastro DESC";
        }
        
        // Paginação
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $prestadores = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Buscar serviços do prestador (resumo)
            $row['servicos'] = $this->listarServicosResumo($row['id']);
            $prestadores[] = $row;
        }
        return $prestadores;
    }
    
    /**
     * Conta o total de prestadores para paginação
     */
    public function contarPrestadores($categoria_id = null, $latitude = null, $longitude = null, $raio = 10, $busca = null) {
        $sql = "SELECT COUNT(DISTINCT u.id) as total 
                FROM usuarios u 
                WHERE u.tipo = 'prestador'";
        
        $params = [];
        $types = "";
        
        if ($categoria_id) {
            $sql .= " AND u.id IN (SELECT DISTINCT prestador_id FROM servicos WHERE categoria_id = ? AND ativo = 1)";
            $params[] = $categoria_id;
            $types .= "i";
        }
        
        if ($busca) {
            $sql .= " AND (u.nome LIKE ? OR u.descricao LIKE ?)";
            $busca_param = "%{$busca}%";
            $params[] = $busca_param;
            $params[] = $busca_param;
            $types .= "ss";
        }
        
        // Se tem localização, precisamos fazer uma subconsulta mais complexa
        if ($latitude && $longitude) {
            // Primeiro buscar todos os prestadores que atendem aos critérios
            $sql_temp = "SELECT u.id, 
                        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                         cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                         sin(radians(latitude)))) AS distancia
                        FROM usuarios u 
                        WHERE u.tipo = 'prestador'";
            
            $temp_params = [$latitude, $longitude, $latitude];
            $temp_types = "ddd";
            
            if ($categoria_id) {
                $sql_temp .= " AND u.id IN (SELECT DISTINCT prestador_id FROM servicos WHERE categoria_id = ? AND ativo = 1)";
                $temp_params[] = $categoria_id;
                $temp_types .= "i";
            }
            
            if ($busca) {
                $sql_temp .= " AND (u.nome LIKE ? OR u.descricao LIKE ?)";
                $busca_param = "%{$busca}%";
                $temp_params[] = $busca_param;
                $temp_params[] = $busca_param;
                $temp_types .= "ss";
            }
            
            $sql_temp .= " HAVING distancia < ?";
            $temp_params[] = $raio;
            $temp_types .= "d";
            
            $sql = "SELECT COUNT(*) as total FROM ($sql_temp) as temp";
            
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, $temp_types, ...$temp_params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row['total'];
        }
        
        // Sem localização, consulta simples
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    /**
     * Lista resumo dos serviços de um prestador
     */
    private function listarServicosResumo($prestador_id) {
        $sql = "SELECT id, titulo, preco, duracao, categoria_id 
                FROM servicos 
                WHERE prestador_id = ? AND ativo = 1 
                LIMIT 5";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $prestador_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $servicos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $servicos[] = $row;
        }
        return $servicos;
    }
    
    /**
     * Busca detalhada de um prestador com todos os serviços
     */
    public function buscarPrestadorCompleto($id) {
        $prestador = $this->buscarPorId($id);
        if (!$prestador || $prestador['tipo'] !== 'prestador') {
            return null;
        }
        
        // Buscar todos os serviços do prestador
        $sql_servicos = "SELECT s.*, c.nome as categoria_nome 
                        FROM servicos s
                        JOIN categorias c ON s.categoria_id = c.id
                        WHERE s.prestador_id = ? AND s.ativo = 1
                        ORDER BY s.data_cadastro DESC";
        $stmt = mysqli_prepare($this->conn, $sql_servicos);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $servicos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $servicos[] = $row;
        }
        
        // Buscar avaliações recentes
        $sql_avaliacoes = "SELECT a.*, u.nome as cliente_nome, u.foto as cliente_foto
                          FROM avaliacoes a
                          JOIN usuarios u ON a.cliente_id = u.id
                          WHERE a.prestador_id = ?
                          ORDER BY a.data DESC
                          LIMIT 10";
        $stmt = mysqli_prepare($this->conn, $sql_avaliacoes);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $avaliacoes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $avaliacoes[] = $row;
        }
        
        $prestador['servicos'] = $servicos;
        $prestador['avaliacoes'] = $avaliacoes;
        $prestador['total_avaliacoes'] = count($avaliacoes); // idealmente COUNT(*)
        
        return $prestador;
    }
    
    public function salvar(Usuario $usuario) {
        if ($usuario->getId()) {
            // Update
            $sql = "UPDATE usuarios SET nome=?, telefone=?, endereco=?, latitude=?, longitude=?, foto=?, descricao=? WHERE id=?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssddssi", 
                $usuario->getNome(),
                $usuario->getTelefone(),
                $usuario->getEndereco(),
                $usuario->getLatitude(),
                $usuario->getLongitude(),
                $usuario->getFoto(),
                $usuario->getDescricao(),
                $usuario->getId()
            );
        } else {
            // Insert
            $sql = "INSERT INTO usuarios (nome, email, senha, telefone, tipo, endereco, latitude, longitude, foto, descricao, data_cadastro) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssddss", 
                $usuario->getNome(),
                $usuario->getEmail(),
                $usuario->getSenha(),
                $usuario->getTelefone(),
                $usuario->getTipo(),
                $usuario->getEndereco(),
                $usuario->getLatitude(),
                $usuario->getLongitude(),
                $usuario->getFoto(),
                $usuario->getDescricao()
            );
        }
        
        $result = mysqli_stmt_execute($stmt);
        if ($result && !$usuario->getId()) {
            $usuario->setId(mysqli_insert_id($this->conn));
        }
        return $result;
    }
    
    public function atualizarAvaliacaoMedia($prestador_id) {
        $sql = "UPDATE usuarios u 
                SET avaliacao_media = (SELECT COALESCE(AVG(nota), 0) FROM avaliacoes WHERE prestador_id = ?) 
                WHERE u.id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $prestador_id, $prestador_id);
        return mysqli_stmt_execute($stmt);
    }
}
?>