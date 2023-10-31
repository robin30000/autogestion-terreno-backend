<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Concodigoincompleto extends CI_Controller {

	function __construct() {
		parent::__construct();
        $this->load->model('Modeloconsultagpon');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'Consulta Gpon';
	}

	public function gettareagpon()
	{
		echo 'Hola Robin';exit();
		$fecha = date('Y-m-d H:i:s');

		$jwt = $this->input->get_request_header('x-token', TRUE);

		$tarea = trim(htmlentities($this->input->get('tarea'),ENT_QUOTES));

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}


	}

}
