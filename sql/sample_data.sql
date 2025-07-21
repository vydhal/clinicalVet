-- Script SQL com dados de exemplo para o Sistema Veterinário
-- Execute este script APÓS criar as tabelas com database.sql

-- Inserir espécies comuns
INSERT INTO especies (nome_especie) VALUES
('Cão'),
('Gato'),
('Coelho'),
('Hamster'),
('Pássaro'),
('Peixe'),
('Tartaruga'),
('Ferret');

-- Inserir medicamentos comuns
INSERT INTO medicamentos (nome_medicamento) VALUES
('Acetilcisteína'),
('Adrenalina'),
('Alantol (pomada)'),
('Anti tóxico'),
('Amoxicilina'),
('Dipirona'),
('Dexametasona'),
('Meloxicam'),
('Tramadol'),
('Cefalexina'),
('Metronidazol'),
('Omeprazol'),
('Furosemida'),
('Prednisolona'),
('Ivermectina');

-- Inserir clínicas de exemplo
INSERT INTO clinicas (nome_clinica, endereco_clinica, telefone_clinica, email_clinica) VALUES
('Clínica Veterinária Pet Care', 'Rua das Flores, 123 - Centro', '(11) 1234-5678', 'contato@petcare.com.br'),
('Hospital Veterinário Animal Life', 'Av. Principal, 456 - Jardim América', '(11) 8765-4321', 'atendimento@animallife.com.br');

-- Inserir usuários de exemplo
-- Senha padrão para todos: "123456" (hash MD5: e10adc3949ba59abbe56e057f20f883e)
INSERT INTO usuarios (nome_usuario, email, senha, tipo_usuario, id_clinica) VALUES
('Super Administrador', 'superadmin@sistema.com', 'e10adc3949ba59abbe56e057f20f883e', 'superadmin', NULL),
('Dr. João Silva', 'admin1@petcare.com.br', 'e10adc3949ba59abbe56e057f20f883e', 'admin', 1),
('Dra. Maria Santos', 'admin2@animallife.com.br', 'e10adc3949ba59abbe56e057f20f883e', 'admin', 2),
('Dr. Carlos Oliveira', 'vet1@petcare.com.br', 'e10adc3949ba59abbe56e057f20f883e', 'veterinario', 1),
('Dra. Ana Costa', 'vet2@petcare.com.br', 'e10adc3949ba59abbe56e057f20f883e', 'veterinario', 1),
('Dr. Pedro Lima', 'vet3@animallife.com.br', 'e10adc3949ba59abbe56e057f20f883e', 'veterinario', 2);

-- Inserir tutores de exemplo
INSERT INTO tutores (nome_tutor, telefone_tutor, email_tutor, cpf_tutor, endereco_tutor, id_clinica) VALUES
('José da Silva', '(11) 99999-1111', 'jose.silva@email.com', '123.456.789-01', 'Rua A, 100 - Bairro X', 1),
('Maria Oliveira', '(11) 99999-2222', 'maria.oliveira@email.com', '234.567.890-12', 'Rua B, 200 - Bairro Y', 1),
('Carlos Santos', '(11) 99999-3333', 'carlos.santos@email.com', '345.678.901-23', 'Rua C, 300 - Bairro Z', 1),
('Ana Paula', '(11) 99999-4444', 'ana.paula@email.com', '456.789.012-34', 'Rua D, 400 - Bairro W', 2),
('Roberto Costa', '(11) 99999-5555', 'roberto.costa@email.com', '567.890.123-45', 'Rua E, 500 - Bairro V', 2);

-- Inserir animais de exemplo
INSERT INTO animais (nome_animal, id_especie, raca, sexo, porte, pelagem, peso, data_nascimento, observacoes_animal, id_tutor, id_clinica) VALUES
('Rex', 1, 'Labrador', 'Macho', 'Grande', 'Dourada', 30.50, '2020-03-15', 'Animal dócil e brincalhão', 1, 1),
('Mimi', 2, 'Persa', 'Fêmea', 'Pequeno', 'Branca', 4.20, '2021-07-22', 'Gata muito carinhosa', 2, 1),
('Buddy', 1, 'Golden Retriever', 'Macho', 'Grande', 'Dourada', 28.00, '2019-11-10', 'Cão muito ativo', 3, 1),
('Luna', 2, 'Siamês', 'Fêmea', 'Pequeno', 'Rajada', 3.80, '2022-01-05', 'Gata independente', 4, 2),
('Max', 1, 'Poodle', 'Macho', 'Médio', 'Preta', 12.50, '2020-09-18', 'Cão inteligente e obediente', 5, 2),
('Bella', 1, 'Shih Tzu', 'Fêmea', 'Pequeno', 'Branca e marrom', 6.20, '2021-12-03', 'Cadela muito sociável', 1, 1);

-- Inserir histórico médico de exemplo
INSERT INTO historico_medico (id_animal, data_hora_evento, tipo_evento, medicacao, dose, via_administracao, responsavel_registro, observacoes_evento, id_veterinario, necessita_retorno, data_hora_retorno, id_clinica) VALUES
(1, '2024-01-15 10:30:00', 'Consulta', 'Amoxicilina', '250mg', 'Oral', 'vet1@petcare.com.br', 'Consulta de rotina. Animal apresenta boa saúde geral. Prescrição de antibiótico preventivo.', 4, TRUE, '2024-01-29 10:30:00', 1),
(1, '2024-01-20 14:15:00', 'Medicação', 'Amoxicilina', '250mg', 'Oral', 'vet1@petcare.com.br', 'Administração de medicação prescrita na consulta anterior.', 4, FALSE, NULL, 1),
(2, '2024-01-18 09:45:00', 'Consulta', 'Meloxicam', '0.5mg', 'Oral', 'vet2@petcare.com.br', 'Consulta por claudicação. Prescrição de anti-inflamatório.', 5, TRUE, '2024-02-01 09:45:00', 1),
(3, '2024-01-22 16:20:00', 'Vacina', '', '', 'Subcutânea', 'vet1@petcare.com.br', 'Aplicação de vacina antirrábica anual.', 4, FALSE, NULL, 1),
(4, '2024-01-25 11:10:00', 'Consulta', 'Dipirona', '25mg/kg', 'Oral', 'vet3@animallife.com.br', 'Consulta por febre. Prescrição de antitérmico.', 6, TRUE, '2024-02-08 11:10:00', 2),
(5, '2024-01-28 13:30:00', 'Exame', '', '', '', 'vet3@animallife.com.br', 'Exame de sangue de rotina. Resultados dentro da normalidade.', 6, FALSE, NULL, 2);

-- Inserir medicamentos das consultas (tabela de relacionamento)
INSERT INTO consultas_medicamentos (id_historico, id_medicamento, dose, via_administracao, observacoes) VALUES
(1, 5, '250mg', 'Oral', 'Administrar 2x ao dia por 7 dias'),
(2, 5, '250mg', 'Oral', 'Continuação do tratamento'),
(3, 8, '0.5mg', 'Oral', 'Administrar 1x ao dia por 5 dias'),
(5, 6, '25mg/kg', 'Oral', 'Administrar conforme necessário para febre');

