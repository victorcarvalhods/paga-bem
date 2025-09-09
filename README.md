# PagaBem API

API RESTful desenvolvida com o intuito de simular as operações básicas de uma plataforma de pagamentos, permitindo transferências de valores entre usuários comuns e lojistas.

## ✨ Features

- [x] Transferência de valores entre usuários.
- [x] Validação de regras de negócio (lojistas não podem enviar, saldo insuficiente, etc.).
- [x] Integração com serviço externo de autorização de transações.
- [x] Envio de notificação assíncrona após uma transferência bem-sucedida.
- [x] Ambiente de desenvolvimento totalmente containerizado com Docker.
- [x] Cobertura de testes para a lógica de negócio principal.

## 🚀 Tecnologias Utilizadas

- **PHP 8.4**
- **Laravel 12**
- **MySQL**
- **Redis** (para o sistema de filas)
- **Docker & Docker Compose**
- **PHPUnit** (para testes)
- **PHPStan** (para análise estática de código)

---

## 💻 Como Executar o Projeto

Siga os passos abaixo para configurar e executar a aplicação em seu ambiente local.

### Opção 1: Execução com Docker (Recomendado)

#### Pré-requisitos

- **Docker**
- **Docker Compose**
- **Git**

#### Passos para Instalação

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/victorcarvalhods/paga-bem.git
    cd paga-bem
    ```

2.  **Configure o arquivo de ambiente:**
    ```bash
    cp .env.example .env
    ```
    *(Nenhuma alteração no arquivo `.env` é necessária para o ambiente local, as configurações padrão já apontam para os serviços do Docker.)*

3.  **Suba os containers do Docker:**
    ```bash
    docker-compose up -d --build
    ```

4.  **Instalação das dependências:**
    O próprio container instala as dependências necessárias. Porém se estiver buscando adicionar e instalar pacotes, pasta executar:
    ```bash
    docker-compose exec app composer require {nome_pacote}
    ```

5.  **Execute as migrations e seeders:**
    *Este passo irá criar as tabelas no banco de dados e popular com usuários de teste (comuns e lojistas).*
    ```bash
    docker-compose exec app php artisan migrate --seed
    ```

6.  **Pronto!** A aplicação estará rodando e acessível em `http://localhost:8000`.
    E o banco de dados pode ser acessado em `http://localhost:3306`.

### Opção 2: Execução sem Docker

#### Pré-requisitos

- **PHP 8.4+**
- **Composer**
- **MySQL 8.0+**
- **Redis** (opcional, mas recomendado para o sistema de filas)
- **Git**

#### Passos para Instalação

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/victorcarvalhods/paga-bem.git
    cd paga-bem
    ```

2.  **Instale as dependências:**
    ```bash
    composer install
    ```

3.  **Configure o arquivo de ambiente:**
    ```bash
    cp .env.example .env
    ```

4.  **Configure as variáveis de ambiente no arquivo `.env`:**
    ```env
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=pagabem
    DB_USERNAME=seu_usuario
    DB_PASSWORD=sua_senha
    
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
    ```

5.  **Gere a chave da aplicação:**
    ```bash
    php artisan key:generate
    ```

6.  **Execute as migrations e seeders:**
    ```bash
    php artisan migrate --seed
    ```

7.  **Inicie o servidor de desenvolvimento:**
    ```bash
    php artisan serve
    ```

8.  **Em outro terminal, execute o worker das filas:**
    ```bash
    php artisan queue:work
    ```

9.  **Pronto!** A aplicação estará rodando em `http://localhost:8000`.

---

## 🧪 Rodando os Testes

Para garantir a qualidade e a integridade do código, o projeto conta com uma suíte de testes e ferramentas de análise.

- **Rodar a suíte de testes (PHPUnit):**
    ```bash
    docker-compose exec app php artisan test
     ```

- **Rodar a análise estática de código (PHPStan):**
    ```bash
    docker-compose exec app composer stan
    ```

    ---

## 📖 Documentação da API

### Endpoint Principal: Realizar Transferência

Para fins de desenvolvimento, essa rota não possui requer autenticação

**POST /api/transfer**

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
        "data": {
                "id": 1,
                "value": 50.5,
                "payer": 4,
                "payee": 2,
                "created_at": "2025-09-09T16:44:00.000000Z"
        }
}
```

#### O pagador não possui saldos o suficiente para realizar a transferência (409 Conflict)
```json
{
        "error": true,
        "message": "Insufficient balance to complete this transfer.",
        "code": 409
}
```

#### Pagador Possui carteira do tipo lojista (403 Forbidden)
```json
{
        "error": true,
        "message": "Merchant accounts cannot initiate transfers.",
        "code": 403
}
```

#### A transferência não foi aprovada pelo serviço autorizador (401)
```json
{
        "error": true,
        "message": "Transfer not authorized by payment service.",
        "code": 401
}
```

---

## 🏛️ Decisões Arquiteturais

Durante o desenvolvimento, algumas decisões foram tomadas para garantir que o código fosse limpo, manutenível, testável e seguro.

**Arquitetura - Estrutura Padrão do Laravel:** Optei por seguir a arquitetura padrão do Laravel, organizando o código em Controllers, Actions, Services, Repositories e Models dentro da estrutura convencional do framework. Essa escolha surgiu por ser a arquitetura que mais possuo familiaridade Laravel e facilita a manutenção e evolução do código.

**Lógica de Negócio - Single Action Controllers + Padrão Action + DTOs + Repository:** A lógica de negócio foi encapsulada em classes Action com responsabilidade única (ex: `ProcessTransferAction`, `EnsurePayerCanTransferAction`), tornando o código mais explícito, legível e fácil de testar. Os Repositories foram utilizados para abstrair a interação com a base de dados, enquanto os Data Transfer Objects (DTOs) garantem contratos de dados claros e imutáveis entre as diferentes camadas da aplicação, promovendo maior segurança de tipos e manutenibilidade.

**Serviços Externos - Padrão Gateway com Interfaces:** Optei por abstrair as chamadas para os serviços externos (autorizador e notificador) através de Interfaces (`...GatewayInterface`). Isso desacopla a aplicação de implementações concretas e facilita os testes, permitindo o uso de implementações Fake em ambiente de teste e Http em produção. Essa abordagem permite alterar as entidades de serviço se for necessário futuramente, utilizando Injeção de Dependência e o Service Container do Laravel.

**Notificações Assíncronas:** O sistema de notificações foi implementado de forma assíncrona utilizando Jobs e Listeners do Laravel, garantindo que operações instáveis não impactem na performance da API. Essa abordagem permite que a aplicação responda imediatamente ao usuário, enquanto o processamento da notificação ocorre em background através do sistema de filas. Para aumentar a confiabilidade, foram configurados mecanismos de retry e backoff, assegurando que falhas temporárias na comunicação com o serviço de notificação sejam tratadas com novas tentativas automáticas antes de considerar a operação como falhada definitivamente.


## 🔮 Próximos Passos (Melhorias Futuras)

- Criar um Handler de exceções mais robusto para padronizar todos os formatos de erro da API.
- Refatoração da arquitetura do projeto para Monolito Modular.
- Aumentar a cobertura de testes, especialmente para as actions fora do fluxo principal."