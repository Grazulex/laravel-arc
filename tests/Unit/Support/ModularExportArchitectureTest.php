<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Export\ExporterFactory;
use Grazulex\LaravelArc\Support\Export\Exporters\JsonExporter;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;

// Test DTO class using ConvertsData trait
final class ModularArchitectureTestDto
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

describe('Modular Export Architecture', function () {
    beforeEach(function () {
        // Reset the factory to ensure clean state
        ExporterFactory::reset();

        $this->dto = new ModularArchitectureTestDto(1, 'John Doe', 'john@example.com', 'active');
        $this->models = collect([
            (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
        ]);
    });

    describe('ExporterFactory', function () {
        it('creates default manager with all exporters', function () {
            $manager = ExporterFactory::getInstance();

            expect($manager->hasExporter('json'))->toBe(true);
            expect($manager->hasExporter('yaml'))->toBe(true);
            expect($manager->hasExporter('csv'))->toBe(true);
            expect($manager->hasExporter('xml'))->toBe(true);
            expect($manager->hasExporter('toml'))->toBe(true);
            expect($manager->hasExporter('markdown'))->toBe(true);
            expect($manager->hasExporter('php_array'))->toBe(true);
            expect($manager->hasExporter('query_string'))->toBe(true);
            expect($manager->hasExporter('msgpack'))->toBe(true);
        });

        it('allows custom exporter registration', function () {
            $manager = ExporterFactory::createEmptyManager();
            $jsonExporter = new JsonExporter();

            expect($manager->hasExporter('json'))->toBe(false);

            $manager->registerExporter('json', $jsonExporter);

            expect($manager->hasExporter('json'))->toBe(true);
            expect($manager->getExporter('json'))->toBe($jsonExporter);
        });

        it('supports format discovery', function () {
            $manager = ExporterFactory::getInstance();

            $formats = $manager->getSupportedFormats();
            expect($formats)->toContain('json');
            expect($formats)->toContain('yaml');
            expect($formats)->toContain('csv');
            expect($formats)->toContain('xml');
        });
    });

    describe('Individual Exporters', function () {
        describe('JsonExporter', function () {
            it('exports single DTO correctly', function () {
                $exporter = new JsonExporter();
                $data = $this->dto->toArray();

                $result = $exporter->export($data);
                $decoded = json_decode($result, true);

                expect($decoded)->toEqual($data);
                expect($exporter->getFormat())->toBe('json');
            });

            it('exports collection correctly', function () {
                $exporter = new JsonExporter();
                $data = $this->models->map(fn ($model) => ModularArchitectureTestDto::fromModel($model)->toArray())->toArray();

                $result = $exporter->exportCollection($data);
                $decoded = json_decode($result, true);

                expect($decoded)->toHaveKey('data');
                expect($decoded['data'])->toHaveCount(2);
                expect($decoded['data'][0])->toEqual($data[0]);
            });

            it('supports custom options', function () {
                $exporter = new JsonExporter();
                $data = $this->dto->toArray();

                $result = $exporter->export($data, ['flags' => JSON_PRETTY_PRINT]);

                expect($result)->toContain("\n"); // Pretty print should add newlines
            });
        });
    });

    describe('Integration with ConvertsData trait', function () {
        it('uses modular architecture when available', function () {
            // Ensure the factory is properly initialized
            $manager = ExporterFactory::getInstance();
            expect($manager->hasExporter('json'))->toBe(true);

            $json = $this->dto->toJson();
            $decoded = json_decode($json, true);

            expect($decoded)->toEqual($this->dto->toArray());
        });

        it('maintains backward compatibility', function () {
            // Even with new architecture, the API should remain the same
            $json = $this->dto->toJson(JSON_PRETTY_PRINT);

            expect($json)->toBeString();
            expect($json)->toContain("\n"); // Pretty print
        });
    });

    describe('Error Handling', function () {
        it('throws appropriate exceptions for unsupported formats', function () {
            $manager = ExporterFactory::createEmptyManager();

            expect(fn () => $manager->getExporter('nonexistent'))
                ->toThrow(InvalidArgumentException::class, 'No exporter registered for format: nonexistent');
        });

        it('handles extension dependencies gracefully', function () {
            $manager = ExporterFactory::getInstance();

            // MessagePack should either work or throw a proper exception
            if (! function_exists('msgpack_pack')) {
                expect(fn () => $this->dto->toMessagePack())
                    ->toThrow(RuntimeException::class);
            } else {
                $result = $this->dto->toMessagePack();
                expect($result)->toBeString();
            }
        });
    });
});
