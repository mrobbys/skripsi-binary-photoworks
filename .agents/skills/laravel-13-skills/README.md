# Laravel 13 Skills

AI skill package for Laravel 13 PHP 8 attributes - write new code and refactor old Laravel code to attribute syntax.

[![PHP Version](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-4DC143?style=flat)](LICENSE)
[![Total Attributes](https://img.shields.io/badge/Total-74%2B-4DC143?style=flat)](#)

## Description

This package provides AI agents with comprehensive knowledge of Laravel 13 PHP 8 attributes. It enables:

- **Writing new Laravel 13 code** using modern attribute syntax
- **Refactoring existing code** from property-based configuration to PHP 8 attributes
- Supporting all Laravel 13 components: Models, Jobs, Commands, Controllers, FormRequests, Factories, Testing, Notifications, Middleware, Events, and Container DI

## Installation

### Composer

```bash
composer require shamimstack/laravel-13-skills
```

### npm

```bash
npm install @shamimstack/laravel-13-skills
```

## Package Contents

| File              | Description                          |
| ----------------- | ------------------------------------ |
| `SKILL.md`        | Main AI skill definition             |
| `QUICKREF.md`     | Quick attribute conversion reference |
| `INDEX.md`        | Package index and overview           |
| `CHANGELOG.md`    | Version history                      |
| `CONTRIBUTING.md` | Contribution guidelines              |

### Examples (`src/Examples/`)

| File               | Component            | Examples |
| ------------------ | -------------------- | -------- |
| `Models.md`        | Eloquent Models      | 11       |
| `Jobs.md`          | Queue Jobs           | 10       |
| `Commands.md`      | Artisan Commands     | 7        |
| `Controllers.md`   | HTTP Controllers     | 6        |
| `FormRequests.md`  | Form Requests        | 7        |
| `Factories.md`     | Model Factories      | 2        |
| `Testing.md`       | Test Cases           | 5        |
| `Container.md`     | Dependency Injection | 11       |
| `Resources.md`     | API Resources        | 4        |
| `Events.md`        | Events & Listeners   | 5        |
| `Notifications.md` | Notifications        | 4        |
| `Middleware.md`    | Middleware           | 5        |
| `Migrations.md`    | Migrations           | 3        |

## Usage with AI Agents

### For opencode

```bash
# The skill will be loaded automatically when you ask about Laravel 13
```

### For skills.sh

1. Upload this package or the `SKILL.md` file
2. The AI agent will automatically use it for Laravel 13 related tasks

## Supported Components

| Component                | Attributes Supported                                                                                                                                                                                                                                                                                                                                                                                               |
| ------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Models**               | `#[Table]`, `#[Fillable]`, `#[Guarded]`, `#[Unguarded]`, `#[Hidden]`, `#[Visible]`, `#[Cast]`, `#[Appends]`, `#[With]`, `#[WithRelations]`, `#[Touches]`, `#[Connection]`, `#[DateFormat]`, `#[WithoutIncrementing]`, `#[WithoutTimestamps]`, `#[ObservedBy]`, `#[ScopedBy]`, `#[CollectedBy]`, `#[UseFactory]`, `#[UsePolicy]`, `#[UseResource]`, `#[UseEloquentBuilder]`, `#[Boot]`, `#[Initialize]`, `#[Scope]` |
| **Jobs**                 | `#[Tries]`, `#[Timeout]`, `#[Backoff]`, `#[MaxExceptions]`, `#[Middleware]`, `#[Connection]`, `#[Queue]`, `#[UniqueFor]`, `#[FailOnTimeout]`, `#[ReadsQueueAttributes]`, `#[DeleteWhenMissingModels]`, `#[WithoutRelations]`                                                                                                                                                                                       |
| **Commands**             | `#[Signature]`, `#[Description]`, `#[Help]`, `#[Hidden]`, `#[Aliases]`, `#[Usage]`                                                                                                                                                                                                                                                                                                                                 |
| **Controllers**          | `#[Middleware]`, `#[Authorize]`                                                                                                                                                                                                                                                                                                                                                                                    |
| **Form Requests**        | `#[ErrorBag]`, `#[RedirectTo]`, `#[RedirectToRoute]`, `#[RedirectToAction]`, `#[StopOnFirstFailure]`                                                                                                                                                                                                                                                                                                               |
| **Resources**            | `#[Collects]`, `#[PreserveKeys]`                                                                                                                                                                                                                                                                                                                                                                                   |
| **Factories**            | `#[UseModel]`                                                                                                                                                                                                                                                                                                                                                                                                      |
| **Testing**              | `#[Seed]`, `#[Seeder]`, `#[SetUp]`, `#[TearDown]`                                                                                                                                                                                                                                                                                                                                                                  |
| **Container DI**         | `#[CurrentUser]`, `#[Config]`, `#[DB]`, `#[Database]`, `#[Cache]`, `#[Log]`, `#[Storage]`, `#[Auth]`, `#[Authenticated]`, `#[RouteParameter]`, `#[Context]`, `#[Give]`, `#[Tag]`                                                                                                                                                                                                                                   |
| **Service Registration** | `#[Bind]`, `#[Singleton]`, `#[Scoped]`                                                                                                                                                                                                                                                                                                                                                                             |

## Example Usage

### Refactor a Model

**Before:**

```php
class Post extends Model
{
    protected $table = 'blog_posts';
    protected $fillable = ['title', 'body'];
    protected $casts = ['is_published' => 'boolean'];
}
```

**After:**

```php
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Cast;

#[Table('blog_posts')]
#[Fillable(['title', 'body'])]
#[Cast('is_published', 'boolean')]
class Post extends Model
{
}
```

## Requirements

- PHP 8.3+
- Laravel 13+

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Author

- **Shamim** - [shamimstack@gmail.com](mailto:shamimstack@gmail.com)

## Resources

- [Laravel 13 Documentation](https://laravel.com/docs/13.x)
- [PHP 8 Attributes](https://www.php.net/manual/en/language.attributes.overview.php)
- [Laravel Daily - 36 New Attributes](https://laraveldaily.com/post/php-attributes-in-laravel-13-the-ultimate-guide-36-new-attributes)

## Credits

Inspired by Laravel 13's new attribute system introduced at Laracon EU 2026.
