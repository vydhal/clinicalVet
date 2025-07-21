-- Script SQL para criação do banco de dados do Sistema Veterinário
-- Compatível com MySQL

-- Criação da tabela `clinicas`
CREATE TABLE clinicas (
    id_clinica INT PRIMARY KEY AUTO_INCREMENT,
    nome_clinica VARCHAR(255) NOT NULL,
    endereco_clinica TEXT,
    telefone_clinica VARCHAR(20),
    email_clinica VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criação da tabela `usuarios`
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome_usuario VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM("superadmin", "admin", "veterinario") NOT NULL,
    id_clinica INT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_clinica) REFERENCES clinicas(id_clinica) ON DELETE SET NULL
);

-- Criação da tabela `tutores`
CREATE TABLE tutores (
    id_tutor INT PRIMARY KEY AUTO_INCREMENT,
    nome_tutor VARCHAR(255) NOT NULL,
    telefone_tutor VARCHAR(20),
    email_tutor VARCHAR(255),
    cpf_tutor VARCHAR(14) UNIQUE NOT NULL,
    endereco_tutor TEXT,
    id_clinica INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_clinica) REFERENCES clinicas(id_clinica) ON DELETE CASCADE,
    INDEX idx_cpf_tutor (cpf_tutor),
    INDEX idx_nome_tutor (nome_tutor)
);

-- Criação da tabela `especies`
CREATE TABLE especies (
    id_especie INT PRIMARY KEY AUTO_INCREMENT,
    nome_especie VARCHAR(100) UNIQUE NOT NULL,
    ativo BOOLEAN DEFAULT TRUE
);

-- Criação da tabela `medicamentos`
CREATE TABLE medicamentos (
    id_medicamento INT PRIMARY KEY AUTO_INCREMENT,
    nome_medicamento VARCHAR(255) UNIQUE NOT NULL,
    ativo BOOLEAN DEFAULT TRUE
);

-- Criação da tabela `animais`
CREATE TABLE animais (
    id_animal INT PRIMARY KEY AUTO_INCREMENT,
    nome_animal VARCHAR(255) NOT NULL,
    id_especie INT NOT NULL,
    raca VARCHAR(100),
    sexo ENUM("Macho", "Fêmea") NOT NULL,
    porte ENUM("Pequeno", "Médio", "Grande") NOT NULL,
    pelagem VARCHAR(100),
    peso DECIMAL(5,2),
    data_nascimento DATE,
    foto_path VARCHAR(500),
    observacoes_animal TEXT,
    id_tutor INT NOT NULL,
    id_clinica INT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_especie) REFERENCES especies(id_especie),
    FOREIGN KEY (id_tutor) REFERENCES tutores(id_tutor) ON DELETE CASCADE,
    FOREIGN KEY (id_clinica) REFERENCES clinicas(id_clinica) ON DELETE CASCADE,
    INDEX idx_nome_animal (nome_animal),
    INDEX idx_tutor (id_tutor),
    INDEX idx_clinica (id_clinica)
);

-- Criação da tabela `historico_medico`
CREATE TABLE historico_medico (
    id_historico INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT NOT NULL,
    data_hora_evento DATETIME NOT NULL,
    tipo_evento ENUM("Consulta", "Medicação", "Internação", "Alta", "Vacina", "Cirurgia", "Exame") NOT NULL,
    medicacao VARCHAR(255),
    dose VARCHAR(100),
    via_administracao VARCHAR(100),
    responsavel_registro VARCHAR(255) NOT NULL,
    observacoes_evento TEXT,
    id_veterinario INT,
    necessita_retorno BOOLEAN DEFAULT FALSE,
    data_hora_retorno DATETIME NULL,
    id_clinica INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_animal) REFERENCES animais(id_animal) ON DELETE CASCADE,
    FOREIGN KEY (id_veterinario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_clinica) REFERENCES clinicas(id_clinica) ON DELETE CASCADE,
    INDEX idx_animal (id_animal),
    INDEX idx_data_evento (data_hora_evento),
    INDEX idx_tipo_evento (tipo_evento),
    INDEX idx_clinica (id_clinica)
);

-- Criação da tabela `consultas_medicamentos`
CREATE TABLE consultas_medicamentos (
    id_consulta_medicamento INT PRIMARY KEY AUTO_INCREMENT,
    id_historico INT NOT NULL,
    id_medicamento INT NOT NULL,
    dose VARCHAR(100),
    via_administracao VARCHAR(100),
    observacoes TEXT,
    FOREIGN KEY (id_historico) REFERENCES historico_medico(id_historico) ON DELETE CASCADE,
    FOREIGN KEY (id_medicamento) REFERENCES medicamentos(id_medicamento)
);


