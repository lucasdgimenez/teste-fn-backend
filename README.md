# Teste FN — Backend

API REST desenvolvida com **Laravel 12** e autenticação via **JWT**. O ambiente local é provisionado com **Docker** (PHP-FPM, Nginx e MariaDB).

---

## Requisitos

- [Docker](https://www.docker.com/) e Docker Compose instalados
- Git

---

## Passo a passo para rodar localmente

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd teste-fn-backend
```

---

### 2. Configure o arquivo `.env`

Copie o arquivo de exemplo e edite as variáveis:

```bash
cp .env.example .env
```

Abra o `.env` e ajuste as variáveis de banco de dados para corresponder ao container Docker:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=testefnbackend_app_db
DB_PORT=3306
DB_DATABASE=teste_fn_backend
DB_USERNAME=root
DB_PASSWORD=secret
```

> **Importante:** `DB_HOST` deve ser o nome do container MariaDB (`testefnbackend_app_db`), não `127.0.0.1`.
> `DB_PASSWORD` precisa coincidir com `MYSQL_ROOT_PASSWORD` definido no `docker-compose.yml`.

---

### 3. Suba os containers

```bash
docker compose up -d --build
```

Isso irá iniciar:

| Container                   | Serviço       | Porta local |
|-----------------------------|---------------|-------------|
| `testefnbackend_app_fpm`    | PHP 8.2-FPM   | —           |
| `testefnbackend_app_nginx`  | Nginx         | `8000`      |
| `testefnbackend_app_db`     | MariaDB 11    | `3307`      |

O container da aplicação aguarda o banco estar saudável antes de subir (healthcheck configurado).

---

### 4. Gere a Application Key

```bash
docker exec testefnbackend_app_fpm php artisan key:generate
```

---

### 5. Gere o secret do JWT

```bash
docker exec testefnbackend_app_fpm php artisan jwt:secret
```

Isso adiciona automaticamente a variável `JWT_SECRET` ao seu `.env`.

---

### 6. Execute as migrations

```bash
docker exec testefnbackend_app_fpm php artisan migrate
```

---

### 7. Execute os Seeders

```bash
docker exec testefnbackend_app_fpm php artisan db:seed
```

Isso criará os seguintes usuários de teste:

| Nome    | E-mail               | Senha                | Perfil    |
|---------|----------------------|----------------------|-----------|
| Admin   | `admin@teste.com`    | `admin@teste.com`    | `admin`   |
| Usuário | `usuario@teste.com`  | `usuario@teste.com`  | `usuario` |

---

### 8. Acesse a API

A API estará disponível em:

```
http://localhost:8000/api
```

---

## Autenticação JWT

Todas as rotas (exceto `/api/login`) exigem autenticação via token JWT.

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@teste.com",
  "password": "admin@teste.com"
}
```

A resposta retornará um `access_token`. Use-o nas requisições seguintes:

```http
Authorization: Bearer <access_token>
```

### Logout

```http
POST /api/logout
Authorization: Bearer <access_token>
```

---

## Rotas disponíveis

| Método   | Rota                          | Autenticação      | Descrição                     |
|----------|-------------------------------|-------------------|-------------------------------|
| `POST`   | `/api/login`                  | Pública           | Autenticar usuário            |
| `POST`   | `/api/logout`                 | JWT               | Encerrar sessão               |
| `GET`    | `/api/dashboard`              | JWT               | Dados do dashboard            |
| `GET`    | `/api/produtos`               | JWT               | Listar produtos               |
| `POST`   | `/api/produtos`               | JWT               | Criar produto                 |
| `GET`    | `/api/produtos/{id}`          | JWT               | Exibir produto                |
| `PUT`    | `/api/produtos/{id}`          | JWT               | Atualizar produto             |
| `DELETE` | `/api/produtos/{id}`          | JWT               | Remover produto               |
| `GET`    | `/api/compras`                | JWT + Admin       | Listar compras                |
| `POST`   | `/api/compras`                | JWT + Admin       | Criar compra                  |
| `GET`    | `/api/compras/{id}`           | JWT + Admin       | Exibir compra                 |
| `PUT`    | `/api/compras/{id}`           | JWT + Admin       | Atualizar compra              |
| `DELETE` | `/api/compras/{id}`           | JWT + Admin       | Remover compra                |
| `GET`    | `/api/vendas`                 | JWT               | Listar vendas                 |
| `POST`   | `/api/vendas`                 | JWT               | Criar venda                   |
| `GET`    | `/api/vendas/{id}`            | JWT               | Exibir venda                  |
| `PUT`    | `/api/vendas/{id}`            | JWT               | Atualizar venda               |
| `PATCH`  | `/api/vendas/{id}/cancelar`   | JWT               | Cancelar venda                |
| `DELETE` | `/api/vendas/{id}`            | JWT               | Remover venda                 |

> As rotas de **compras** exigem o perfil `admin`. Use as credenciais do usuário Admin para acessá-las.

---

## Comandos úteis

```bash
# Ver logs da aplicação
docker compose logs -f

# Acessar o shell do container PHP
docker exec -it testefnbackend_app_fpm sh

# Rodar um comando Artisan qualquer
docker exec testefnbackend_app_fpm php artisan <comando>

# Derrubar os containers (mantém volumes)
docker compose down

# Derrubar e remover volumes (apaga o banco de dados)
docker compose down -v
```
