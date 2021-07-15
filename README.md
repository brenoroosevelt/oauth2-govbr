# Cliente OAuth2 para Gov.br
[![CI Build](https://github.com/brenoroosevelt/oauth2-govbr/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/brenoroosevelt/oauth2-govbr/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/brenoroosevelt/oauth2-govbr/branch/main/graph/badge.svg?token=S1QBA18IBX)](https://codecov.io/gh/brenoroosevelt/oauth2-govbr) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/brenoroosevelt/habemus/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/brenoroosevelt/oauth2-govbr/?branch=main) 
[![Latest Version](https://img.shields.io/github/release/brenoroosevelt/oauth2-govbr.svg?style=flat)](https://github.com/brenoroosevelt/oauth2-govbr/releases) 
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE.md) 

Este pacote fornece suporte OAuth2 para Gov.br em PHP usando a biblioteca cliente para OAuth 2.0 do PHP League.

## Instalação
Via composer:

```bash
composer brenoroosevelt/oauth2-govbr 
```

### Exemplo de Uso
O uso é do mesmo jeito que se usa qualquer cliente OAuth do League.
```php
use BrenoRoosevelt\OAuth2\Client\GovBr;

$govBr = new GovBr([
    'clientId'     => 'XXXXXXXX', // Client ID fornecido pelo GovBr
    'clientSecret' => 'YYYYYYYY', // Senha fornecida pelo provedor GovBr
    'redirectUri'  => "https://seu-app-dominio.com.br/seu-login" // Url de redirecionamento
]);

// Obtendo a url de autorização
$urlAutorizacao = $this->govBr->getAuthorizationUrl();
$state = $this->govBr->getState();

// Obtendo o token de acesso
$authorizationCode = $_GET['code'];
$accessToken = 
       $govBr->getAccessToken(
            new AuthorizationCode(), 
            ['code' => $authorizationCode]
       );

// Obtendo mais informações do usuário
$govBrUser = $govBr->getResourceOwner($accessToken);         
$govBrUser->getName();
$govBrUser->getCpf();
$govBrUser->getAvatarUrl();
$govBrUser->getProfile();
$govBrUser->getPhoneNumber();
$govBrUser->phoneNumberVerified();
$govBrUser->getEmail();
$govBrUser->emailVerified();

// Obtendo o avatar do usuário
$avatar = $this->govBr->getAvatar($govBrUser);
if ($avatar !== null) {
    $avatar->image();
    $avatar->imageBase64();
    $avatar->mimeType();
    $avatar->toHtml(['width' => 60]);
}
```

### Ambientes
Por padrão, o ambiente será de _produção_, mas você pode escolher o ambiente de _**homologação**_ (staging) solicitando uma instância da seguinte forma:
```php
$govBr = GovBr::staging([
    'clientId'     => 'XXXXXXXX', // Client ID fornecido pelo GovBr
    'clientSecret' => 'YYYYYYYY', // Senha fornecida pelo provedor GovBr
    'redirectUri'  => "https://seu-app-dominio.com.br/seu-login" // Url de redirecionamento
]);
```

```bash
cd [app-name]
composer start
```

Or using Docker: 
```bash
cd [app-name]
docker-compose up -d
```
Depois disso, abra `http://localhost:8080` em seu browser.

## Requirements
Este pacote suporta as seguintes versões do PHP:

* PHP 8.0
* PHP 7.4
* PHP 7.3
* PHP 7.2
* PHP 7.1

## Exemplo de uso
Para exemplos de uso e código, confira o guia básico.

## Contribuindo

Para contribuir com esse projeto, por favor veja [nossas diretrizes](https://github.com/thephpleague/oauth2-client/blob/master/CONTRIBUTING.md).

## Licença
Este projeto está licenciado sob os termos da licença MIT. Consulte o arquivo [LICENSE](LICENSE.md) para entender os direitos e limitações da licença.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md