<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Concodigoincompleto_back extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Modelocodigoincompleto');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'codigoincompleto';
	}

	public function getcodigoincompleto()
	{

		$fecha = date('Y-m-d H:i:s');

		$jwt = $this->input->get_request_header('x-token', true);

		$tarea = trim(htmlentities($this->input->get('tarea'), ENT_QUOTES));

		$tarea   = '37730234';
		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		/*if (!$payload) {
				  $arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
				  echo json_encode($arrayResult);
				  die();
			  }*/

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarCodInc/$tarea");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);

		$datacodinc = json_decode($data, true);

		//var_dump($datacodinc);
		//exit();

		if (count($datacodinc) == 0) {
			$arrayResult = array('type' => 'error', 'message' => 'Tarea no existe.');
			echo json_encode($arrayResult);
			die();
		}

		$appointmentStart = date('Y-m-d H:i:s', strtotime($datacodinc[0]['AppointmentStart']));

		$fecha_respuesta     = '';
		$unepedido           = $datacodinc[0]['UNEPedido'];
		$unemunicipio        = $datacodinc[0]['UNEMunicipio'];
		$uneproductos        = $datacodinc[0]['UNEProductos'];
		$engineerid          = $datacodinc[0]['EngineerID'];
		$engineername        = $datacodinc[0]['EngineerName'];
		$unenombrecontacto   = $datacodinc[0]['UNENombreContacto'];
		$unetelefonocontacto = $datacodinc[0]['UNETelefonoContacto'];
		$tasktypecategory    = $datacodinc[0]['tasktypecategory'];
		$mobilephone         = $datacodinc[0]['MobilePhone'];
		$fecha_respuesta     = $datacodinc[0]['TimeCreated'];
		$fecha_inicio        = $datacodinc[0]['AppointmentStart'];
		$respuesta           = $datacodinc[0]['Estado'];
		$Description         = $datacodinc[0]['Description'];
		$codigo              = $datacodinc[0]['UNEIncompletionAC'];

		$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);

		if ($datagestioncodinc) {
//echo 1;exit();
			$to_time   = strtotime(date('Y-m-d H:i:s'));
			$from_time = strtotime($datacodinc[0]['TimeCreated']);
			$minutes   = round(abs($to_time - $from_time) / 60, 0);

			if ($appointmentStart > $fecha) {
				$arrayResult = array('type' => 'error', 'message' => 'Cliente posee una cita programada posterior.');
				echo json_encode($arrayResult);
				die();
			}

			/*if ($datacodinc[0]['Estado'] != 'En Sitio') {
				$arrayResult = array('type' => 'error', 'message' => 'Debes estar en sitio para continuar.');
				echo json_encode($arrayResult);
				die();
			}*/
			//echo 'PEPE ' . $datacodinc[0]['ticket'];
			//exit();

			//$cadena = "pep 01 | CI  Imputable al cliente";
			//$f = '011';
			//$res = strpos($cadena, $f);
			//echo $res . ' Respuesta';exit();
			// respuesta si tipo pendiente es supervisor
			$subcadenas = array('01', '012', '013', '014', '017', '021', '022');
			foreach ($subcadenas as $subcadena) {
				$posicion = strpos($datacodinc[0]['ticket'], $subcadena);

				if ($posicion || $posicion == 0) {
					$this->Modelocodigoincompleto->putgestioncodigoincompleto(
						$unepedido,
						$unemunicipio,
						$uneproductos,
						$engineerid,
						$engineername,
						$unenombrecontacto,
						$unetelefonocontacto,
						$tasktypecategory,
						$mobilephone,
						$tarea,
						$fecha,
						$respuesta,
						$Description,
						$codigo
					);

					$arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
					echo json_encode($arrayResult);
					exit();
				}
			}

			if ($datacodinc[0]['Quantity'] == '81') {
				$arrayResult = array('type' => 'error', 'message' => 'SARA ya te entregó una respuesta.');
				echo json_encode($arrayResult);
				die();
			}

			if (($datacodinc[0]['Quantity'] == '70' || $datacodinc[0]['Quantity'] == '0') && $minutes <= 10) {
				$arrayResult = array('type' => 'error', 'message' => 'Debes esperar 10 min, antes de realizar solicitud.');
				echo json_encode($arrayResult);
				die();
			}

			$mystringpend = ($datacodinc[0]['ticket'] != null) ? $datacodinc[0]['ticket'] : '';
			$findmepend   = '010';
			$pospend      = strpos($mystringpend, $findmepend);

			if ($pospend === false) {
				$arrayResult = array('type' => 'error', 'message' => 'Pendiente no es imputable al cliente.');
				echo json_encode($arrayResult);
				die();
			}

			$this->Modelocodigoincompleto->putgestioncodigoincompleto(
				$unepedido,
				$unemunicipio,
				$uneproductos,
				$engineerid,
				$engineername,
				$unenombrecontacto,
				$unetelefonocontacto,
				$tasktypecategory,
				$mobilephone,
				$tarea,
				$fecha,
				$respuesta,
				$Description,
				$codigo
			);

		} else {

			$this->Modelocodigoincompleto->postgestioncodigoincompleto(
				$unepedido,
				$unemunicipio,
				$uneproductos,
				$engineerid,
				$engineername,
				$unenombrecontacto,
				$unetelefonocontacto,
				$tasktypecategory,
				$mobilephone,
				$tarea,
				$fecha,
				$fecha_respuesta,
				$respuesta,
				$Description
			);
		}
//echo 2;exit();

		if ($appointmentStart > $fecha) {
			$arrayResult = array('type' => 'error', 'message' => 'Cliente posee una cita programada posterior.');
			echo json_encode($arrayResult);
			die();
		}

		if ($datacodinc[0]['Estado'] != 'En Sitio') {
			$arrayResult = array('type' => 'error', 'message' => 'Debes estar en sitio para continuar.');
			echo json_encode($arrayResult);
			die();
		}
		//echo $datacodinc[0]['ticket'];
		//exit();
		// respuesta si tipo pendiente es supervisor
		$subcadenas = array('011', '012', '013', '014', '017', '021', '022');
		foreach ($subcadenas as $subcadena) {
			$posicion = strpos($datacodinc[0]['ticket'], $subcadena);

			if ($posicion || $posicion == 0) {

				$this->Modelocodigoincompleto->postgestioncodigoincompleto(
					$unepedido,
					$unemunicipio,
					$uneproductos,
					$engineerid,
					$engineername,
					$unenombrecontacto,
					$unetelefonocontacto,
					$tasktypecategory,
					$mobilephone,
					$tarea,
					$fecha,
					$fecha_respuesta,
					$respuesta,
					$Description
				);

				$arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
				echo json_encode($arrayResult);
				exit();
			}
		}

		if ($datacodinc[0]['Quantity'] == '81' and $Description != 'Error de sistema, comuníquese con despacho.') {
			$arrayResult = array('type' => 'error', 'message' => 'SARA ya te entregó una respuesta.');
			echo json_encode($arrayResult);
			die();
		}


		$to_time   = strtotime(date('Y-m-d H:i:s'));
		$from_time = strtotime($datacodinc[0]['TimeCreated']);
		$minutes   = round(abs($to_time - $from_time) / 60, 0);

		if (($datacodinc[0]['Quantity'] == '70' || $datacodinc[0]['Quantity'] == '0') && $minutes <= 10) {
			$arrayResult = array('type' => 'error', 'message' => 'Debes esperar 10 min, antes de realizar solicitud.');
			echo json_encode($arrayResult);
			die();
		}

		$mystringpend = ($datacodinc[0]['ticket'] != null) ? $datacodinc[0]['ticket'] : '';
		$findmepend   = '010';
		$pospend      = strpos($mystringpend, $findmepend);

		if ($pospend === false) {
			$arrayResult = array('type' => 'error', 'message' => 'Pendiente no es imputable al cliente.');
			echo json_encode($arrayResult);
			die();
		}

		$arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
		echo json_encode($arrayResult);

	}

}
