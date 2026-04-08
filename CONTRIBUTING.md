# Contributing to Reviactyl
> Thank you for considering contributing to Reviactyl <3 <br />
> I hope we could build the future together ;)

Welcome to Reviactyl Project! You can ask for guidance anytime on our [Discord Server]() in the [`#modding-and-api`](https://discord.com/channels/1378329228310741053/1487778044264452246) channel.

We are Excited to have here! This guide will help you on getting started with setting up the dev environment, understanding our coding standards, and making your first or next contribution.

## Getting Started
To start contributing to Reviactyl Panel, you need to have a basic understanding of the following:
- [PHP](https://www.php.net/) & [Laravel Framework](https://laravel.com/) `[Backend]`
- [TypeScript](https://www.typescriptlang.org/) + [React](https://react.dev/) `[Frontend]`

**Panel Structure**
- [FilamentPHP](https://filamentphp.com/) `[Admin Dashboard]`
- [React](https://react.dev/) `[Client Panel / User Dashboard]`

## Requirements
- PHP 8.2+ **(Recommended to use PHP 8.5)**
- Composer
- NodeJS 22+
- pnpm & corepack

## Setting up Environment Setup
1. [Fork the Repository](https://github.com/reviactyl/panel/fork)
2. Clone your forked Repository
3. Install PHP Dependencies using Composer `composer install`
4. Copy Environment (`cp -rf .env.example .env`)
5. Generate Encryption Key (`php artisan key:generate --force`)
6. Build the Frontend (`pnpm install && pnpm build`)
7. Create your First User (`php artisan p:user:make`)
8. Serve content (`php artisan serve`)

**You can also use Nginx/Apache to run Reviactyl Panel, there aren't any strict restrictions on setting up your environment**

You can follow reviactyl's production installation guide, but rather than installing tarball, you can clone the repository.

## Our Coding Standards
We use PHPStan (Larastan), and PHP-CS_Fixer (Pint) to enforce code styles & standards.

You can run Pint(PHP-CS_Fixer) via `./vendor/bin/pint` (Add `--fix` to fix the issues)

You can run PHPStan(Larastan) via `./vendor/bin/phpstan`

## Making Contributions

From your forked repository, make your own changes.

When you are ready, you can submit a pull request to the reviactyl/panel repository. If you still work on your pull request or need help with something make sure to mark it as Draft.

Once the Project Developers review your PR. It'll be merged to the `develop` branch.

Afterwards you can join our discord and request `Contributors` role.

## Translations

For Translations, please use crowdin; https://translate.reviactyl.dev/

You can Request Translation via joining our discord, or using crowdin to.

Translators get `translator` role in our discord server.

## Security

If you've found what you believe is a security issue please email `maintainers@reviactyl.dev`. Please check
[SECURITY.md](/SECURITY.md) for additional details.

### Contact Us

You can find us in a couple places online. First and foremost, we're active right here on GitHub. If you encounter a
bug or other problems, open an issue on here for us to take a look at it. We also accept feature requests here as well.

You can also find us on [Discord](https://reviactyl.app/discord).
