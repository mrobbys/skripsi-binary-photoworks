# Laravel 13 Skills - Index

This package provides comprehensive Laravel 13 PHP 8 attribute support for AI agents.

## Quick Start

### Installation

```bash
# Composer
composer require shamimstack/laravel-13-skills

# npm
npm install @shamimstack/laravel-13-skills
```

## Package Contents

### Core Files

| File           | Description                                       |
| -------------- | ------------------------------------------------- |
| `SKILL.md`     | Main AI skill definition (for opencode/skills.sh) |
| `README.md`    | Package documentation                             |
| `CHANGELOG.md` | Version history                                   |

### Examples

| File                           | Component            |
| ------------------------------ | -------------------- |
| `src/Examples/Models.md`       | Eloquent Models      |
| `src/Examples/Jobs.md`         | Queue Jobs           |
| `src/Examples/Commands.md`     | Artisan Commands     |
| `src/Examples/Controllers.md`  | HTTP Controllers     |
| `src/Examples/FormRequests.md` | Form Requests        |
| `src/Examples/Factories.md`    | Model Factories      |
| `src/Examples/Testing.md`      | Test Cases           |
| `src/Examples/Container.md`    | Dependency Injection |

## Usage with AI Agents

### opencode

Copy `SKILL.md` to your project or reference the package directly.

### skills.sh

Upload this package or the `SKILL.md` file to register the skill.

## Example Prompts

- "Refactor this Post model to use Laravel 13 attributes"
- "Convert this job to use #[Tries] and #[Queue] attributes"
- "Write a new Laravel 13 command with #[Signature] and #[Description]"
- "Convert this controller constructor middleware to #[Middleware] attributes"
- "Refactor this FormRequest to use #[ErrorBag] and #[RedirectToRoute]"

## Requirements

- PHP 8.3+
- Laravel 13+

## Resources

- [Laravel 13 Documentation](https://laravel.com/docs/13.x)
- [Laravel Daily - 36 New Attributes](https://laraveldaily.com/post/php-attributes-in-laravel-13-the-ultimate-guide-36-new-attributes)

## License

MIT License - see [LICENSE](LICENSE) file.
