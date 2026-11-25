ALTER TABLE usuarios MODIFY perfil ENUM ('cidadao', 'operador', 'admin') NOT NULL DEFAULT 'cidadao';

ALTER TABLE cotas_inundacao MODIFY bairro_id BIGINT UNSIGNED NULL;

ALTER TABLE cotas_inundacao MODIFY cota_metros DECIMAL(7, 2) NULL;

ALTER TABLE cotas_inundacao ADD cota_maxima DECIMAL(7, 2) NULL;

ALTER TABLE cotas_inundacao ADD maxima_aberta TINYINT (1) NOT NULL DEFAULT 0;

ALTER TABLE cotas_inundacao ADD ativo TINYINT (1) NOT NULL DEFAULT 1;

ALTER TABLE cotas_inundacao ADD chave_fonte VARCHAR(64) NULL UNIQUE;