# Мини-сервис заявок в поддержку (Helpdesk Lite)

## Разворачивание проекта (dev)

Рекомендуется перед разворачиванием установить [Symfony CLI](https://symfony.com/doc/current/setup/symfony_cli.html) (локальный сервера разработки) — он позволяет более гибко работать с разными версиями PHP одновременно и даёт больше отладочной информации чем встроенный в PHP сервер разработки. Но развернуть проект можно и без Symfony CLI, если версия PHP 8.2+ (проект проверялся на версиях PHP 8.2.30 и 8.5.3).

1. Склонировать репозиторий

```bash
git clone https://github.com/npub/mti-helpdesk-lite.git
```

2. Установить зависимости

```bash
composer install
```

3. Настроить окружение (ENV)

Скопировать файл `.env.example` в `.env.local` и настроить переменные окружения в нём.

```env
DB_USER=root
DB_PASS=
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=test
APP_API_KEY=123
```

4. Создать структуру БД (ключ `--dump-sql` выдаст SQL для создания структуры БД вручную).

```bash
php bin/console doctrine:schema:create [--dump-sql]
```

Также запросы на создание БД можно найти в файлах `private/scheme.sql` (только структура) и в файле `private/data.sql` (структура и тестовые данные).

5. Запустить автотесты

```bash
symfony composer run test
```

или

```bash
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

## Примеры запросов

В адресе запросов необходимо использовать базовый адрес (протокол/домен/порт) из предыдущего шага.
См. примеры запросов в формате _Postman Collection_ в `private/*.json`.

### CURL
#### Создание заявки
```curl
curl -X "POST" "https://localhost:8000/api/v1/tickets" \
     -H 'X-API-KEY: 123' \
     -H 'Content-Type: text/plain; charset=utf-8' \
     -d $'{
  "title": "Не работает отчёт",
  "description": "При открытии 500 ошибка",
  "author_email": "user@company.local" 
}'
```

#### Получение списка заявок
```curl
curl "https://localhost:8000/api/v1/tickets?status=in_progress&page=1&per_page=10&q=%D0%BE%D1%82%D0%BA%D1%80%D1%8B%D0%B2%D0%B0%D0%B5%D1%82%D1%81%D1%8F&sort=-created_at" \
     -H 'X-API-KEY: 123'
```

#### Получение карточки заявки с комментариями.
```curl
curl "https://localhost:8000/api/v1/tickets/1" \
     -H 'X-API-KEY: 123'
```

#### Добавление комментария и смена статуса заявки
```curl
curl -X "POST" "https://localhost:8000/api/v1/tickets/1/events" \
     -H 'X-API-KEY: 123' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{
  "version": 4,
  "comment": {
    "message": "Заработало. Спасибо!",
    "author": "Иван"
  },
  "status": "closed"
}'
```
