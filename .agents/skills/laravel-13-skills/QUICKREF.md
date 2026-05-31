# Laravel 13 Attributes Quick Reference

## Models

| Old                                           | New                             |
| --------------------------------------------- | ------------------------------- |
| `protected $table = 'posts'`                  | `#[Table('posts')]`             |
| `protected $fillable = ['title']`             | `#[Fillable(['title'])]`        |
| `protected $guarded = ['id']`                 | `#[Guarded(['id'])]`            |
| `protected $guarded = []`                     | `#[Unguarded]`                  |
| `protected $hidden = ['password']`            | `#[Hidden(['password'])]`       |
| `protected $visible = ['name']`               | `#[Visible(['name'])]`          |
| `protected $casts = ['price' => 'decimal:2']` | `#[Cast('price', 'decimal:2')]` |
| `protected $appends = ['full_name']`          | `#[Appends(['full_name'])]`     |
| `protected $with = ['user']`                  | `#[With(['user'])]`             |
| `protected $touches = ['post']`               | `#[Touches(['post'])]`          |
| `protected $connection = 'analytics'`         | `#[Connection('analytics')]`    |

## Jobs

| Old                            | New                      |
| ------------------------------ | ------------------------ |
| `public $tries = 5`            | `#[Tries(5)]`            |
| `public $timeout = 120`        | `#[Timeout(120)]`        |
| `public $backoff = [10, 30]`   | `#[Backoff([10, 30])]`   |
| `public $maxExceptions = 3`    | `#[MaxExceptions(3)]`    |
| `public $queue = 'high'`       | `#[Queue('high')]`       |
| `public $connection = 'redis'` | `#[Connection('redis')]` |
| `public $uniqueFor = 3600`     | `#[UniqueFor(3600)]`     |
| `public $failOnTimeout = true` | `#[FailOnTimeout]`       |

## Commands

| Old                                  | New                         |
| ------------------------------------ | --------------------------- |
| `protected $signature = 'make:post'` | `#[Signature('make:post')]` |
| `protected $description = '...' `    | `#[Description('...')]`     |
| `protected $help = '...'`            | `#[Help('...')]`            |
| `protected $hidden = true`           | `#[Hidden]`                 |
| `protected $aliases = ['mp']`        | `#[Aliases(['mp'])]`        |

## Controllers

| Old                             | New                                    |
| ------------------------------- | -------------------------------------- |
| `$this->middleware('auth')`     | `#[Middleware('auth')]`                |
| `$this->authorizeResource(...)` | `#[Authorize('policy', Model::class)]` |

## Form Requests

| Old                                    | New                          |
| -------------------------------------- | ---------------------------- |
| `protected $errorBag = 'submit'`       | `#[ErrorBag('submit')]`      |
| `protected $redirect = '/url'`         | `#[RedirectTo('/url')]`      |
| `protected $redirectRoute = 'home'`    | `#[RedirectToRoute('home')]` |
| `protected $stopOnFirstFailure = true` | `#[StopOnFirstFailure]`      |

## Container DI

| Old                            | New                                   |
| ------------------------------ | ------------------------------------- |
| `config('key')` in constructor | `#[Config('key')] $value`             |
| `DB::connection('name')`       | `#[DB('name')] Connection $db`        |
| `Cache::store('name')`         | `#[Cache('name')] Repository $cache`  |
| `Log::channel('name')`         | `#[Log('name')] LoggerInterface $log` |
| `auth()->user()`               | `#[CurrentUser] User $user`           |

## Testing

| Old                                 | New                          |
| ----------------------------------- | ---------------------------- |
| `protected $seed = true`            | `#[Seed]`                    |
| `protected $seeder = Seeder::class` | `#[Seeder(Seeder::class)]`   |
| `protected function setUp()`        | `#[SetUp] void methodName()` |

## Factories

| Old                               | New                         |
| --------------------------------- | --------------------------- |
| `protected $model = Model::class` | `#[UseModel(Model::class)]` |
