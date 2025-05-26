<?php

namespace App\Models;

class User extends Usuario
{
  /**
   * Indica a qué factory usar cuando alguien invoque User::factory()
   */
  protected static function newFactory()
  {
    return \Database\Factories\UsuarioFactory::new();
  }
}
