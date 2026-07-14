# Развёртывание naryk.kz

На сервере уже работают два других проекта: один занимает 80/443, другой — 8080.
Этот проект: каталог `/var/www/naryk`, порт **8081**, база **`naryk`**.

## Чем этот проект отличается от памятки

**`php artisan migrate` не запускается — ни при первом развёртывании, ни при
деплое.** Схема принадлежит клиенту и меняться не должна. В `database/migrations/`
лежит только `.gitkeep`. Всё, что legacy-схема требует от кода, решено в моделях:

- `permissions.alias` (`NOT NULL` без DEFAULT) заполняет `App\Models\Permission`;
- `socialmedia.id` без `AUTO_INCREMENT` — id присваивает модель;
- булевы `yes`/`no`, `1`/`0`, `y`/`n` читает каст `App\Casts\Flag`.

**Очереди и сессии — файловые.** В дампе клиента нет таблиц `sessions`, `cache`,
`jobs`. Поэтому `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`.
Воркер очереди не нужен.

**`route:cache` не делаем** (как и в памятке). `config:cache` и `view:cache` — можно,
`env()` вне конфигов нет.

**npm не нужен.** Фронт — обычный CSS и ванильный JS в `public/assets/`.

---

## Первое развёртывание

### 1. Код

```bash
cd /var/www
git clone https://github.com/Shahiahmed/naryk.git naryk
cd naryk
git config --global --add safe.directory /var/www/naryk
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader
```

### 2. База

```bash
mysql -e "CREATE DATABASE IF NOT EXISTS naryk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'naryk_user'@'localhost' IDENTIFIED BY '<ПАРОЛЬ>';"
mysql -e "GRANT ALL PRIVILEGES ON naryk.* TO 'naryk_user'@'localhost'; FLUSH PRIVILEGES;"

# дамп клиента (45 МБ) заливаем с ноутбука
# scp database.sql "$SSH_USER@$SERVER:/root/"
mysql naryk < /root/database.sql

# проверка: должно быть 26 таблиц и 7988 постов
mysql naryk -e "SELECT COUNT(*) tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='naryk';"
mysql naryk -e "SELECT COUNT(*) posts FROM posts;"
```

`migrate` после импорта **не запускаем.**

### 3. Окружение

```bash
cp .env.example .env
php artisan key:generate
```

Правим `.env`:

```ini
APP_NAME="Naryk.kz"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://<IP-СЕРВЕРА>:8081     # позже https://naryk.kz

APP_LOCALE=kk
APP_FALLBACK_LOCALE=ru

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=naryk
DB_USERNAME=naryk_user
DB_PASSWORD=<ПАРОЛЬ>

# в базе клиента нет таблиц sessions/cache/jobs
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

`APP_KEY` — новый. Зашифрованных данных в базе нет, старый ключ не нужен.

### 4. Storage клиента (1.37 ГБ, 20 117 файлов)

Папка `storage/app/public` **не в git** — там свой `.gitignore`. Заливаем отдельно,
один раз. `rsync` на Windows нет, но есть `ssh`, `scp` и встроенный `tar`.

Копировать 20 тысяч файлов по одному через `scp` — часы. Пакуем в один архив.

Архив не сжимаем: там JPEG и PNG, они уже сжаты — `gzip` съест время и ничего не даст.

Адрес сервера и пользователя держим в переменных — этот файл лежит в публичном
репозитории.

**Вариант А — потоком, без временного файла. Git Bash:**

```bash
SERVER=<IP-СЕРВЕРА>
SSH_USER=<ПОЛЬЗОВАТЕЛЬ>

cd /c/путь/к/naryk/project/storage/app
tar -cf - public | ssh "$SSH_USER@$SERVER" \
  "mkdir -p /var/www/naryk/storage/app && tar -xf - -C /var/www/naryk/storage/app"
```

Ничего не занимает на диске ни здесь, ни там. Пароль спросит один раз.

**Вариант Б — через файл, если поток оборвётся. PowerShell:**

```powershell
cd путь\к\naryk\project\storage\app
tar -cf storage-public.tar public
scp storage-public.tar <ПОЛЬЗОВАТЕЛЬ>@<IP-СЕРВЕРА>:/root/
scp ..\..\..\database.sql <ПОЛЬЗОВАТЕЛЬ>@<IP-СЕРВЕРА>:/root/
```

```bash
# на сервере
df -h /var/www          # нужно ~3 ГБ: архив + распакованное
cd /var/www/naryk/storage/app
tar -xf /root/storage-public.tar
rm /root/storage-public.tar
```

> **Не перепутай оболочки.** В Git Bash `tar` — это GNU tar 1.35, и путь вида
> `C:/...` после `-f` он принимает за адрес удалённого хоста: `Cannot connect to C`.
> Там нужны пути `/c/...`. В PowerShell `tar` — это bsdtar от Microsoft, он
> понимает `C:\...` нормально. Потоковый вариант А от этого не страдает: там после
> `-f` стоит дефис.

Затем на сервере:

```bash
chown -R www-data:www-data /var/www/naryk/storage
php artisan storage:link
```

Проверка — файлов должно быть столько же:

```bash
find /var/www/naryk/storage/app/public -type f | wc -l   # 20117
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/storage/assets/logo.svg   # 200
```

### 4a. Лимиты загрузки PHP

Стоковый PHP на Ubuntu разрешает загрузку **2 МБ**. Фото с камеры весит 3–5 МБ, и
редакция получает молчаливый отказ — обложка просто не ставится.

```bash
cat > /etc/php/8.3/fpm/conf.d/99-naryk.ini <<'INI'
upload_max_filesize = 12M
post_max_size = 16M
memory_limit = 256M
max_execution_time = 120
INI

systemctl reload php8.3-fpm

# проверка
php-fpm8.3 -i | grep -E 'upload_max_filesize|post_max_size'
```

`client_max_body_size 100M` в nginx уже стоит — он не был ограничением.

### 5. Сборка, права, nginx

```bash
bash deploy.sh          # публикует ассеты Filament, кеширует конфиг, чинит права

cp deploy/nginx/naryk.conf /etc/nginx/sites-available/naryk
ln -s /etc/nginx/sites-available/naryk /etc/nginx/sites-enabled/naryk
nginx -t && systemctl reload nginx
```

### 6. Проверка

```bash
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/            # 200
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/admin/login # 200
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/about       # 200
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/joq-bet     # 404
```

Соседние сайты не тронуты:

```bash
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8080/   # соседний проект
systemctl status nginx php8.3-fpm --no-pager | head -5
```

---

## Автодеплой

`.github/workflows/deploy.yml` дёргает `deploy.sh` по SSH при пуше в `main`.

Секреты репозитория (Settings → Secrets and variables → Actions): `SSH_HOST`,
`SSH_USER`, `SSH_PORT`, `SSH_KEY` — приватный ключ; публичный кладём в
`~/.ssh/authorized_keys` на сервере.

Значения здесь не приводятся: репозиторий публичный.

---

## Что нужно от клиента до боевого запуска

1. **Свежий `storage/app/public/images/2026/07/`** — 13 обложек и 3 аватара
   отсутствуют в переданном архиве. Без них карточки рисуются без картинки
   (это предусмотрено, но выглядит беднее).
2. **`FRHC` в `https://apps.naryk.kz/get-sum`** — сейчас сервис отдаёт 10 тикеров,
   Freedom среди них нет. Плюс логотип `FRHC.png`.
3. **Категория «Арнайы жобалар»** — левая колонка главной пуста, пока её не создадут
   в админке (слаг `arnayy-jobalar`).
4. **Включить размещение `home-horizontal`** — баннер в ленте, сейчас выключен.
5. **Сменить пароль `info@sait.kz`.**
