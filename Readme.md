# Automatização extremamente simples de envio de e-mails utilizando Gmail.

Esse script utiliza o Gmail, então você vai precisar autorizar a execução do script na conta que enviará os e-mails. Recomendo que não utilize sua conta pessoal de maneira alguma. Você vai ter que desativar a autenticação em 2 fatores, e depois liberar o acesso de 'less secure apps' aqui https://myaccount.google.com/u/0/lesssecureapps.

1- Copie o `.env-example` para um arquivo chamado `.env`

```
cp .env-example .env
```

2- Faça sua busca por usuários no github, como por exemplo:

```
https://github.com/search?q=location%3ABrazil+language%3APHP&type=Users&ref=advsearch&l=PHP
```

3- Preencha a variável `GITHUB_SEARCH` do `.env` com a query de busca a partir da interrogação.

```
GITHUB_SEARCH="?q=location%3ABrazil+language%3APHP&type=Users&ref=advsearch&l=PHP"
```

4- Obtenha um token do Github. Para criar um token no GitHub, basta acessar https://github.com/settings/tokens.

5- Preencha a variável `GITHUB_TOKEN` do `.env` com o token que você obteve.

6- Atualize o valor das demais variáveis do `.env` para os seus valores pessoais.

8- Rodar o docker

```
docker-compose build
docker-compose up -d
docker exec -it mailer_php /bin/bash
```

9- No bash do docker

```
php mail explore-github
php mail send
```

### php mail explore-github

Esse comando vai buscar por novos e-mails no GitHub, filtrando sempre aqueles que estão na blacklist, ou na recipients-csv ou na sentlist-csv.

O comando aceita a opção `-m` na qual você determina o total mínimo de e-mails que você quer pegar. Assim, caso hajam resultados suficientes, ele só irá pegar e-mail até a página que ele pegou o e-mail número `-m`, podendo assim pegar mais que `-m`. Caso não hajam resultados suficientes, ficará com menos e-mails que `-m`. O padrão é 1000.

Ex:

```
php mail explore-github -m=50
```

Para saber mais:

```
php mail send --help
```

### php mail send

Esse comando vai enviar todos os e-mails que ainda não foram enviados de `recipients-csv` e que não estão na blacklist.

Para saber mais:

```
php mail send --help
```

### Limpar tudo

Caso resolva mandar um novo tipo de e-mail para um novo conjunto de usuários, e evitar perdas, salve todos os dados do envio anterior num local seguro, e apague todos os arquivos de `sentlist-csv`, `recipients-csv`, e se desejar de `blacklist-csv`.

### Blacklist

Você pode preencher sua blacklist de e-mails em `blacklist-csv/blacklist.csv`. Insira apenas e-mails, e separe-os com enter.

### E-mails enviados

Os endereços para os quais e-mails já foram enviados ficam guardados em `sentlist-csv`, evitando assim envios duplicados

### Log

E-mails que deram erro de imediato são registrados em `log`
