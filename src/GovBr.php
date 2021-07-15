<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

class GovBr extends GenericProvider
{
    final public function __construct(array $options = [], array $collaborators = [])
    {
        $production = self::productionEnvironment();
        if (!isset($options['urlAuthorize']) ||
            !isset($options['urlAccessToken']) ||
            !isset($options['urlResourceOwnerDetails'])
        ) {
            $options['urlAuthorize'] = $production['urlAuthorize'];
            $options['urlAccessToken'] = $production['urlAccessToken'];
            $options['urlResourceOwnerDetails'] = $production['urlResourceOwnerDetails'];
        }

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
        return new self(array_merge($options, self::stagingEnvironment()), $collaborators);
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
        return new self(array_merge($options, self::productionEnvironment()), $collaborators);
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        if (!isset($options['nonce'])) {
            $options['nonce'] = md5(uniqid('govbr', true));
        }

        return parent::getAuthorizationUrl($options);
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

    public function getScopeSeparator(): string
    {
        return '+';
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

    private static function productionEnvironment(): array
    {
        return [
            'urlAuthorize'            => 'https://sso.acesso.gov.br/authorize',
            'urlAccessToken'          => 'https://sso.acesso.gov.br/token',
            'urlResourceOwnerDetails' => 'https://sso.acesso.gov.br/userinfo',
        ];
    }

    private static function stagingEnvironment(): array
    {
        return [
            'urlAuthorize'            => 'https://sso.staging.acesso.gov.br/authorize',
            'urlAccessToken'          => 'https://sso.staging.acesso.gov.br/token',
            'urlResourceOwnerDetails' => 'https://sso.staging.acesso.gov.br/userinfo',
        ];
    }
}
