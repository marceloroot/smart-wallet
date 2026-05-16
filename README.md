# Carteira Financeira (Laravel + DDD)

API de carteira digital com cadastro, autenticação (Sanctum), depósito, transferência e reversão de transações.

## Arquitetura

- **Domain**: regras de negócio (`Wallet`, `Money`, exceções de domínio)
- **Application**: casos de uso (handlers/commands)
- **Infrastructure**: persistência Eloquent
- **Http**: controllers e validação de entrada

## Requisitos

- PHP 8.1+
- Composer
- MySQL (produção) ou SQLite (testes)

## Instalação local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Acesse a interface web: **http://localhost:8000** (login com usuários demo ou cadastre-se).

## Docker

```bash
docker compose up --build
docker compose exec app php artisan migrate --seed
```

API: `http://localhost:8000/api`

## Usuários de demonstração (seed)

| Email           | Senha        | Saldo inicial |
|-----------------|--------------|---------------|
| alice@demo.test | password123  | R$ 1.000,00   |
| bob@demo.test   | password123  | R$ 500,00     |

## Endpoints

| Método | Rota | Auth | Descrição |
|--------|------|------|-----------|
| POST | `/api/register` | Não | Cadastro + carteira |
| POST | `/api/login` | Não | Login (retorna token) |
| POST | `/api/logout` | Sim | Revoga token |
| GET | `/api/wallet/balance` | Sim | Saldo |
| POST | `/api/wallet/deposit` | Sim | Depósito |
| POST | `/api/wallet/transfer` | Sim | Transferência |
| GET | `/api/transactions` | Sim | Histórico |
| POST | `/api/transactions/{id}/reverse` | Sim | Reversão |

Header opcional: `Idempotency-Key` em depósito/transferência.

## Testes

```bash
php artisan test
```

## Exemplo (curl)

```bash
# Cadastro
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Maria","email":"maria@test.com","password":"password123","password_confirmation":"password123"}'

# Depósito (use o token retornado)
curl -X POST http://localhost:8000/api/wallet/deposit \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"amount":100.50}'
```
