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
		$hora_fin = '19:00';

		if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
			$arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 7pm'];
			echo json_encode($arrayResult);
			die();
		}

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
			$modulo = 'bb8 aplicación mobil';
			$this->Modelobb8->contador($modulo);

			$arrayResult = array('type' => 'success', 'message' => $databb8);
			echo json_encode($arrayResult);
		} else {


			$ch = curl_init();
			//curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetClick/$pedido");
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV/Buscar/$pedido");

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$databb8 = json_decode($data, TRUE);
			$gis = $databb8['ID_GIS'];

			/**
			 * velocidad
			 */
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanBaMSS/$pedido");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$dataDecode = json_decode($data, TRUE);

			if (!$dataDecode) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanBaMSS/$gis");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data = curl_exec($ch);
				curl_close($ch);

				$dataDecode = json_decode($data, TRUE);
			}

			//var_dump($dataDecode);die();

			$vel = [];
			foreach ($dataDecode as $elemento) {
				if ($elemento["VALUE_LABEL"] == "Qty") {
					$vel["VELOCIDAD"] = $elemento['VALID_VALUE'];
				}
				if (!$elemento["LINEA"]) {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanTOMSS/$pedido");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					$data = curl_exec($ch);
					curl_close($ch);

					$dataDecode = json_decode($data, TRUE);
					foreach ($dataDecode as $elemento) {
						if ($elemento["LINEA"]) {
							$vel["LINEA"] = $elemento['LINEA'];
						}
					}
				} else {
					$vel["LINEA"] = $elemento['LINEA'];
				}
			}

			/**
			 * paquetes
			 */
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanTVMSS/$pedido");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$dataDecode = json_decode($data, TRUE);

			if (!$dataDecode) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/GetPlanTVMSS/$gis");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data = curl_exec($ch);
				curl_close($ch);
				$dataDecode = json_decode($data, TRUE);
			}

			$paquetes = [];
			foreach ($dataDecode as $elemento) {
				if ($elemento["ITEM_ALIAS"] === "PaqueteTV") {
					$paquetes[] = $elemento["VALUE_LABEL"] . " | " . $elemento["VALID_VALUE"];
				}
			}

			$paquetesString = implode(' - ', $paquetes);

			/**
			 * equipos
			 */
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/nuevoBB8/$gis");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$dataDecode = json_decode($data, TRUE);

			$equipos = [];
			foreach ($dataDecode as $elemento) {
				$equipos[] = [
					"SERIAL" => $elemento["SERIAL"],
					"MAC" => $elemento["MAC"],
					"MARCA" => $elemento["MARCA"],
				];
			}


			foreach ($equipos as &$equipo) {
				$equipo['PAQUETE'] = $paquetesString;
				$equipo['VELOCIDAD'] = $vel["VELOCIDAD"];
				$equipo['LINEA'] = $vel["LINEA"];
			}

			if (count($equipos) == 0) {
				$arrayResult = array('type' => 'error', 'message' => 'Sin datos para listar.');
				echo json_encode($arrayResult);
				die();
			}

			$modulo = 'Datos técnicos';
			$this->Modelobb8->contador($modulo);

			$arrayResult = array('type' => 'success', 'message' => $equipos);
			echo json_encode($arrayResult);

		}
	}

	public function getbb8Puertos()
	{
		$jwt = $this->input->get_request_header('x-token', TRUE);

		if (!$jwt) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		$hora_actual = date('H:i');

		$hora_inicio = '07:00';
		$hora_fin = '19:00';

		if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
			$arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 7pm'];
			echo json_encode($arrayResult);
			die();
		}

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$olt = trim(htmlentities($this->input->get('olt'), ENT_QUOTES));
		$arpon = trim(htmlentities($this->input->get('arpon'), ENT_QUOTES));
		$nap = trim(htmlentities($this->input->get('nap'), ENT_QUOTES));

		$consulta = $olt . '-' . $arpon . '-' . $nap;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/BB8/contingencias/Buscar/getBB8Puertos/$consulta");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);
		$dataDecode = json_decode($data, TRUE);


		if ($dataDecode) {
			$modulo = 'Ocupación NAP';
			$this->Modelobb8->contador($modulo);
			$arrayResult = array('type' => 'success', 'message' => $dataDecode);
		} else {
			$arrayResult = array('type' => 'error', 'message' =>  'Sin datos para listar.');
		}

		echo json_encode($arrayResult);
		die();

	}
}
