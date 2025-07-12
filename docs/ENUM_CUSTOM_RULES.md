# Règles de validation personnalisées pour les Enums PHP 8.0

Laravel Arc prend en charge les classes enum PHP 8.0 natives et propose des règles de validation personnalisées pour une validation plus granulaire et flexible.

## Vue d'ensemble

En plus de la règle `enum:` standard de Laravel, Laravel Arc propose deux règles de validation personnalisées :

- `in_enum` : Alternative à la règle `enum:` avec des vérifications supplémentaires
- `enum_exists` : Vérifie l'existence de la classe enum et valide la valeur

**💡 Nouveau !** Les DTOs générés incluent maintenant des traits puissants pour la validation, la conversion et les utilitaires. Voir le [Guide des Traits](TRAITS_GUIDE.md) pour plus de détails.

## Configuration YAML

### Enum de base avec classe PHP

```yaml
fields:
  status:
    type: enum
    class: App\Enums\Status
    
  priority:
    type: enum
    class: App\Enums\Priority
    nullable: true
```

### Enum avec règles personnalisées

```yaml
fields:
  status:
    type: enum
    class: App\Enums\Status
    rules:
      - in_enum        # Règle personnalisée avec vérifications supplémentaires
      - required       # Règle Laravel standard
      
  priority:
    type: enum
    class: App\Enums\Priority
    nullable: true
    rules:
      - enum_exists    # Vérifie l'existence de l'enum
      - in_enum        # Validation avec vérifications supplémentaires
```

## Exemples d'enums PHP 8.0

### Enum Backed (avec valeurs)

```php
<?php

namespace App\Enums;

enum Status: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case COMPLETED = 'completed';
}
```

### Enum Pure (sans valeurs)

```php
<?php

namespace App\Enums;

enum Priority
{
    case LOW;
    case MEDIUM;
    case HIGH;
    case URGENT;
}
```

## Génération de DTO

### Résultat pour un enum backed

```php
<?php

namespace App\Dto;

use App\Enums\Status;

class UserDto
{
    public function __construct(
        public ?Status $status = null,
    ) {
    }
}
```

### Résultat pour un enum pure

```php
<?php

namespace App\Dto;

use App\Enums\Priority;

class TaskDto
{
    public function __construct(
        public ?Priority $priority = null,
    ) {
    }
}
```

## Règles de validation générées

### Avec la règle `in_enum`

```php
'status' => [
    'nullable',
    'enum:App\Enums\Status',
    'in_enum:App\Enums\Status',
],
```

### Avec la règle `enum_exists`

```php
'priority' => [
    'required',
    'enum:App\Enums\Priority',
    'enum_exists:App\Enums\Priority',
],
```

## Règles personnalisées détaillées

### `in_enum` Rule

La règle `in_enum` fournit une alternative à la règle `enum:` native de Laravel avec des vérifications supplémentaires :

- Vérifie que la classe enum existe
- Vérifie que c'est bien un enum PHP 8.0
- Pour les enums backed : compare la valeur avec `$case->value`
- Pour les enums purs : compare avec `$case->name`
- Gestion robuste des erreurs

```php
// Utilisation manuelle
$rule = new InEnum(Status::class);
$validator = Validator::make($data, [
    'status' => [$rule],
]);
```

### `enum_exists` Rule

La règle `enum_exists` vérifie l'existence et la validité de l'enum :

- Vérifie que la classe enum existe
- Vérifie que c'est bien un enum PHP 8.0
- Utilise `tryFrom()` pour les enums backed
- Parcourt les cases pour les enums purs
- Gestion des cas d'erreur

```php
// Utilisation manuelle
$rule = new EnumExists(Priority::class);
$validator = Validator::make($data, [
    'priority' => [$rule],
]);
```

## Exemples d'utilisation complète

### Fichier YAML complet

```yaml
# example-enum-validation.yaml
dto:
  name: OrderDto
  namespace: App\Dto
  
fields:
  status:
    type: enum
    class: App\Enums\OrderStatus
    required: true
    rules:
      - in_enum
      
  priority:
    type: enum
    class: App\Enums\Priority
    nullable: true
    rules:
      - enum_exists
      
  category:
    type: enum
    class: App\Enums\Category
    rules:
      - in_enum
      - enum_exists
```

### DTO généré

```php
<?php

namespace App\Dto;

use App\Enums\OrderStatus;
use App\Enums\Priority;
use App\Enums\Category;

class OrderDto
{
    public function __construct(
        public OrderStatus $status,
        public ?Priority $priority = null,
        public ?Category $category = null,
    ) {
    }
}
```

### Validation générée

```php
return [
    'status' => [
        'required',
        'enum:App\Enums\OrderStatus',
        'in_enum:App\Enums\OrderStatus',
    ],
    'priority' => [
        'nullable',
        'enum:App\Enums\Priority',
        'enum_exists:App\Enums\Priority',
    ],
    'category' => [
        'enum:App\Enums\Category',
        'in_enum:App\Enums\Category',
        'enum_exists:App\Enums\Category',
    ],
];
```

## Avantages des règles personnalisées

1. **Vérifications supplémentaires** : S'assure que la classe enum existe et est valide
2. **Compatibilité** : Fonctionne avec les enums backed et purs
3. **Flexibilité** : Peut être combinée avec d'autres règles Laravel
4. **Robustesse** : Gestion des erreurs et des cas limites
5. **Messages d'erreur clairs** : Messages personnalisés pour chaque type d'erreur

## Compatibilité Laravel

- **Laravel 9+** : Support complet des règles `enum:` et des règles personnalisées
- **Laravel 8** : Les règles personnalisées fonctionnent, mais pas la règle `enum:` native
- **PHP 8.0+** : Requis pour les enums natifs
