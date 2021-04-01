# Automatização extremamente simples de envio de e-mails, e nem um pouco preocupada com padrões de projeto ou padrões de código.

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

5- Mova o arquivo para a pasta do projeto, substituindo o arquivo original.

6- No `mail.php` preencha as variáveis $email, $password, $content, $from e $fromName.

7- Rodar o docker

```
docker-compose build
docker-compose up -d
docker exec -it mailer_php /bin/bash
```

8- No bash do docker

```
php mail.php
```
