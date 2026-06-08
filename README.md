# Precificação de Viagem

Sistema para cotação de seguro viagem. O usuário informa destino, período e viajantes; a API calcula o valor conforme regras de negócio e persiste cada cotação bem-sucedida. O frontend exibe o resultado e permite consultar o histórico.

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Next.js 16, React 19, TypeScript, Tailwind CSS 4 |
| Banco | SQLite (padrão), MySQL/MariaDB ou PostgreSQL |

## Estrutura do repositório

```
precificacao-viagem/
├── backend/          # API Laravel
│   ├── app/
│   │   ├── Http/Controllers/QuoteController.php
│   │   ├── Http/Requests/CreateQuoteRequest.php
│   │   ├── Http/Resources/
│   │   ├── Models/           # QuoteGroup, Viajante, Aviso
│   │   ├── Repositories/QuoteRepository.php
│   │   └── Services/QuoteCalculatorService.php
│   ├── database/migrations/
│   ├── routes/api.php
│   └── tests/Unit/QuoteCalculatorServiceTest.php
└── frontend/         # App Next.js
    ├── app/
    │   ├── page.tsx              # Formulário de cotação
    │   ├── resultado/page.tsx    # Resultado após cálculo
    │   ├── listar-cotacoes/      # Histórico salvo
    │   ├── context/QuoteContext.tsx
    │   └── components/cardResult.tsx
    └── app/hookies/              # API client e tipos
```

## Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 20+
- npm

## Execução

### 1. Backend

```bash
cd backend
composer install
copy .env.example .env   # Linux/macOS: cp .env.example .env
php artisan key:generate
```

**Banco de dados (SQLite — padrão)**

```bash
# Criar arquivo SQLite (se ainda não existir)
type nul > database\database.sqlite   # Linux/macOS: touch database/database.sqlite

php artisan migrate
php artisan serve
```

A API ficará disponível em `http://127.0.0.1:8000`.

**Banco MySQL/MariaDB (XAMPP)**

No `.env`, ajuste:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=precificacao_viagem
DB_USERNAME=root
DB_PASSWORD=
```

Crie o banco no phpMyAdmin e execute `php artisan migrate`.

**Banco PostgreSQL**

O backend expõe o driver `pgsql` em `backend/config/database.php` (porta padrão `5432`). No `.env`, ajuste:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=precificacao_viagem
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Crie o banco no PostgreSQL e execute `php artisan migrate`. As migrations usam o Schema Builder do Laravel e funcionam da mesma forma que em SQLite e MySQL.

### 2. Frontend

Em outro terminal:

```bash
cd frontend
npm install
npm run dev
```

A aplicação ficará em `http://localhost:3000`.

**Variável de ambiente (opcional)**

Crie `frontend/.env.local` se a API não estiver no endereço padrão:

```env
NEXT_PUBLIC_API_URL=http://SEU_DOMINIO/api
```

### 3. Testes do backend

```bash
cd backend
php artisan test
```

## API

| Método | Rota | Descrição |
|---|---|---|
| `POST` | `/api/quotes` | Calcula e persiste uma cotação |
| `GET` | `/api/quotes` | Lista cotações salvas |

### Exemplo de requisição (`POST /api/quotes`)

```json
{
  "destino": "EUROPA",
  "data_inicio": "2026-07-10",
  "data_fim": "2026-07-20",
  "viajantes": [
    {
      "nome": "Ana",
      "data_nascimento": "1990-03-15",
      "adicionais": ["BAGAGEM", "ESPORTES_AVENTURA"]
    }
  ]
}
```

### Resposta do cálculo

Retorna dias cobrados, subtotal por viajante, avisos, desconto de grupo e total final. Erros de validação respondem em JSON (`422`).

## Frontend

| Rota | Descrição |
|---|---|
| `/` | Formulário para nova cotação |
| `/resultado` | Detalhe da cotação recém-calculada |
| `/listar-cotacoes` | Listagem das cotações persistidas (accordion) |

Fluxo: preencher formulário → calcular → redirecionamento para `/resultado` com o detalhe completo.

## Decisões técnicas

### Backend

- **Laravel como API REST** — frontend separado em Next.js; comunicação via JSON.
- **Banco configurável** — SQLite por padrão; MySQL/MariaDB e PostgreSQL via `DB_CONNECTION` em `backend/config/database.php`, sem alteração de código nas migrations ou models.
- **`QuoteCalculatorService` com métodos privados** — cada regra (dias cobrados, faixa etária, adicionais, desconto de grupo) fica isolada, facilitando leitura e testes unitários.
- **Idade na data de início da viagem** — o multiplicador etário usa a idade na `data_inicio`, não na data da cotação, para refletir corretamente aniversários entre a simulação e a viagem.
- **Persistência normalizada** — tabelas `quote_group`, `viajantes` e `avisos` em vez de um único JSON; permite consultas e relacionamentos Eloquent.
- **`QuoteRepository`** — encapsula a gravação transacional e o mapeamento entre payload da requisição e resultado do cálculo.
- **`CreateQuoteRequest`** — validação centralizada (destino, datas, viajantes, adicionais permitidos, `data_inicio >= hoje`).
- **API Resources** — formatação da resposta da listagem sem expor `id` interno; apenas dados necessários ao frontend.
- **Respostas JSON em rotas `api/*`** — erros de validação não redirecionam para a página inicial do Laravel.
- **`distinct` no array `adicionais`** — impede duplicatas por viajante, sem comparar adicionais entre viajantes diferentes.
- **Sem autenticação** — requisito do projeto; todas as cotações são públicas na listagem.

### Frontend

- **Next.js App Router** — páginas em `app/` com componentes client onde há interatividade (`"use client"`).
- **React Context (`QuoteContext`)** — compartilha o resultado do cálculo entre `/` e `/resultado` após o redirect, sem biblioteca externa de estado.
- **`providers.tsx`** — wrapper client no layout para usar Context sem converter o `layout.tsx` em client component.
- **`CardResult`** — componente reutilizável para exibir o detalhe da cotação calculada (dias, desconto, viajantes, avisos).
- **Listagem com accordion** — resumo compacto (data início + total); detalhe expandido sob demanda, reutilizando o mesmo padrão visual da home (Tailwind, cards zinc/emerald).
- **Fetch direto na listagem** — `/listar-cotacoes` busca `GET /api/quotes` independentemente do Context, pois exibe histórico persistido no banco.

### Cálculo e arredondamento

- Ordem por viajante: tarifa × dias × idade → esportes aventura (se elegível) → bagagem.
- `ESPORTES_AVENTURA` fora da faixa 18–64 gera aviso, mas não interrompe a cotação.
- Desconto de grupo (10% para 5+ viajantes) aplicado sobre a soma dos subtotais brutos.
- Arredondamento half-up apenas no total final; subtotais intermediários mantêm precisão até a apresentação.
