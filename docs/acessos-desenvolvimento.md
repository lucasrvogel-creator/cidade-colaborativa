# Acessos do ambiente local de desenvolvimento

Estes acessos pertencem ao ambiente local de desenvolvimento. Não utilizar em produção.

## Administrador

- E-mail: admin@cidadecolaborativa.local
- Senha: `Cc!be92a77c3a10c9e3`

## Banco da aplicação

A conexão abaixo aponta para o banco local; não é um banco hospedado pelo GitHub.

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=cidadecolaborativa
DB_USERNAME=cidade_app
DB_PASSWORD=5df8c76c06c21e428c1a9516db7b620e2a93a710cb17b48a
```

## Remoção posterior

Trocar a senha administrativa e a senha do usuário MySQL, atualizar o .env local e remover este documento. Apagar o arquivo não remove as credenciais dos commits anteriores. Não tornar o repositório público antes de revogar estes acessos.

Não estão incluídos tokens do GitHub nem a credencial de administração do servidor MySQL.
