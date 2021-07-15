<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client\Example;

use BrenoRoosevelt\OAuth2\Client\GovBr;
use Laminas\Diactoros\Response\JsonResponse as Json;
use Laminas\Diactoros\Response\RedirectResponse as Redirect;
use League\OAuth2\Client\Grant\AuthorizationCode;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

/**
 * Exemplo do fluxo Authorization Code.
 */
final class AuthorizationCodeFlow
{
    private $govBr;
    private $stateStorage;

    public function __construct(GovBr $govBr, StateStorage $stateStorage)
    {
        $this->govBr = $govBr;
        $this->stateStorage = $stateStorage;
    }

    public function __invoke(Request $request): Response
    {
        $authorizationCode = $request->getQueryParams()['code'] ?? null;
        if (empty($authorizationCode)) { // Se não temos um código de autorização, vamos obter um
            $url = $this->govBr->getAuthorizationUrl();
            $this->stateStorage->store($this->govBr->getState());
            return new Redirect($url); // redireciona o usuário para obter a autorização
        }

        $state = $request->getQueryParams()['state'] ?? null;
        if (empty($state) || !$this->stateStorage->has($state)) { // Possível ataque CSRF em andamento
            $this->stateStorage->clear();
            return new Json(['error' => 'Invalid state'], 401);
        }

        // Obtém o Access Token usando o Authorization Code
        try {
            $accessToken = $this->govBr->getAccessToken(new AuthorizationCode(), ['code' => $authorizationCode]);
        } catch (Throwable $e) {
            return new Json(['error' => $e->getMessage()], 401); // Erro ao obter o Access Token
        }

        // \o/ ... Já temos o Access Token ===> $accessToken

        // Opcionalmente você pode requisitar mais informações do usuário
        $userGovBr = $this->govBr->getResourceOwner($accessToken);

        // Você pode ainda solicitar o avatar (foto)
        // $avatar = $this->govBr->getAvatar($userGovBr);

        return new Json($userGovBr->toArray()); // Sua aplicação decide o que fazer com os dados obtidos
    }
}
