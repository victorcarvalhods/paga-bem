# PagaBem API
<a id="visao-geral"></a>
API RESTful desenvolvida para simular operações básicas de transferências de valores entre usuários comuns e lojistas.

## Ir para uma seção específica
- [Features](#features)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Como Executar o Projeto](#como-executar-o-projeto)
- [Rodando os Testes](#rodando-os-testes)
- [Documentação da API](#documentacao-da-api)
- [Dados para testes](#dados-para-teste)
- [Modelagem do Banco de Dados](#modelagem-do-banco-de-dados)
- [Decisões Arquiteturais](#decisoes-arquiteturais)
- [Melhorias Futuras](#melhorias-futuras)


<a id="features"></a>

## Funcionalidades
- Transferência de valores entre usuários.
- Regras de negócio:
    - Os usuários podem realizar transferências entre si, porém lojistas só podem receber;
    - Validação do saldo do pagador antes de realizar a transferência;
    - Consulta em serviço externo (mock) para validação da transferência antes de concluir;
    - Envio de notificação assíncrona por um serviço externo(mock); 
- Envio de notificação assíncrona após uma transferência bem-sucedida.

<a id="tecnologias-utilizadas"></a>

## Tecnologias Utilizadas
- PHP 8.4
- Laravel 12
- MySQL
- Redis (para o sistema de filas e sessão (aplicável somente para o horizon))
- Docker & Docker Compose
- PHPUnit (para testes)
- PHPStan (para análise estática de código)

<a id="como-executar-o-projeto"></a>

## 💻 Como Executar o Projeto
Siga os passos abaixo para configurar e executar a aplicação em seu ambiente local.

<a id="opcao-1-execucao-com-docker-recomendado"></a>

### Opção 1: Execução com Docker (Recomendado)

<a id="pre-requisitos-docker"></a>

#### Pré-requisitos
- Docker
- Docker Compose
- Git

<a id="passos-docker"></a>

#### Passos para Instalação

<a id="docker-step-clone"></a>

1. **Clone o repositório:**

```bash
git clone https://github.com/victorcarvalhods/paga-bem.git
cd paga-bem
```

<a id="docker-step-env"></a>

2. **Configure o arquivo de ambiente:**

```bash
cp .env.example .env
```
*(Nenhuma alteração no arquivo .env é necessária para o ambiente local, as configurações padrão já apontam para os serviços do Docker.)*

<a id="docker-step-up"></a>

3. **Suba os containers do Docker:**

```bash
docker compose up --build -d 
```

<a id="docker-step-composer"></a>

4. **Instalação das dependências:**

```bash
docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader
```

<a id="docker-step-migrate"></a>

5. **Execute as migrations e seeders:**
Este passo irá criar as tabelas no banco de dados e popular com usuários de teste (comuns e lojistas).

```bash
docker compose exec app php artisan migrate --seed
```

<a id="docker-step-comandos"></a>

6. **Execução de comandos (php, Artisan, Composer, etc):**
Para executar comandos dentro da aplicação, basta seguir um dos passos abaixo.

- **Utilizando o container docker**

```bash
docker compose exec app {comando}
```

- **Utilizando o terminal local do usuário, mas torna necessário possuir as dependências (PHP, Composer, etc)**

```bash
php artisan ... || composer ....
```

#### Pronto!
- A aplicação é executada na url http://localhost:8000
- É possível acessar o banco de dados MySQL na url http://localhost:3306

<a id="opcao-2-execucao-sem-docker"></a>

### Opção 2: Execução sem Docker

<a id="pre-requisitos-local"></a>

#### Pré-requisitos
- PHP 8.4+
- Composer
- MySQL 8.0+
- Git

<a id="passos-local"></a>

#### Passos para Instalação

<a id="local-step-clone"></a>

1. **Clone o repositório:**

```bash
git clone https://github.com/victorcarvalhods/paga-bem.git
cd paga-bem
```

<a id="local-step-composer"></a>

2. **Instale as dependências:**

```bash
composer install
```

<a id="local-step-env"></a>

3. **Configure o arquivo de ambiente:**

```bash
cp .env.example .env
```

<a id="local-step-env-vars"></a>

4. **Configure as variáveis de ambiente no arquivo .env:**

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pagabem
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

CACHE_STORE=array
SESSION_DRIVER=array
```

<a id="local-step-key"></a>

5. **Gere a chave da aplicação:**

```bash
php artisan key:generate
```

<a id="local-step-migrate"></a>

6. **Execute as migrations e seeders:**

```bash
php artisan migrate --seed
```

<a id="local-step-serve"></a>

7. **Inicie o servidor de desenvolvimento:**

```bash
php artisan serve
```

<a id="local-step-queue"></a>

8. **Em outro terminal, execute o worker das filas:**

```bash
php artisan queue:work
```

#### Pronto! A aplicação estará rodando em http://localhost:8000.

<a id="rodando-os-testes"></a>

## 🧪 Rodando os Testes
Para garantir a qualidade e a integridade do código, o projeto conta com testes e ferramentas de análise.

**Rodar a suíte de testes (PHPUnit):**
- Docker:
    ```bash
    docker compose exec app php artisan test
    ```
- Localmente:
    ```bash
    php artisan test
    ```

**Rodar a análise estática de código (PHPStan):**
- Docker:
    ```bash
    docker compose exec app composer stan
    ```
- Localmente:
    ```bash
    composer stan
    ```

<a id="documentacao-da-api"></a>

## 📖 Documentação da API

### Endpoint Principal: Realizar Transferência
*Para fins de desenvolvimento, essa rota não requer autenticação*

**POST** `/api/transfer`

#### Headers
```json
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
        "value": 50.50,
        "payee": 2,
        "payer": 4
}
```

#### Exemplo de Resposta de Sucesso (201 Created)
```json
{
        {
                "id": 1,
                "value": 50.5,
                "payer": 4,
                "payee": 2,
                "status": "COMPLETED",
                "created_at": "2025-09-09T16:44:00.000000Z"
                "updated_at": "2025-09-09T16:44:00.000000Z"
        }
}
```

#### O pagador não possui saldos o suficiente para realizar a transação (409 Conflict)
```json
{
        "error": true,
        "message": "Insufficient balance to complete this transaction.",
        "code": 409
}
```

#### A carteira do pagador é do tipo lojista (403 Forbidden)
```json
{
        "error": true,
        "message": "Merchant accounts cannot initiate transactions.",
        "code": 403
}
```

#### A transação não foi aprovada pelo serviço autorizador (401)
```json
{
        "error": true,
        "message": "Transaction not authorized by payment service.",
        "code": 401
}
```
<a id="dados-teste"></a>

### Dados para teste
O seeder cria 20 carteiras com as seguintes convenções:
- IDs 1–10: carteiras de usuários (wallet_type: customer)
- IDs 11–20: carteiras de lojistas (wallet_type: merchant)
- O saldo inicial das carteiras variam entre 0 e 10000

Observações e comandos úteis:
- Execute o seeder (caso ainda não tenha feito): 
    - Docker: `docker compose exec app php artisan migrate --seed`
    - Local: `php artisan migrate --seed`

<a id="modelagem-do-banco-de-dados"></a>

## Modelagem do Banco de Dados
A modelagem do banco de dados foi projetada para ser simples, eficiente e atender aos requisitos.

### Estrutura das Tabelas
*Nos trechos que possuem ENUM, interprete que o Laravel realiza o controle dos tipos e não uma ENUM a nível de banco de dados*

#### Tabela users (usuários)
Armazena as informações dos usuários.

```sql
- id (Primary Key)
- name (String: nome completo do usuário)
- document_type (Enum: CPF para pessoa física, CNPJ para pessoa jurídica)
- document_number (String unique: número do documento CPF/CNPJ)
- email (String unique: endereço de e-mail)
- password (String: senha criptografada)
- timestamps (created_at, updated_at)
```

#### Tabela wallets (Carteiras)
Armazena as informações das carteiras dos usuários, incluindo saldo e tipo (Usuário/Lojista).

```sql
- id (Primary Key)
- user_id (Foreign Key: referencia a tabela users)
- wallet_type (Enum: "customer" para usuário comum, "merchant" para lojista)
- balance (Decimal: saldo atual da carteira)
- timestamps (created_at, updated_at)
- (user_id, wallet_type) => Unique: Um mesmo usuário pode possuir 1 carteira de cada tipo
```

#### Tabela transactions (Transações)
Registra todas as transações realizadas no sistema.

```sql
- id (Primary Key)
- value (Decimal: valor da transação)
- payer_id (Foreign Key: referencia a tabela wallets - pagador)
- payee_id (Foreign Key: referencia a tabela wallets - recebedor)
- status (Enum: status da transação - "PENDING", "COMPLETED", "FAILED_NO_FUNDS", "FAILED_UNAUTHORIZED", "FAILED_INVALID_WALLET_TYPE", "FAILED_UNKNOWN_REASON")
- timestamps (created_at, updated_at)
```

### Relacionamentos
- **Users ↔ Wallets:** Relacionamento um-para-muitos, onde um usuário pode possuir **uma** carteira de cada tipo (customer/merchant)
- **Wallets ↔ Transactions:** Relacionamento um-para-muitos, onde uma carteira pode participar de múltiplas transações, atuando tanto como pagadora (payer_id) quanto como recebedora (payee_id)

**Foreign Keys:**
- `user_id` na tabela wallets referencia `users.id`
- `payer_id` e `payee_id` na tabela transactions referenciam `wallets.id`

### Validações a Nível de Banco
- A tabela de users não permite a repetição de `document_number` e `email`
- A tabela de wallets não permite que um mesmo usuário tenha duas carteiras com um mesmo tipo

### Considerações e Melhorias

#### Design das entidades
- **Tipagem dos valores (balance e value):** evite usar tipos de ponto flutuante (float/double), pois eles introduzem imprecisão em operações com casas decimais. A alternativa recomendada: armazenar valores em unidades mínimas (ex.: centavos) como inteiros — solução mais segura para cálculos financeiros. O uso de DECIMAL se tornou mais simples de implementar;

- **Modelagem das carteiras:** Como ambos tipos de usuários compartilham os mesmos campos (name, email, document_number, etc.), controlar o tipo diretamente na tabela wallets (coluna wallet_type) é aceitável e simples. Porém, se cada tipo tiver atributos específicos, é preferível criar tabelas separadas (ex.: customers, merchants) e usar relacionamentos polimórficos ou similares para manter o modelo claro e flexível.

- **Modelagem das transações:** Em caso de adição de outros tipos de transações(Pix, Crédito, Débito, etc.), pode ser viável a adição de um coluna ou até mesmo uma nova tabela para controlar a tipagem e campos específicos.

<a id="decisoes-arquiteturais"></a>

## Decisões Arquiteturais

**Estrutura do projeto:** O código está organizado em Controllers, Actions, Services, Repositories e Models, adaptando a estrutura padrão do Laravel.

Desenho resumido da arquitetura (fluxo de uma requisição de transferência):

```text
Cliente (HTTP) (Route: api/transfer)
    |
    v
StoreTransactionController
    |
    v
ProcessTransactionAction --> AuthorizationGateway/Notification
    |
    v
+----------------------+
| Repositories           | ---> Models (Eloquent)
| (acesso a dados)     |        |
+----------------------+        v
                            Banco de Dados
```

**Lógica de Negócio - Single Action Controllers + Padrão Action + Repository:** 
A lógica de negócio foi encapsulada em classes Action com responsabilidade única (ex: ProcessTransactionAction e DebitWalletAction). Os Repositories abstraem a comunicação com o banco de dados e com o ORM.

**Services**: Os services para essa aplicação representam toda e qualquer lógica executada por um serviço externo (ex: aprovar a transação ou enviar notificação).

<a id="melhorias-futuras"></a>

## Melhorias Futuras
- Refatoração da arquitetura do projeto para Monólito Modular;
- Adicionar Event Sourcing para lidar com os estados das Transações e Carteiras;
- Implementar Locks para impedir condições de corrida entre múltiplas transações;
- Criação de providers para garantir o contrato com os serviços externos;
- Aumentar a cobertura de testes, especialmente para as actions fora do fluxo principal.