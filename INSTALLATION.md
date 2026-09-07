# Installation guide

This guide covers a local install, the optional Discord and GeoIP integrations, and a production deployment on a Debian/Ubuntu VPS.

---

## 1. Local installation

### 1.1 Prerequisites

- PHP 8.3+ with the `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `curl`, `fileinfo` and `sqlite3` extensions
- Composer 2
- Node.js 20+ and npm

The `intl` extension is optional; without it, country names in the analytics page fall back to a short built-in list.

### 1.2 Install

```bash
git clone https://github.com/ArthurAugis/dotbio.git
cd dotbio
composer setup
```

`composer setup` runs the full bootstrap: dependencies, `.env`, application key, migrations, `storage:link` and the asset build.

### 1.3 Create the first profile

```bash
php artisan db:seed
```

This creates the admin user `admin@dotbio.local` / `password` and a starter profile with sample links.

> Change the password before exposing the site. Either sign in and use Discord OAuth instead, or run:
> ```bash
> php artisan tinker --execute="App\Models\User::first()->update(['password' => bcrypt('your-new-password')]);"
> ```

### 1.4 Run it

```bash
php artisan serve
```

- Public profile: <http://localhost:8000>
- Admin area: <http://localhost:8000/admin>

To run everything at once (server, queue, Vite, Discord bot):

```bash
composer dev
```

---

## 2. Using MySQL or PostgreSQL instead of SQLite

Edit `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dotbio
DB_USERNAME=dotbio
DB_PASSWORD=your-password
```

Create the database, then run:

```bash
php artisan migrate --force
php artisan db:seed
```

---

## 3. Discord integration (optional)

Two separate features share one Discord application:

| Feature | Needs |
| --- | --- |
| Sign in with Discord | client ID, client secret, redirect URI |
| Live presence card | bot token, privileged intents |

### 3.1 Create the application

1. Open the [Discord Developer Portal](https://discord.com/developers/applications) and click **New Application**.
2. Under **OAuth2**, copy the **Client ID** and **Client Secret**.
3. Under **OAuth2 → Redirects**, add:
   - `http://localhost:8000/auth/discord/callback` for local development
   - `https://your-domain.tld/auth/discord/callback` for production
4. Under **Bot**, click **Add Bot** and copy the **Token**.
5. Still under **Bot**, enable **Presence Intent**, **Server Members Intent** and **Message Content Intent**.

### 3.2 Configure the application

```dotenv
DISCORD_CLIENT_ID=your-client-id
DISCORD_CLIENT_SECRET=your-client-secret
DISCORD_REDIRECT_URI="${APP_URL}/auth/discord/callback"
DISCORD_BOT_TOKEN=your-bot-token
DISCORD_STATUS_SECRET=
```

Then:

```bash
php artisan config:clear
```

### 3.3 Invite the bot to a shared server

The gateway only reports the presence of users who share a server with the bot. Invite it to any server you are a member of:

```
https://discord.com/api/oauth2/authorize?client_id=YOUR_CLIENT_ID&permissions=0&scope=bot
```

### 3.4 Run the presence daemon

```bash
php artisan discord:bot
```

It opens a WebSocket connection to the Discord gateway, mirrors presence updates into the `profiles` table and reconnects automatically. Section 5.3 covers running it as a service.

### 3.5 Pushing status from an external bot (alternative)

If you prefer to push status from your own bot instead of running the daemon:

```bash
curl -X POST https://your-domain.tld/api/discord/update-status \
  -H "Content-Type: application/json" \
  -H "X-Bot-Secret: $DISCORD_STATUS_SECRET" \
  -d '{"discord_id":"123456789012345678","status":"online","activity":"Playing Valorant"}'
```

Set `DISCORD_STATUS_SECRET` to a random string. If left empty, the endpoint falls back to comparing against `DISCORD_BOT_TOKEN`. The endpoint is CSRF-exempt and rejects any request without a matching secret with a `401`.

---

## 4. GeoIP country analytics (optional)

Country data is resolved locally from a MaxMind GeoLite2 database. Nothing is sent to a third party.

1. Create a free account at [maxmind.com](https://www.maxmind.com/en/geolite2/signup).
2. Download **GeoLite2 Country** in the MMDB format.
3. Place the file at:

```
storage/app/geoip/GeoLite2-Country.mmdb
```

The file is gitignored on purpose - MaxMind's license does not allow redistribution. Without it, views are still recorded but no country breakdown appears.

Lookups are cached for 7 days per IP address. Loopback addresses are skipped.

---

## 5. Production deployment (Debian / Ubuntu VPS)

### 5.1 Deploy the code

```bash
sudo apt update
sudo apt install -y php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl php8.3-sqlite3 php8.3-intl nginx git unzip

cd /var/www
sudo git clone https://github.com/ArthurAugis/dotbio.git
cd dotbio

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.tld
```

Then:

```bash
php artisan migrate --force
php artisan storage:link
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> `config:cache` makes `.env` unreadable at runtime. Every value the app needs is already read through `config()`, so this is safe - but re-run it after every `.env` change.

### 5.2 File permissions

```bash
sudo chown -R www-data:www-data /var/www/dotbio/storage /var/www/dotbio/bootstrap/cache
sudo chmod -R 775 /var/www/dotbio/storage /var/www/dotbio/bootstrap/cache
```

### 5.3 Run the Discord bot as a service

Create `/etc/systemd/system/dotbio-bot.service`:

```ini
[Unit]
Description=DotBio Discord presence daemon
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/dotbio
ExecStart=/usr/bin/php artisan discord:bot
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now dotbio-bot
sudo systemctl status dotbio-bot
```

### 5.4 Scheduler and queue

Add the scheduler to the crontab of the `www-data` user:

```cron
* * * * * cd /var/www/dotbio && php artisan schedule:run >> /dev/null 2>&1
```

If you run the bot through systemd (5.3), remove the `discord:bot` entry from `routes/console.php` so the two do not compete.

The queue is only used for background work; a single worker is enough:

```bash
php artisan queue:work --tries=3
```

### 5.5 Nginx

```nginx
server {
    listen 80;
    server_name your-domain.tld;
    root /var/www/dotbio/public;

    index index.php;
    charset utf-8;
    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/dotbio /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d your-domain.tld
```

### 5.6 Upload limits

The customizer reads PHP's own limits to size its validation rules. To allow larger backgrounds and audio files, raise both values in `/etc/php/8.3/fpm/php.ini`:

```ini
upload_max_filesize = 64M
post_max_size = 64M
```

```bash
sudo systemctl restart php8.3-fpm
```

### 5.7 One-click updates

The admin area has an **Updates** page that compares the installed commit against
`main` on GitHub and applies new commits for you. An hourly scheduled check keeps
the badge in the sidebar accurate, so make sure the scheduler runs:

```bash
* * * * * cd /var/www/dotbio && php artisan schedule:run >> /dev/null 2>&1
```

Pressing **Update now** puts the site in maintenance mode, backs up the SQLite
database, then runs `git pull`, `composer install`, `npm ci`, `npm run build` and
`php artisan migrate --force`. The Updates page stays reachable during the run so
you can watch the log, and a failed update offers a one-click rollback to the
previous commit and database.

For this to work the web user must own the checkout and reach the tooling:

```bash
sudo chown -R www-data:www-data /var/www/dotbio
sudo -u www-data git -C /var/www/dotbio config --global --add safe.directory /var/www/dotbio
```

`git`, `composer` and `npm` must all be in that user's `PATH`. If you would
rather keep updates manual, disable the button entirely:

```env
DOTBIO_SELF_UPDATE_ENABLED=false
```

Installs that were not created with `git clone` cannot self-update; the page
detects this and tells you to update manually.

---

## 6. Troubleshooting

| Symptom | Cause and fix |
| --- | --- |
| `/` redirects to `/login` | No profile row exists. Run `php artisan db:seed`, or sign in and open the dashboard. |
| Uploaded images return 404 | `php artisan storage:link` was not run, or the symlink was lost during deployment. |
| Discord login fails with a redirect error | The redirect URI in the Developer Portal does not match `DISCORD_REDIRECT_URI` exactly, scheme and trailing slash included. |
| Presence stays offline | The bot shares no server with your account, or the privileged intents are disabled. Check `journalctl -u dotbio-bot -f`. |
| SSL errors from the bot on Windows | Certificate verification is relaxed only when `APP_ENV=local`. In production, install a valid CA bundle and set `curl.cainfo` in `php.ini`. |
| Analytics show no countries | `storage/app/geoip/GeoLite2-Country.mmdb` is missing, or the visitors are on loopback addresses. |
| Config changes have no effect | Run `php artisan config:clear`, then `php artisan config:cache` in production. |
| Update fails on "Pulling latest code" | The checkout has local modifications, or the web user cannot write to it. Run `git status` as that user and see section 5.7. |
| Update fails on composer or npm | Those binaries are not in the web user's `PATH`. Run the step manually over SSH, or set `DOTBIO_SELF_UPDATE_ENABLED=false`. |
| Update button never appears | The install is not a git checkout, self-update is disabled, or the scheduler is not running the hourly version check. |
