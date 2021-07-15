<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

final class GovBr extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        $options = array_merge($options, Environment::production());
        parent::__construct($options, $collaborators);
    }

    public static function stagingEnvironment(array $options): self
    {
        return new self(array_merge($options, Environment::staging()));
    }

    public static function productionEnvironment(array $options): self
    {
        return new self(array_merge($options, Environment::production()));
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        if (!isset($options['nonce'])) {
            $options['nonce'] = $this->getNonce();
        }

        return parent::getAuthorizationUrl($options);
    }

    private function getNonce(): string
    {
        return md5(uniqid('govbr', true));
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
}
