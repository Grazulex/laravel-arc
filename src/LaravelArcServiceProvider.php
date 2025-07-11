<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc;

use Grazulex\LaravelArc\Console\Commands\DtoDefinitionInitCommand;
use Grazulex\LaravelArc\Console\Commands\DtoDefinitionListCommand;
use Grazulex\LaravelArc\Console\Commands\DtoGenerateCommand;
use Grazulex\LaravelArc\Support\Validation\Rules\EnumExists;
use Grazulex\LaravelArc\Support\Validation\Rules\InEnum;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

final class LaravelArcServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/dto.php' => config_path('dto.php'),
        ], 'dto-config');

        // Enregistrer les règles de validation personnalisées pour les enums
        $this->registerCustomValidationRules();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/Config/dto.php', 'dto');
        $this->commands([
            DtoDefinitionInitCommand::class,
            DtoDefinitionListCommand::class,
            DtoGenerateCommand::class,
        ]);
    }

    /**
     * Register custom validation rules for enum support.
     */
    private function registerCustomValidationRules(): void
    {
        // Règle in_enum: valide qu'une valeur fait partie d'un enum
        Validator::extend('in_enum', function ($attribute, $value, $parameters): bool {
            if (empty($parameters[0])) {
                return false;
            }

            $enumClass = $parameters[0];
            $rule = new InEnum($enumClass);

            return $rule->passes($attribute, $value);
        });

        // Règle enum_exists: valide qu'un enum existe et que la valeur est valide
        Validator::extend('enum_exists', function ($attribute, $value, $parameters): bool {
            if (empty($parameters[0])) {
                return false;
            }

            $enumClass = $parameters[0];
            $rule = new EnumExists($enumClass);

            return $rule->passes($attribute, $value);
        });

        // Messages de validation personnalisés
        Validator::replacer('in_enum', function ($message, $attribute, $rule, $parameters): string {
            $enumClass = $parameters[0] ?? 'enum';

            return str_replace(':enum', $enumClass, "The :attribute field must be a valid {$enumClass} value.");
        });

        Validator::replacer('enum_exists', function ($message, $attribute, $rule, $parameters): string {
            $enumClass = $parameters[0] ?? 'enum';

            return str_replace(':enum', $enumClass, "The :attribute field must be a valid case of the {$enumClass} enum.");
        });
    }
}
