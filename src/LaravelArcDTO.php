<?php

namespace Grazulex\Arc;

use Grazulex\Arc\Abstract\AbstractDTO;
use Grazulex\Arc\Traits\DTOFactoryTrait;

/**
 * Classe de base principale pour Laravel Arc DTOs.
 *
 * Cette classe offre une API simple et élégante pour créer des DTOs
 * avec validation automatique, getters/setters magiques et accès direct aux propriétés.
 *
 * @example
 * ```php
 * use Grazulex\Arc\LaravelArcDTO;
 * use Grazulex\Arc\Attributes\Property;
 *
 * class UserDTO extends LaravelArcDTO
 * {
 *     #[Property(type: 'string', required: true, validation: 'max:255')]
 *     public string $name;
 *
 *     #[Property(type: 'string', required: true, validation: 'email')]
 *     public string $email;
 * }
 *
 * $user = new UserDTO(['name' => 'Jean-Marc', 'email' => 'test@example.com']);
 * echo $user->name; // Accès direct
 * $user->email = 'new@example.com'; // Assignment direct
 *
 * // Factory usage
 * $fakeUser = UserDTO::fake(); // DTO avec données générées
 * $users = UserDTO::fakeMany(5); // 5 DTOs avec données générées
 * $customUser = UserDTO::factory()->with('name', 'Custom Name')->fake()->create();
 * ```
 */
class LaravelArcDTO extends AbstractDTO
{
    use DTOFactoryTrait;

    // Cette classe hérite de toute la logique d'AbstractDTO
    // mais offre un nom plus explicite pour les utilisateurs du package
}
