<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Conseguimientoclick extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Modeloseguimientoclick');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'seguimientoclick';
	}

	public function gettecnicosbysupervisor()
	{
		$jwt = $this->input->get_request_header('x-token', TRUE);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$cedula_supervisor = $payload->iduser;

		$datasoportegpon = $this->Modeloseguimientoclick->gettecnicosbysupervisor($cedula_supervisor);

		if ($datasoportegpon != 0) {
			$arrayResult = array('type' => 'success', 'message' => $datasoportegpon);
		} else {
			$arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
		}

		echo json_encode($arrayResult);

	}

	public function gettareasbytecnico()
    {

		$jwt = $this->input->get_request_header('x-token', TRUE);

        $cedula_tecnico = trim(htmlentities($this->input->get('cedula_tecnico'),ENT_QUOTES));

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/TareasTec/$cedula_tecnico");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);

		$datatareas = json_decode($data, TRUE);

        if (count($datatareas) == 0) {
			$arrayResult = array('type' => 'error', 'message' => 'Sin datos para listar.');
			echo json_encode($arrayResult);
			die();
		}

        $arrayResult = array('type' => 'success', 'message' => $datatareas);
        echo json_encode($arrayResult);

    }
}
