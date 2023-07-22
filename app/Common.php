<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter4.github.io/CodeIgniter4/
 */

/**
 * @return \App\Models\Usuarios
 */
function model_usuarios(){
  return new \App\Models\Usuarios();
}

/**
 * @return \App\Models\Usuarios\Tokens
 */
function model_usuario_tokens(){
  return new \App\Models\Usuarios\Tokens();
}
