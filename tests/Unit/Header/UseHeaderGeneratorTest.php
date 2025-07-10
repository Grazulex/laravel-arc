<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Headers\UseHeaderGenerator;

describe('UseHeaderGenerator', function () {
    it('supports use key', function () {
        $generator = new UseHeaderGenerator();
        
        expect($generator->supports('use'))->toBeTrue();
        expect($generator->supports('extends'))->toBeFalse();
    });

    it('generates single use statement', function () {
        $generator = new UseHeaderGenerator();
        $context = new DtoGenerationContext();
        
        $header = [
            'use' => 'App\\Traits\\HasUuid'
        ];
        
        $result = $generator->generate('use', $header, $context);
        
        expect($result)->toBe('use App\\Traits\\HasUuid;');
    });

    it('generates multiple use statements', function () {
        $generator = new UseHeaderGenerator();
        $context = new DtoGenerationContext();
        
        $header = [
            'use' => [
                'App\\Traits\\HasUuid',
                'Illuminate\\Support\\Facades\\Validator'
            ]
        ];
        
        $result = $generator->generate('use', $header, $context);
        
        expect($result)->toBe("use App\\Traits\\HasUuid;\nuse Illuminate\\Support\\Facades\\Validator;");
    });

    it('handles empty use array', function () {
        $generator = new UseHeaderGenerator();
        $context = new DtoGenerationContext();
        
        $header = [
            'use' => []
        ];
        
        $result = $generator->generate('use', $header, $context);
        
        expect($result)->toBe('');
    });

    it('handles missing use key', function () {
        $generator = new UseHeaderGenerator();
        $context = new DtoGenerationContext();
        
        $header = [];
        
        $result = $generator->generate('use', $header, $context);
        
        expect($result)->toBe('');
    });

    it('handles use statements with semicolons', function () {
        $generator = new UseHeaderGenerator();
        $context = new DtoGenerationContext();
        
        $header = [
            'use' => 'App\\Traits\\HasUuid;'
        ];
        
        $result = $generator->generate('use', $header, $context);
        
        expect($result)->toBe('use App\\Traits\\HasUuid;');
    });

    it('handles use statements with extra spaces', function () {
        $generator = new UseHeaderGenerator();
        $context = new DtoGenerationContext();
        
        $header = [
            'use' => '  App\\Traits\\HasUuid  '
        ];
        
        $result = $generator->generate('use', $header, $context);
        
        expect($result)->toBe('use App\\Traits\\HasUuid;');
    });
});