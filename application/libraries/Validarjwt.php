<?php
if ( ! defined('BASEPATH') )
	exit('No direct script access allowed');

//require_once('/var/www/html/autogestionterreno/application/src/JWT.php');
//require_once('/var/www/html/autogestionterreno/application/src/Key.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Validarjwt
{
	public function __construct()
	{
		
	}

	public function verificarjwtlocal($jwt)
	{
		try {
			$decoded = JWT::decode($jwt, new Key(SIGNATURE_JWT, 'HS256'));
			return $decoded;
		} catch (\Exception $th) {
			return FALSE;
		}
	}
}
