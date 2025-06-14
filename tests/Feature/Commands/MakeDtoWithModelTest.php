<?php

use Grazulex\Arc\Commands\MakeDtoCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

// Mock User model for testing
class TestUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'age',
        'is_active',
        'phone',
        'website_url'
    ];

    /**
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'last_login_at'
    ];
}

describe('MakeDtoCommand with Model', function () {
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

    it('creates DTO from model with proper properties', function () {
        // Create a mock model class
        if (!class_exists('App\\Models\\TestUser')) {
            class_alias(TestUser::class, 'App\\Models\\TestUser');
        }

        $this->artisan('make:dto', [
            'name' => 'UserFromModel',
            '--model' => 'TestUser'
        ])
            ->expectsOutput('DTO UserFromModelDTO.php created successfully in app/Data!')
            ->assertExitCode(0);

        $filePath = base_path('app/Data/UserFromModelDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        
        // Debug: Let's see what was actually generated
        // dump($content);
        
        expect($content)
            ->toContain('class UserFromModelDTO extends LaravelArcDTO')
            ->and($content)->toContain('$name')
            ->and($content)->toContain('$email')
            ->and($content)->toContain('email')
            ->and($content)->toContain('$age')
            ->and($content)->toContain('$is_active')
            ->and($content)->toContain('$phone')
            ->and($content)->toContain('$website_url')
            ->and($content)->toContain('url')
            ->and($content)->toContain('DateProperty')
            ->and($content)->toContain('$created_at')
            ->and($content)->toContain('$updated_at');
    });

    it('handles non-existent model gracefully', function () {
        $this->artisan('make:dto', [
            'name' => 'NonExistentModel',
            '--model' => 'NonExistentModel'
        ])
            ->expectsOutput('Model App\Models\NonExistentModel not found. Creating empty DTO.')
            ->expectsOutput('DTO NonExistentModelDTO.php created successfully in app/Data!')
            ->assertExitCode(0);

        $filePath = base_path('app/Data/NonExistentModelDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        expect($content)->toContain('// Add your properties here');
    });
});

