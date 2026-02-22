# Micro Api for tickets 

## Разворачивание проекта (dev)

Рекомендуется перед разворачиванием установить [Symfony CLI](https://symfony.com/doc/current/setup/symfony_cli.html) (локальный сервера разработки) — он позволяет более гибко работать с разными версиями PHP одновременно и даёт больше отладочной информации чем встроенный в PHP сервер разработки. Но развернуть проект можно и без Symfony CLI, если версия PHP 8.2+ (проект проверялся на версиях PHP 8.2.30 и 8.5.3).

1. Склонировать репозиторий

```bash
git clone https://github.com/npub/micro-api-for-tickets.git
```

2. Установить зависимости

```bash
composer install
```

3. Настроить окружение (ENV)

Скопировать файл `.env.example` в `.env.local` и настроить переменные окружения в нём.

DB_USER=root
DB_PASS=
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=test
APP_API_KEY=my_api_key

4. Создать структуру БД (ключ `--dump-sql` выдаст SQL для создания структуры БД вручную).

```bash
php bin/console doctrine:schema:create [--dump-sql]
```

Также запросы на создание структуры БД можно найти в файле `private/dump.sql`.

5. Запустить автотесты

```bash
symfony composer run test
или
composer run test
```

6. Запустить проект

Если в системе установлен Symfony CLI (рекомендованный вариант):

```bash
symfony server:start
```
После запуска консоль должна вывести похожую строку (порт выбирается автоматически из свободных):

```bash
 [OK] Web server listening
      The Web server is using PHP FPM 8.2.30
      http://127.0.0.1:8000
```

Иначе можно запустить проект командой (с текущей версией PHP системы):

```bash
php -S localhost:8000 -t public/
```

7. Примеры запросов

В адресе запросов необходимо использовать базовый адрес (протокол/домен/порт) из предыдущего шага.

