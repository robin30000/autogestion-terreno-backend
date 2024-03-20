<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(E_ALL);
ini_set('display_errors', 1);

use jwt\src\JWT;
use jwt\src\Key;

class TareaSupervisor extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('ModeloTareaSupervisor');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'TareaSupervisor';
	}

	public function tareasupervisor(){
		$jwt = $this->input->get_request_header('x-token', TRUE);
		$tarea = trim(htmlentities($this->input->get('tarea'),ENT_QUOTES));

		$hora_actual = date('H:i');
		$hora_inicio = '07:00';
		$hora_fin    = '19:00';



		if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
			$arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 7pm'];
			echo json_encode($arrayResult);
			die();
		}

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$data_tarea = $this->ModeloTareaSupervisor->tarea_supervisor($tarea);

		if ($data_tarea) {
			$arrayResult = array('type' => 'success', 'message' => $data_tarea);
		} else {
			$arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
		}

		echo json_encode($arrayResult);
	}
}
