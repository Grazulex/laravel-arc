# Laravel Arc - Gestion de Collections DTO

## Vue d'ensemble

Laravel Arc fournit une fonctionnalité complète de gestion de collections pour les DTOs, similaire aux Laravel API Resources mais avec des avantages supplémentaires :

- **Conversion automatique** : Transformez facilement des modèles Eloquent en DTOs
- **Collections spécialisées** : Utilisez `DtoCollection` pour des fonctionnalités avancées
- **Format API** : Sortie automatique au format JSON API standard
- **Validation intégrée** : Validation des données avec gestion d'erreurs
- **Méthodes de collection** : Filtrage, groupement, pagination, etc.

## Comparaison avec Laravel Resources

### Laravel Resources (Traditionnel)
```php
// Contrôleur
return UserResource::collection($users);
return new UserResource($user);

// UserResource
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### Laravel Arc DTOs (Nouveau)
```php
// Contrôleur
return UserDto::fromModels($users)->toArrayResource();
return UserDto::fromModel($user)->toArray();

// UserDto (généré automatiquement)
class UserDto
{
    use ConvertsData, ValidatesData, DtoUtilities;
    
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
    
    // Méthodes générées automatiquement
    public static function fromModel($model): self { ... }
    public function toArray(): array { ... }
    public function isValid(): bool { ... }
}
```

## Utilisation de base

### 1. Convertir des modèles en DTOs

```php
// Un seul modèle
$user = User::find(1);
$userDto = UserDto::fromModel($user);

// Collection de modèles
$users = User::all();
$userDtos = UserDto::fromModels($users); // Retourne DtoCollection

// Collection standard Laravel
$standardCollection = UserDto::fromModelsAsCollection($users);
```

### 2. Format API Resource

```php
// Obtenir le format API standard
$users = User::all();
$userDtos = UserDto::fromModels($users);

// Format tableau
$apiArray = $userDtos->toArrayResource();
// Résultat : ['data' => [...]]

// Format JSON
$apiJson = $userDtos->toJsonResource();
// Résultat : '{"data": [...]}'

// Avec méta-données
$apiWithMeta = $userDtos->toArrayResource([
    'total' => 100,
    'page' => 1
]);
// Résultat : ['data' => [...], 'meta' => [...]]
```

### 3. Gestion de la pagination

```php
// Pagination automatique
$users = User::paginate(15);
$result = UserDto::fromPaginator($users);

return response()->json($result);
// Résultat :
// {
//   "data": [...],
//   "meta": {
//     "current_page": 1,
//     "per_page": 15,
//     "total": 100,
//     "last_page": 7,
//     "has_more_pages": true
//   }
// }
```

## Fonctionnalités avancées

### 1. Filtrage et groupement

```php
$users = User::all();
$userDtos = UserDto::fromModels($users);

// Filtrage
$activeUsers = $userDtos->where('status', 'active');
$adminUsers = $userDtos->filter(fn($dto) => $dto->role === 'admin');

// Groupement
$groupedByStatus = $userDtos->groupBy('status');
$groupedByRole = $userDtos->groupBy('role');

// Tri
$sortedByName = $userDtos->sortBy('name');
$sortedByIdDesc = $userDtos->sortByDesc('id');
```

### 2. Sélection de champs

```php
$userDto = UserDto::fromModel($user);

// Sélectionner seulement certains champs
$minimal = $userDto->only(['id', 'name', 'email']);

// Exclure certains champs
$withoutSensitive = $userDto->except(['password', 'remember_token']);
```

### 3. Validation intégrée

```php
// Validation lors de la création
$userDto = UserDto::fromArray($request->all());

if (!$userDto->isValid()) {
    return response()->json([
        'message' => 'Validation failed',
        'errors' => $userDto->getErrors()
    ], 422);
}

// Validation d'une collection
$userDtos = UserDto::fromModels($users);
$invalid = $userDtos->reject(fn($dto) => $dto->isValid());
```

### 4. Statistiques et agrégations

```php
$userDtos = UserDto::fromModels($users);

// Comptage
$total = $userDtos->count();
$activeCount = $userDtos->where('status', 'active')->count();

// Statistiques
$stats = [
    'total' => $userDtos->count(),
    'by_status' => $userDtos->groupBy('status')->map->count(),
    'active_percentage' => $userDtos->where('status', 'active')->count() / $userDtos->count() * 100
];
```

## Exemples d'utilisation en contrôleur

### Contrôleur API complet

```php
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDto::fromModels($users);
        
        return response()->json(
            $userDtos->toArrayResource()
        );
    }
    
    public function paginated(Request $request): JsonResponse
    {
        $users = User::paginate($request->get('per_page', 15));
        $result = UserDto::fromPaginator($users);
        
        return response()->json($result);
    }
    
    public function filtered(Request $request): JsonResponse
    {
        $users = User::where('status', $request->get('status'))->get();
        $userDtos = UserDto::fromModels($users);
        
        if ($request->has('role')) {
            $userDtos = $userDtos->where('role', $request->get('role'));
        }
        
        return response()->json([
            'data' => $userDtos->toArray(),
            'meta' => ['total' => $userDtos->count()]
        ]);
    }
    
    public function stats(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDto::fromModels($users);
        
        return response()->json([
            'total' => $userDtos->count(),
            'by_status' => $userDtos->groupBy('status')->map->count(),
            'recent' => $userDtos->sortByDesc('created_at')->take(10)->toArray()
        ]);
    }
}
```

## Génération automatique

### Fichier YAML

```yaml
# user.yaml
name: User
namespace: App\DTOs

fields:
  id:
    type: integer
    validation: [required, integer, min:1]
  
  name:
    type: string
    validation: [required, string, max:255]
  
  email:
    type: string
    validation: [required, email, max:255]
  
  status:
    type: string
    default: "active"
    validation: [required, in:active,inactive,pending]

options:
  use_traits:
    - "Grazulex\\LaravelArc\\Support\\Traits\\ConvertsData"
    - "Grazulex\\LaravelArc\\Support\\Traits\\ValidatesData"
    - "Grazulex\\LaravelArc\\Support\\Traits\\DtoUtilities"
```

### Génération

```bash
php artisan arc:generate user.yaml
```

## Méthodes disponibles

### ConvertsData Trait

- `fromModels(iterable $models): DtoCollection` - Convertit une collection de modèles
- `fromModelsAsCollection(iterable $models): Collection` - Convertit vers une collection standard
- `fromPaginator(Paginator $paginator): array` - Gère la pagination
- `collectionToJson(iterable $models): string` - Convertit directement en JSON API
- `toJson(int $options = 0): string` - Convertit un DTO en JSON
- `toCollection(): Collection` - Convertit un DTO en collection
- `only(array $keys): array` - Sélectionne certains champs
- `except(array $keys): array` - Exclut certains champs

### DtoCollection Class

- `toArrayResource(array $meta = []): array` - Format API Resource
- `toJsonResource(array $meta = []): string` - Format JSON API Resource
- Plus toutes les méthodes de Collection Laravel (filter, map, groupBy, etc.)

## Avantages

1. **Typage fort** : Propriétés readonly avec types PHP
2. **Validation intégrée** : Règles de validation automatiques
3. **Performance** : Pas de overhead des Resources Laravel
4. **Flexibilité** : Méthodes de collection avancées
5. **Génération automatique** : Moins de code à écrire
6. **Compatibilité** : Fonctionne avec tous les systèmes Laravel existants

## Migration depuis Laravel Resources

```php
// Avant (Laravel Resources)
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}

// Utilisation
return UserResource::collection($users);

// Après (Laravel Arc DTOs)
// Génération automatique du DTO depuis YAML
php artisan arc:generate user.yaml

// Utilisation
return UserDto::fromModels($users)->toArrayResource();
```

Cette approche offre tous les avantages des Laravel Resources avec en plus :
- Typage fort
- Validation automatique
- Méthodes de collection avancées
- Génération automatique
- Performance améliorée
