<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client;

final class Environment
{
    public static function production(): array
    {
        return [
           'urlAuthorize'            => 'https://sso.staging.acesso.gov.br/authorize',
           'urlAccessToken'          => 'https://sso.staging.acesso.gov.br/token',
           'urlResourceOwnerDetails' => 'https://sso.staging.acesso.gov.br/userinfo',
        ];
    }

    public static function staging(): array
    {
        return [
            'urlAuthorize'            => 'https://sso.staging.acesso.gov.br/authorize',
            'urlAccessToken'          => 'https://sso.staging.acesso.gov.br/token',
            'urlResourceOwnerDetails' => 'https://sso.staging.acesso.gov.br/userinfo',
        ];
    }

    private function __construct()
    {
    }
}
