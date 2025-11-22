# Data Mapper API - Agent Reference

## Project Overview

**Name**: Data Mapper API  
**Type**: PHP Library  
**Purpose**: Exposes the [DealNews Data Mapper](https://github.com/dealnews/data-mapper) library as a RESTful HTTP API, enabling external services to perform CRUD operations and searches on mapped data objects.

**Key Dependencies**:
- `dealnews/data-mapper` (^3.3) - Object-relational mapping
- `dealnews/db` (^4.0) - Database abstraction layer
- `moonspot/value-objects` (^2.1) - Value object support
- PHP 8.2+

---

## Architecture

### Core Components

#### 1. **API Class** (`src/API.php`)
Entry point for routing and action execution. Provides:
- Route definitions compatible with PageMill Router
- Action execution with proper input formatting
- Configurable URL prefix (default: `/api`)

**Key Methods**:
- `executeAction(string $action, array $tokens, string $base_url, Repository $repository)` - Invokes an action with formatted inputs
- `getRoute(string $route_name, string $route_prefix = '/api')` - Retrieves a single route configuration
- `getAllRoutes(string $route_prefix = '/api')` - Returns all route configurations

**Available Routes**:
- `get_object_route` - GET `/api/{object_name}/{object_id}/`
- `get_objects_route` - GET `/api/{object_name}/` (deprecated, use search)
- `search_objects_route` - POST `/api/{object_name}/_search/`
- `update_object_route` - PUT `/api/{object_name}/{object_id}/`
- `create_object_route` - POST `/api/{object_name}/`
- `delete_object_route` - DELETE `/api/{object_name}/{object_id}/`

#### 2. **Action Classes** (`src/Action/`)

All actions extend `Base` and implement the `loadData()` method.

**Base.php**:
- Abstract base providing common functionality
- `__invoke(array $inputs, Repository $repository, bool $throw = false)` - Main entry point
- `respond(array $data)` - JSON response handler with HTTP status codes
- `formatObject(Export $data): array` - Converts value objects to arrays
- Error handling with LogicException for 400 errors

**GetObject.php**:
- Retrieves a single object by ID
- Special case: `object_id=0` returns a new empty object
- Returns 404 if object not found

**GetObjects.php**:
- Retrieves multiple objects filtered by query parameters
- DEPRECATED - Use SearchObjects instead
- Validates filter properties against object schema

**SearchObjects.php**:
- Advanced search with JSON Query DSL
- Requires DB-backed mappers (instances of `\DealNews\DB\AbstractMapper`)
- Auto-detects database driver from PDO for SQL dialect selection
- Dependency injection support for CRUD and SearchQuery objects

**UpdateObject.php**:
- Handles both create (POST) and update (PUT) operations
- Returns 201 for creates, 200 for updates
- Validates incoming data against object properties
- Throws LogicException for invalid properties or data

**DeleteObject.php**:
- Deletes an object by ID
- Returns 200 on success, 500 on failure

#### 3. **SearchQuery Class** (`src/SearchQuery.php`)

Converts JSON Query DSL to SQL queries with prepared statements.

**Supported Modes**:
- `default` - Generic SQL with double quotes for identifiers (PostgreSQL-style)
- `mysql` - MySQL SQL with backticks for identifiers

**Query DSL Features**:
- `filter` - Field-value pairs with comparison operators
  - Scalar value = equality check
  - Array of values = IN clause
  - Object with operator key (`<`, `<=`, `>`, `>=`, `between`)
  - `null` value = IS NULL check
  - All filters are AND'd together (no OR support)
- `sort` - Field-direction pairs (`asc`/`desc`)
- `limit` - Maximum records to return
- `start` - Offset for pagination

**Key Methods**:
- `prepareQuery(string $table, string $field, array $query): array` - Returns `['query' => $sql, 'params' => $params]`
- Protected methods: `buildWhere()`, `buildOrder()`, `buildLimit()`, `parseFields()`, `addParam()`

**Important Notes**:
- PostgreSQL mode (`pgsql`) exists in code but is NOT in the MODES constant - use `default` mode for PostgreSQL-style syntax
- MySQL limit syntax: `LIMIT start, count`
- Default mode limit: `LIMIT count OFFSET start`

---

## HTTP API Interface

See `HTTP-API-INTERFACE.md` for detailed endpoint documentation.

**Response Format**: JSON with HTTP status codes
- 200 OK - Successful read/update/delete
- 201 Created - Successful create
- 400 Bad Request - Invalid input, LogicException
- 404 Not Found - Object not found
- 500 Internal Server Error - Save/delete failed

**Error Response Structure**:
```json
{
    "http_status": 400,
    "error": "Error message"
}
```

---

## Testing Strategy

### Test Structure

All tests use PHPUnit 11 with PHP 8.2+ features (attributes, typed properties).

**Test Files**:
- `tests/APITest.php` - Route configuration and action execution
- `tests/SearchQueryTest.php` - Query DSL parsing and SQL generation
- `tests/Action/*Test.php` - Individual action behaviors

**Test Helpers**:
- `tests/Action/TestCase.php` - Base class with `invoke()` helper
- `tests/Repository.php` - Mock repository for testing
- `tests/TestObject.php` - Sample value object
- `tests/TestAction.php` - Test action class

### Mock Objects for Testing

**When testing SearchObjects**, you MUST provide:
1. Mock PDO object with `getAttribute()` method returning database driver name
2. Mock CRUD object injected with the PDO mock
3. Optional mock SearchQuery object for error testing

**Example Pattern**:
```php
$pdo = new class extends \PDO {
    public function __construct() {
        // noop
    }
    
    public function getAttribute($attribute): mixed {
        return 'mysql'; // or 'pgsql' for PostgreSQL
    }
};

$crud = new class($pdo) extends \DealNews\DB\CRUD {
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function runFetch(string $query, array $params = []): array {
        return [/* test data */];
    }
};

$obj = new SearchObjects($crud);
```

### Expected Query Syntax in Tests

- **Default mode tests**: Use double quotes (`"`) for identifiers
- **MySQL mode tests**: Use backticks (`` ` ``) for identifiers
- PostgreSQL `LIMIT ... OFFSET` syntax only appears in default mode

---

## Code Style & Standards

This codebase follows DealNews PHP coding standards:

### Critical Rules
- **Braces**: 1TBS style (same line except multi-line conditions)
- **Naming**: 
  - Variables/properties: `snake_case`
  - Methods/functions: `camelCase`
  - Constants: `UPPER_SNAKE_CASE`
- **Types**: Always declare parameter and return types
- **Visibility**: Use `protected` not `private` unless instructed otherwise
- **Arrays**: Short syntax `[]` only, never `array()`
- **Returns**: Single return point preferred (except early guards)
- **Exceptions**: Catch `\Throwable`, not `\Exception`
- **Line Length**: 80 characters max

### PHPDoc Requirements
- All classes need class-level docblocks
- All methods need docblocks with:
  - `@param` for parameters
  - `@return` for return values
  - `@throws` for exceptions
- Use present tense, action-oriented language

### Namespace Rules
- Top-level: `DealNews`
- One `use` statement per import
- Always specify leading backslash in use statements

---

## Common Development Patterns

### Adding a New Action

1. Create class in `src/Action/` extending `Base`
2. Define required properties (typed, `protected`)
3. Implement `loadData(): array` returning data with optional `http_status` key
4. Add route definition to `API.php`
5. Add route name to `$routes_list` array
6. Create test in `tests/Action/` extending `TestCase`

### Adding Search Query Features

1. Update `SearchQuery::prepareQuery()` or helper methods
2. Add validation in appropriate `build*()` method
3. Throw `\InvalidArgumentException` with unique code for errors
4. Add test cases to `SearchQueryTest::buildQueryData()` data provider
5. Test both `default` and `mysql` modes if identifier quoting matters

### Debugging Test Failures

**Mock PDO errors** (`Call to a member function getAttribute() on null`):
- Ensure CRUD mock receives PDO in constructor
- PDO mock must implement `getAttribute()` returning driver name

**Query syntax mismatches**:
- Check mode parameter in test (`default` vs `mysql`)
- Verify identifier quotes match mode (`` ` `` for mysql, `"` for default)
- Confirm limit syntax matches mode

**LogicException wrapping**:
- SearchObjects wraps all query building errors with "Invalid search query: "
- Check inner exception message in test expectations

---

## Integration Requirements

### Using This Library

1. Install via Composer: `composer require dealnews/data-mapper-api`
2. Create a `\DealNews\DataMapper\Repository` with your mappers
3. Set up routing with PageMill Router
4. Call `API::executeAction()` when routes match

**Minimal Example**:
```php
$repo = new \DealNews\DataMapper\Repository();
$repo->addMapper("MyObject", new \My\Namespace\MyObjectMapper());

$api = new \DealNews\DataMapperAPI\API();
$router = new \PageMill\Router\Router($api->getAllRoutes());

$route = $router->match();
if (!empty($route)) {
    $api->executeAction(
        $route['action'], 
        $route['tokens'], 
        'https://api.example.com', 
        $repo
    );
}
```

### Mapper Requirements

**For basic CRUD**:
- Implement `\DealNews\DataMapper\Interfaces\Mapper`

**For search functionality**:
- Extend `\DealNews\DB\AbstractMapper`
- Define constants: `DATABASE_NAME`, `TABLE`, `PRIMARY_KEY`

---

## Known Issues & Gotchas

1. **PostgreSQL Mode Confusion**: Code checks for `$this->mode == 'pgsql'` but `'pgsql'` is NOT in `MODES` constant. Use `'default'` mode for PostgreSQL-style syntax.

2. **GetObjects Deprecated**: The `GET /api/{object_name}/` endpoint is deprecated. Use the search endpoint instead for production code.

3. **No OR Filters**: Query DSL only supports AND across filter fields. OR operations are not supported.

4. **Object Property Validation**: UpdateObject validates properties exist on the object. Sending unknown properties throws LogicException with 400 status.

5. **Error Handling**: Base action catches LogicException for 400 errors. Other exceptions are not automatically caught by the base class.

6. **HTTP Method Dependency**: `API::executeAction()` reads `$_SERVER['REQUEST_METHOD']` to determine if POST data should be read from `php://input`.

---

## Testing Commands

```bash
# Run all tests
composer test

# Run linting only
composer lint

# Run PHPUnit only
vendor/bin/phpunit

# Fix code style
composer fix
```

**Coverage**: Current coverage is ~93% line coverage (233/249 lines).

---

## Maintenance Notes

- This is an active library used in production
- Breaking changes require major version bump
- New features should maintain backward compatibility
- Always add tests for new functionality
- Update `HTTP-API-INTERFACE.md` for API changes
