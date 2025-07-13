<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoCollection;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// Test DTO class using ConvertsData trait
final class ConvertedTestDto
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

describe('ConvertsData Trait', function () {
    beforeEach(function () {
        $this->models = collect([
            (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
            (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active'],
        ]);
    });

    describe('Model Collection Conversion', function () {
        describe('fromModels and collection methods', function () {
            it('converts models to DTO collection', function () {
                $dtos = ConvertedTestDto::fromModels($this->models);

                expect($dtos)->toBeInstanceOf(DtoCollection::class);
                expect($dtos->count())->toBe(3);
                expect($dtos->first())->toBeInstanceOf(ConvertedTestDto::class);
            });

            it('converts models to DTO collection using collection() method', function () {
                $dtos = ConvertedTestDto::collection($this->models);

                expect($dtos)->toBeInstanceOf(DtoCollection::class);
                expect($dtos->count())->toBe(3);
                expect($dtos->first())->toBeInstanceOf(ConvertedTestDto::class);
            });

            it('collection() method is equivalent to fromModels()', function () {
                $dtos1 = ConvertedTestDto::collection($this->models);
                $dtos2 = ConvertedTestDto::fromModels($this->models);

                expect($dtos1)->toBeInstanceOf(DtoCollection::class);
                expect($dtos2)->toBeInstanceOf(DtoCollection::class);
                expect($dtos1->count())->toBe($dtos2->count());
                expect($dtos1->first()->toArray())->toBe($dtos2->first()->toArray());
            });
        });

        describe('fromModelsAsCollection method', function () {
            it('converts models to standard collection', function () {
                $dtos = ConvertedTestDto::fromModelsAsCollection($this->models);

                expect($dtos)->toBeInstanceOf(Collection::class);
                expect($dtos)->not->toBeInstanceOf(DtoCollection::class);
                expect($dtos->count())->toBe(3);
                expect($dtos->first())->toBeInstanceOf(ConvertedTestDto::class);
            });
        });

        describe('edge cases', function () {
            it('handles empty models collection', function () {
                $emptyModels = collect([]);
                $dtos = ConvertedTestDto::fromModels($emptyModels);

                expect($dtos)->toBeInstanceOf(DtoCollection::class);
                expect($dtos->count())->toBe(0);
            });

            it('handles array input for models', function () {
                $modelsArray = $this->models->toArray();
                $dtos = ConvertedTestDto::fromModels($modelsArray);

                expect($dtos)->toBeInstanceOf(DtoCollection::class);
                expect($dtos->count())->toBe(3);
            });
        });
    });

    describe('Paginator Conversion', function () {
        it('converts paginator to array', function () {
            $paginator = new LengthAwarePaginator(
                $this->models->take(2),
                5, // total
                2, // per page
                1, // current page
                ['path' => '/users']
            );

            $result = ConvertedTestDto::fromPaginator($paginator);

            expect($result)->toBeArray();
            expect($result)->toHaveKeys(['data', 'meta']);
            expect($result['data'])->toHaveCount(2);
            expect($result['meta'])->toHaveKeys(['current_page', 'per_page', 'total', 'last_page']);
        });
    });

    describe('JSON Conversion', function () {
        describe('collection to JSON', function () {
            it('converts collection to JSON', function () {
                $json = ConvertedTestDto::collectionToJson($this->models);

                expect($json)->toBeString();
                $decoded = json_decode($json, true);
                expect($decoded)->toHaveKey('data');
                expect($decoded['data'])->toHaveCount(3);
            });
        });

        describe('single DTO to JSON', function () {
            it('converts DTO to JSON', function () {
                $dto = ConvertedTestDto::fromModel($this->models->first());
                $json = $dto->toJson();

                expect($json)->toBeString();
                $decoded = json_decode($json, true);
                expect($decoded)->toEqual([
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'status' => 'active',
                ]);
            });

            it('converts DTO to JSON with options', function () {
                $dto = ConvertedTestDto::fromModel($this->models->first());
                $json = $dto->toJson(JSON_PRETTY_PRINT);

                expect($json)->toBeString();
                expect($json)->toContain("\n"); // Should contain newlines due to pretty print
            });
        });
    });

    describe('Collection and Array Utilities', function () {
        it('converts DTO to collection', function () {
            $dto = ConvertedTestDto::fromModel($this->models->first());
            $collection = $dto->toCollection();

            expect($collection)->toBeInstanceOf(Collection::class);
            expect($collection->toArray())->toEqual([
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'status' => 'active',
            ]);
        });

        it('gets only specified keys', function () {
            $dto = ConvertedTestDto::fromModel($this->models->first());
            $filtered = $dto->only(['id', 'name']);

            expect($filtered)->toEqual([
                'id' => 1,
                'name' => 'John Doe',
            ]);
        });

        it('gets all keys except specified ones', function () {
            $dto = ConvertedTestDto::fromModel($this->models->first());
            $filtered = $dto->except(['id', 'status']);

            expect($filtered)->toEqual([
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]);
        });
    });

    describe('Export Formats', function () {
        describe('Single DTO Export', function () {
            describe('YAML export', function () {
                it('exports to YAML format', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $yaml = $dto->toYaml();

                    expect($yaml)->toBeString();
                    expect($yaml)->toContain('id:');
                    expect($yaml)->toContain('name:');
                    expect($yaml)->toContain('John Doe');
                });
            });

            describe('CSV export', function () {
                it('exports to CSV format', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $csv = $dto->toCsv();

                    expect($csv)->toBeString();
                    expect($csv)->toContain('id,name,email,status');
                    expect($csv)->toContain('1,"John Doe",john@example.com,active');
                });

                it('exports to CSV without headers', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $csv = $dto->toCsv(includeHeaders: false);

                    expect($csv)->toBeString();
                    expect($csv)->not->toContain('id,name,email,status');
                    expect($csv)->toContain('1,"John Doe",john@example.com,active');
                });
            });

            describe('XML export', function () {
                it('exports to XML format', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $xml = $dto->toXml();

                    expect($xml)->toBeString();
                    expect($xml)->toContain('<?xml');
                    expect($xml)->toContain('<dto>');
                    expect($xml)->toContain('<id>1</id>');
                    expect($xml)->toContain('<name>John Doe</name>');
                });
            });

            describe('TOML export', function () {
                it('exports to TOML format', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $toml = $dto->toToml();

                    expect($toml)->toBeString();
                    expect($toml)->toContain('id = 1');
                    expect($toml)->toContain('name = "John Doe"');
                    expect($toml)->toContain('email = "john@example.com"');
                });
            });

            describe('Markdown export', function () {
                it('exports to Markdown table format', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $markdown = $dto->toMarkdownTable();

                    expect($markdown)->toBeString();
                    expect($markdown)->toContain('| id | name | email | status |');
                    expect($markdown)->toContain('| --- | --- | --- | --- |');
                    expect($markdown)->toContain('| 1 | John Doe | john@example.com | active |');
                });
            });

            describe('PHP Array export', function () {
                it('exports to PHP array format', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $phpArray = $dto->toPhpArray();

                    expect($phpArray)->toBeString();
                    expect($phpArray)->toContain('array (');
                    expect($phpArray)->toContain("'id' => 1");
                    expect($phpArray)->toContain("'name' => 'John Doe'");
                });
            });

            describe('Query String export', function () {
                it('exports to query string format', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());
                    $queryString = $dto->toQueryString();

                    expect($queryString)->toBeString();
                    expect($queryString)->toContain('id=1');
                    expect($queryString)->toContain('name=John+Doe');
                    expect($queryString)->toContain('email=john%40example.com');
                });
            });

            describe('MessagePack export', function () {
                it('throws exception for MessagePack when extension not available', function () {
                    $dto = ConvertedTestDto::fromModel($this->models->first());

                    if (! function_exists('msgpack_pack')) {
                        expect(fn () => $dto->toMessagePack())->toThrow(RuntimeException::class);
                    } else {
                        $msgpack = $dto->toMessagePack();
                        expect($msgpack)->toBeString();
                    }
                });
            });
        });

        describe('Collection Export', function () {
            describe('YAML export', function () {
                it('exports collection to YAML format', function () {
                    $yaml = ConvertedTestDto::collectionToYaml($this->models);

                    expect($yaml)->toBeString();
                    expect($yaml)->toContain('data:');
                    expect($yaml)->toContain('John Doe');
                    expect($yaml)->toContain('Jane Smith');
                });
            });

            describe('CSV export', function () {
                it('exports collection to CSV format', function () {
                    $csv = ConvertedTestDto::collectionToCsv($this->models);

                    expect($csv)->toBeString();
                    expect($csv)->toContain('id,name,email,status');
                    expect($csv)->toContain('1,"John Doe",john@example.com,active');
                    expect($csv)->toContain('2,"Jane Smith",jane@example.com,inactive');
                    expect($csv)->toContain('3,"Bob Johnson",bob@example.com,active');
                });

                it('exports collection to CSV without headers', function () {
                    $csv = ConvertedTestDto::collectionToCsv($this->models, includeHeaders: false);

                    expect($csv)->toBeString();
                    expect($csv)->not->toContain('id,name,email,status');
                    expect($csv)->toContain('1,"John Doe",john@example.com,active');
                });
            });

            describe('XML export', function () {
                it('exports collection to XML format', function () {
                    $xml = ConvertedTestDto::collectionToXml($this->models);

                    expect($xml)->toBeString();
                    expect($xml)->toContain('<?xml');
                    expect($xml)->toContain('<collection>');
                    expect($xml)->toContain('<item>');
                    expect($xml)->toContain('<id>1</id>');
                    expect($xml)->toContain('<name>John Doe</name>');
                });
            });

            describe('Markdown export', function () {
                it('exports collection to Markdown table format', function () {
                    $markdown = ConvertedTestDto::collectionToMarkdownTable($this->models);

                    expect($markdown)->toBeString();
                    expect($markdown)->toContain('| id | name | email | status |');
                    expect($markdown)->toContain('| --- | --- | --- | --- |');
                    expect($markdown)->toContain('| 1 | John Doe | john@example.com | active |');
                    expect($markdown)->toContain('| 2 | Jane Smith | jane@example.com | inactive |');
                });
            });

            describe('edge cases', function () {
                it('handles empty collection exports gracefully', function () {
                    $emptyModels = collect([]);

                    expect(ConvertedTestDto::collectionToCsv($emptyModels))->toBe('');
                    expect(ConvertedTestDto::collectionToMarkdownTable($emptyModels))->toBe('');
                });
            });
        });
    });
});
