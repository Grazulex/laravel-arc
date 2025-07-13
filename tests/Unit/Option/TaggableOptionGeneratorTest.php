<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Options\TaggableOptionGenerator;

describe('TaggableOptionGenerator', function () {
    it('generates tags field when enabled', function () {
        $generator = new TaggableOptionGenerator();
        $result = $generator->expandFields(true);

        expect($result)->toBe([
            'tags' => [
                'type' => 'array',
                'required' => false,
                'default' => [],
                'rules' => ['array'],
            ],
        ]);
    });

    it('returns empty array when disabled', function () {
        $generator = new TaggableOptionGenerator();
        $result = $generator->expandFields(false);

        expect($result)->toBe([]);
    });

    it('generates tagging methods when enabled', function () {
        $generator = new TaggableOptionGenerator();
        $context = new Grazulex\LaravelArc\Generator\DtoGenerationContext();

        $result = $generator->generate('taggable', true, $context);

        expect($result)
            ->toContain('addTag(')
            ->toContain('removeTag(')
            ->toContain('hasTag(')
            ->toContain('getTags()')
            ->toContain('withTag(');
    });
});
