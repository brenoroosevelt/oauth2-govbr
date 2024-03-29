<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client\Test;

use BrenoRoosevelt\OAuth2\Client\Avatar;
use BrenoRoosevelt\OAuth2\Client\GovBr;
use BrenoRoosevelt\OAuth2\Client\GovBrUser;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ResponseFactory;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

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

    /**
     * @test
     */
    public function construtorDeveGerarInstanciaAmbienteProducao()
    {
        $govBr = $this->newGovBr();
        $authUrl = $govBr->getBaseAuthorizationUrl();
        $accessTokenUrl = $govBr->getBaseAccessTokenUrl([]);

        $this->assertNotStrContainsStr('staging', $authUrl);
        $this->assertNotStrContainsStr('staging', $accessTokenUrl);
    }

    /**
     * @test
     */
    public function deveGerarInstanciaAmbienteProducao()
    {
        $govBr = GovBr::production([
            'clientId' => 'my_client_id',
            'clientSecret' => 'my_secret',
            'redirectUri' => 'my_redirect_uri'
        ]);

        $authUrl = $govBr->getBaseAuthorizationUrl();
        $accessTokenUrl = $govBr->getBaseAccessTokenUrl([]);

        $this->assertNotStrContainsStr('staging', $authUrl);
        $this->assertNotStrContainsStr('staging', $accessTokenUrl);
    }

    /**
     * @test
     */
    public function deveGerarUrlDetalheCorretamente(): void
    {
        $token = new AccessToken(['access_token' => 'mock_token']);
        $url = $this->newGovBr()->getResourceOwnerDetailsUrl($token);

        $this->assertEquals('https://sso.acesso.gov.br/userinfo', $url);
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
    public function podeSobrescreverEAdicionarParametrosAoGerarUrlAutorizacao(): void
    {
        $url = $this->newGovBr()->getAuthorizationUrl(['nonce'=> '112abc', 'p1' => 'v1', 'state' => 'st1']);
        $query = $this->urlQueryParams($url);

        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('nonce', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('p1', $query);
        $this->assertEquals('v1', $query['p1']);
        $this->assertEquals('112abc', $query['nonce']);
        $this->assertEquals('st1', $query['state']);
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

    /**
     * @test
     */
    public function deveGerarUmaInstanciaParaAmbienteHomologacao()
    {
        $govBrStaging = GovBr::staging([
            'clientId' => 'my_client_id',
            'clientSecret' => 'my_secret',
            'redirectUri' => 'my_redirect_uri'
        ]);

        $this->assertStrContainsStr('staging', $govBrStaging->getBaseAuthorizationUrl());
    }

    /**
     * @test
     */
    public function deveGerarComScopePadrao()
    {
        $url = $this->newGovBr()->getAuthorizationUrl();
        $query = $this->urlQueryParams($url);

        $this->assertStrContainsStr('email', $query['scope']);
        $this->assertStrContainsStr('profile', $query['scope']);
        $this->assertStrContainsStr('openid', $query['scope']);
        $this->assertStrContainsStr('govbr_confiabilidades', $query['scope']);
    }

    /**
     * @test
     */
    public function deveRetornarDadosDoUsuarios(): void
    {
        // arrange
        $response = [
            'sub' => '99999999999',
            'name' => 'Cidadao Brasileiro',
            'email' => 'email@domain.com',
            'phone_number' => '33999999999',
            'phone_number_verified' => 0,
            'email_verified' => 1,
            'picture' => 'https://localhost/avatar',
            'profile' => 'https://localhost/userinfo'
        ];

        $govBr = \Mockery::mock(GovBr::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        /** @phpstan-ignore-next-line */
        $govBr->shouldReceive('fetchResourceOwnerDetails')
            ->once()
            ->andReturn($response);

        // act
        $accessToken = new AccessToken(['access_token' => 'mock_token']);
        /** @var GovBrUser $govBrUser */
        /** @phpstan-ignore-next-line */
        $govBrUser = $govBr->getResourceOwner($accessToken);

        // assert
        $this->assertInstanceOf(ResourceOwnerInterface::class, $govBrUser);
        $this->assertEquals('99999999999', $govBrUser->getId());
        $this->assertEquals('99999999999', $govBrUser->getCpf());
        $this->assertEquals('Cidadao Brasileiro', $govBrUser->getName());
        $this->assertEquals('email@domain.com', $govBrUser->getEmail());
        $this->assertEquals('https://localhost/avatar', $govBrUser->getAvatarUrl());
        $this->assertEquals('33999999999', $govBrUser->getPhoneNumber());
        $this->assertEquals('https://localhost/userinfo', $govBrUser->getProfile());
        $this->assertFalse($govBrUser->phoneNumberVerified());
        $this->assertTrue($govBrUser->emailVerified());
        $this->assertArrayHasKey('cpf', $govBrUser->toArray());
        $this->assertSame($accessToken, $govBrUser->token());
//
        $userDataArray = $govBrUser->toArray();
        $this->assertArrayHasKey('sub', $userDataArray);
        $this->assertArrayHasKey('cpf', $userDataArray);
        $this->assertArrayHasKey('name', $userDataArray);
        $this->assertArrayHasKey('email', $userDataArray);
        $this->assertArrayHasKey('picture', $userDataArray);
        $this->assertArrayHasKey('phone_number', $userDataArray);
    }

    /**
     * @test
     */
    public function deveRetornarImagemDoAvatar(): void
    {
        $respose = Factory::createResponse(200);
        $respose = $respose->withAddedHeader('Content-type', 'image/jpeg');
        $respose->getBody()->write('img');

        $userGovBr = new GovBrUser([
            'sub' => '99999999999',
            'name' => 'Cidadao Brasileiro',
            'email' => 'email@domain.com',
            'phone_number' => '33999999999',
            'phone_number_verified' => 0,
            'email_verified' => 1,
            'picture' => 'https://localhost/avatar',
            'profile' => 'https://localhost/userinfo'
        ], new AccessToken(['access_token' => 'mock_token']));

        $govBr = \Mockery::mock(GovBr::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $request = (new RequestFactory())->createRequest('GET', 'https://localhost/avatar');

        /** @phpstan-ignore-next-line */
        $govBr->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->andReturn($request);

        /** @phpstan-ignore-next-line */
        $govBr->shouldReceive('getResponse')
            ->once()
            ->andReturn($respose);

        // act
        /** @var Avatar $avatar */
        /** @phpstan-ignore-next-line */
        $avatar = $govBr->getAvatar($userGovBr);

        // assert
        $this->assertInstanceOf(Avatar::class, $avatar);
        $this->assertEquals('image/jpeg', $avatar->mimeType());
        $this->assertEquals('img', $avatar->image());
    }

    /**
     * @test
     */
    public function deveAnalisarRespostaErroLancarExcecao()
    {
        $response = (new ResponseFactory())->createResponse(400);
        $data['error'] = ["invalid request"];
        $govBr = $this->newGovBr();
        $checkResponse = self::getMethod(GovBr::class, 'checkResponse');

        $this->expectException(IdentityProviderException::class);
        $checkResponse->invokeArgs($govBr, [$response, $data]);
    }

    /**
     * @test
     */
    public function deveObterUrlLogout()
    {
        $govBr = new GovBr([
            'redirectUriLogout' => 'https://meu-dominio.com/meu-logout'
        ]);

        $urlLogout = $govBr->getLogoutUrl();
        $this->assertStrContainsStr('meu-logout', $urlLogout);
        $this->assertStrContainsStr('\/logout', $urlLogout);
        $this->assertNotStrContainsStr('staging', $urlLogout);
    }

    /**
     * @test
     */
    public function deveObterUrlLogoutEmHomologacao()
    {
        $govBr = GovBr::staging([
            'redirectUriLogout' => 'https://meu-dominio.com/meu-logout'
        ]);

        $urlLogout = $govBr->getLogoutUrl();
        $this->assertStrContainsStr('meu-logout', $urlLogout);
        $this->assertStrContainsStr('\/logout', $urlLogout);
        $this->assertStrContainsStr('staging', $urlLogout);
    }

    /**
     * @test
     */
    public function deveObterErroQuandoPedirUrlLogoutSemRedirectLogoutUri()
    {
        $govBr = new GovBr([]);

        $this->expectException(\UnexpectedValueException::class);
        $urlLogout = $govBr->getLogoutUrl();
    }

    public function assertStrContainsStr($neddle, $haystack)
    {
        $this->assertTrue(preg_match(sprintf('/%s/', $neddle), $haystack) > 0);
    }

    public function assertNotStrContainsStr($neddle, $haystack)
    {
        $this->assertFalse(preg_match(sprintf('/%s/', $neddle), $haystack) > 0);
    }

    protected static function getMethod($className, $methodName): ReflectionMethod
    {
        $className = new ReflectionClass($className);
        $method = $className->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
