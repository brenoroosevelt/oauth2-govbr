# Como contribuir

Existem várias maneiras de ajudar:
* Crie um tíquete no GitHub (issue), se você encontrou um bug;
* Escreva teste unitários para tíquetes de bug abertos;
* Escreva patches para tíquetes de bug / novos recursos abertos, de preferência com casos de teste incluídos;
* Contribua com a documentação;

Existem algumas diretrizes que precisamos que os contribuidores sigam para que tenhamos um
chance de manter o controle das coisas.

## Começando

* Envie um tíquete para o seu problema, supondo que ainda não exista um.
* Descreva claramente o problema, incluindo etapas para reproduzi-lo quando for um bug.
* Certifique-se de preencher a versão mais antiga que você sabe que apresenta o problema.
* Faça um fork do repositório no GitHub.

## Fazendo mudanças

* Crie um ramo (branch) de onde você deseja basear seu trabalho.
* Faça commits de unidades lógicas.
* Use mensagens de commit descritivas e faça referência ao número da #issue.  
* Os testes unitários devem continuar passando.
* Seu trabalho deve aplicar nossos padrões de codificação.

## Enviando alterações

* Envie suas alterações para a branch em seu fork do repositório.
* Envie um Pull Request para o repositório com a branch de destino correta.

## Teste unitário e padrão de codificação
Certifique-se que todas as análises e testes continuam passado para todas as versões do PHP suportadas. Para executar todas as análises, use o seguinte comando:

```bash
composer check
```
