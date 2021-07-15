<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client\Test;

use BrenoRoosevelt\OAuth2\Client\GovBr;
use PHPUnit\Framework\TestCase;

class GovBrTest extends TestCase
{

    public function newGovBr()
    {
        return new GovBr([
            'clientId' => 'my_client_id',
            'clientSecret' => 'my_secret',
            'redirectUri' => 'my_redirect_uri'
        ]);
    }

    /**
     * Garante que a url de autorização contém os parâmetros obrigatórios
     *
     * @see https://manual-roteiro-integracao-login-unico.servicos.gov.br/pt/stable/iniciarintegracao.html#autenticacao
     * @test
     */
    public function urlAutorizacaoDeveConterParamatrosObrigatorios(): void
    {
        $url = $this->newGovBr()->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('nonce', $query);
        $this->assertArrayHasKey('state', $query);
    }
}
