-- CidadeColaborativa / MySQL 8+ ou MariaDB 10.4+. Importar em banco vazio.
SET NAMES utf8mb4;
CREATE TABLE usuarios (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE, senha_hash VARCHAR(255) NOT NULL,
 perfil ENUM('cidadao','admin') NOT NULL DEFAULT 'cidadao', status ENUM('ativo','suspenso','anonimizado') NOT NULL DEFAULT 'ativo',
 exclusao_solicitada_em DATETIME NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE bairros (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(120) NOT NULL,
 municipio VARCHAR(120) NOT NULL DEFAULT 'Rio do Sul', ativo TINYINT(1) NOT NULL DEFAULT 1,
 UNIQUE KEY bairro_municipio (nome, municipio)
) ENGINE=InnoDB;
CREATE TABLE categorias (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(120) NOT NULL UNIQUE,
 descricao VARCHAR(255) NOT NULL DEFAULT '', icone VARCHAR(60) NOT NULL DEFAULT 'geo-alt',
 cor VARCHAR(7) NOT NULL DEFAULT '#2563eb', ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;
CREATE TABLE subcategorias (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, categoria_id BIGINT UNSIGNED NOT NULL, nome VARCHAR(120) NOT NULL,
 ativo TINYINT(1) NOT NULL DEFAULT 1, UNIQUE KEY subcategoria_categoria (id,categoria_id),
 UNIQUE KEY nome_categoria (categoria_id,nome), FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;
CREATE TABLE ocorrencias (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id BIGINT UNSIGNED NOT NULL,
 categoria_id BIGINT UNSIGNED NOT NULL, subcategoria_id BIGINT UNSIGNED NULL, bairro_id BIGINT UNSIGNED NOT NULL,
 titulo VARCHAR(160) NOT NULL, descricao TEXT NOT NULL, endereco_referencia VARCHAR(255) NOT NULL DEFAULT '',
 latitude DECIMAL(10,7) NOT NULL, longitude DECIMAL(10,7) NOT NULL,
 status ENUM('PENDENTE','CORRECAO_SOLICITADA','PUBLICADA','EM_ACOMPANHAMENTO','RESOLVIDA','REJEITADA','DUPLICADA','ARQUIVADA') NOT NULL DEFAULT 'PENDENTE',
 ocorrencia_principal_id BIGINT UNSIGNED NULL, demonstracao TINYINT(1) NOT NULL DEFAULT 0,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 publicado_em DATETIME NULL, resolvido_em DATETIME NULL,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id), FOREIGN KEY (categoria_id) REFERENCES categorias(id),
 FOREIGN KEY (subcategoria_id,categoria_id) REFERENCES subcategorias(id,categoria_id), FOREIGN KEY (bairro_id) REFERENCES bairros(id),
 FOREIGN KEY (ocorrencia_principal_id) REFERENCES ocorrencias(id), CHECK(latitude BETWEEN -90 AND 90), CHECK(longitude BETWEEN -180 AND 180),
 INDEX filtro_publico(status,publicado_em), INDEX filtro_categoria(categoria_id,status), INDEX filtro_bairro(bairro_id,status),
 INDEX mapa(latitude,longitude), INDEX autor(usuario_id,criado_em)
) ENGINE=InnoDB;
CREATE TABLE ocorrencia_imagens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ocorrencia_id BIGINT UNSIGNED NOT NULL,
 caminho_arquivo VARCHAR(100) NOT NULL UNIQUE, tipo_mime VARCHAR(30) NOT NULL, tamanho_bytes INT UNSIGNED NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id)
) ENGINE=InnoDB;
CREATE TABLE confirmacoes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ocorrencia_id BIGINT UNSIGNED NOT NULL, usuario_id BIGINT UNSIGNED NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY confirmacao_unica (ocorrencia_id,usuario_id),
 FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id), FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE historico_ocorrencias (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ocorrencia_id BIGINT UNSIGNED NOT NULL, usuario_id BIGINT UNSIGNED NOT NULL,
 status_anterior VARCHAR(30) NULL, status_novo VARCHAR(30) NOT NULL, justificativa TEXT NOT NULL,
 publico TINYINT(1) NOT NULL DEFAULT 0, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id), FOREIGN KEY (usuario_id) REFERENCES usuarios(id), INDEX historico(ocorrencia_id,criado_em)
) ENGINE=InnoDB;
CREATE TABLE denuncias (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ocorrencia_id BIGINT UNSIGNED NOT NULL, usuario_id BIGINT UNSIGNED NOT NULL,
 motivo VARCHAR(60) NOT NULL, descricao TEXT NOT NULL, status ENUM('PENDENTE','RESOLVIDA','ARQUIVADA') NOT NULL DEFAULT 'PENDENTE',
 administrador_id BIGINT UNSIGNED NULL, parecer TEXT NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, analisado_em DATETIME NULL,
 FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id), FOREIGN KEY (usuario_id) REFERENCES usuarios(id), FOREIGN KEY (administrador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE solicitacoes_resolucao (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ocorrencia_id BIGINT UNSIGNED NOT NULL, usuario_id BIGINT UNSIGNED NOT NULL,
 descricao TEXT NOT NULL, status ENUM('PENDENTE','RESOLVIDA','ARQUIVADA') NOT NULL DEFAULT 'PENDENTE',
 administrador_id BIGINT UNSIGNED NULL, parecer TEXT NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, analisado_em DATETIME NULL,
 FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id), FOREIGN KEY (usuario_id) REFERENCES usuarios(id), FOREIGN KEY (administrador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE notificacoes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id BIGINT UNSIGNED NOT NULL, ocorrencia_id BIGINT UNSIGNED NOT NULL,
 titulo VARCHAR(160) NOT NULL, mensagem TEXT NOT NULL, lida_em DATETIME NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id), FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id), INDEX usuario_lida(usuario_id,lida_em)
) ENGINE=InnoDB;
CREATE TABLE avisos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, administrador_id BIGINT UNSIGNED NOT NULL,
 titulo VARCHAR(160) NOT NULL, descricao TEXT NOT NULL, fonte VARCHAR(190) NOT NULL,
 url_fonte VARCHAR(500) NOT NULL, status ENUM('PUBLICADO','ARQUIVADO') NOT NULL DEFAULT 'PUBLICADO',
 publicado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, expira_em DATETIME NULL,
 FOREIGN KEY (administrador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE niveis_rio (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, estacao VARCHAR(120) NOT NULL, referencia VARCHAR(120) NOT NULL,
 nivel_metros DECIMAL(7,2) NOT NULL, fonte VARCHAR(190) NOT NULL, url_fonte VARCHAR(500) NOT NULL,
 demonstracao TINYINT(1) NOT NULL DEFAULT 0, registrado_em DATETIME NOT NULL, coletado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX estacao_data(estacao,referencia,registrado_em), CHECK(nivel_metros >= 0)
) ENGINE=InnoDB;
CREATE TABLE cotas_inundacao (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, bairro_id BIGINT UNSIGNED NOT NULL, rua VARCHAR(160) NOT NULL,
 trecho VARCHAR(255) NOT NULL, cota_metros DECIMAL(7,2) NOT NULL, estacao_referencia VARCHAR(120) NOT NULL,
 referencia VARCHAR(120) NOT NULL, fonte VARCHAR(190) NOT NULL, url_fonte VARCHAR(500) NOT NULL,
 demonstracao TINYINT(1) NOT NULL DEFAULT 0, atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (bairro_id) REFERENCES bairros(id), INDEX cotas_estacao(estacao_referencia,referencia,cota_metros), CHECK(cota_metros >= 0)
) ENGINE=InnoDB;
CREATE TABLE auditoria (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id BIGINT UNSIGNED NOT NULL, acao VARCHAR(80) NOT NULL,
 entidade VARCHAR(60) NOT NULL, entidade_id BIGINT UNSIGNED NOT NULL, detalhes TEXT NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
