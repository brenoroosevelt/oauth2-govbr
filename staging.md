# Testando em Ambiente de Homologação
Além do provider OAuth2 para Gov.br, disponibilizamos um servidor para você testar a integração em um ambiente de homologação. 
É muito importante que você conheça o [Roteiro de Integração do Login Único](https://manual-roteiro-integracao-login-unico.servicos.gov.br/pt/stable/index.html) do Gov.br, tenha preenchido o formulário e já tenha recebido as configurações referentes a sua aplicação.

### Requisitos
Você vai precisar de: 
* Ambiente Linux (com _super usuário_);
* Docker instalado;
* Composer v2 instalado.

Então vamos começar! São apenas **6 passos simples**.

#### 1. Baixar o projeto 
Baixe o projeto usando o Composer e subtituindo `[nova-pasta]` pelo nome do seu projeto.
```bash
$ composer create-project brenoroosevelt/oauth2-govbr [nova-pasta]
```
Entre na pasta
```bash
$ cd  [nova-pasta]
```
#### 2. Redirecior do domínio
Precisamos redirecionar o dominio da sua aplicação para `localhost`. Portanto, inclua a seguinte linha em seu arquivo `/etc/hosts`:
```
127.0.1.1       seu-app-dominio.com.br
```
#### 3. Inicializar o container docker
O Container vai construir um servidor usando portas `80` e `443`, por isso, antes de executar desative qualquer serviço escutando essas portas (por exemplo: seu servidor web local), e depois:
```bash
$ docker-compose up -d
```
#### 4. Inspecionar o log
Para inspecionar o log do container, execute o seguinte comando:
```bash
$ docker ps
```
Encontre o ID do container e depois execute:
```bash
$ docker logs <container_id> --follow
```

#### 5. Configurar 
Você precisa alterar as seguitens configurações no arquivo [run-example.php](run-example.php) disponível na raiz do projeto:
* `clientId`: identificador de seuaplicativo no Gov.br
* `clientSecret`: senha do aplicativo fornecida pelo Gov.br
* `redirectUri`: uma das urls de redirecionamento de login cadastrada no Gov.br para esta aplicação
* `redirectUriLogout`: uma das urls de redirecionamento de logout cadastrada no Gov.br para esta aplicação

#### 6. Testar no browser (com https!):
Abra no browser a sua aplicação `https://seu-app-dominio.com.br/`. 

Ao final, você terá um container docker rodando com Apache e PHP 8.0.

Isso deve funcionar porque este domínio foi redirecionado para `localhost` (no passo 2) e o container docker irá responder às solicitações.

Não se preocupe com o roteamento dentro desse servidor, para facilitar, qualquer caminho (rota) no servidor irá executar o arquivo `run-example.php`.

#### 7. Resultado esperado:

Se tudo estiver certo, após executar o último passo,você será direcionado ao site do Gov.br se autenticar, em seguida será redirecionado de volta para sua aplicação com os dados do usuário. Esse fluxo pode ser verificado no arquivo [AuthorizationCodeFlow.php](example/AuthorizationCodeFlow.php). Você deverá ver na tela um JSON com os dados do usuário autenticado.
