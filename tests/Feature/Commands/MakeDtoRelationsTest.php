<?php

use Grazulex\Arc\Commands\MakeDtoCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

// Mock models for testing
class RelationTestUser extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email'];
    
    public function orders(): HasMany
    {
        return $this->hasMany(RelationTestOrder::class);
    }
    
    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RelationTestProfile::class);
    }
}

class RelationTestOrder extends Model
{
    protected $table = 'orders';
    protected $fillable = ['amount', 'status'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(RelationTestUser::class);
    }
}

class RelationTestProfile extends Model
{
    protected $table = 'profiles';
    protected $fillable = ['bio', 'avatar'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(RelationTestUser::class);
    }
}

describe('MakeDtoCommand Relations Auto-Discovery', function () {
    beforeEach(function () {
        // Clean up any test files
        $testPaths = ['app/Data', 'app/DTOs'];
        foreach ($testPaths as $path) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
            }
        }
    });

    afterEach(function () {
        // Clean up test files
        $testPaths = ['app/Data', 'app/DTOs'];
        foreach ($testPaths as $path) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
            }
        }
    });

    it('can generate DTO with all relations when --with-relations flag is used', function () {
        // Mock the model class resolution
        // This test requires real Laravel environment with proper model setup
        expect(true)->toBeTrue(); // Placeholder assertion
    })->skip('Requires real model mocking');

    it('can generate DTO with specific relations only', function () {
        $this->artisan('make:dto', [
            'name' => 'TestUser',
            '--relations' => ['orders'],
            '--path' => 'app/DTOs'
        ])->assertExitCode(0);

        $filePath = base_path('app/DTOs/TestUserDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        
        // Should contain orders relation but not profile
        expect($content)
            ->toContain('$orders')
            ->not->toContain('$profile');
    })->skip('Requires real model mocking');

    it('handles models without relations gracefully', function () {
        // Create a model without relations
        class SimpleModel extends Model {
            protected $fillable = ['name'];
            
            public function notARelation(): string {
                return 'test';
            }
            
            public function getSomethingAttribute(): string {
                return 'getter';
            }
        }
        
        $this->artisan('make:dto', [
            'name' => 'Simple',
            '--with-relations' => true,
            '--path' => 'app/DTOs'
        ])->assertExitCode(0);

        $filePath = base_path('app/DTOs/SimpleDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        
        // Should not contain any relation comments
        expect($content)->not->toContain('// Relation:');
    })->skip('Requires real model mocking');

    it('shows relation detection info in verbose mode', function () {
        $this->artisan('make:dto', [
            'name' => 'TestUser',
            '--with-relations' => true,
            '--path' => 'app/DTOs',
            '-v' => true
        ])
        ->expectsOutput(function ($output) {
            return str_contains($output, 'Detected relation:');
        })
        ->assertExitCode(0);
    })->skip('Requires real model mocking');

    it('handles relation detection errors gracefully', function () {
        // Test with a model that throws exceptions
        class ProblematicModel extends Model {
            public function brokenRelation() {
                throw new \Exception('Broken relation');
            }
        }
        
        $this->artisan('make:dto', [
            'name' => 'Problematic',
            '--with-relations' => true,
            '--path' => 'app/DTOs'
        ])
        ->expectsOutput(function ($output) {
            return str_contains($output, 'Could not analyze relations:');
        })
        ->assertExitCode(0);
    })->skip('Requires real model mocking');
});

