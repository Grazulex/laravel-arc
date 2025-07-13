<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

/**
 * Générateur moderne qui abandonne les "options" au profit des traits purs
 * Utilise le système de stubs existant pour garantir la cohérence
 */
final class ModernDtoGenerator
{
    private const FUNCTIONAL_TRAITS = [
        'Grazulex\LaravelArc\Support\Traits\ValidatesData',
        'Grazulex\LaravelArc\Support\Traits\ConvertsData',
        'Grazulex\LaravelArc\Support\Traits\DtoUtilities',
    ];

    public function __construct(
        private BehavioralTraitRegistry $traitRegistry,
        private DtoTemplateRenderer $templateRenderer
    ) {}

    public function generateFromDefinition(array $yaml): string
    {
        // Support du nouveau format sans "header"
        $header = $yaml['header'] ?? $yaml; // Fallback si pas de header
        $fields = $yaml['fields'] ?? [];
        $relations = $yaml['relations'] ?? [];

        // Récupérer les traits comportementaux déclarés
        $behavioralTraits = $header['traits'] ?? [];

        // Construire la liste complète des traits
        $this->buildTraitsList($behavioralTraits);

        // Générer les champs avec expansion automatique des traits
        $expandedFields = $this->expandFieldsFromTraits($fields, $behavioralTraits);

        // Construire les use statements pour les traits comportementaux
        $headerExtra = $this->buildBehavioralTraitUseStatements($behavioralTraits);

        // Construire la clause extends (par défaut aucune extension)
        $extendsClause = isset($header['extends']) ? " extends {$header['extends']}" : '';

        // Utiliser le système de stubs existant
        return $this->templateRenderer->renderFullDto(
            namespace: $header['namespace'] ?? 'App\DTO',
            className: $header['dto'] ?? $header['class_name'] ?? 'GeneratedDto',
            fields: $expandedFields,
            modelFQCN: $header['model'] ?? 'App\Models\Model',
            extraMethods: $this->generateExtraMethods($relations),
            headerExtra: $headerExtra,
            extendsClause: $extendsClause
        );
    }

    private function buildTraitsList(array $behavioralTraits): array
    {
        $allTraits = self::FUNCTIONAL_TRAITS;
        // 2. Ajouter les traits comportementaux déclarés
        foreach ($behavioralTraits as $traitName) {
            $traitClass = $this->traitRegistry->resolveTrait($traitName);
            if ($traitClass) {
                $allTraits[] = $traitClass;
            }
        }

        return array_unique($allTraits);
    }

    /**
     * Construire les use statements pour les traits comportementaux seulement
     * Les traits fonctionnels sont déjà dans le stub de base
     */
    private function buildBehavioralTraitUseStatements(array $behavioralTraits): string
    {
        $statements = [];

        foreach ($behavioralTraits as $traitName) {
            $traitClass = $this->traitRegistry->resolveTrait($traitName);
            if ($traitClass) {
                $statements[] = "use {$traitClass};";
            }
        }

        return implode("\n", $statements);
    }

    /**
     * Générer des méthodes supplémentaires pour les relations
     */
    private function generateExtraMethods(array $relations): array
    {
        $methods = [];

        foreach ($relations as $relationName => $relationConfig) {
            $type = $relationConfig['type'] ?? 'hasOne';
            $target = $relationConfig['target'] ?? 'Model';

            // Exemple simple de génération de méthode de relation
            $methods[] = "    // Relation: {$relationName} ({$type} -> {$target})";
        }

        return $methods;
    }

    private function expandFieldsFromTraits(array $fields, array $behavioralTraits): array
    {
        $expandedFields = $fields;

        // Expansion automatique des champs selon les traits
        $traitFields = BehavioralTraitRegistry::getFieldsForTraits($behavioralTraits);

        return array_merge($expandedFields, $traitFields);
    }
}
