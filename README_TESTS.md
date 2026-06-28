# Backend Tests Guide

This guide explains how to run and maintain tests for the EPF Marketplace Laravel backend.

## Test Structure

```
tests/
├── Feature/              # Feature/Integration tests
│   ├── AuthTest.php
│   └── ProductTest.php
├── Unit/                 # Unit tests (add as needed)
├── performance/          # Performance tests
│   └── load-test.js
└── pest.php             # Pest configuration
```

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test File
```bash
php artisan test tests/Feature/AuthTest.php
```

### Run with Coverage
```bash
php artisan test --coverage
```

### Run in Parallel (Faster)
```bash
php artisan test --parallel
```

### Run Specific Test
```bash
php artisan test --filter test_user_can_login
```

## Writing New Tests

### Feature Test Example

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('user can perform action', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/endpoint', [
            'data' => 'value',
        ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});
```

### Unit Test Example

```php
<?php

use App\Services\YourService;

test('service method returns expected result', function () {
    $service = new YourService();
    $result = $service->method();
    
    expect($result)->toBe('expected');
});
```

## Test Coverage

Current coverage targets:
- Minimum 80% coverage
- Critical paths: 100% coverage
- Generated files: excluded

View coverage report:
```bash
php artisan test --coverage
# Open coverage/index.html in browser
```

## Database Testing

Tests use a separate test database. Configure in `.env.testing`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=epf_marketplace_test
DB_USERNAME=root
DB_PASSWORD=
```

Or use SQLite for faster tests:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## Factories

Use factories to create test data:

```php
$user = User::factory()->create();
$product = Product::factory()->create([
    'seller_id' => $user->id,
]);
```

## Authentication Testing

### Acting as User
```php
$response = $this->actingAs($user)
    ->getJson('/api/endpoint');
```

### With Token
```php
$token = $user->createToken('test-token')->plainTextToken;
$response = $this->withToken($token)
    ->getJson('/api/endpoint');
```

## API Testing

### JSON Response
```php
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['data' => ['id', 'name']]);
```

### Status Codes
```php
$response->assertStatus(200);
$response->assertCreated();
$response->assertNoContent();
$response->assertUnauthorized();
$response->assertForbidden();
$response->assertNotFound();
```

### Validation Errors
```php
$response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
```

## Performance Testing

Run k6 performance tests:

```bash
# Install k6 first
# Then run:
k6 run tests/performance/load-test.js
```

Or with API URL:
```bash
API_URL=https://your-api.com/api k6 run tests/performance/load-test.js
```

## CI/CD Integration

Tests run automatically in GitHub Actions on:
- Push to `main` branch
- Pull requests to `main` branch
- Pull requests to `develop` branch

The pipeline will fail if:
- Tests fail
- Coverage drops below 80%
- Code style issues (Pint)

## Common Issues

### Database Connection Failed
Ensure test database exists and credentials are correct in `.env.testing`.

### Migration Not Found
Run migrations for test database:
```bash
php artisan migrate --database=testing
```

### Factory Not Found
Ensure you've published factories:
```bash
php artisan make:factory YourFactory
```

### Time-based Tests Failing
Use `Carbon::setTestNow()` to freeze time:
```php
use Carbon\Carbon;
Carbon::setTestNow('2024-01-01');
```

## Best Practices

1. **Use RefreshDatabase** - Clean database between tests
2. **Use Factories** - Don't manually create test data
3. **Test Happy Path** - Test successful scenarios first
4. **Test Edge Cases** - Test validation, errors, edge cases
5. **Avoid External APIs** - Mock external service calls
6. **Keep Tests Fast** - Use in-memory database if possible
7. **Descriptive Names** - Use clear test names that describe what's being tested

## Debugging Failed Tests

### See Detailed Output
```bash
php artisan test --verbose
```

### Stop on First Failure
```bash
php artisan test --stop-on-failure
```

### Run with Debugging
```bash
php artisan test --debug
```

## Adding New Test Dependencies

```bash
composer require --dev your/package
```

Then update `phpunit.xml` or `pest.php` if needed.
