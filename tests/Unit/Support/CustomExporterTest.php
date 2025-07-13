<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Export\ExporterFactory;
use Grazulex\LaravelArc\Support\Export\Exporters\HtmlExporter;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;

// Extended test DTO that includes HTML export capability
final class ExtendedTestDto
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

    /**
     * Static method for collection HTML export.
     */
    public static function collectionToHtml(iterable $models, array $options = []): string
    {
        $manager = ExporterFactory::getInstance();

        // Register HTML exporter if not already registered
        if (! $manager->hasCollectionExporter('html')) {
            $manager->registerCollectionExporter('html', new HtmlExporter());
        }

        $data = self::fromModels($models)->map(fn ($dto) => $dto->toArray())->toArray();
        $exporter = $manager->getCollectionExporter('html');

        return $exporter->exportCollection($data, $options);
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

    /**
     * Custom HTML export method using the modular architecture.
     */
    public function toHtml(array $options = []): string
    {
        $manager = ExporterFactory::getInstance();

        // Register HTML exporter if not already registered
        if (! $manager->hasExporter('html')) {
            $manager->registerExporter('html', new HtmlExporter());
        }

        $exporter = $manager->getExporter('html');

        return $exporter->export($this->toArray(), $options);
    }
}

describe('Custom Export Format (HTML)', function () {
    beforeEach(function () {
        // Reset the factory to ensure clean state
        ExporterFactory::reset();

        $this->dto = new ExtendedTestDto(1, 'John Doe', 'john@example.com', 'active');
        $this->models = collect([
            (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
        ]);
    });

    describe('HtmlExporter', function () {
        it('exports single DTO to HTML table', function () {
            $exporter = new HtmlExporter();
            $data = $this->dto->toArray();

            $html = $exporter->export($data);

            expect($html)->toContain('<table class="dto-table">');
            expect($html)->toContain('<th>id</th>');
            expect($html)->toContain('<th>name</th>');
            expect($html)->toContain('<td>1</td>');
            expect($html)->toContain('<td>John Doe</td>');
            expect($html)->toContain('</table>');
        });

        it('exports collection to HTML table', function () {
            $exporter = new HtmlExporter();
            $data = $this->models->map(fn ($model) => ExtendedTestDto::fromModel($model)->toArray())->toArray();

            $html = $exporter->exportCollection($data);

            expect($html)->toContain('<table class="dto-collection-table">');
            expect($html)->toContain('<thead>');
            expect($html)->toContain('<tbody>');
            expect($html)->toContain('John Doe');
            expect($html)->toContain('Jane Smith');
            expect($html)->toContain('</table>');
        });

        it('supports custom options', function () {
            $exporter = new HtmlExporter();
            $data = $this->dto->toArray();

            $html = $exporter->export($data, [
                'table_class' => 'custom-table',
                'include_headers' => false,
            ]);

            expect($html)->toContain('<table class="custom-table">');
            expect($html)->not->toContain('<thead>');
            expect($html)->not->toContain('<th>');
        });

        it('handles empty collection gracefully', function () {
            $exporter = new HtmlExporter();

            $html = $exporter->exportCollection([]);

            expect($html)->toContain('<table class="dto-collection-table">');
            expect($html)->toContain('No data');
        });
    });

    describe('Integration with DTO', function () {
        it('allows custom export methods on DTOs', function () {
            $html = $this->dto->toHtml();

            expect($html)->toContain('<table class="dto-table">');
            expect($html)->toContain('John Doe');
        });

        it('supports custom options in DTO methods', function () {
            $html = $this->dto->toHtml(['table_class' => 'my-custom-table']);

            expect($html)->toContain('<table class="my-custom-table">');
        });

        it('supports static collection methods', function () {
            $html = ExtendedTestDto::collectionToHtml($this->models);

            expect($html)->toContain('<table class="dto-collection-table">');
            expect($html)->toContain('John Doe');
            expect($html)->toContain('Jane Smith');
        });
    });

    describe('Dynamic Registration', function () {
        it('allows runtime registration of new exporters', function () {
            $manager = ExporterFactory::getInstance();

            // Initially, HTML should not be supported
            expect($manager->hasExporter('html'))->toBe(false);

            // Register the HTML exporter
            $htmlExporter = new HtmlExporter();
            $manager->registerExporter('html', $htmlExporter);
            $manager->registerCollectionExporter('html', $htmlExporter);

            // Now it should be supported
            expect($manager->hasExporter('html'))->toBe(true);
            expect($manager->hasCollectionExporter('html'))->toBe(true);

            // And we should be able to use it
            $exporter = $manager->getExporter('html');
            expect($exporter)->toBe($htmlExporter);
            expect($exporter->getFormat())->toBe('html');
        });
    });
});
