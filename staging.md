# Ambiente de Homologação

Disponibilizamos um servidor você testar a integração com o Gov.br em um ambiente de homologação. 
É muito importante que você conheça o guia oficial e já tenha preenchido o formulário e tenha recebido as configurações do seu ambiente.

### Requisitos
Você vai precisar de: 
* Ambiente Linux (com _super usuário_);
* Docker instalado;
* Composer v2 instalado.

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
Precisamos redirecionar o dominio de seu aplicativo para `localhost`. Portanto, inclua a seguinte linha em seu arquivo `/etc/hosts`:
```
127.0.1.1       seu-app-dominio.com.br
```
#### 3. Inicializar o container docker
O Container vai construir um servidor usando portas `80` e `443`, por isso, antes de executar desative qualquer serviço escutando essas portas (por exemplo: seu servidor web local), e depois:
```bash
$ docker-compose up -d
```
#### 4. Inspecionar o log
Para inspecionar o log do container,execute o seguinte comando:
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

Isso deve funcionar porque este caminho foi redirecionado para `localhost` e o container docker irá responder às solicitações.

Não se preocupe com o roteamento dentro desse servidor, para facilitar, qualquer caminho (rota) no servidor irá executar o arquivo `run-example`.php

#### 7. Resultado esperado:

Se tudo estiver certo, você será direcionado ao site do Gov.br para consentir e se autenticar, em seguida será redirecionado de volta para sua aplicação com os dados do usuário. Esse fluxo pode ser verificado no arquivo [AuthorizationCodeFlow.php](example/AuthorizationCodeFlow.php).