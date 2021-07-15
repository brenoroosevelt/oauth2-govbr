<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class GovBrUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var AccessToken
     */
    protected $token;

    public function __construct(array $response, AccessToken $token)
    {
        $this->response = $response;
        $this->token = $token;
    }

    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->getResponseValue('sub');
    }

    /**
     * @return mixed|null
     */
    public function getCpf()
    {
        return $this->getResponseValue('sub');
    }

    /**
     * @return mixed|null
     */
    public function getName()
    {
        return $this->getResponseValue('name');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $response = $this->response;
        $response['cpf'] = $this->getCpf();
        return $response;
    }

    /**
     * @return mixed|null
     */
    public function getEmail()
    {
        return $this->getResponseValue('email');
    }

    /**
     * @return bool
     */
    public function emailVerified(): bool
    {
        return (bool) $this->getResponseValue('email_verified');
    }

    /**
     * @return mixed|null
     */
    public function getPhoneNumber()
    {
        return $this->getResponseValue('phone_number');
    }

    /**
     * @return bool
     */
    public function phoneNumberVerified(): bool
    {
        return (bool) $this->getResponseValue('phone_number_verified');
    }

    /**
     * @return mixed|null
     */
    public function getAvatarUrl()
    {
        return $this->getResponseValue('picture');
    }

    /**
     * @return mixed|null
     */
    public function getProfile()
    {
        return $this->getResponseValue('profile');
    }

    public function token(): AccessToken
    {
        return $this->token;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    private function getResponseValue(string $key)
    {
        return $this->response[$key] ?? null;
    }
}
