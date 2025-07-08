<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Headers\TableHeaderGenerator;

it('generates table annotation from table header', function () {
    $generator = new TableHeaderGenerator();

    $yaml = ['table' => 'trainings'];
    $result = $generator->generate($yaml, 'TrainingDTO');

    expect($result)->toBe("/**\n * Data Transfer Object for table `trainings`.\n */");
});

it('returns null if table header is missing', function () {
    $generator = new TableHeaderGenerator();

    $yaml = [];
    $result = $generator->generate($yaml, 'TrainingDTO');

    expect($result)->toBeNull();
});
