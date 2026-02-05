# Livewire 4 + Laravel 12 Project

## Architecture Overview

This is a **Laravel 12** + **Livewire 4** application using **maryUI** component library and **Spatie Permission** for access control. The codebase follows Livewire's single-file component pattern with view-based routing.

### Key Stack Components

- **Frontend**: Livewire 4, maryUI (DaisyUI wrapper), Tailwind CSS 4, Vite
- **Backend**: Laravel 12 (PHP 8.2+), Spatie Laravel-Permission, Spatie Simple-Excel
- **Testing**: Pest PHP
- **Queue**: Database driver with custom monitoring UI

## Critical Patterns

### Livewire Component Architecture

**Single-File Components**: Components live in `resources/views/pages/` as `.blade.php` files with anonymous PHP classes:

```php
<?php
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public function mount(): void { }

    // Component logic here
}; ?>

<div>
    {{-- Blade template here --}}
</div>
```

**Routing**: Use Livewire routing syntax in `routes/web.php`:

```php
Route::livewire('/contact', 'pages::contact.index')->name('contact.index');
```

**Namespaces**: Configured in `config/livewire.php`:

- `pages::` → `resources/views/pages/`
- `layouts::` → `resources/views/layouts/`

**Traditional Components**: Some components in `app/Livewire/` (Queue, Permission modules) use traditional class-based structure with separate views.

### Enums with UI Methods

Enums provide both value storage AND display logic:

```php
enum OrderStatus: string {
    case New = 'new';

    public function label(): string { return 'New'; }
    public function color(): string { return 'badge-info text-white'; }
}
```

Use `->value` for database operations, call methods for UI display.

### Filterable Trait

Models use `App\Traits\Filterable` for reusable query scopes:

```php
use Filterable;

// Available scopes (all chainable):
->filterLike('name', $search)      // Case-insensitive LIKE
->filterWhere('status', $value)    // Exact match
->isActive()                       // Active records only
```

**Usage in Components**:

```php
Product::query()
    ->filterLike('name', $this->search)
    ->filterWhere('status', $this->status)
    ->paginate($this->perPage);
```

### Permission System

**Spatie Laravel-Permission** with Gate integration:

- **Super Admin Bypass**: `super-admin` role bypasses all checks (see `AppServiceProvider::boot()`)
- **Permission Format**: `{resource}.{action}` (e.g., `contacts.view`, `orders.create`)
- **Check Permissions**: Use `Gate::authorize('contacts.view')` in mount() or middleware
- **UI Hiding**: `hidden="auth()->user()->cannot('contacts.view')"` on menu items
- **Model Assignment**: Users get roles via Spatie's `assignRole()` method

### Session-Based Filters

Use `#[Session]` attribute for persistent filters across requests:

```php
#[Session(key: 'contact_per_page')]
public int $perPage = 10;

#[Session(key: 'contact_name')]
public string $name = '';
```

Filters survive page navigation and persist in query strings.

### Queue Management

Custom queue monitoring system (see `QUEUE_MODULE_README.md`):

- **Models**: `Job`, `FailedJob`, `QueueLog` for tracking
- **UI Components**: `app/Livewire/Queue/JobQueue.php`, `FailedJobs.php`
- **Features**: Retry/delete jobs, view full exceptions, real-time stats
- **Job Pattern**: Jobs receive `QueueLog` in constructor for tracking user context

Example job structure:

```php
class ImportContacts implements ShouldQueue {
    use Queueable;

    public function __construct(protected QueueLog $queueLog) { }

    public function handle(): void {
        // Access user: $this->queueLog->data['user_id']
        // Send notification when complete
    }
}
```

## Development Workflow

### Setup & Running

```bash
# Initial setup
composer setup

# Development (runs 3 concurrent processes)
composer dev
# Starts: Laravel server (8000), queue listener, Vite HMR

# Alternative: Run individually
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### Testing

```bash
composer test  # Clears config and runs Pest tests
```

### Code Style

Use **Laravel Pint** for formatting:

```bash
./vendor/bin/pint
```

## Component Library - maryUI

**maryUI** wraps DaisyUI components for Livewire:

- Components: `<x-button>`, `<x-input>`, `<x-table>`, `<x-modal>`, etc.
- Forms: Auto-bind with `wire:model`, built-in validation display
- Toast notifications: Use `Toast` trait, dispatch `success`/`error` events
- Theme toggle: `<x-theme-toggle>` with dark mode support

**Common Patterns**:

```blade
{{-- Data tables --}}
<x-table :headers="$this->headers()" :rows="$this->contacts()"
    :sort-by="$sortBy" with-pagination />

{{-- Modals with wire:model --}}
<x-modal wire:model="showModal" title="Edit">
    <x-input label="Name" wire:model="name" />
</x-modal>

{{-- Toast from component --}}
$this->dispatch('success', message: 'Saved!');
```

## File Locations

- **Views**: `resources/views/pages/{module}/{action}.blade.php`
- **Components**: `resources/views/components/` (reusable Blade components)
- **Livewire Classes**: `app/Livewire/{Module}/{Component}.php` (when not single-file)
- **Models**: `app/Models/` (use Filterable trait for common queries)
- **Jobs**: `app/Jobs/` (queue-able background tasks)
- **Enums**: `app/Enums/` (with label/color methods for UI)
- **Migrations**: `database/migrations/` (timestamped)

## Common Pitfalls

1. **Livewire Namespace**: Use `pages::` prefix, not full paths. Config defines namespaces.
2. **Enum Values**: Use `->value` when storing/querying, not the case directly
3. **Gate Before Hook**: Returns `null` to allow Spatie permission checks to run (not `false`)
4. **Session Attributes**: Changes auto-persist; use `$queryString` for URL state too
5. **Mary Components**: Most accept Livewire wire:model directly - no need for Alpine
6. **Vite Ignores**: `storage/framework/views/` ignored to prevent watch loops

## External Dependencies

- **Cropper.js**: Loaded via CDN for avatar uploads
- **Chart.js**: Loaded via CDN for dashboard charts (see `home.blade.php`)
- **Concurrently**: NPM package for running multiple dev processes
