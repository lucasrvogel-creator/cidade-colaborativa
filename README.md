# Cidade Colaborativa

Projeto acadêmico em preparação. Os requisitos funcionais serão documentados após a definição do escopo.

## Interface web

A pasta `frontend` contém a base do site com React, TypeScript e Vite. Os estilos utilizam Tailwind CSS, os componentes utilizam shadcn/ui e os ícones utilizam Lucide React.

Pré-requisito: Node.js 24 LTS com npm.

```sh
cd frontend
npm ci
npm run dev
```

Abra o endereço local informado pelo Vite. A página inicial é apenas uma demonstração da instalação, sem funcionalidades de negócio ou persistência de dados.

### Verificações

```sh
npm run lint
npm run build
npm run preview
```

O comando `build` verifica o TypeScript e gera a versão de produção em `frontend/dist`. O comando `preview` permite conferir essa versão localmente.

### Componentes e estilos

- Componentes reutilizáveis: `frontend/src/components/ui`.
- Tema e estilos globais: `frontend/src/index.css`.
- Ícones: importar de `lucide-react`.
- O alias `@/` aponta para `frontend/src/`.
- As versões resolvidas das dependências são registradas no `frontend/package-lock.json`; use `npm ci` para reproduzir a instalação.

Referências: [Vite](https://vite.dev/guide/), [Tailwind CSS](https://tailwindcss.com/docs/installation/using-vite) e [shadcn/ui](https://ui.shadcn.com/docs/installation/vite).

## Histórico de desenvolvimento

O desenvolvimento será registrado em commits por etapa, com mensagens descritivas em português, para permitir acompanhar e avaliar a evolução do projeto.

Use `git log --oneline --graph --all` para consultar o histórico local ou a página de commits no GitHub.
