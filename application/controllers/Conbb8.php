<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Conbb8 extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Modelobb8');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'bb8';
	}

	public function getbb8()
	{
		$fecha = date('Y-m-d');
		$fecha = $fecha . ' 00:00:00';

		$jwt = $this->input->get_request_header('x-token', TRUE);

		$direccion = trim(htmlentities($this->input->get('direccion'), ENT_QUOTES));
		$ciudad = trim(htmlentities($this->input->get('ciudad'), ENT_QUOTES));

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		$hora_actual = date('H:i');

		$hora_inicio = '07:00';
		$hora_fin = '20:00';

		/*if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
			$arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 8pm'];
			echo json_encode($arrayResult);
			die();
		}*/

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		/* $direccion = trim(htmlentities($reqjson['direccion'],ENT_QUOTES));
        $ciudad = trim(htmlentities($reqjson['ciudad'],ENT_QUOTES)); */

		$direccion = strtoupper(trim(htmlentities($this->input->get('direccion'), ENT_QUOTES)));
		$ciudad = trim(htmlentities($this->input->get('ciudad'), ENT_QUOTES));
		$pedido = trim(htmlentities($this->input->get('pedido'), ENT_QUOTES));

		$patronesDireccion = array('/ /', '/#/');
		$sustitucionDireccion = array('*', '$');

		$patronesCiudad = array('/ /');
		$sustitucionCiudad = array('*');

		$dirFormat = preg_replace($patronesDireccion, $sustitucionDireccion, $direccion);
		$ciuFormat = preg_replace($patronesCiudad, $sustitucionCiudad, $ciudad);


		if ($pedido === '') {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/Direcciones/Buscars/$ciuFormat/$dirFormat");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$databb8 = json_decode($data, TRUE);

			if (count($databb8) == 0) {
				$arrayResult = array('type' => 'error', 'message' => 'Sin datos para listar.');
				echo json_encode($arrayResult);
				die();
			}

			$this->Modelobb8->contador();

			$arrayResult = array('type' => 'success', 'message' => $databb8);
			echo json_encode($arrayResult);
		} else {
			$response = array();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV/Buscar/$pedido");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			//var_dump($data);exit();
			curl_close($ch);

			$databb8 = json_decode($data, TRUE);

			$miArray = ['GIS' => $databb8['ID_GIS'], 'TYPE' => $databb8['Type']];
			foreach ($databb8['Equipos'] as &$equipo) {
				$equipo = array_merge($miArray, $equipo);
			}

			$gis = $databb8['ID_GIS'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanBaMSS/$gis");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			//var_dump($data);exit();
			curl_close($ch);

			$dataDecode = json_decode($data, TRUE);

			foreach ($dataDecode as $elemento) {
				if ($elemento["VALUE_LABEL"] == "Qty") {
					$r = [
						"VELOCIDAD" => $elemento['VALID_VALUE']
					];
				}
			}

			foreach ($databb8['Equipos'] as &$equipo) {
				$equipo = array_merge($r, $equipo);
			}

			$response = $databb8['Equipos'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanTVMSS/$pedido");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$b = json_decode($data, TRUE);
			//$response[] = $b;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanTOMSS/$pedido");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$c = json_decode($data, TRUE);
			//$response[] = $c;

			if (count($response) == 0) {
				$arrayResult = array('type' => 'error', 'message' => 'Sin datos para listar.');
				echo json_encode($arrayResult);
				die();
			}


			//$this->Modelobb8->contador();

			$arrayResult = array('type' => 'success', 'message' => $response);
			echo json_encode($arrayResult);

		}
	}
}
