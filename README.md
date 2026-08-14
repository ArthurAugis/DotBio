# DotBio

A self-hosted, single-tenant bio page: one public profile, one admin area, no accounts to manage and no third-party service in the loop.

Built with Laravel 13, Tailwind CSS 4 and Alpine.js. It renders a customizable link-in-bio page with a live Discord presence card, an audio player, an entry screen, and privacy-friendly analytics stored in your own database.

## Features

- **Visual customizer** - a live preview editor for layout, colors, borders, glow, hover effects, fonts and per-element ordering. Everything is stored on a single `profiles` row.
- **Live Discord presence** - a long-running gateway daemon (`php artisan discord:bot`) mirrors your real Discord status, custom status, avatar decoration and clan tag onto the page. No Lanyard or external relay required.
- **Discord OAuth login** - sign in to the admin area with the same Discord account.
- **Social links** - reorderable links with per-link icon, color and hover effect, plus click tracking.
- **Analytics** - daily views and clicks with a country breakdown resolved locally from a MaxMind GeoLite2 database. No analytics vendor, no cookies.
- **SEO panel** - meta title, description, keywords, Open Graph image, robots directive and custom favicon.
- **Audio player** - optional background track with cover art, styled to match the profile.

## Requirements

| Requirement | Version |
| --- | --- |
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 20+ |
| Database | SQLite (default), MySQL 8+ or PostgreSQL 13+ |

Optional: a Discord application (for OAuth and the presence bot) and a MaxMind GeoLite2 Country database (for analytics country data). Both are optional - the app runs without them.

## Quick start

```bash
git clone https://github.com/ArthurAugis/dotbio.git
cd dotbio
composer setup
php artisan db:seed
php artisan serve
```

`composer setup` installs dependencies, creates `.env`, generates the application key, runs the migrations, links the storage disk and builds the front-end assets.

The seeder creates an admin account:

| Email | Password |
| --- | --- |
| `admin@dotbio.local` | `password` |

Change it immediately, and see [INSTALLATION.md](INSTALLATION.md) for Discord, GeoIP and production deployment.

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
- **The bot is a daemon, not a queue job.** `discord:bot` holds an open WebSocket connection to the Discord gateway and reconnects on failure. The scheduler re-runs it every minute with `withoutOverlapping()` so it self-heals after a crash.
- **Uploads live on the `public` disk** and are served through `php artisan storage:link`.

## License

MIT - see [LICENSE](LICENSE).
