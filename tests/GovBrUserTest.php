<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client\Test;

use BrenoRoosevelt\OAuth2\Client\Avatar;
use BrenoRoosevelt\OAuth2\Client\GovBrUser;
use League\OAuth2\Client\Token\AccessToken;

class GovBrUserTest extends TestCase
{
    public function userData(): array
    {
        return [
            'sub' => '99999999999',
            'name' => 'Cidadao Brasileiro',
            'email' => 'email@domain.com',
            'phone_number' => '33999999999',
            'phone_number_verified' => 0,
            'email_verified' => 1,
            'picture' => 'https://localhost/avatar',
            'profile' => 'https://localhost/userinfo'
        ];
    }
    /** @test */
    public function deveCriarUsuarioComDadosDoArray()
    {

        $accessToken = new AccessToken(['access_token' => 'token']);
        $userData = $this->userData();
        $govBrUser = new GovBrUser($userData, $accessToken);

        $id = '99999999999';
        $this->assertEquals($id, $govBrUser->getId());
        $this->assertEquals($id, $govBrUser->getCpf());
        $this->assertEquals('Cidadao Brasileiro', $govBrUser->getName());
        $this->assertEquals('email@domain.com', $govBrUser->getEmail());
        $this->assertEquals('https://localhost/avatar', $govBrUser->getAvatarUrl());
        $this->assertEquals('33999999999', $govBrUser->getPhoneNumber());
        $this->assertEquals('https://localhost/userinfo', $govBrUser->getProfile());
        $this->assertFalse($govBrUser->phoneNumberVerified());
        $this->assertTrue($govBrUser->emailVerified());
        $this->assertArrayHasKey('cpf', $govBrUser->toArray());
    }
}
