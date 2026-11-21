# Sistema de Controle de Estoque

Aplicação web em Symfony para controlar categorias, produtos e movimentações simples de estoque. O acesso é protegido por login, e todas as telas de gestão ficam sob `/app`.

## Funcionalidades

- Login com a entidade `Usuario` e logout.
- Painel com totais de categorias, produtos e unidades em estoque.
- CRUD de categorias.
- CRUD de produtos com nome, descrição, categoria, valor e quantidade.
- Ações de **+1** e **-1** unidade no estoque, sempre por `POST` e com proteção CSRF.
- O estoque nunca fica negativo: a remoção é bloqueada quando a quantidade já está zerada.
- Validação de formulários no servidor (campos obrigatórios e quantidade/valor não negativos).
- Interface simples com Bootstrap 5 e textos em português.

## Telas

As imagens abaixo foram capturadas da aplicação em execução, usando dados de demonstração.

### Login

![Tela de login](docs/screenshots/login.png)

### Painel

![Painel com totais](docs/screenshots/painel.png)

### Categorias

![Lista de categorias](docs/screenshots/categorias.png)

![Formulário de categoria](docs/screenshots/categoria-formulario.png)

### Produtos

![Lista de produtos com ações de estoque](docs/screenshots/produtos.png)

![Formulário de produto](docs/screenshots/produto-formulario.png)

## Requisitos

- PHP 8.4 ou superior
- Composer 2
- MySQL 8.0 ou superior
- Extensões PHP: `ctype`, `iconv`, `mbstring`, `intl`, `pdo_mysql` e `xml`
- Symfony CLI (opcional, mas facilita a execução local)

## Instalação

1. Clone o repositório e entre na pasta do projeto:

   ```bash
   git clone https://github.com/ht-dutra/estoque-symfony.git
   cd estoque-symfony
   ```

2. Instale as dependências:

   ```bash
   composer install
   ```

3. Configure a conexão com o MySQL em um arquivo `.env.local` (não versionado):

   ```dotenv
   DATABASE_URL="mysql://usuario:senha@127.0.0.1:3306/estoque_symfony?serverVersion=8.0&charset=utf8mb4"
   ```

   Troque `usuario` e `senha` pelos dados do seu MySQL. Não coloque credenciais reais em arquivos versionados.

4. Crie o banco de dados:

   ```bash
   php bin/console doctrine:database:create
   ```

5. Execute as migrations:

   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

6. Crie o primeiro usuário de acesso:

   ```bash
   php bin/console app:create-user admin@estoque.local
   ```

   O comando solicita a senha de forma oculta. Também é possível informá-la diretamente (evite deixar a senha no histórico do shell):

   ```bash
   php bin/console app:create-user admin@estoque.local "uma-senha-segura"
   ```

7. Inicie o servidor:

   ```bash
   symfony serve
   ```

   Ou, sem a Symfony CLI:

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

8. Acesse <http://127.0.0.1:8000/login> e entre com o usuário criado.

## Rotas principais

| Método | Rota | Descrição |
| --- | --- | --- |
| GET/POST | `/login` | Login |
| GET | `/logout` | Logout |
| GET | `/app` | Painel |
| GET | `/app/categorias` | Lista de categorias |
| GET/POST | `/app/categorias/new` | Cadastro de categoria |
| GET | `/app/categorias/{id}` | Visualização de categoria |
| GET/POST | `/app/categorias/{id}/edit` | Edição de categoria |
| POST | `/app/categorias/{id}` | Exclusão de categoria |
| GET | `/app/produtos` | Lista de produtos |
| GET | `/app/produtos/cadastrar` | Formulário de cadastro de produto |
| POST | `/app/produtos` | Cadastro de produto |
| GET | `/app/produtos/{id}` | Formulário de edição de produto |
| POST | `/app/produtos/{id}` | Atualização de produto |
| POST | `/app/produtos/{id}/remover` | Exclusão de produto |
| POST | `/app/produtos/{id}/adicionar` | Adiciona uma unidade ao estoque |
| POST | `/app/produtos/{id}/vender` | Remove uma unidade do estoque |

Todas as rotas `/app` exigem autenticação (`ROLE_USER`). O login redireciona para `/app`.

## Movimentação de estoque

As ações **+1** e **-1** ficam na listagem de produtos e são enviadas por `POST`, com token CSRF específico para cada ação. A remoção só acontece quando a quantidade é maior que zero; caso contrário, o sistema exibe um aviso e mantém o estoque em `0`.

## Testes e validação

Validações rápidas:

```bash
php bin/console lint:twig templates
php bin/console lint:yaml config
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/console debug:router
```

Testes automatizados:

```bash
# Cria e prepara o banco de testes (executar uma vez)
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Executa os testes
php bin/phpunit
```

Os testes cobrem proteção das rotas, página de login, ações de estoque com CSRF, bloqueio de quantidade negativa, exclusão de produto/categoria e páginas do CRUD.

## Estrutura do projeto

```text
assets/                  JavaScript e CSS (Bootstrap via AssetMapper)
config/                  Configuração do Symfony, segurança, Doctrine e Twig
docs/screenshots/        Capturas de tela reais da aplicação
migrations/              Migrations do banco de dados
src/Command/             Comando para criar o primeiro usuário
src/Controller/          Controllers de login, painel, categorias e produtos
src/Entity/              Entidades Doctrine (Usuario, Categoria e Produto)
src/Form/                Formulários de categoria e produto
src/Repository/          Repositórios Doctrine
templates/               Templates Twig
tests/                   Testes funcionais
```

## Observações

- Use `.env.local` para credenciais e configurações específicas da sua máquina; esse arquivo não é versionado.
- As capturas em `docs/screenshots/` usam dados de demonstração e devem ser refeitas caso a interface mude.
- O projeto não inclui uma tela de cadastro público de usuários. Novos acessos devem ser criados pelo comando `app:create-user`.
