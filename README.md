## Ambiente Docker

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

1. Clonar o repositório:
 `git clone https://github.com/nilbertooliveira/flights.git`
2. Acessar a pasta do projeto "docker" e rodar o comando:
	`docker-compose up -d`
3. Conectar a uma ferramenta de banco como o "DBeaver" e criar o database com o nome "flights":
```
Host: 10.5.0.4
Port: 3306
User: root
Pass: Nil#123@
```
4. Rodar o comando abaixo e pegar o {CONTAINER_ID} da image php_fpm:
`docker ps`
5. Acessar o container
`docker exec -it {CONTAINER_ID} /bin/bash`
6. Configurar a base de dados
```
php artisan migrate
php artisan passport:install
```


##Utilização das APIS
1. Efetuar o registro do usuário
2. Logar na api
3. Usar o token gerado no login e setar no campo superior direito "Authorize" no formato: Bearer {bearer}
4. Consumir a api flights
