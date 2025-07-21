<?php
/**
 * Classe User
 * Gerencia operações relacionadas aos usuários
 */

class User {
    private $conn;
    private $table_name = "usuarios";

    // Propriedades do usuário
    public $id_usuario;
    public $nome_usuario;
    public $email;
    public $senha;
    public $tipo_usuario;
    public $id_clinica;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autentica um usuário
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function login($email, $password) {
        $query = "SELECT u.*, c.nome_clinica 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN clinicas c ON u.id_clinica = c.id_clinica 
                  WHERE u.email = ? AND u.ativo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar senha (usando MD5 para compatibilidade com dados de exemplo)
            if (md5($password) === $row['senha'] || hash('sha256', $password) === $row['senha']) {
                // Remove a senha do retorno por segurança
                unset($row['senha']);
                return $row;
            }
        }
        
        return false;
    }

    /**
     * Cria um novo usuário
     * @return bool
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome_usuario, email, senha, tipo_usuario, id_clinica, ativo) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash da senha
        $hashed_password = hash('sha256', $this->senha);
        
        $stmt->bindParam(1, $this->nome_usuario);
        $stmt->bindParam(2, $this->email);
        $stmt->bindParam(3, $hashed_password);
        $stmt->bindParam(4, $this->tipo_usuario);
        $stmt->bindParam(5, $this->id_clinica);
        $stmt->bindParam(6, $this->ativo);
        
        if ($stmt->execute()) {
            $this->id_usuario = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Lista usuários de uma clínica
     * @param int $clinic_id
     * @return PDOStatement
     */
    public function readByClinic($clinic_id) {
        $query = "SELECT u.*, c.nome_clinica 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN clinicas c ON u.id_clinica = c.id_clinica 
                  WHERE u.id_clinica = ? AND u.ativo = 1 
                  ORDER BY u.nome_usuario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $clinic_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Lista todos os usuários (apenas superadmin)
     * @return PDOStatement
     */
    public function readAll() {
        $query = "SELECT u.*, c.nome_clinica 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN clinicas c ON u.id_clinica = c.id_clinica 
                  WHERE u.ativo = 1 
                  ORDER BY u.tipo_usuario, u.nome_usuario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Busca um usuário por ID
     * @return array|false
     */
    public function readOne() {
        $query = "SELECT u.*, c.nome_clinica 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN clinicas c ON u.id_clinica = c.id_clinica 
                  WHERE u.id_usuario = ? AND u.ativo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_usuario);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            unset($row['senha']); // Remove senha por segurança
            return $row;
        }
        
        return false;
    }

    /**
     * Atualiza dados do usuário
     * @return bool
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome_usuario = ?, email = ?, tipo_usuario = ?, id_clinica = ? 
                  WHERE id_usuario = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->nome_usuario);
        $stmt->bindParam(2, $this->email);
        $stmt->bindParam(3, $this->tipo_usuario);
        $stmt->bindParam(4, $this->id_clinica);
        $stmt->bindParam(5, $this->id_usuario);
        
        return $stmt->execute();
    }

    /**
     * Atualiza senha do usuário
     * @param string $new_password
     * @return bool
     */
    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " SET senha = ? WHERE id_usuario = ?";
        
        $stmt = $this->conn->prepare($query);
        $hashed_password = hash('sha256', $new_password);
        
        $stmt->bindParam(1, $hashed_password);
        $stmt->bindParam(2, $this->id_usuario);
        
        return $stmt->execute();
    }

    /**
     * Desativa usuário (soft delete)
     * @return bool
     */
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id_usuario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_usuario);
        
        return $stmt->execute();
    }

    /**
     * Lista veterinários de uma clínica
     * @param int $clinic_id
     * @return PDOStatement
     */
    public function getVeterinariosByClinic($clinic_id) {
        $query = "SELECT id_usuario, nome_usuario 
                  FROM " . $this->table_name . " 
                  WHERE id_clinica = ? AND tipo_usuario = 'veterinario' AND ativo = 1 
                  ORDER BY nome_usuario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $clinic_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Verifica se email já existe
     * @param string $email
     * @param int $exclude_id (opcional, para updates)
     * @return bool
     */
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT id_usuario FROM " . $this->table_name . " WHERE email = ?";
        
        if ($exclude_id) {
            $query .= " AND id_usuario != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        
        if ($exclude_id) {
            $stmt->bindParam(2, $exclude_id);
        }
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>