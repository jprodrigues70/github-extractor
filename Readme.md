# Automatização extremamente simples de envio de e-mails utilizando Gmail.

Esse script utiliza o Gmail, então você vai precisar autorizar a execução do script na conta que enviará os e-mails. Recomendo que não utilize sua conta pessoal de maneira alguma. Você vai ter que desativar a autenticação em 2 fatores, e depois liberar o acesso de 'less secure apps' aqui https://myaccount.google.com/u/0/lesssecureapps.

1- Faça sua busca por usuários no github, como por exemplo:

```
https://github.com/search?q=location%3ABrazil+language%3APHP&type=Users&ref=advsearch&l=PHP
```

2- Abra o console do navegador, e cole o código que está em `get-users.js`

3- No console do navegador, digite `find(n)`, onde n é o total mínimo de usuários que você deseja, ou seja, pode ter mais que n, mas não pode ter menos. Ex:

```
find(100)
```

4- Quando o total mínimo for atingido, ou acabarem as páginas, um arquivo CSV será baixado (`emails.csv`).

5- Mova o arquivo para a pasta `recipients-csv`, no projeto.

6- Copie o `.env-example` para um arquivo chamado `.env`

```
cp .env-example .env
```

7- Atualize o valor das variáveis do `.env` para os seus valores pessoais.

8- Rodar o docker

```
docker-compose build
docker-compose up -d
docker exec -it mailer_php /bin/bash
```

9- No bash do docker

```
php mail send
```

### Limpar tudo

Caso resolva mandar um novo tipo de e-mail para um novo conjunto de usuários, e evitar perdas, salve todos os dados do envio anterior num local seguro, e apague todos os arquivos de `sentlist-csv`, `recipients-csv`, e se desejar de `blacklist-csv`.

### Blacklist

Você pode preencher sua blacklist de e-mails em `blacklist-csv/blacklist.csv`. Insira apenas e-mails, e separe-os com enter.

### E-mails enviados

Os endereços para os quais e-mails já foram enviados ficam guardados em `sentlist-csv`, evitando assim envios duplicados

### Log

E-mails que deram erro de imediato são registrados em `log`
