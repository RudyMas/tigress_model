# Tigress Model — Programmer's Manual

## Overview

`tigress/model` is the model component of the Tigress Framework, a PHP 8.5+ library providing:

- **`Tigress\Model`** — A base class that manages an associative array of typed properties with type validation, getter/setter magic methods, and full `Iterator` support for `foreach`.
- **`Tigress\DBModel`** — Extends `Model` to auto-generate typed properties from a MySQL/MariaDB table schema via `DESCRIBE`.
- **`Model\DefaultModel`** — A minimal concrete example extending `Model`.

## Installation

```bash
composer require tigress/model
```

Requires PHP `>= 8.5`. No other runtime dependencies.

## Class Hierarchy

```
Tigress\Model (implements Iterator)
    └── Tigress\DBModel (auto-generates from DESCRIBE)
```

You may also create your own classes extending `Model` or `DBModel` directly.

---

## 1. Tigress\Model — Base Model

### Defining Properties

Properties are **not** declared as class fields. Instead they are stored internally in a private `$properties` array keyed by name. Each property has an associated PHP type string stored in `$types`.

Properties are initialized via `initiateModel()`:

```php
$model = new MyModel();
$model->initiateModel([
    'id'      => ['value' => 0,    'type' => 'integer'],
    'name'    => ['value' => '',   'type' => 'string'],
    'price'   => ['value' => 0.0,  'type' => 'float'],
    'created' => ['value' => '',   'type' => 'string'],
]);
```

### Constructor

```php
public function __construct(?object $data = null)
```

If an object is passed, `update($data)` is called immediately to populate properties (by property name matching — no type validation on this path).

### Type System & Magic Setters

When a property is set via `$model->property = $value`, `__set()` is invoked which:

1. Checks the property exists.
2. Compares `gettype($value)` against the stored type.
3. Allows empty strings and `null` (when `$value !== '' && $actualType !== 'NULL'`).
4. Throws `Exception` on type mismatch.

```php
$model->name = 'Alice';   // OK
$model->id = 'abc';       // Throws: Type mismatch for property id (abc). Expected integer, got string.
```

> **Known issue:** `gettype()` returns `"double"` for float values in PHP, but the library stores the type as `"float"`. Setting a float property via `$model->price = 3.14` will throw a type mismatch. Always use `initiateModel()`, `update()`, or `updateByArray()` for float properties, which bypass `__set`.

### Magic Getter

```php
public function __get(string $property): mixed
```

Returns the property value or throws `Exception` if the property does not exist.

### Batch Updates

Three methods update multiple properties at once. **None perform type validation** — they write directly to the internal array.

| Method | Input | Use Case |
|---|---|---|
| `update(object $data)` | Object with public properties | Hydrating from a database row or API response |
| `updateByArray(array $data)` | Associative array | Hydrating from an array |
| `updateByPost(array $data)` | Associative array | Alias for `updateByArray()` (intended for `$_POST`) |

Only keys that already exist as model properties are accepted; unknown keys are silently ignored.

```php
$model->update((object) ['id' => 1, 'name' => 'Bob']);
$model->updateByArray(['id' => 2, 'name' => 'Charlie']);
$model->updateByPost($_POST);
```

### Checking Properties

```php
$model->has('name');       // true/false — does the property exist?
$model->isset('name');     // true/false — is the array key set?
__isset($model->name);     // same, via magic method
```

Note: `isset()` checks via `array_key_exists()`, not PHP's `isset()`, so it returns `true` even for `null` values.

### Getting Type Information

```php
$model->getType('id');     // 'integer'
$model->getTypes();        // ['id' => 'integer', 'name' => 'string', ...]
```

### Getting All Properties

```php
$model->getProperties();   // ['id' => 1, 'name' => 'Bob', ...]
```

### Iterator Support

The class implements `Iterator`, so you can `foreach` over a model instance:

```php
foreach ($model as $key => $value) {
    echo "$key: $value\n";
}
```

Iteration covers only registered properties, in the order they were defined.

### Version

```php
echo Model::version();   // '2026.01.22'
```

---

## 2. Tigress\DBModel — Database-Backed Model

`DBModel` auto-generates properties by reading a MySQL/MariaDB table schema.

### Constructor

```php
public function __construct(Database $db, string $table, ?object $data = null)
```

| Parameter | Type | Description |
|---|---|---|
| `$db` | `Database` | A database connection object (must provide `query()`, `getRows()`, and `fetchAll()` methods — the Tigress Database class). |
| `$table` | `string` | The table name to introspect. |
| `$data` | `?object` | Optional initial data (passed to parent constructor). |

On construction, `DBModel` runs `DESCRIBE <tablename>` and maps SQL column types to PHP types:

| SQL Type Pattern | PHP Type |
|---|---|
| `int`, `tinyint`, `smallint`, `mediumint`, `bigint` | `integer` |
| `float`, `double`, `decimal` | `float` |
| `varchar`, `text`, `char`, `blob` | `string` |
| `date`, `time`, `datetime`, `timestamp` | `string` |

Default values are populated:
- **Nullable columns** (`Null = 'YES'`): value set to the literal **string** `'null'` (not PHP `null` — see known issues).
- **Non-nullable columns**: uses the database default, or a type-appropriate empty value (`0`, `0.0`, `''`, or `'0000-00-00 00:00:00'` for datetime types).

### Example

```php
use Tigress\DBModel;

// Assuming $db is an established Tigress Database connection
$user = new DBModel($db, 'users', null);
echo $user->getType('email');     // 'string'
echo $user->getType('id');        // 'integer'
$user->updateByArray(['id' => 42, 'email' => 'a@b.com']);
```

### Version

```php
echo DBModel::version();   // '2025.12.09.0'
```

---

## 3. Creating Custom Models

### Extending Model (standalone, no DB)

```php
use Tigress\Model;

class User extends Model
{
    public function __construct(?object $data = null)
    {
        $this->initiateModel([
            'id'       => ['value' => 0,    'type' => 'integer'],
            'username' => ['value' => '',   'type' => 'string'],
            'email'    => ['value' => '',   'type' => 'string'],
            'score'    => ['value' => 0.0,  'type' => 'float'],
        ]);
        parent::__construct($data);
    }
}

$user = new User();
$user->updateByPost($_POST);
```

### Extending DBModel (auto-generated from table)

```php
use Tigress\DBModel;

class User extends DBModel
{
    public function __construct(Database $db, ?object $data = null)
    {
        parent::__construct($db, 'users', $data);
    }
}

$user = new User($db);
```

If your custom model needs additional computed properties or methods, add them as regular class methods.

---

## 4. Code Review — Notable Issues

1. **Float type mismatch (`src/Model.php:96-100`):** `__set()` uses `gettype($value)` for validation. PHP's `gettype()` returns `"double"` for float values, but the library stores types as `"float"`. Any assignment like `$model->price = 1.99` via `__set` will throw a type mismatch exception. The `update()`, `updateByArray()`, and `updateByPost()` methods bypass `__set` entirely (no type checking), which is why float values work through those paths.

2. **Nullable default is string `'null'` (`src/DBModel.php:69`):** When a column is nullable (`Null = 'YES'`), the default value is set to the *string* `'null'`, not PHP's `null`. This means `$model->nullableField === 'null'` (string) rather than `$model->nullableField === null`. This is likely unintentional.

3. **`update()` skips type validation (`src/Model.php:228-235`):** Unlike `__set()`, the batch update methods write directly to `$this->properties` without checking types. Type consistency relies on the caller.

4. **Unquoted table name in SQL (`src/DBModel.php:55`):** `"DESCRIBE " . $this->table` directly interpolates the table name without backtick quoting or prepared statements. If the table name comes from untrusted input, this is a SQL injection vector. Table names should be wrapped in backticks and validated.

5. **`DefaultModel` serves no runtime purpose:** `src/models/DefaultModel.php` is an empty extension of `Model` in the `Model\` namespace. It acts as a usage example but offers no functionality beyond what `Model` already provides.

6. **`updateByPost()` is redundant (`src/Model.php:258-261`):** The method simply delegates to `updateByArray()` with no additional processing (no filtering, no sanitization). Consider adding CSRF token validation, input trimming, or type coercion if this is meant for HTTP request handling.

---

## 5. Quick Reference

| Method | Description | Type Validation |
|---|---|---|
| `initiateModel(array $data)` | Define properties and types | No (sets raw arrays) |
| `__get(string $property)` | Get property value | N/A |
| `__set(string $property, $value)` | Set property value | Yes (but broken for `float`) |
| `update(object $data)` | Batch update from object | No |
| `updateByArray(array $data)` | Batch update from array | No |
| `updateByPost(array $data)` | Batch update for POST data | No |
| `has(string $property)` | Check property exists | N/A |
| `isset(string $property)` | Check property key is set | N/A |
| `getProperties()` | Get all property values | N/A |
| `getType(string $property)` | Get property type | N/A |
| `getTypes()` | Get all property types | N/A |
| `current()` / `key()` / `next()` / `rewind()` / `valid()` | Iterator interface | N/A |
