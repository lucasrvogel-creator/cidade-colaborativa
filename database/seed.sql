-- Cadastros de referência. Confirmar delimitação dos bairros com o grupo antes de uso real.
INSERT INTO
    bairros (nome)
VALUES
    ('Centro'),
    ('Canoas'),
    ('Canta Galo'),
    ('Jardim América'),
    ('Santana'),
    ('Progresso'),
    ('Budag'),
    ('Taboão');

INSERT INTO
    categorias (nome, descricao, icone, cor)
VALUES
    (
        'Infraestrutura urbana',
        'Vias, pontes e calçadas',
        'cone-striped',
        '#2563eb'
    ),
    (
        'Iluminação pública',
        'Postes e iluminação',
        'lightbulb',
        '#b7791f'
    ),
    (
        'Saneamento',
        'Água, esgoto e drenagem',
        'droplet',
        '#087e8b'
    ),
    (
        'Meio ambiente',
        'Resíduos e áreas públicas',
        'tree',
        '#16804a'
    ),
    (
        'Trânsito',
        'Sinalização e acessibilidade',
        'stoplights',
        '#b64424'
    ),
    (
        'Defesa Civil e enchentes',
        'Relatos comunitários; não é canal de emergência',
        'water',
        '#7352a3'
    ),
    (
        'Outros',
        'Outras situações urbanas',
        'geo-alt',
        '#64748b'
    );

INSERT INTO
    subcategorias (categoria_id, nome)
VALUES
    (1, 'Buracos nas ruas'),
    (1, 'Calçadas danificadas'),
    (1, 'Pavimentação irregular'),
    (1, 'Pontes danificadas'),
    (1, 'Problemas em vias públicas'),
    (2, 'Poste apagado'),
    (2, 'Iluminação defeituosa'),
    (2, 'Estrutura danificada'),
    (3, 'Vazamento de água'),
    (3, 'Esgoto a céu aberto'),
    (3, 'Bueiros entupidos'),
    (3, 'Problemas de drenagem'),
    (4, 'Descarte irregular de lixo'),
    (4, 'Poluição'),
    (4, 'Acúmulo de resíduos'),
    (4, 'Problemas em áreas públicas'),
    (5, 'Semáforo defeituoso'),
    (5, 'Sinalização danificada'),
    (5, 'Via obstruída'),
    (5, 'Problemas de acessibilidade'),
    (6, 'Alagamento'),
    (6, 'Rua inundada'),
    (6, 'Deslizamento'),
    (6, 'Queda de árvore'),
    (6, 'Interdição de via'),
    (6, 'Problemas relacionados às cheias'),
    (7, 'Outras ocorrências');

-- Usuário não autenticável: hash aleatório de senha desconhecida, perfil suspenso.
INSERT INTO
    usuarios (nome, email, senha_hash, status)
VALUES
    (
        'Pessoa fictícia — demonstração',
        'demonstracao@example.invalid',
        'conta-de-demonstracao-sem-login',
        'suspenso'
    );

INSERT INTO
    ocorrencias (
        usuario_id,
        categoria_id,
        bairro_id,
        titulo,
        descricao,
        latitude,
        longitude,
        status,
        demonstracao,
        publicado_em,
        resolvido_em
    )
VALUES
    (
        1,
        1,
        1,
        'Demonstração: calçada danificada',
        'Registro fictício para apresentar o sistema. Não indica um problema real neste local.',
        -27.214,
        -49.643,
        'PUBLICADA',
        1,
        NOW (),
        NULL
    ),
    (
        1,
        2,
        2,
        'Demonstração: iluminação em acompanhamento',
        'Registro fictício. Acompanhamento pela plataforma não significa atendimento pela prefeitura.',
        -27.205,
        -49.635,
        'EM_ACOMPANHAMENTO',
        1,
        NOW (),
        NULL
    ),
    (
        1,
        4,
        4,
        'Demonstração: limpeza concluída',
        'Registro fictício de uma situação resolvida, sem vínculo com acontecimentos reais.',
        -27.222,
        -49.652,
        'RESOLVIDA',
        1,
        NOW (),
        NOW ()
    );

INSERT INTO
    historico_ocorrencias (
        ocorrencia_id,
        usuario_id,
        status_anterior,
        status_novo,
        justificativa,
        publico
    )
SELECT
    id,
    1,
    NULL,
    status,
    'Carga de dados fictícios para demonstração acadêmica.',
    1
FROM
    ocorrencias
WHERE
    demonstracao = 1;

-- Não inserir níveis, cotas, alertas ou informações de barragens inventados.