---
name: Laravel 13 Attribute Refactor
description: Write new Laravel 13 code with PHP 8 attributes, and refactor old Laravel code from property-based configuration to attribute syntax. Supports Models, Jobs, Commands, Controllers, FormRequests, Factories, Testing, and Container DI.
version: 1.0.1
author: shamimstack
tags: [laravel, php, laravel-13, laravel-13.2, attributes, refactor, eloquent, queue, commands]
requires: Laravel 13.0+ | PHP 8.3+
---

# Laravel 13 Attribute Refactor Skill

## Purpose

This skill enables AI agents to:

1. **Write new Laravel 13 code** using PHP 8 attribute syntax
2. **Refactor existing Laravel code** from property-based configuration to PHP 8 attributes

The skill preserves all business logic and maintains backward compatibility. Attributes are optional alternatives to properties, not replacements.

## When to Use

- User provides old Laravel code (Model, Job, Command, Controller, etc.) and asks to convert to attribute syntax
- User requests new Laravel 13 code with attribute syntax
- Code is running on Laravel 13+ with PHP 8.3+

## How to Use

1. Identify the Laravel component type (Model, Job, Command, etc.)
2. Extract configuration properties from the class
3. Convert each property to its attribute equivalent
4. Add required `use` statements for attribute classes
5. Remove original property definitions
6. Return refactored code with business logic intact

## Supported Conversions

### Models (Eloquent)

| Old Property            | New Attribute                          | Import                                                        |
| ----------------------- | -------------------------------------- | ------------------------------------------------------------- |
| `$table`                | `#[Table('name', key: 'id', ...)]`     | `Illuminate\Database\Eloquent\Attributes\Table`               |
| `$fillable`             | `#[Fillable(['col1', 'col2'])]`        | `Illuminate\Database\Eloquent\Attributes\Fillable`            |
| `$guarded`              | `#[Guarded(['col1'])]`                 | `Illuminate\Database\Eloquent\Attributes\Guarded`             |
| `$guarded = []`         | `#[Unguarded]`                         | `Illuminate\Database\Eloquent\Attributes\Unguarded`           |
| `$hidden`               | `#[Hidden(['col1'])]`                  | `Illuminate\Database\Eloquent\Attributes\Hidden`              |
| `$visible`              | `#[Visible(['col1'])]`                 | `Illuminate\Database\Eloquent\Attributes\Visible`             |
| `$casts`                | `#[Cast('column', 'type')]` (multiple) | `Illuminate\Database\Eloquent\Attributes\Cast`                |
| `$appends`              | `#[Appends(['accessor'])]`             | `Illuminate\Database\Eloquent\Attributes\Appends`             |
| `$with`                 | `#[With(['relation'])]`                | `Illuminate\Database\Eloquent\Attributes\With`                |
| `$withRelations`        | `#[WithRelations(['relation'])]`       | `Illuminate\Database\Eloquent\Attributes\WithRelations`       |
| `$touches`              | `#[Touches(['relation'])]`             | `Illuminate\Database\Eloquent\Attributes\Touches`             |
| `$connection`           | `#[Connection('name')]`                | `Illuminate\Database\Eloquent\Attributes\Connection`          |
| `$primaryKey`           | (use `#[Table]` with `key`)            | -                                                             |
| `$keyType`              | (use `#[Table]` with `keyType`)        | -                                                             |
| `$incrementing`         | (use `#[Table]` with `incrementing`)   | -                                                             |
| `$timestamps`           | (use `#[Table]` with `timestamps`)     | -                                                             |
| `$dateFormat`           | `#[DateFormat('Y-m-d')]`               | `Illuminate\Database\Eloquent\Attributes\DateFormat`          |
| `$incrementing = false` | `#[WithoutIncrementing]`               | `Illuminate\Database\Eloquent\Attributes\WithoutIncrementing` |
| `$timestamps = false`   | `#[WithoutTimestamps]`                 | `Illuminate\Database\Eloquent\Attributes\WithoutTimestamps`   |
| `$dispatchesEvents`     | `#[DispatchesEvents]`                  | `Illuminate\Database\Eloquent\Attributes\DispatchesEvents`    |
| `$observables`          | `#[Observers([...])]`                  | `Illuminate\Database\Eloquent\Attributes\Observers`           |

### Model Pre-existing Attributes

| Attribute                                     | Import                                                          |
| --------------------------------------------- | --------------------------------------------------------------- |
| `#[ObservedBy(Observer::class)]`              | `Illuminate\Database\Eloquent\Attributes\ObservedBy`            |
| `#[ScopedBy(Scope::class)]`                   | `Illuminate\Database\Eloquent\Attributes\ScopedBy`              |
| `#[CollectedBy(Collection::class)]`           | `Illuminate\Database\Eloquent\Attributes\CollectedBy`           |
| `#[UseFactory(Factory::class)]`               | `Illuminate\Database\Eloquent\Attributes\UseFactory`            |
| `#[UseEloquentBuilder(Builder::class)]`       | `Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder`    |
| `#[UsePolicy(Policy::class)]`                 | `Illuminate\Database\Eloquent\Attributes\UsePolicy`             |
| `#[UseResource(Resource::class)]`             | `Illuminate\Database\Eloquent\Attributes\UseResource`           |
| `#[UseResourceCollection(Collection::class)]` | `Illuminate\Database\Eloquent\Attributes\UseResourceCollection` |
| `#[Boot]` (on method)                         | `Illuminate\Database\Eloquent\Attributes\Boot`                  |
| `#[Initialize]` (on method)                   | `Illuminate\Database\Eloquent\Attributes\Initialize`            |
| `#[Scope]` (on method)                        | `Illuminate\Database\Eloquent\Attributes\Scope`                 |

### Jobs (Queue)

| Old Property          | New Attribute                                                                         | Import                                             |
| --------------------- | ------------------------------------------------------------------------------------- | -------------------------------------------------- |
| `$tries`              | `#[Tries(5)]`                                                                         | `Illuminate\Queue\Attributes\Tries`                |
| `$timeout`            | `#[Timeout(120)]`                                                                     | `Illuminate\Queue\Attributes\Timeout`              |
| `$backoff`            | `#[Backoff(30)]` or `#[Backoff(10, 30, 60)]` (variadic, Laravel 13.2+)                | `Illuminate\Queue\Attributes\Backoff`              |
| `$maxExceptions`      | `#[MaxExceptions(3)]`                                                                 | `Illuminate\Queue\Attributes\MaxExceptions`        |
| `$middleware`         | `#[Middleware([...])]`                                                                | `Illuminate\Queue\Attributes\Middleware`           |
| `$connection`         | `#[Connection('redis')]` or `#[Connection(MyEnum::CONNECTION)]` (enum, Laravel 13.2+) | `Illuminate\Queue\Attributes\Connection`           |
| `$queue`              | `#[Queue('high')]` or `#[Queue(MyEnum::HIGH)]` (enum, Laravel 13.2+)                  | `Illuminate\Queue\Attributes\Queue`                |
| `$uniqueFor`          | `#[UniqueFor(3600)]`                                                                  | `Illuminate\Queue\Attributes\UniqueFor`            |
| `$failOnTimeout`      | `#[FailOnTimeout]`                                                                    | `Illuminate\Queue\Attributes\FailOnTimeout`        |
| (new in Laravel 13.2) | `#[ReadsQueueAttributes]`                                                             | `Illuminate\Queue\Attributes\ReadsQueueAttributes` |

### Job Pre-existing Attributes

| Attribute                    | Import                                                |
| ---------------------------- | ----------------------------------------------------- |
| `#[DeleteWhenMissingModels]` | `Illuminate\Queue\Attributes\DeleteWhenMissingModels` |
| `#[WithoutRelations]`        | `Illuminate\Queue\Attributes\WithoutRelations`        |

### Commands (Artisan Console)

| Old Property   | New Attribute                | Import                                      |
| -------------- | ---------------------------- | ------------------------------------------- |
| `$signature`   | `#[Signature('name {arg}')]` | `Illuminate\Console\Attributes\Signature`   |
| `$description` | `#[Description('...')]`      | `Illuminate\Console\Attributes\Description` |
| `$help`        | `#[Help('...')]`             | `Illuminate\Console\Attributes\Help`        |
| `$hidden`      | `#[Hidden]`                  | `Illuminate\Console\Attributes\Hidden`      |
| `$aliases`     | `#[Aliases(['alias'])]`      | `Illuminate\Console\Attributes\Aliases`     |

### Controllers (Routing)

| Old Usage                            | New Attribute                          | Import                                                 |
| ------------------------------------ | -------------------------------------- | ------------------------------------------------------ |
| `$this->middleware()` in constructor | `#[Middleware('auth')]`                | `Illuminate\Routing\Attributes\Controllers\Middleware` |
| `$this->authorizeResource()`         | `#[Authorize('policy', Model::class)]` | `Illuminate\Routing\Attributes\Controllers\Authorize`  |

### Form Requests

| Old Property          | New Attribute                              | Import                                                     |
| --------------------- | ------------------------------------------ | ---------------------------------------------------------- |
| `$errorBag`           | `#[ErrorBag('name')]`                      | `Illuminate\Foundation\Http\Attributes\ErrorBag`           |
| `$redirect`           | `#[RedirectTo('/url')]`                    | `Illuminate\Foundation\Http\Attributes\RedirectTo`         |
| `$redirectRoute`      | `#[RedirectToRoute('route.name')]`         | `Illuminate\Foundation\Http\Attributes\RedirectToRoute`    |
| `$redirectAction`     | `#[RedirectToAction('Controller@method')]` | `Illuminate\Foundation\Http\Attributes\RedirectToAction`   |
| `$stopOnFirstFailure` | `#[StopOnFirstFailure]`                    | `Illuminate\Foundation\Http\Attributes\StopOnFirstFailure` |

### HTTP Resources

| Old Property    | New Attribute                  | Import                                              |
| --------------- | ------------------------------ | --------------------------------------------------- |
| `$collects`     | `#[Collects(Resource::class)]` | `Illuminate\Http\Resources\Attributes\Collects`     |
| `$preserveKeys` | `#[PreserveKeys]`              | `Illuminate\Http\Resources\Attributes\PreserveKeys` |

### Factories

| Old Property | New Attribute               | Import                                                       |
| ------------ | --------------------------- | ------------------------------------------------------------ |
| `$model`     | `#[UseModel(Model::class)]` | `Illuminate\Database\Eloquent\Factories\Attributes\UseModel` |

### Testing

| Old Property              | New Attribute                   | Import                                              |
| ------------------------- | ------------------------------- | --------------------------------------------------- |
| `$seed = true`            | `#[Seed]`                       | `Illuminate\Foundation\Testing\Attributes\Seed`     |
| `$seeder = Seeder::class` | `#[Seeder(Seeder::class)]`      | `Illuminate\Foundation\Testing\Attributes\Seeder`   |
| `setUp()` method          | `#[SetUp] void methodName()`    | `Illuminate\Foundation\Testing\Attributes\SetUp`    |
| `tearDown()` method       | `#[TearDown] void methodName()` | `Illuminate\Foundation\Testing\Attributes\TearDown` |

### Container / Dependency Injection

| Old Usage                      | New Attribute                                   | Import                                           |
| ------------------------------ | ----------------------------------------------- | ------------------------------------------------ |
| Constructor injection          | `#[CurrentUser] User $user`                     | `Illuminate\Container\Attributes\CurrentUser`    |
| Config injection               | `#[Config('key')] string $value`                | `Illuminate\Container\Attributes\Config`         |
| Database injection             | `#[DB('connection')] Connection $db`            | `Illuminate\Container\Attributes\DB`             |
| Cache injection                | `#[Cache('store')] Repository $cache`           | `Illuminate\Container\Attributes\Cache`          |
| Log injection                  | `#[Log('channel')] LoggerInterface $log`        | `Illuminate\Container\Attributes\Log`            |
| Storage injection              | `#[Storage('disk')] Filesystem $disk`           | `Illuminate\Container\Attributes\Storage`        |
| Auth guard injection           | `#[Auth('guard')] Guard $auth`                  | `Illuminate\Container\Attributes\Auth`           |
| Authenticated user (via guard) | `#[Authenticated('web')] Authenticatable $user` | `Illuminate\Container\Attributes\Authenticated`  |
| Route parameter                | `#[RouteParameter('name')] $value`              | `Illuminate\Container\Attributes\RouteParameter` |
| Context injection              | `#[Context('key')] $value`                      | `Illuminate\Container\Attributes\Context`        |
| Database (alias for DB)        | `#[Database('connection')] Connection $db`      | `Illuminate\Container\Attributes\Database`       |
| Give specific implementation   | `#[Give(Implementation::class)] $service`       | `Illuminate\Container\Attributes\Give`           |
| Tagged services                | `#[Tag('tag.name')] iterable $services`         | `Illuminate\Container\Attributes\Tag`            |

### Container Service Registration

| Old Usage       | New Attribute           | Import                                      |
| --------------- | ----------------------- | ------------------------------------------- |
| Service binding | `#[Bind]` on class      | `Illuminate\Container\Attributes\Bind`      |
| Singleton       | `#[Singleton]` on class | `Illuminate\Container\Attributes\Singleton` |
| Scoped          | `#[Scoped]` on class    | `Illuminate\Container\Attributes\Scoped`    |

## Refactoring Rules

1. **Never change business logic** - method bodies, constructors, relationships remain untouched
2. **Convert only known properties** to their attribute equivalents
3. **Add required `use` statements** for attribute classes at the top
4. **Remove original property definitions** after conversion
5. **Preserve existing code style** (indentation, spacing)
6. **Use fully qualified class names** or add proper imports
7. **For multiple casts**, use multiple `#[Cast]` attributes
8. **For multiple middleware**, use multiple `#[Middleware]` attributes

## Example Conversions

### Model - Full Conversion

**Before:**

```php
class Post extends Model
{
    protected $table = 'blog_posts';
    protected $primaryKey = 'post_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['title', 'body', 'status'];
    protected $guarded = ['id', 'is_admin'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'title' => 'string',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
    protected $appends = ['full_title'];
    protected $with = ['author'];
    protected $touches = ['category'];
}
```

**After:**

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\With;
use Illuminate\Database\Eloquent\Attributes\Touches;

#[Table(
    name: 'blog_posts',
    key: 'post_id',
    keyType: 'string',
    incrementing: false,
)]
#[Fillable(['title', 'body', 'status'])]
#[Guarded(['id', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
#[Cast('title', 'string')]
#[Cast('is_published', 'boolean')]
#[Cast('published_at', 'datetime')]
#[Appends(['full_title'])]
#[With(['author'])]
#[Touches(['category'])]
class Post extends Model
{
    // Business logic remains unchanged
}
```

### Job - Full Conversion

**Before:**

```php
class ProcessPayment implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60];
    public $maxExceptions = 3;
    public $queue = 'high';
    public $connection = 'redis';
}
```

**After:**

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Connection;

#[Tries(3)]
#[Timeout(120)]
#[Backoff([10, 30, 60])]
#[MaxExceptions(3)]
#[Queue('high')]
#[Connection('redis')]
class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
}
```

### Command - Full Conversion

**Before:**

```php
class SendEmails extends Command
{
    protected $signature = 'mail:send {user} {--subject=Default Subject}';
    protected $description = 'Send emails to a user';
    protected $help = 'This command sends emails. Usage: php artisan mail:send user@example.com';
    protected $hidden = false;
    protected $aliases = ['mail'];
}
```

**After:**

```php
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Help;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Console\Attributes\Aliases;

#[Signature('mail:send {user} {--subject=Default Subject}')]
#[Description('Send emails to a user')]
#[Help('This command sends emails. Usage: php artisan mail:send user@example.com')]
#[Hidden(false)]
#[Aliases(['mail'])]
class SendEmails extends Command
{
}
```

### Controller - Middleware Conversion

**Before:**

```php
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('admin', only: ['destroy']);
        $this->middleware('throttle:60,1', except: ['index']);
    }

    public function index() { /* ... */ }
    public function show($id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
```

**After:**

```php
use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
#[Middleware('verified')]
#[Middleware('admin', only: ['destroy'])]
#[Middleware('throttle:60,1', except: ['index', 'show'])]
class DashboardController extends Controller
{
    public function index() { /* ... */ }
    public function show($id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
```

### Controller - Authorization Conversion

**Before:**

```php
class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    public function edit(Post $post) { /* ... */ }
}
```

**After:**

```php
use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use App\Models\Post;

#[Authorize('viewAny', Post::class)]
class PostController extends Controller
{
    #[Authorize('update', Post::class)]
    public function edit(Post $post) { /* ... */ }
}
```

### Form Request Conversion

**Before:**

```php
class CreatePostRequest extends FormRequest
{
    protected $errorBag = 'createPost';
    protected $redirect = '/posts/create';
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return ['title' => 'required|max:255'];
    }
}
```

**After:**

```php
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Http\Attributes\ErrorBag;
use Illuminate\Foundation\Http\Attributes\RedirectTo;
use Illuminate\Foundation\Http\Attributes\StopOnFirstFailure;

#[ErrorBag('createPost')]
#[RedirectTo('/posts/create')]
#[StopOnFirstFailure]
class CreatePostRequest extends FormRequest
{
    public function rules(): array
    {
        return ['title' => 'required|max:255'];
    }
}
```

### Container DI Conversion

**Before:**

```php
class PaymentService
{
    public function __construct(
        private string $stripeSecret,
        private LoggerInterface $logger,
    ) {
        $this->stripeSecret = config('services.stripe.secret');
    }
}
```

**After:**

```php
use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\Log;
use Psr\Log\LoggerInterface;

class PaymentService
{
    public function __construct(
        #[Config('services.stripe.secret')] private string $stripeSecret,
        #[Log('payments')] private LoggerInterface $logger,
    ) {
    }
}
```

## Interaction Workflow

1. **Acknowledge** the user's request
2. **Identify** the Laravel component type
3. **Extract** configuration properties
4. **Generate** the attribute version with proper imports
5. **Output** the full refactored class
6. **Summarize** the changes made
7. **Offer** to refactor more files

## Limitations

- Attributes require PHP 8.3+ (Laravel 13 requirement)
- Not all properties have attribute equivalents (e.g., custom properties)
- Complex inheritance may need manual review
- Dynamic property definitions cannot be converted automatically

## Safety Recommendations

- Run tests after refactoring
- Commit changes one file at a time
- Start with models, then jobs, then commands

## Example Prompts

- "Refactor this Post model to use Laravel 13 attributes"
- "Convert this job to use attribute syntax"
- "Write a new Laravel 13 command with attributes"
- "Convert this controller to use #[Middleware] and #[Authorize]"
- "Refactor this FormRequest to use attributes"
