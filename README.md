# DotBio

A self-hosted, single-tenant bio page: one public profile, one admin area, no accounts to manage and no third-party service in the loop.

Built with Laravel 13, Tailwind CSS 4 and Alpine.js. It renders a customizable link-in-bio page with a live Discord presence card, an audio player, an entry screen, and privacy-friendly analytics stored in your own database.

## Features

- **Visual customizer** - a live preview editor for layout, colors, borders, glow, hover effects, fonts and per-element ordering. Everything is stored on a single `profiles` row.
- **Live Discord presence** - a long-running gateway daemon (`php artisan discord:bot`) mirrors your real Discord status, custom status, avatar decoration and clan tag onto the page. No Lanyard or external relay required.
- **Flexible sign in** - claim the admin area with Discord, Google, GitHub, Twitch, Steam, Spotify, Patreon or an email and password. Once claimed, only the owner's sign in method is offered. Email accounts get email verification and password reset.
- **Social links** - reorderable links with per-link icon, color and hover effect, plus click tracking.
- **Analytics** - daily views and clicks with a country breakdown resolved locally from the free DB-IP country database, downloaded and refreshed automatically. No analytics vendor, no cookies.
- **SEO panel** - meta title, description, keywords, Open Graph image, robots directive and custom favicon.
- **Audio player** - optional background track with cover art, styled to match the profile.

## Requirements

| Requirement | Version |
| --- | --- |
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 20+ |
| Database | SQLite (default), MySQL 8+ or PostgreSQL 13+ |

Optional: OAuth applications for the sign in providers you want (Discord also powers the presence bot), an SMTP service such as Resend or Brevo (for email verification and password reset). The app runs without them.

## Quick start

```bash
git clone https://github.com/ArthurAugis/dotbio.git
cd dotbio
composer setup
php artisan db:seed
php artisan serve
```

`composer setup` installs dependencies, creates `.env`, generates the application key, runs the migrations, links the storage disk and builds the front-end assets.

The seeder creates a starter profile with sample links.

Open `/login` to claim the profile: the first account to sign in becomes its owner, either
through a configured OAuth provider or by creating an email and password account. See
[INSTALLATION.md](INSTALLATION.md) for the sign in providers, email delivery, Discord, GeoIP
and production deployment.

## Development

```bash
composer dev
```

Runs the PHP server, the queue worker, the Vite dev server and the Discord bot together.

```bash
composer test    # Pest test suite
composer lint    # Pint, check only
vendor/bin/pint  # Pint, apply fixes
```

## Project layout

```
app/
├── Console/Commands/DiscordBotWorker.php   Discord gateway daemon
├── Http/Controllers/Admin/                 Dashboard, customizer, links, analytics, SEO
├── Http/Controllers/Auth/                  OAuth, email login, registration, password reset, verification
├── Http/Requests/                          Form request validation
├── Models/                                 Profile, Link, Analytic, User
├── Services/DiscordApi.php                 Discord REST client
└── Support/                                Upload limits, WebSocket frame codec
resources/views/
├── profile.blade.php                       Public profile page
└── admin/                                  Admin area and customizer panels
```

## Architecture notes

- **Single tenant by design.** The public route always renders the first `Profile` row. Multi-user hosting is out of scope.
- **One owner, one sign in method.** The first account to sign in owns the profile. After that, the login page only shows the owner's method and every other account is refused. A provider only appears on the login page once its secret is set in `.env`.
- **The bot is a daemon, not a queue job.** `discord:bot` holds an open WebSocket connection to the Discord gateway and reconnects on failure. The scheduler re-runs it every minute with `withoutOverlapping()` so it self-heals after a crash.
- **Uploads live on the `public` disk** and are served through `php artisan storage:link`.

## License

MIT - see [LICENSE](LICENSE).
