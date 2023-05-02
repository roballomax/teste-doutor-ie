# Teste Doutor IE

Para iniciar o projeto, é necessário utlizar o seguinte comando:
```bash
./vendo/bin/sail up
```

Em seguida é necessário entrar na máquina do docker para executar as migrations, usando o comando
```bash
docker-compose exec laravel.test
php artisan migrate --seed
```

*Obs*:
Deixei alguns seeders para evitar o trabalho de criação de usuário ou população prévia para testar as listagens, o login e senha é:
test@example.com
123456

Junto também está um arquivo com dados do postman para impotar os endpoints para testes e o xml do teste em formato transcrito, ambos na raiz do projeto

É isso, espero que gostem :)