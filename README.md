# Cliente OAuth2 para Gov.br
[![CI Build](https://github.com/brenoroosevelt/oauth2-govbr/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/brenoroosevelt/oauth2-govbr/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/brenoroosevelt/oauth2-govbr/branch/main/graph/badge.svg?token=S1QBA18IBX)](https://codecov.io/gh/brenoroosevelt/oauth2-govbr) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/brenoroosevelt/habemus/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/brenoroosevelt/oauth2-govbr/?branch=main) 
[![Latest Version](https://img.shields.io/github/release/brenoroosevelt/oauth2-govbr.svg?style=flat)](https://github.com/brenoroosevelt/oauth2-govbr/releases) 
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE.md) 

Cliente OAuth2 para Gov.br.

## Instalação

```bash
composer brenoroosevelt/oauth2-govbr 
```


### Exemplo de Uso

```php
use BrenoRoosevelt\OAuth2\Client\GovBr;

$govBr =  new GovBr([
    'clientId'      => 'XXXXXXXX', // Client ID fornecido pelo GovBr
    'clientSecret'  => 'YYYYYYYY', // Senha fornecida pelo provedor GovBr
    'redirectUri'   => "https://seu-app-dominio.com.br/seu-oauth-login" // Url de redirecionamento cadastrada no GovBr
]);
```

## Exemplo de Uso


## Ambientes

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

### Linkss
 * Leia o [guia de integração](https://manual-roteiro-integracao-login-unico.servicos.gov.br/pt/stable/index.html) oficial.
