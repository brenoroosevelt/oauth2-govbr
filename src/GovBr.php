<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

class GovBr extends AbstractProvider
{
    const STAGING = 'https://sso.staging.acesso.gov.br';
    const PRODUCTION = 'https://sso.acesso.gov.br';

    use BearerAuthorizationTrait;

    protected $urlAuthorize;
    protected $urlAccessToken;
    protected $urlResourceOwnerDetails;
    protected $urlLogout;

    /** @var string */
    protected $redirectUriLogout = "";

    final public function __construct(array $options = [], array $collaborators = [])
    {
        list(
            $this->urlAuthorize,
            $this->urlAccessToken,
            $this->urlResourceOwnerDetails,
            $this->urlLogout
        ) = array_values(self::getEnvironment(GovBr::PRODUCTION));

        parent::__construct($options, $collaborators);
    }

    /**
     * Cria uma instância para ambiente de homologação
     *
     * @param array $options
     * @param array $collaborators
     * @return self
     */
    public static function staging(array $options, array $collaborators = []): self
    {
        $staging = new self($options, $collaborators);
        list(
            $staging->urlAuthorize,
            $staging->urlAccessToken,
            $staging->urlResourceOwnerDetails,
            $staging->urlLogout
        ) = array_values(self::getEnvironment(GovBr::STAGING));

        return $staging;
    }

    /**
     * Cria uma instância para ambiente de produção
     * Pode ser criado diretamente via construtor
     *
     * @param array $options
     * @param array $collaborators
     * @return self
     */
    public static function production(array $options, array $collaborators = []): self
    {
        return new self($options, $collaborators);
    }

    public function getDefaultScopes(): array
    {
        return [
            'openid',
            'email',
            'phone',
            'profile',
            'govbr_confiabilidades'
        ];
    }

    public function buildQueryString(array $params)
    {
        return http_build_query($params);
    }

    public function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function getAuthorizationParameters(array $options): array
    {
        if (!isset($options['nonce'])) {
            $options['nonce'] = md5(uniqid('govbr', true));
        }

        return parent::getAuthorizationParameters($options);
    }
    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GovBrUser($response, $token);
    }

    public function getAvatar(GovBrUser $govBrUser): ?Avatar
    {
        $request = $this->getAuthenticatedRequest(
            self::METHOD_GET,
            $govBrUser->getAvatarUrl(),
            $govBrUser->token()
        );

        $response = $this->getResponse($request);

        return
            new Avatar(
                (string) $response->getBody(),
                $response->getHeaderLine('Content-type')
            );
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->urlAuthorize;
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->urlAccessToken;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->urlResourceOwnerDetails;
    }

    public function getLogoutUrl(): string
    {
        if (empty($this->redirectUriLogout)) {
            throw new UnexpectedValueException("Parâmetro redirectUriLogout não foi definido");
        }

        $query  = $this->buildQueryString(['client_id'=>$this->clientId, 'post_logout_redirect_uri' => $this->redirectUriLogout]);
        return $this->appendQuery($this->urlLogout, $query);
    }

    /**
     * @param ResponseInterface $response
     * @param array|string $data
     * @throws IdentityProviderException
     * @see https://manual-roteiro-integracao-login-unico.servicos.gov.br/pt/stable/iniciarintegracao.html#resultados-esperados-ou-erros-do-acesso-ao-servicos-do-login-unico
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        $code = $response->getStatusCode();
        $errorResponse = ($code >= 400 && $code <= 599);

        if (isset($data['error']) || $errorResponse) {
            $error = $data['descricao'] ?? $data['error'] ?? (string) $response->getBody();
            if (!is_string($error)) {
                $error = var_export($error, true);
            }

            $errorCode = $data['codigo'] ?? $code;
            throw new IdentityProviderException($error, $errorCode, $data);
        }
    }

    final public static function getEnvironment(string $env): array
    {
        return [
            'urlAuthorize'            => $env . '/authorize',
            'urlAccessToken'          => $env . '/token',
            'urlResourceOwnerDetails' => $env . '/userinfo',
            'urlLogout'               => $env . '/logout',
        ];
    }
}
