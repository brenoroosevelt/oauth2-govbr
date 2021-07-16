<?php
declare(strict_types=1);

use BrenoRoosevelt\OAuth2\Client\Example\AuthorizationCodeFlow;
use BrenoRoosevelt\OAuth2\Client\Example\StateStorage;
use BrenoRoosevelt\OAuth2\Client\GovBr;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Middlewares\Utils\CallableHandler;
use Middlewares\Utils\Dispatcher;
use Middlewares\Whoops;

require __DIR__ . '/../vendor/autoload.php';

/**
 * -----------------------------------------------------------------------
 * ROTEIRO PARA RODAR ESSE EXEMPLO NO LINUX:
 * -----------------------------------------------------------------------
 *
 * $ composer create-project brenoroosevelt/oauth2-govbr [nova-pasta]
 * -----------------------------------------------------------------------
 * $ cd nova-pasta
 * -----------------------------------------------------------------------
 * Inclua a seguinte linha em seu arquivo /etc/hosts:
 *
 * 127.0.1.1       seu-app-dominio.com.br
 * -----------------------------------------------------------------------
 * Obs: Desative qualquer serviço escutando as portas 80 e 443, e depois:
 *
 * $ docker-compose up -d
 * -----------------------------------------------------------------------
 * Altere as seguitens configurações (neste arquivo aqui):
 * "clientId", "clientSecret" e "redirectUri"
 * -----------------------------------------------------------------------
 * Abra o browser (https!):
 *
 * https://seu-app-dominio.com.br
 * -----------------------------------------------------------------------
 * Ao final, você terá um container docker rodando com Apache e PHP 8.0.
 * Não se preocupe com o roteamento dentro desse servidor, para facilitar,
 * qualquer caminho (rota) no servidor irá executar este arquivo index.php
 * -----------------------------------------------------------------------
 * Se precisar conferir o log do servidor:
 *
 * $ docker ps
 * $ docker logs <container_id> --follow
 * -----------------------------------------------------------------------
 */


/**
 * Provider GovBr
 * Atenção!:
 * - Os parâmetros abaixos são sigilosos, evite enviar esses valores para seu repositório git
 * - Ao invés de fixar no código, prefira obtê-los usando getenv(...)
 * - Em ambiente de produção use GovBr::production(...), ou use o construtor da classe
 */
$govBr = GovBr::staging([
    'clientId'            => 'XXXXXXXX', // Client ID fornecido pelo GovBr
    'clientSecret'        => 'YYYYYYYY', // Senha fornecida pelo provedor GovBr
    'redirectUri'         => "https://seu-app-dominio.com.br/seu-oauth-login", // Url de redirecionamento cadastrada no GovBr
    'redirectUriLogout'   => "https://seu-app-dominio.com.br/seu-oauth-login" // Url de redirecionamento cadastrada no GovBr
]);

// O fluxo está implementado nessa classe
$authorizationCodeFlow = new AuthorizationCodeFlow($govBr, new StateStorage());

/** Despacha a requisição http (Seu framework faz isso) */
$response =
    Dispatcher::run([
            new Whoops(),
            new CallableHandler($authorizationCodeFlow)
        ],
        ServerRequestFactory::fromGlobals()
    );

(new SapiEmitter())->emit($response);