# CLI Commands Reference

Laravel Arc provides powerful CLI commands to help you manage your DTOs efficiently. This guide covers all available commands with examples and usage patterns.

## Available Commands

| Command | Description |
|---------|-------------|
| `dto:generate` | Generate DTOs from YAML definitions |
| `dto:definition-list` | List all available DTO definition files |
| `dto:definition-init` | Create new DTO definition files |

## dto:generate

Generate DTOs from YAML definition files.

### Basic Usage

```bash
# Generate a single DTO
php artisan dto:generate user.yaml

# Generate with custom output path
php artisan dto:generate user.yaml --output=/custom/path/UserDTO.php

# Generate all definitions
php artisan dto:generate --all

# Preview without saving (dry run)
php artisan dto:generate user.yaml --dry-run

# Force overwrite existing files
php artisan dto:generate user.yaml --force
```

### Options

| Option | Description | Example |
|--------|-------------|---------|
| `--output` | Custom output path | `--output=/custom/path/UserDTO.php` |
| `--all` | Generate all YAML files | `--all` |
| `--dry-run` | Preview code without saving | `--dry-run` |
| `--force` | Overwrite existing files | `--force` |

### Examples

#### Generate Single DTO

```bash
php artisan dto:generate user.yaml
```

**Output:**
```
✅ DTO generated successfully!
   File: /path/to/app/DTOs/UserDTO.php
   Namespace: App\DTOs
   Class: UserDTO
```

#### Generate with Custom Output

```bash
php artisan dto:generate user.yaml --output=/custom/path/UserDTO.php
```

**Output:**
```
✅ DTO generated successfully!
   File: /custom/path/UserDTO.php
   Namespace: App\DTOs
   Class: UserDTO
```

#### Generate All DTOs

```bash
php artisan dto:generate --all
```

**Output:**
```
✅ Generated 5 DTOs successfully!
   
   Files generated:
   • /path/to/app/DTOs/UserDTO.php
   • /path/to/app/DTOs/ProductDTO.php
   • /path/to/app/DTOs/OrderDTO.php
   • /path/to/app/DTOs/ProfileDTO.php
   • /path/to/app/DTOs/CompanyDTO.php
```

#### Dry Run Preview

```bash
php artisan dto:generate user.yaml --dry-run
```

**Output:**
```
🔍 Dry run mode - no files will be written

Generated code for UserDTO:
=====================================
<?php
declare(strict_types=1);

namespace App\DTOs;

final class UserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?\DateTime $email_verified_at = null,
    ) {}
}
=====================================

Would be saved to: /path/to/app/DTOs/UserDTO.php
```

#### Force Overwrite

```bash
php artisan dto:generate user.yaml --force
```

**Output:**
```
⚠️  Overwriting existing file...
✅ DTO generated successfully!
   File: /path/to/app/DTOs/UserDTO.php
   Namespace: App\DTOs
   Class: UserDTO
```

## dto:definition-list

List all available DTO definition files in your definitions directory.

### Basic Usage

```bash
# List all definitions
php artisan dto:definition-list

# Compact view (names only)
php artisan dto:definition-list --compact

# Custom definitions path
php artisan dto:definition-list --path=/custom/path
```

### Options

| Option | Description | Example |
|--------|-------------|---------|
| `--compact` | Show compact view | `--compact` |
| `--path` | Custom definitions path | `--path=/custom/path` |

### Examples

#### Standard View

```bash
php artisan dto:definition-list
```

**Output:**
```
📋 Available DTO Definitions
============================

Found 5 definition files in /path/to/database/dto_definitions:

📄 user.yaml
   ├── DTO: UserDTO
   ├── Table: users
   ├── Model: App\Models\User
   └── Namespace: App\DTOs

📄 product.yaml
   ├── DTO: ProductDTO
   ├── Table: products
   ├── Model: App\Models\Product
   └── Namespace: App\DTOs

📄 order.yaml
   ├── DTO: OrderDTO
   ├── Table: orders
   ├── Model: App\Models\Order
   └── Namespace: App\DTOs\Ecommerce

📄 profile.yaml
   ├── DTO: ProfileDTO
   ├── Table: profiles
   ├── Model: App\Models\Profile
   └── Namespace: App\DTOs

📄 company.yaml
   ├── DTO: CompanyDTO
   ├── Table: companies
   ├── Model: App\Models\Company
   └── Namespace: App\DTOs
```

#### Compact View

```bash
php artisan dto:definition-list --compact
```

**Output:**
```
📋 Available DTO Definitions
============================

user.yaml       → UserDTO
product.yaml    → ProductDTO
order.yaml      → OrderDTO
profile.yaml    → ProfileDTO
company.yaml    → CompanyDTO
```

#### Custom Path

```bash
php artisan dto:definition-list --path=/custom/definitions
```

**Output:**
```
📋 Available DTO Definitions
============================

Found 2 definition files in /custom/definitions:

📄 custom-user.yaml
   ├── DTO: CustomUserDTO
   ├── Table: users
   ├── Model: App\Models\User
   └── Namespace: Custom\DTOs

📄 custom-product.yaml
   ├── DTO: CustomProductDTO
   ├── Table: products
   ├── Model: App\Models\Product
   └── Namespace: Custom\DTOs
```

## dto:definition-init

Create new DTO definition files with basic structure.

### Basic Usage

```bash
# Create basic definition
php artisan dto:definition-init UserDTO

# Create with model and table
php artisan dto:definition-init UserDTO --model=App\Models\User --table=users

# Create with custom path
php artisan dto:definition-init ProductDTO --model=App\Models\Product --table=products --path=/custom/path

# Force overwrite existing file
php artisan dto:definition-init UserDTO --model=App\Models\User --table=users --force
```

### Options

| Option | Description | Example |
|--------|-------------|---------|
| `--model` | Associated Eloquent model | `--model=App\Models\User` |
| `--table` | Database table name | `--table=users` |
| `--path` | Custom output path | `--path=/custom/path` |
| `--force` | Overwrite existing files | `--force` |

### Examples

#### Basic Definition

```bash
php artisan dto:definition-init UserDTO
```

**Output:**
```
✅ DTO definition created successfully!
   File: /path/to/database/dto_definitions/UserDTO.yaml
   
   Next steps:
   1. Edit the definition file to add your fields
   2. Run: php artisan dto:generate UserDTO.yaml
```

**Generated file (`UserDTO.yaml`):**
```yaml
header:
  dto: UserDTO

fields:
  id:
    type: integer
    required: true
  
  # Add your fields here
  name:
    type: string
    required: true
    rules: [min:2, max:100]

options:
  timestamps: true
  namespace: App\DTOs
```

#### With Model and Table

```bash
php artisan dto:definition-init UserDTO --model=App\Models\User --table=users
```

**Output:**
```
✅ DTO definition created successfully!
   File: /path/to/database/dto_definitions/UserDTO.yaml
   
   Configuration:
   • Model: App\Models\User
   • Table: users
   
   Next steps:
   1. Edit the definition file to add your fields
   2. Run: php artisan dto:generate UserDTO.yaml
```

**Generated file (`UserDTO.yaml`):**
```yaml
header:
  dto: UserDTO
  table: users
  model: App\Models\User

fields:
  id:
    type: integer
    required: true
  
  # Add your fields here
  name:
    type: string
    required: true
    rules: [min:2, max:100]

options:
  timestamps: true
  namespace: App\DTOs
```

#### Custom Path

```bash
php artisan dto:definition-init ProductDTO --model=App\Models\Product --table=products --path=/custom/definitions
```

**Output:**
```
✅ DTO definition created successfully!
   File: /custom/definitions/ProductDTO.yaml
   
   Configuration:
   • Model: App\Models\Product
   • Table: products
   • Path: /custom/definitions
```

## Command Workflows

### Complete DTO Creation Workflow

```bash
# 1. Create definition
php artisan dto:definition-init UserDTO --model=App\Models\User --table=users

# 2. Edit the generated YAML file
# (Add your fields, relationships, etc.)

# 3. Preview the generated code
php artisan dto:generate UserDTO.yaml --dry-run

# 4. Generate the DTO
php artisan dto:generate UserDTO.yaml

# 5. Verify the generated file
php artisan dto:definition-list
```

### Batch Processing

```bash
# Create multiple definitions
php artisan dto:definition-init UserDTO --model=App\Models\User --table=users
php artisan dto:definition-init ProductDTO --model=App\Models\Product --table=products
php artisan dto:definition-init OrderDTO --model=App\Models\Order --table=orders

# Edit all YAML files...

# Generate all DTOs at once
php artisan dto:generate --all
```

### Development and Testing

```bash
# Test changes with dry run
php artisan dto:generate user.yaml --dry-run

# Generate with force to overwrite during development
php artisan dto:generate user.yaml --force

# List all definitions to verify
php artisan dto:definition-list --compact
```

## Error Handling

### Common Error Messages

#### File Not Found
```bash
php artisan dto:generate nonexistent.yaml
```

**Output:**
```
❌ DTO Generation Error (File Not Found)

Error: YAML definition file not found
File: /path/to/database/dto_definitions/nonexistent.yaml

💡 Suggestions:
  • Check the filename and path
  • Use 'php artisan dto:definition-list' to see available files
  • Create the file with 'php artisan dto:definition-init'
```

#### Invalid YAML Syntax
```bash
php artisan dto:generate invalid.yaml
```

**Output:**
```
❌ DTO Generation Error (YAML Parsing)

Error: YAML parsing failed: Syntax error on line 5
File: /path/to/database/dto_definitions/invalid.yaml

💡 Suggestions:
  • Check YAML syntax and indentation
  • Verify all required sections are present (header, fields)
  • Use 'php artisan dto:generate invalid.yaml --dry-run' to test
```

#### File Already Exists
```bash
php artisan dto:generate user.yaml
```

**Output:**
```
❌ DTO Generation Error (File Exists)

Error: DTO file already exists
File: /path/to/app/DTOs/UserDTO.php

💡 Suggestions:
  • Use --force to overwrite: php artisan dto:generate user.yaml --force
  • Choose a different output path: --output=/different/path/UserDTO.php
  • Delete the existing file manually
```

## Best Practices

### 1. Use Meaningful Names

```bash
# Good
php artisan dto:definition-init UserProfileDTO
php artisan dto:definition-init ProductCatalogDTO

# Less clear
php artisan dto:definition-init DataDTO
php artisan dto:definition-init InfoDTO
```

### 2. Preview Before Generating

```bash
# Always preview complex DTOs
php artisan dto:generate complex-order.yaml --dry-run

# Then generate
php artisan dto:generate complex-order.yaml
```

### 3. Use Batch Operations

```bash
# Generate all DTOs after making changes
php artisan dto:generate --all

# List to verify
php artisan dto:definition-list --compact
```

### 4. Version Control Integration

```bash
# Add to your deployment scripts
php artisan dto:generate --all --force

# Or add to composer scripts
"scripts": {
    "post-install-cmd": [
        "php artisan dto:generate --all"
    ]
}
```

## Advanced Usage

### Custom Scripts

Create custom scripts for complex workflows:

```bash
#!/bin/bash
# generate-all-dtos.sh

echo "🚀 Generating all DTOs..."

# Generate core DTOs
php artisan dto:generate user.yaml --force
php artisan dto:generate product.yaml --force
php artisan dto:generate order.yaml --force

# Generate nested DTOs
php artisan dto:generate profile.yaml --force
php artisan dto:generate address.yaml --force

echo "✅ All DTOs generated successfully!"
```

### Integration with CI/CD

```yaml
# .github/workflows/generate-dtos.yml
name: Generate DTOs

on:
  push:
    paths:
      - 'database/dto_definitions/**'

jobs:
  generate-dtos:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
      - name: Install dependencies
        run: composer install
      - name: Generate DTOs
        run: php artisan dto:generate --all --force
      - name: Commit generated files
        run: |
          git config user.name "GitHub Actions"
          git config user.email "actions@github.com"
          git add app/DTOs/
          git commit -m "Auto-generate DTOs" || exit 0
          git push
```

## See Also

- [Getting Started Guide](GETTING_STARTED.md)
- [YAML Schema Documentation](YAML_SCHEMA.md)
- [Field Types Reference](FIELD_TYPES.md)
- [Advanced Usage](ADVANCED_USAGE.md)