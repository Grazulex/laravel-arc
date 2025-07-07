<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | DTO Definition Files Path
    |--------------------------------------------------------------------------
    |
    | This is the absolute or relative path (from base_path) where your YAML
    | definition files for DTOs are located. These are typically stored outside
    | the package, inside the Laravel application using it.
    |
    */

    'definitions_path' => base_path('database/dto_definitions'),

    /*
    |--------------------------------------------------------------------------
    | DTO Output Path
    |--------------------------------------------------------------------------
    |
    | The directory where the generated DTO PHP classes will be written.
    | This should typically point to app/DTOs or a custom directory
    | depending on how you structure your application.
    |
    */

    'output_path' => base_path('app/DTOs'),

];
