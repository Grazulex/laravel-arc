# Export Formats Examples

This document demonstrates the various export formats available in Laravel Arc DTOs.

## Basic Usage

```php
<?php

use Grazulex\LaravelArc\Support\Traits\ConvertsData;

class UserDto
{
    use ConvertsData;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $status = 'active'
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            status: $model->status ?? 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ];
    }
}
```

## Single DTO Export Formats

### JSON Export
```php
$user = new UserDto(1, 'John Doe', 'john@example.com');
echo $user->toJson();
// {"id":1,"name":"John Doe","email":"john@example.com","status":"active"}

echo $user->toJson(JSON_PRETTY_PRINT);
// {
//     "id": 1,
//     "name": "John Doe",
//     "email": "john@example.com",
//     "status": "active"
// }
```

### YAML Export
```php
echo $user->toYaml();
// id: 1
// name: "John Doe"
// email: "john@example.com"
// status: "active"
```

### CSV Export
```php
echo $user->toCsv();
// id,name,email,status
// 1,"John Doe",john@example.com,active

echo $user->toCsv(includeHeaders: false);
// 1,"John Doe",john@example.com,active
```

### XML Export
```php
echo $user->toXml();
// <?xml version="1.0" encoding="UTF-8"?>
// <dto>
//   <id>1</id>
//   <name>John Doe</name>
//   <email>john@example.com</email>
//   <status>active</status>
// </dto>

echo $user->toXml('user');
// <?xml version="1.0" encoding="UTF-8"?>
// <user>
//   <id>1</id>
//   <name>John Doe</name>
//   <email>john@example.com</email>
//   <status>active</status>
// </user>
```

### TOML Export
```php
echo $user->toToml();
// id = 1
// name = "John Doe"
// email = "john@example.com"
// status = "active"
```

### Markdown Table Export
```php
echo $user->toMarkdownTable();
// | id | name | email | status |
// | --- | --- | --- | --- |
// | 1 | John Doe | john@example.com | active |
```

### PHP Array Export
```php
echo $user->toPhpArray();
// array (
//   'id' => 1,
//   'name' => 'John Doe',
//   'email' => 'john@example.com',
//   'status' => 'active',
// )
```

### Query String Export
```php
echo $user->toQueryString();
// id=1&name=John+Doe&email=john%40example.com&status=active
```

### MessagePack Export (requires msgpack extension)
```php
try {
    $packed = $user->toMessagePack();
    // Binary data ready for transmission
} catch (\RuntimeException $e) {
    echo "MessagePack extension not available";
}
```

## Collection Export Formats

### JSON Collection Export
```php
$users = collect([
    (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
    (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
]);

echo UserDto::collectionToJson($users);
// {"data":[{"id":1,"name":"John Doe","email":"john@example.com","status":"active"},{"id":2,"name":"Jane Smith","email":"jane@example.com","status":"active"}]}
```

### YAML Collection Export
```php
echo UserDto::collectionToYaml($users);
// data:
//   - id: 1
//     name: "John Doe"
//     email: "john@example.com"
//     status: "active"
//   - id: 2
//     name: "Jane Smith"
//     email: "jane@example.com"
//     status: "active"
```

### CSV Collection Export
```php
echo UserDto::collectionToCsv($users);
// id,name,email,status
// 1,"John Doe",john@example.com,active
// 2,"Jane Smith",jane@example.com,active

echo UserDto::collectionToCsv($users, includeHeaders: false);
// 1,"John Doe",john@example.com,active
// 2,"Jane Smith",jane@example.com,active
```

### XML Collection Export
```php
echo UserDto::collectionToXml($users);
// <?xml version="1.0" encoding="UTF-8"?>
// <collection>
//   <item>
//     <id>1</id>
//     <name>John Doe</name>
//     <email>john@example.com</email>
//     <status>active</status>
//   </item>
//   <item>
//     <id>2</id>
//     <name>Jane Smith</name>
//     <email>jane@example.com</email>
//     <status>active</status>
//   </item>
// </collection>

echo UserDto::collectionToXml($users, 'users', 'user');
// <?xml version="1.0" encoding="UTF-8"?>
// <users>
//   <user>
//     <id>1</id>
//     <name>John Doe</name>
//     <email>john@example.com</email>
//     <status>active</status>
//   </user>
//   ...
// </users>
```

### Markdown Table Collection Export
```php
echo UserDto::collectionToMarkdownTable($users);
// | id | name | email | status |
// | --- | --- | --- | --- |
// | 1 | John Doe | john@example.com | active |
// | 2 | Jane Smith | jane@example.com | active |
```

## Real-world Use Cases

### API Response in Multiple Formats
```php
class UserController extends Controller
{
    public function show(User $user, Request $request)
    {
        $dto = UserDto::fromModel($user);
        
        return match ($request->query('format', 'json')) {
            'json' => response()->json($dto->toArray()),
            'xml' => response($dto->toXml(), 200, ['Content-Type' => 'application/xml']),
            'csv' => response($dto->toCsv(), 200, ['Content-Type' => 'text/csv']),
            'yaml' => response($dto->toYaml(), 200, ['Content-Type' => 'application/yaml']),
            default => response()->json($dto->toArray()),
        };
    }
    
    public function index(Request $request)
    {
        $users = User::all();
        
        return match ($request->query('format', 'json')) {
            'json' => response()->json(['data' => UserDto::fromModels($users)->toArray()]),
            'xml' => response(UserDto::collectionToXml($users), 200, ['Content-Type' => 'application/xml']),
            'csv' => response(UserDto::collectionToCsv($users), 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="users.csv"',
            ]),
            'yaml' => response(UserDto::collectionToYaml($users), 200, ['Content-Type' => 'application/yaml']),
            default => response()->json(['data' => UserDto::fromModels($users)->toArray()]),
        };
    }
}
```

### Configuration Export
```php
// Export DTO as TOML configuration
$config = $dto->toToml();
file_put_contents('config.toml', $config);

// Export as YAML for Docker or Kubernetes
$yaml = $dto->toYaml();
file_put_contents('deployment.yaml', $yaml);
```

### Documentation Generation
```php
// Generate Markdown tables for documentation
$examples = UserDto::collectionToMarkdownTable($sampleUsers);
$documentation = "# User API Examples\n\n" . $examples;
file_put_contents('docs/api-examples.md', $documentation);
```

### Data Exchange with Legacy Systems
```php
// XML for SOAP services
$xmlPayload = UserDto::collectionToXml($users, 'users', 'user');

// Query strings for GET requests
$queryParams = $user->toQueryString();
$url = "https://legacy-api.com/users?" . $queryParams;
```

## Performance Considerations

- **JSON**: Fastest for web APIs, smallest payload
- **MessagePack**: Most efficient binary format, requires extension
- **CSV**: Best for data analysis, Excel compatibility
- **XML**: Verbose but widely supported
- **YAML**: Human-readable, good for configuration
- **TOML**: Modern configuration format
- **Markdown**: Perfect for documentation

## Extensions Required

Some formats require PHP extensions:
- **YAML**: `php-yaml` extension (fallback available)
- **MessagePack**: `php-msgpack` extension (throws exception if not available)

Install with:
```bash
# Ubuntu/Debian
sudo apt-get install php-yaml php-msgpack

# CentOS/RHEL
sudo yum install php-yaml php-msgpack

# macOS with Homebrew
brew install php-yaml php-msgpack
```
