<?php
declare(strict_types=1);

use BrenoRoosevelt\OAuth2\Client\GovBr;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\OAuth2\Client\Grant\AuthorizationCode;
use Middlewares\Utils\Dispatcher;
use Middlewares\Whoops;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require __DIR__ . '/../vendor/autoload.php';

/**
 * ----------------------------------------------------------------------
 * Guia para rodar esse exemplo no Linux:
 * ----------------------------------------------------------------------
 * $ composer create-project brenoroosevelt/oauth2-govbr [nova-pasta]
 * ----------------------------------------------------------------------
 * $ cd nova-pasta
 * ----------------------------------------------------------------------
 * Inclua em seu arquivo /etc/hosts a seguinte linha:
 *
 * 127.0.1.1       seu-app-dominio.com.br
 * ----------------------------------------------------------------------
 * $ docker-compose up -d
 * ----------------------------------------------------------------------
 * Neste arquivo, altere as seguitens configurações
 * "clientId", "clientSecret" e "redirectUri"
 * ----------------------------------------------------------------------
 * Abra o browser (com https):
 *
 * https://seu-app-dominio.com.br
 * ----------------------------------------------------------------------
 */

/**
 * Criamos um ajudante para armazenar o 'state' na sessão
 * Você pode armazernar no Redis, ou qualquer outro lugar
 * Mas não ignore a validação do state.
 */
$stateStorage = new class {
    public function __construct() {
        @session_start();
    }

    public function store($v): void {
        $_SESSION['oauth'] = $v;
    }

    public function has($v): bool {
        return isset($_SESSION['oauth']) && $_SESSION['oauth'] === $v;
    }

    public function clear(): void {
        unset($_SESSION['oauth']);
    }
};

/**
 * Criamos uma instância do Provider GovBr
 * Observação:
 *     - Os parâmetros abaixos são sigilosos, evite comitar os valores no seu repositório
 *     - Prefira obter os valores abaixo usando getenv(...) ao invés de fixar no código
 */
$govBr =  new GovBr([
    'clientId'      => 'XXXXXXXX', // Client ID fornecido pelo GovBr
    'clientSecret'  => 'YYYYYYYY', // Senha fornecida pelo provedor GovBr
    'redirectUri'   => "https://seu-app-dominio.com.br/seu-oauth-login" // Url de redirecionamento cadastrada no GovBr
]);

/**
 * Exemplo do fluxo Authorization Code.
 * O fluxo abaixo deve ser implementado dentro do controlador/classe responsável
 * pela rota informada na configuração acima ==> 'redirectUri'
 */
$authorizationCodeFlow =
    function(ServerRequestInterface $request) use ($govBr, $stateStorage) : ResponseInterface {

        $authorizationCode = $request->getQueryParams()['code'] ?? null;
        // Se não tivermos um código de autorização, vamos obter um
        if (empty($authorizationCode)) {
            $url = $govBr->getAuthorizationUrl();
            $stateStorage->store($govBr->getState());
            return new RedirectResponse($url); // redireciona o usuário para obter a autorização
        }

        $state = $request->getQueryParams()['state'] ?? null;
        // Possível ataque CSRF em andamento. Não ignore a validação do "state"
        if (empty($state) || !$stateStorage->has($state)) {
            $stateStorage->clear();
            return new JsonResponse(['error' => 'Invalid state'], 401);
        }

        // Tenta obter o access Access Token usando o Authorization Code
        try {
            $accessToken = $govBr->getAccessToken(new AuthorizationCode(), ['code' => $authorizationCode]);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401); // Erro ao obter o Access Token
        }

        $userGovBr = $govBr->getResourceOwner($accessToken); //Opcional: pedir informações do usuário
        // $avatar = $govBr->getAvatar($userGovBr);

        // Sua aplicação deve decidir o que fazer com o Access Token e os dados obtidos
        return new JsonResponse($userGovBr->toArray());
    };


// Despachamos a requisição http (seu framework faz isso para você)
$request  = ServerRequestFactory::fromGlobals();
$response = Dispatcher::run([new Whoops(), $authorizationCodeFlow], $request);
(new SapiEmitter())->emit($response);
