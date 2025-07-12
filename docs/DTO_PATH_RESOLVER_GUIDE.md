# DtoPathResolver - Guide d'utilisation

Le `DtoPathResolver` est une classe utilitaire qui centralise la logique de résolution de chemins et de namespaces pour la génération de DTOs. Cette classe offre des méthodes bidirectionnelles pour convertir entre namespaces et chemins de fichiers.

## Fonctionnalités principales

### 1. Résolution de chemin de sortie

```php
use Grazulex\LaravelArc\Support\DtoPathResolver;

// Résoudre le chemin de sortie basé sur le namespace
$path = DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTOs\Admin');
// Résultat: /path/to/app/DTOs/Admin/UserDTO.php
```

### 2. Dérivation de namespace depuis un chemin

```php
// Dériver le namespace depuis un chemin de fichier
$namespace = DtoPathResolver::resolveNamespaceFromPath('/path/to/app/DTOs/Admin/UserDTO.php');
// Résultat: App\DTOs\Admin
```

### 3. Validation de namespaces

```php
// Valider un namespace
$isValid = DtoPathResolver::isValidNamespace('App\DTOs\Admin');
// Résultat: true

$isValid = DtoPathResolver::isValidNamespace('App\\DTOs'); // Double backslash
// Résultat: false
```

### 4. Normalisation de namespaces

```php
// Normaliser un namespace
$normalized = DtoPathResolver::normalizeNamespace('\App\DTOs\Admin\\');
// Résultat: App\DTOs\Admin
```

### 5. Relations de sous-namespaces

```php
// Vérifier si un namespace est un sous-namespace d'un autre
$isSubNamespace = DtoPathResolver::isSubNamespaceOf('App\DTOs\Admin', 'App\DTOs');
// Résultat: true

$isSubNamespace = DtoPathResolver::isSubNamespaceOf('App\DTOs', 'App\DTOs');
// Résultat: false (même namespace)
```

## Exemples d'utilisation

### Configuration standard

Avec la configuration standard (`dto.output_path` = `app/DTOs` et `dto.namespace` = `App\DTOs`):

```php
// Namespace de base
DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTOs');
// → /path/to/app/DTOs/UserDTO.php

// Sous-namespace
DtoPathResolver::resolveOutputPath('AdminUserDTO', 'App\DTOs\Admin');
// → /path/to/app/DTOs/Admin/AdminUserDTO.php

// Sous-namespace profond
DtoPathResolver::resolveOutputPath('ProductDTO', 'App\DTOs\Admin\Catalog\Products');
// → /path/to/app/DTOs/Admin/Catalog/Products/ProductDTO.php
```

### Namespace externe

Pour des namespaces complètement différents de la configuration de base :

```php
DtoPathResolver::resolveOutputPath('ExternalDTO', 'Library\External\Data');
// → /path/to/Library/External/Data/ExternalDTO.php
```

### Conversion bidirectionnelle

```php
$originalNamespace = 'App\DTOs\Admin\Users';
$dtoName = 'UserDTO';

// 1. Résoudre le chemin
$path = DtoPathResolver::resolveOutputPath($dtoName, $originalNamespace);

// 2. Dériver le namespace depuis le chemin
$derivedNamespace = DtoPathResolver::resolveNamespaceFromPath($path);

// 3. Vérifier la cohérence
assert($derivedNamespace === $originalNamespace); // true
```

## Intégration avec la commande artisan

La classe est automatiquement utilisée par la commande `dto:generate` :

```yaml
# admin-user.yaml
header:
  dto: AdminUserDTO
  
options:
  namespace: App\DTOs\Admin
  
fields:
  id:
    type: integer
    required: true
  name:
    type: string
    required: true
```

```bash
php artisan dto:generate admin-user.yaml
```

Le DTO sera généré dans `app/DTOs/Admin/AdminUserDTO.php` avec le namespace `App\DTOs\Admin`.

## Gestion des cas spéciaux

### Chemins Windows

La classe gère automatiquement les chemins Windows :

```php
$windowsPath = 'C:\path\to\app\DTOs\Admin\UserDTO.php';
$namespace = DtoPathResolver::resolveNamespaceFromPath($windowsPath);
// Résultat: App\DTOs\Admin (identique aux chemins Unix)
```

### Acronymes et abréviations

La classe préserve les acronymes connus :

```php
$namespace = DtoPathResolver::resolveNamespaceFromPath('/path/to/app/DTOs/APIs/UserDTO.php');
// Résultat: App\DTOs\APIs (pas App\DTOs\Apis)
```

## Validation et erreurs

### Namespaces valides

- `App\DTOs`
- `App\DTOs\Admin`
- `MyCompany\Project\DTOs`
- `_Underscore\Name`

### Namespaces invalides

- `""` (vide)
- `App\\DTOs` (backslashes consécutifs)
- `App\DTOs\` (backslash final)
- `\App\DTOs` (backslash initial)
- `App\123Invalid` (commence par un chiffre)
- `App\Invalid-Name` (caractère invalide)

## Bonnes pratiques

1. **Utilisez toujours la validation** : Validez vos namespaces avant de les utiliser
2. **Normalisez les entrées** : Utilisez `normalizeNamespace()` pour nettoyer les entrées utilisateur
3. **Testez la bidirectionnalité** : Vérifiez que vos conversions sont cohérentes
4. **Respectez les conventions** : Suivez les conventions de nommage PHP pour les namespaces

## Tests

La classe est entièrement testée avec des tests unitaires et d'intégration :

```bash
# Tests unitaires
php vendor/bin/pest tests/Unit/Support/DtoPathResolverTest.php

# Tests d'intégration
php vendor/bin/pest tests/Feature/DtoPathResolverIntegrationTest.php
```
