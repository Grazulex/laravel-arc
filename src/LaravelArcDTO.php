<?php

namespace Grazulex\Arc;

use Grazulex\Arc\Abstract\AbstractDTO;

/**
 * Classe de base principale pour Laravel Arc DTOs
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
 * ```
 */
class LaravelArcDTO extends AbstractDTO
{
    // Cette classe hérite de toute la logique d'AbstractDTO
    // mais offre un nom plus explicite pour les utilisateurs du package
}

