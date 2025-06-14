<?php

use Grazulex\Arc\Commands\MakeDtoCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

// Advanced User model with casts for testing
class AdvancedTestUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'age',
        'is_active',
        'metadata',
        'salary',
        'created_at',
        'updated_at'
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'age' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}

describe('MakeDtoCommand Advanced Features', function () {
    beforeEach(function () {
        // Clean up any test files
        $testPath = base_path('app/Data');
        if (File::exists($testPath)) {
            File::deleteDirectory($testPath);
        }
    });

    afterEach(function () {
        // Clean up test files
        $testPath = base_path('app/Data');
        if (File::exists($testPath)) {
            File::deleteDirectory($testPath);
        }
    });

    it('detects types from model casts correctly', function () {
        // Create a mock model class with casts
        if (!class_exists('App\\Models\\AdvancedTestUser')) {
            class_alias(AdvancedTestUser::class, 'App\\Models\\AdvancedTestUser');
        }

        $this->artisan('make:dto', [
            'name' => 'AdvancedUser',
            '--model' => 'AdvancedTestUser'
        ])
            ->expectsOutput('DTO AdvancedUserDTO.php created successfully in app/Data!')
            ->assertExitCode(0);

        $filePath = base_path('app/Data/AdvancedUserDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        
        // Verify that casts are correctly detected
        expect($content)
            ->toContain('class AdvancedUserDTO extends LaravelArcDTO')
            // Integer cast should be detected
            ->and($content)->toContain('public int $age') // Not nullable because of cast
            // Boolean cast should be detected
            ->and($content)->toContain('public bool $is_active') // Not nullable because of cast
            // Array cast should be detected
            ->and($content)->toContain('public ?array $metadata')
            // Decimal cast should map to float
            ->and($content)->toContain('public float $salary')
            // DateTime casts should use DateProperty
            ->and($content)->toContain('DateProperty')
            ->and($content)->toContain('public ?Carbon $created_at')
            ->and($content)->toContain('public ?Carbon $updated_at');
    });

    it('shows improved type detection in info output', function () {
        // Create a mock model class with casts
        if (!class_exists('App\\Models\\AdvancedTestUser')) {
            class_alias(AdvancedTestUser::class, 'App\\Models\\AdvancedTestUser');
        }

        $this->artisan('make:dto', [
            'name' => 'AdvancedUser2',
            '--model' => 'AdvancedTestUser'
        ])
            ->assertExitCode(0);

        $filePath = base_path('app/Data/AdvancedUser2DTO.php');
        $content = File::get($filePath);
        
        // Count how many properties were properly typed
        $propertyCount = substr_count($content, 'public ');
        expect($propertyCount)->toBeGreaterThan(4); // Should have multiple properties
        
        // Should have mix of nullable and non-nullable based on casts
        $nullableCount = substr_count($content, 'public ?');
        $nonNullableCount = substr_count($content, 'public int') + substr_count($content, 'public bool') + substr_count($content, 'public float');
        
        expect($nullableCount)->toBeGreaterThan(0); // Some should be nullable
        expect($nonNullableCount)->toBeGreaterThan(0); // Some should not be nullable (from casts)
    });

    it('falls back gracefully when casts and db are not available', function () {
        $this->artisan('make:dto', [
            'name' => 'FallbackUser',
            '--model' => 'NonExistentModel'
        ])
            ->expectsOutput('Model App\\Models\\NonExistentModel not found. Creating empty DTO.')
            ->assertExitCode(0);

        $filePath = base_path('app/Data/FallbackUserDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        expect($content)->toContain('// Add your properties here');
    });
});

