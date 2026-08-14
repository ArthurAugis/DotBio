# Contributing

Thanks for taking the time to contribute. This document covers how to get set up, what the code should look like, and how changes are reviewed.

## Getting started

```bash
composer setup
php artisan db:seed
composer dev
```

See [INSTALLATION.md](INSTALLATION.md) for the optional Discord and GeoIP setup.

## Before opening a pull request

```bash
composer test    # the suite must be green
composer lint    # Pint must report no changes
```

Pull requests that fail either check will not be reviewed until they pass.

## Code style

Formatting is enforced by [Laravel Pint](https://laravel.com/docs/pint) using the `laravel` preset plus the rules in `pint.json`. Run `vendor/bin/pint` before committing.

Beyond formatting:

- Every PHP file declares `strict_types=1` and type-hints its arguments and return values.
- Controllers stay thin. Validation belongs in a `FormRequest`, reusable logic in `app/Services` or `app/Support`.
- Comments explain *why*, never *what*. Code that needs a comment to be readable should be rewritten instead.
- Eloquent relations are typed (`BelongsTo`, `HasMany`, …) and eager-loaded to avoid N+1 queries.
- New profile options need a migration, an entry in `Profile::$fillable`, a validation rule and a customizer control. An option that is stored but never rendered is dead weight.

## Tests

Tests use [Pest](https://pestphp.com) and live in `tests/Feature`. Add a test for any behavior change - a bug fix should come with a test that fails without it.

```bash
php artisan test --filter=DiscordStatus
```

## Commit messages

Follow [Conventional Commits](https://www.conventionalcommits.org):

```
feat(customizer): add per-link hover glow
fix(analytics): ignore malformed country codes
docs(install): document the GeoLite2 download
refactor(bot): extract the WebSocket frame codec
test(seo): cover invalid robots directives
chore(deps): bump tailwindcss to 4.1
```

Types: `feat`, `fix`, `docs`, `refactor`, `test`, `perf`, `build`, `ci`, `chore`.

## Pull requests

- One logical change per pull request.
- Describe what changed and why. Screenshots or a short clip are welcome for anything visual.
- Note any migration, `.env` key or manual step a maintainer has to run.

## Reporting bugs

Open an issue with the PHP version, the database driver, the steps to reproduce, what you expected, and what happened instead. Include the relevant lines from `storage/logs/laravel.log` - with tokens and secrets removed.

## Security

Do not open a public issue for a security problem. Report it privately to the maintainer instead.
