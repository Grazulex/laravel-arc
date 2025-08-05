# Laravel Arc - Architecture Actuelle

## Vue d'ensemble de l'architecture

Laravel Arc est un package Laravel qui génère des classes DTO (Data Transfer Object) à partir de définitions YAML. Le package utilise actuellement **Symfony YAML** directement pour parser les fichiers de définition.

## Structure YAML Attendue

Basé sur l'analyse des tests et fixtures, voici la structure YAML supportée :

```yaml
header:
  dto: ProductDTO              # Nom de la classe DTO (requis)
  table: products             # Table de base de données
  model: App\Models\Product   # Modèle Eloquent associé
  namespace: App\DTO          # Namespace du DTO (optionnel)
  traits: [HasTimestamps]     # Traits comportementaux

fields:
  id:
    type: uuid                # Types supportés: string, integer, float, boolean, array, enum, uuid
    required: true            # Validation
    rules: [unique]           # Règles Laravel supplémentaires
  name:
    type: string
    required: true
  price:
    type: float
    required: false
    default: 0.0              # Valeur par défaut
  status:
    type: enum
    values: [draft, published, archived]

relations:
  category:
    type: belongsTo           # Types: belongsTo, hasMany, hasOne, belongsToMany
    target: App\Models\Category

options:
  timestamps: true            # Ajout automatique created_at/updated_at
  soft_deletes: false         # Ajout automatique deleted_at
  expose_hidden_by_default: false
  namespace: App\DTO          # Namespace du DTO
```

## Points d'Intégration YAML Actuels

### 1. DtoGenerateCommand.php
- **Ligne 67** : `Yaml::parseFile($filePath)` - Parse direct du fichier YAML
- **Lignes 69-82** : Validation de la structure YAML (dto, namespace requis)
- **Lignes 84-110** : Gestion d'erreurs pour parsing YAML invalide

### 2. DtoGenerator.php
- **generateFromDefinition()** : Traite les arrays parsés depuis YAML
- Extrait `header`, `fields`, `relations`, `options` depuis l'array YAML

### 3. Tests
- **CompleteDtoGenerationTest.php** : Tests intégration avec `Yaml::parse()`
- **DtoGenerateCommandTest.php** : Tests commandes avec fichiers YAML fixtures
- **Fixtures** : Exemples YAML complets dans `tests/Feature/DtoGenerator/fixtures/`

## Flux de Données Actuel

```
Fichier YAML → Symfony\Component\Yaml\Yaml::parseFile() → Array PHP → DtoGenerator::generateFromDefinition() → Code DTO
```

## Commandes Disponibles

1. **`dto:generate {filename}`** - Génère un DTO depuis un fichier YAML
   - `--dry-run` : Prévisualise sans créer le fichier
   - `--force` : Écrase les fichiers existants
   - `--all` : Traite tous les fichiers YAML du dossier

2. **`dto:definition-init {name}`** - Crée un fichier YAML vide
   - `--model` : Modèle Eloquent associé
   - `--table` : Table de base de données
   - `--path` : Chemin de sortie

3. **`dto:definition-list`** - Liste les définitions YAML
   - `--path` : Chemin des définitions
   - `--compact` : Vue compacte

## Configuration

Configuration dans `config/dto.php` :
- `definitions_path` : Dossier contenant les fichiers YAML
- `output_path` : Dossier de sortie des DTOs générés
- `namespace` : Namespace par défaut

## Points d'Intégration ModelSchema

### Points de Remplacement Identifiés

1. **DtoGenerateCommand.php:67** - Remplacer `Yaml::parseFile()` par ModelSchema
2. **Tests avec Yaml::parse()** - Adapter pour utiliser ModelSchema
3. **Validation YAML** - Déléguer à ModelSchema

### Interface Cible

Basé sur turbomaker, l'interface ModelSchema attendue :
```php
// Remplacement de Yaml::parseFile($filePath)
$schemas = SchemaService::make()->parseSchemaFromFile($filePath);
foreach ($schemas as $schema) {
    $dtoCode = DtoGenerator::make()->generateFromDefinition($schema->toArray());
}
```

## Dépendances Actuelles

- `symfony/yaml` : Parser YAML (à remplacer)
- `illuminate/support` : ^12.19
- **CIBLE** : `grazulex/laravel-modelschema` (puissance X 1000% pour fields !)

## Architecture ModelSchema Découverte

Le package `grazulex/laravel-modelschema` offre des capacités extraordinaires :

### 🚀 FieldTypeRegistry - Système Extensible
- **30+ types de champs** : string, integer, uuid, enum, geometry, point, polygon, etc.
- **Aliases automatiques** : varchar→string, int→integer, bool→boolean
- **Field Type Plugins** : Système de plugins avec traits pour types custom
- **Auto-discovery** : Détection automatique des plugins dans les dossiers

### ⚡ YamlOptimizationService - Performance Enterprise
- **3 stratégies automatiques** : Standard (<100KB), Lazy Loading (100KB-1MB), Streaming (>1MB)
- **Cache intelligent** : TTL, gestion mémoire, métriques performance
- **Parsing sélectif** : Parse uniquement les sections nécessaires (95% plus rapide)
- **Métriques temps réel** : Monitoring et optimisation automatique

### 🔧 SchemaService - API Centrale
- `parseAndSeparateSchema()` : Sépare core/extensions avec traits
- `validateCoreSchema()` : Validation schema + plugins
- `extractCoreContentForGeneration()` : Données structurées pour génération
- `generateCompleteYamlFromStub()` : YAML complet depuis stubs + extensions

### 🎯 Structure YAML Supportée
```yaml
core:
  model: User
  table: users
  fields:
    homepage:
      type: url                    # Plugin custom
      schemes: ['https']           # Attributs custom du plugin
      verify_ssl: true
      timeout: 45
    coordinates:  
      type: point                  # Type géométrique
      precision: 6
      validate_bounds:
        latitude: [45.0, 50.0]
```

## Prochaines Étapes CORRIGÉES

1. **Phase 2** : Ajouter dépendance `grazulex/laravel-modelschema`
2. **Phase 3** : Créer ModelSchemaAdapter utilisant SchemaService
3. **Phase 4** : Exploiter FieldTypeRegistry pour enrichir les types Arc
4. **Phase 5** : Utiliser YamlOptimizationService pour les performances
