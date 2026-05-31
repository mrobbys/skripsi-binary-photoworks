# Changelog

All notable changes to `laravel-13-skills` will be documented in this file.

## [1.0.1] - 2026-03-29

### Added

- Laravel 13.2 support
- Variadic Backoff attribute syntax: `#[Backoff(10, 30, 60)]`
- Enum support for Queue and Connection attributes
- New examples for Laravel 13.2 features
- Updated version requirements

## [1.0.0] - 2026-03-29

### Added

- Initial release
- Comprehensive Laravel 13 PHP 8 attribute support
- 74+ attributes across 10 Laravel components
- Skill definition for AI agents (compatible with opencode, skills.sh)
- Example conversions for all components

### Components Supported

#### Models (Eloquent)

- `#[Table]` - Table name and key configuration
- `#[Fillable]` - Mass-assignable fields
- `#[Guarded]` - Guarded fields
- `#[Unguarded]` - Disable mass-assignment protection
- `#[Hidden]` - Hidden fields from serialization
- `#[Visible]` - Explicitly visible fields
- `#[Cast]` - Attribute casting (multiple)
- `#[Appends]` - Auto-append accessors
- `#[With]` - Eager loading
- `#[WithRelations]` - Relation loading
- `#[Touches]` - Touch parent timestamps
- `#[Connection]` - Database connection
- `#[DateFormat]` - Date format
- `#[WithoutIncrementing]` - Non-incrementing primary key
- `#[WithoutTimestamps]` - Disable timestamps
- `#[ObservedBy]` - Model observers
- `#[ScopedBy]` - Global scopes
- `#[CollectedBy]` - Custom collection class
- `#[UseFactory]` - Model factory
- `#[UsePolicy]` - Model policy
- `#[UseResource]` - API resource
- `#[UseEloquentBuilder]` - Custom query builder
- `#[Boot]` - Lifecycle hook
- `#[Initialize]` - Initialization hook
- `#[Scope]` - Local scope

#### Jobs (Queue)

- `#[Tries]` - Max attempts
- `#[Timeout]` - Job timeout
- `#[Backoff]` - Retry backoff
- `#[MaxExceptions]` - Max exceptions
- `#[Middleware]` - Job middleware
- `#[Connection]` - Queue connection
- `#[Queue]` - Queue name
- `#[UniqueFor]` - Unique job lock duration
- `#[FailOnTimeout]` - Fail on timeout
- `#[ReadsQueueAttributes]` - Read queue attributes (Laravel 13.2)
- `#[DeleteWhenMissingModels]` - Delete when model missing
- `#[WithoutRelations]` - Strip relations

#### Commands (Artisan Console)

- `#[Signature]` - Command signature
- `#[Description]` - Command description
- `#[Help]` - Help text
- `#[Hidden]` - Hide command
- `#[Aliases]` - Command aliases
- `#[Usage]` - Usage examples

#### Controllers (Routing)

- `#[Middleware]` - Controller middleware
- `#[Authorize]` - Authorization

#### Form Requests

- `#[ErrorBag]` - Named error bag
- `#[RedirectTo]` - Redirect URL
- `#[RedirectToRoute]` - Redirect to route
- `#[RedirectToAction]` - Redirect to action
- `#[StopOnFirstFailure]` - Stop on first failure

#### HTTP Resources

- `#[Collects]` - Collection type
- `#[PreserveKeys]` - Preserve array keys

#### Factories

- `#[UseModel]` - Factory model

#### Testing

- `#[Seed]` - Run default seeder
- `#[Seeder]` - Specific seeder
- `#[SetUp]` - SetUp method
- `#[TearDown]` - TearDown method

#### Container / Dependency Injection

- `#[CurrentUser]` - Authenticated user
- `#[Config]` - Config value
- `#[DB]` - Database connection
- `#[Database]` - Database connection (alias)
- `#[Cache]` - Cache store
- `#[Log]` - Log channel
- `#[Storage]` - Filesystem disk
- `#[Auth]` - Auth guard
- `#[Authenticated]` - Authenticated user (via guard)
- `#[RouteParameter]` - Route parameter
- `#[Context]` - Context value
- `#[Give]` - Specific implementation
- `#[Tag]` - Tagged services

#### Service Registration

- `#[Bind]` - Auto-bind to container
- `#[Singleton]` - Singleton registration
- `#[Scoped]` - Scoped registration
