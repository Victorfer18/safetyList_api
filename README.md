## API para Inspeção de Segurança em Edifícios

### Intenção da API

A API para Inspeção de Segurança em Edifícios foi desenvolvida para facilitar a coleta, armazenamento e análise de dados relacionados a inspeções de segurança em edifícios. Esta API fornece endpoints para registrar relatórios de inspeção, consultar históricos de inspeção e gerar estatísticas sobre as condições de segurança dos edifícios inspecionados.

# CodeIgniter 4 Application Starter

## O que é CodeIgniter?

CodeIgniter é um framework PHP full-stack para web que é leve, rápido, flexível e seguro.
Mais informações podem ser encontradas no [site oficial](https://codeigniter.com).

Este repositório contém um aplicativo inicial instalável via composer.
Ele foi construído a partir do
[repositório de desenvolvimento](https://github.com/codeigniter4/CodeIgniter4).

Mais informações sobre os planos para a versão 4 podem ser encontradas em [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) nos fóruns.

O guia do usuário correspondente à última versão do framework pode ser encontrado
[aqui](https://codeigniter4.github.io/userguide/).

## Instalação e atualizações

`composer create-project codeigniter4/appstarter` e então `composer update` sempre que
houver um novo lançamento do framework.

Ao atualizar, verifique as notas de lançamento para ver se há alguma mudança que você possa precisar aplicar
na sua pasta `app`. Os arquivos afetados podem ser copiados ou mesclados de
`vendor/codeigniter4/framework/app`.

## Configuração

Copie `env` para `.env` e personalize para o seu aplicativo, especificamente o baseURL
e qualquer configuração de banco de dados.

## Mudança importante com index.php

`index.php` não está mais na raiz do projeto! Ele foi movido para dentro da pasta *public*,
para melhor segurança e separação de componentes.

Isso significa que você deve configurar seu servidor web para "apontar" para a pasta *public* do seu projeto, e
não para a raiz do projeto. Uma prática melhor seria configurar um host virtual para apontar para lá. Uma prática ruim seria apontar seu servidor web para a raiz do projeto e esperar entrar em *public/...*, pois o restante da sua lógica e o
framework ficarão expostos.

**Por favor** leia o guia do usuário para uma melhor explicação de como o CI4 funciona!

## Gerenciamento do Repositório

Usamos problemas do GitHub, em nosso repositório principal, para rastrear **BUGS** e para rastrear pacotes de trabalho de **DESENVOLVIMENTO** aprovados.
Usamos nosso [fórum](http://forum.codeigniter.com) para fornecer SUPORTE e discutir
PEDIDOS DE FUNCIONALIDADES.

Este repositório é um "distribuição", construído por nosso script de preparação de lançamento.
Problemas com ele podem ser levantados em nosso fórum, ou como problemas no repositório principal.

## Requisitos do Servidor

É necessária a versão PHP 7.4 ou superior, com as seguintes extensões instaladas:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

Além disso, certifique-se de que as seguintes extensões estão habilitadas no seu PHP:

- json (habilitado por padrão - não desative)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) se você planeja usar MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) se você planeja usar a biblioteca HTTP\CURLRequest

## Licença

Este projeto está licenciado sob a Licença MIT. Veja o arquivo [LICENSE](./LICENSE) para mais detalhes.

