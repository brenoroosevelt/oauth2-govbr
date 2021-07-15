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

    public function urlQueryParams($url): array
    {
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        return $query;
    }

    /*
     * @test
     */
    public function construtorDeveGerarInstanciaAmbienteProducao()
    {
        $govBr = $this->newGovBr();
        $authUrl = $govBr->getBaseAuthorizationUrl();
        $accessTokenUrl = $govBr->getBaseAccessTokenUrl([]);

        $this->assertStringNotContainsString('staging', $authUrl);
        $this->assertStringNotContainsString('staging', $accessTokenUrl);
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
        $query = $this->urlQueryParams($url);

        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('nonce', $query);
        $this->assertArrayHasKey('state', $query);
    }

    /**
     * @test
     */
    public function deveGerarUmStateNaoVazio()
    {
        $govBr = $this->newGovBr();
        $govBr->getAuthorizationUrl();

        $this->assertNotEmpty($govBr->getState());
    }

    public function deveGerarUmaInstanciaParaAmbienteHomologacao()
    {
        $govBrStaging = GovBr::staging([
            'clientId' => 'my_client_id',
            'clientSecret' => 'my_secret',
            'redirectUri' => 'my_redirect_uri'
        ]);

        $this->assertStringContainsString('staging', $govBrStaging->getBaseAuthorizationUrl());
        $this->assertStringContainsString('staging', $govBrStaging->getBaseAccessTokenUrl([]));
    }

    /**
     * @test
     */
    public function deveGerarComScopePadrao()
    {
        $url = $this->newGovBr()->getAuthorizationUrl();
        $query = $this->urlQueryParams($url);

        $this->assertStringContainsString('email', $query['scope']);
        $this->assertStringContainsString('profile', $query['scope']);
        $this->assertStringContainsString('openid', $query['scope']);
        $this->assertStringContainsString('govbr_confiabilidades', $query['scope']);
    }
}
