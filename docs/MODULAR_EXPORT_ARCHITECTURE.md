# Architecture Modulaire des Exporters

## Vue d'ensemble

Laravel Arc utilise désormais une architecture modulaire pour les exporters, permettant d'ajouter facilement de nouveaux formats d'export sans modifier le code existant. Cette architecture respecte les principes SOLID et maintient une compatibilité totale avec l'API existante.

## Architecture

### Composants principaux

1. **Contracts/Interfaces** :
   - `DtoExporter` : Interface pour l'export de DTOs individuels
   - `DtoCollectionExporter` : Interface pour l'export de collections de DTOs
   - `ExporterManager` : Interface pour la gestion des exporters

2. **Manager** :
   - `ExporterManager` : Gère l'enregistrement et la récupération des exporters
   - `ExporterFactory` : Factory singleton pour créer et configurer le manager

3. **Exporters** :
   - `AbstractExporter` : Classe de base abstraite pour faciliter l'implémentation
   - Exporters spécialisés pour chaque format (JSON, YAML, CSV, XML, etc.)

### Formats supportés

Par défaut, les formats suivants sont disponibles :

- **json** : Format JSON standard
- **yaml** : Format YAML (nécessite l'extension php-yaml)
- **csv** : Format CSV avec options personnalisables
- **xml** : Format XML avec structure personnalisable
- **toml** : Format TOML
- **markdown** : Tables Markdown
- **php_array** : Arrays PHP (var_export)
- **query_string** : Chaînes de requête URL
- **msgpack** : MessagePack (nécessite l'extension php-msgpack)

## Utilisation

### Utilisation standard (inchangée)

```php
use Grazulex\LaravelArc\Support\Traits\ConvertsData;

class UserDto
{
    use ConvertsData;
    
    // ... propriétés et méthodes ...
}

$user = new UserDto(1, 'John', 'john@example.com');

// Les méthodes existantes fonctionnent toujours
$json = $user->toJson();
$yaml = $user->toYaml();
$csv = $user->toCsv();

// Collections
$users = UserDto::collection($models);
$json = UserDto::collectionToJson($models);
```

### Ajout d'un exporter personnalisé

#### 1. Créer l'exporter

```php
use Grazulex\LaravelArc\Support\Export\AbstractExporter;

class HtmlExporter extends AbstractExporter
{
    public function export(array $data, array $options = []): string
    {
        // Logique d'export pour un DTO
        $html = '<table>';
        foreach ($data as $key => $value) {
            $html .= "<tr><td>{$key}</td><td>{$value}</td></tr>";
        }
        $html .= '</table>';
        return $html;
    }

    public function exportCollection(array $dataCollection, array $options = []): string
    {
        // Logique d'export pour une collection
        $html = '<table>';
        foreach ($dataCollection as $item) {
            $html .= '<tr>';
            foreach ($item as $value) {
                $html .= "<td>{$value}</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    public function getFormat(): string
    {
        return 'html';
    }
}
```

#### 2. Enregistrer l'exporter

```php
use Grazulex\LaravelArc\Support\Export\ExporterFactory;

$manager = ExporterFactory::getInstance();
$htmlExporter = new HtmlExporter();

$manager->registerExporter('html', $htmlExporter);
$manager->registerCollectionExporter('html', $htmlExporter);
```

#### 3. Utiliser l'exporter

```php
// Via le manager directement
$manager = ExporterFactory::getInstance();
$exporter = $manager->getExporter('html');
$html = $exporter->export($dto->toArray());

// Ou étendre votre DTO
class ExtendedUserDto extends UserDto
{
    public function toHtml(array $options = []): string
    {
        $manager = ExporterFactory::getInstance();
        $exporter = $manager->getExporter('html');
        return $exporter->export($this->toArray(), $options);
    }
}
```

## Avantages

### 1. Extensibilité sans modification

Ajoutez de nouveaux formats d'export sans toucher au code existant :

```php
// Aucune modification nécessaire dans ConvertsData ou les DTOs existants
$manager->registerExporter('my_format', new MyCustomExporter());
```

### 2. Principe de responsabilité unique

Chaque exporter a une seule responsabilité :

```php
class JsonExporter extends AbstractExporter
{
    // Responsable uniquement de l'export JSON
}

class CsvExporter extends AbstractExporter
{
    // Responsable uniquement de l'export CSV
}
```

### 3. Configuration flexible

Chaque exporter peut avoir ses propres options :

```php
$csv = $user->toCsv(
    delimiter: ';',
    enclosure: "'",
    includeHeaders: false
);

$xml = $user->toXml(
    rootElement: 'user',
    encoding: 'ISO-8859-1'
);
```

### 4. Gestion d'erreurs granulaire

```php
try {
    $msgpack = $user->toMessagePack();
} catch (RuntimeException $e) {
    // Extension MessagePack non disponible
    $fallback = $user->toJson();
}
```

### 5. Tests isolés

Chaque exporter peut être testé indépendamment :

```php
describe('JsonExporter', function () {
    it('exports data correctly', function () {
        $exporter = new JsonExporter();
        $result = $exporter->export(['key' => 'value']);
        expect(json_decode($result, true))->toEqual(['key' => 'value']);
    });
});
```

## Migration

### Compatibilité totale

L'architecture existante est **100% compatible**. Aucun code n'a besoin d'être modifié.

### Migration progressive

Vous pouvez migrer progressivement vers la nouvelle architecture :

```php
// Ancien style (toujours supporté)
$json = $user->toJson();

// Nouveau style (recommandé pour de nouveaux formats)
$manager = ExporterFactory::getInstance();
$exporter = $manager->getExporter('json');
$json = $exporter->export($user->toArray());
```

## Configuration avancée

### Factory personnalisé

```php
// Créer un manager vide
$manager = ExporterFactory::createEmptyManager();

// Ajouter seulement les exporters nécessaires
$manager->registerExporter('json', new JsonExporter());
$manager->registerExporter('csv', new CsvExporter());

// Utiliser ce manager
ExporterFactory::setInstance($manager);
```

### Exporters avec dépendances

```php
class DatabaseExporter extends AbstractExporter
{
    public function __construct(
        private Database $db
    ) {}
    
    public function export(array $data, array $options = []): string
    {
        // Sauvegarder en base et retourner un ID
        return $this->db->save($data);
    }
    
    public function getFormat(): string
    {
        return 'database';
    }
}

// Enregistrement avec injection de dépendance
$manager->registerExporter('database', new DatabaseExporter($database));
```

## Exemples pratiques

### Format Excel

```php
class ExcelExporter extends AbstractExporter
{
    public function export(array $data, array $options = []): string
    {
        // Utiliser PhpSpreadsheet ou similaire
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $row = 1;
        foreach ($data as $key => $value) {
            $sheet->setCellValue("A{$row}", $key);
            $sheet->setCellValue("B{$row}", $value);
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
    
    public function getFormat(): string
    {
        return 'excel';
    }
}
```

### Format PDF

```php
class PdfExporter extends AbstractExporter
{
    public function export(array $data, array $options = []): string
    {
        // Utiliser TCPDF, DomPDF ou similaire
        $pdf = new TCPDF();
        $pdf->AddPage();
        
        $html = '<table>';
        foreach ($data as $key => $value) {
            $html .= "<tr><td>{$key}</td><td>{$value}</td></tr>";
        }
        $html .= '</table>';
        
        $pdf->writeHTML($html);
        return $pdf->Output('', 'S');
    }
    
    public function getFormat(): string
    {
        return 'pdf';
    }
}
```

## Conclusion

Cette architecture modulaire rend Laravel Arc beaucoup plus extensible et maintenable, tout en conservant une compatibilité totale avec l'API existante. Les développeurs peuvent facilement ajouter de nouveaux formats d'export selon leurs besoins spécifiques.
