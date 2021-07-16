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

require __DIR__ . '/vendor/autoload.php';

// Para executar siga o roteiro em "staging.md"

// Provider GovBr (homologacao)
$govBr = GovBr::staging([
    'clientId'            => 'XXXXXXXX', // Client ID fornecido pelo GovBr
    'clientSecret'        => 'YYYYYYYY', // Senha fornecida pelo provedor GovBr
    'redirectUri'         => "https://seu-app-dominio.com.br/seu-oauth-login", // Url de redirecionamento cadastrada no GovBr
    'redirectUriLogout'   => "https://seu-app-dominio.com.br/seu-logout" // Url de redirecionamento logout
]);

$authorizationCodeFlow = new AuthorizationCodeFlow($govBr, new StateStorage());

// Despacha a requisição http (seu framework faz isso)
$response =
    Dispatcher::run([
            new Whoops(),
            new CallableHandler($authorizationCodeFlow)
        ],
        ServerRequestFactory::fromGlobals()
    );

(new SapiEmitter())->emit($response);