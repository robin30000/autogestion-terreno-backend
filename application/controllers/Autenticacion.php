<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('/var/www/html/autogestionterreno/application/src/JWT.php');
require_once('/var/www/html/autogestionterreno/application/src/Key.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Autenticacion extends CI_Controller {
	
	function __construct() {
		parent::__construct();
		$this->load->model('Modeloautenticacion');
		$this->load->library('nativesession');
		$this->load->helper('url');
	}

	public function index()
	{
		$this->load->view('login');
	}

	public function autenticar()
	{
		$reqjson = json_decode($this->input->raw_input_stream, true);

		$username = htmlentities($reqjson['user'],ENT_QUOTES);
		$password = md5(htmlentities($reqjson['password'],ENT_QUOTES));

		if (empty($username) || empty($password)) {
			$arrayResult = array('type' => 'error', 'message' => 'No pueden haber campos vacíos, ingrese usuario y clave.');
		} else {
			$valUserQuery = $this->Modeloautenticacion->consultauser($username,$password);

			if ($valUserQuery == 1) {
				$arrayResult = array('type' => 'error', 'message' => 'Usuario no existe.');
			} elseif ($valUserQuery == 2) {
				$arrayResult = array('type' => 'error', 'message' => 'Clave incorrecta.');
			} elseif ($valUserQuery == 3) {
				$arrayResult = array('type' => 'error', 'message' => 'Usuario se encuentra inactivo, comuníquese con el adminstrador.');
			} else {
				$valUserQuery['password'] = '';
				$valUserQuery['logueado'] = 'SI';
				$key = SIGNATURE_JWT;
				$payload = array(
					'iss' => 'http://200.13.250.190/autogetionterreno',
					'sub' => 'autogestionterreno',
					'exp' => time() + 36000,
					'iat' => strtotime(date('Y-m-d H:i:s')),
					'iduser' => $valUserQuery['identificacion'],
					'login' => $valUserQuery['login_click'],
				);

				/**
				 * IMPORTANT:
				 * You must specify supported algorithms for your application. See
				 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
				 * for a list of spec-compliant algorithms.
				 */
				$jwt = JWT::encode($payload, $key, 'HS256');
				
				/* $this->nativesession->set('id', $valUserQuery['id']);
				$this->nativesession->set('identificacion', $valUserQuery['identificacion']);
				$this->nativesession->set('nombre', $valUserQuery['nombre']);
				$this->nativesession->set('empresa', $valUserQuery['empresa']);
				$this->nativesession->set('ciudad', $valUserQuery['ciudad']);
				$this->nativesession->set('celular', $valUserQuery['celular']);
				$this->nativesession->set('contrato', $valUserQuery['contrato']);
				$this->nativesession->set('region', $valUserQuery['region']);
				$this->nativesession->set('login_click', $valUserQuery['login_click']);
				$this->nativesession->set('estado', $valUserQuery['Usuaestadorio']);
				$this->nativesession->set('Logueado', 'SI'); */
				
				$arrayResult = array('type' => 'success', 'message' => $valUserQuery, 'token' => $jwt);
			}
		}
		echo json_encode($arrayResult);
	}

	public function verificarjwt()
	{
		try {
			$jwt = $this->input->get_request_header('x-token', TRUE);
			$decoded = JWT::decode($jwt, new Key(SIGNATURE_JWT, 'HS256'));

			$arrayResult = array('type' => 'success', 'message' => 'OK');
			echo json_encode($arrayResult);

		} catch (\Throwable $th) {
			
			$arrayResult = array('type' => 'error', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
		}
	}

	public function cambioclave()
	{

		$user = $this->nativesession->get('Usuario');

		$oldPass = htmlentities(trim($this->input->post('oldPass')));
		$newPass = htmlentities(trim($this->input->post('newPass')));
		$renewPass = htmlentities(trim($this->input->post('renewPass')));

		$oldPass = md5($oldPass);

		$valOldPass = $this->Modeloautenticacion->ValidarOldPassword($oldPass, $user);
		if ($valOldPass == 0) {
			$arrayResult = array('type' => 'error', 'message' => 'Clave anterior incorrecta, vuelva a intentar');
			echo json_encode($arrayResult);
			die();
		}

		if ($newPass != $renewPass) {
			$arrayResult = array('type' => 'error', 'message' => 'Clave no son iguales, vuelva a intentar');
			echo json_encode($arrayResult);
			die();
		}

		$newPass = md5($newPass);
		$data = $this->Modeloautenticacion->CambioClave($oldPass, $newPass, $user);
		if ($data == 1) {
			$arrayResult = array('type' => 'success', 'message' => 'Se cambio la clave con exito.');
			echo json_encode($arrayResult);
		}
	}
	
	public function logout()
	{
		$this->nativesession->destroy();
		$this->index();
	}
}
