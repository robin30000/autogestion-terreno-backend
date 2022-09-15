<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Consoportegpon extends CI_Controller {
	
	function __construct() {
		parent::__construct();
		$this->load->model('Modelosoportegpon');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'soportegpon';
	}

	public function postsoportegpon()
	{
		$fecha = date('Y-m-d');
		$fecha = $fecha.' 00:00:00';
		
		$jwt = $this->input->get_request_header('x-token', TRUE);
		$reqjson = json_decode($this->input->raw_input_stream, true);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$tarea = trim(htmlentities($reqjson['tarea'],ENT_QUOTES));
		$arpon = trim(htmlentities($reqjson['arpon'],ENT_QUOTES));
		$nap = trim(htmlentities($reqjson['nap'],ENT_QUOTES));
		$hilo = trim(htmlentities($reqjson['hilo'],ENT_QUOTES));
		$internet_port1 = trim(htmlentities($reqjson['internet_port1'],ENT_QUOTES));
		$internet_port2 = trim(htmlentities($reqjson['internet_port2'],ENT_QUOTES));
		$internet_port3 = trim(htmlentities($reqjson['internet_port3'],ENT_QUOTES));
		$internet_port4 = trim(htmlentities($reqjson['internet_port4'],ENT_QUOTES));
		$tv_port1 = trim(htmlentities($reqjson['tv_port1'],ENT_QUOTES));
		$tv_port2 = trim(htmlentities($reqjson['tv_port2'],ENT_QUOTES));
		$tv_port3 = trim(htmlentities($reqjson['tv_port3'],ENT_QUOTES));
		$tv_port4 = trim(htmlentities($reqjson['tv_port4'],ENT_QUOTES));
		$numero_contaco = trim(htmlentities($reqjson['numero_contaco'],ENT_QUOTES));
		$nombre_contaco = trim(htmlentities($reqjson['nombre_contaco'],ENT_QUOTES));
		$observacion = trim(htmlentities($reqjson['observacion'],ENT_QUOTES));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarTaskGpon/$tarea"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);

		$dataclick = json_decode($data, TRUE);

		if (count($dataclick) == 0) {
			$arrayResult = array('type' => 'error', 'message' => 'Tarea no existe en click o no se encuentra en En Sitio, validar e intentar nuevamente.');
			echo json_encode($arrayResult);
			die();
		}
		
		$user_id = $payload->login;
		$request_id = NULL;
		$user_identification = $payload->iduser;
		$fecha_solicitud = date('Y-m-d H:i:s');

		$unepedido = $dataclick[0]['UNEPedido'];
		$tasktypecategory = $dataclick[0]['categoria'];
		$unemunicipio = $dataclick[0]['UNEMunicipio'];
		$uneproductos = $dataclick[0]['UNEProductos'];
		$engineer_id = $dataclick[0]['EngineerID'];
		$engineer_name = $dataclick[0]['EngineerName'];
		$mobile_phone = $dataclick[0]['MobilePhone'];
		$velocidad_navegacion = $dataclick[0]['VelocidadNavegacion'];

		$serials = implode(',',array_unique(array_column($dataclick, 'SerialNo')));
		$macs = implode(',',array_unique(array_column($dataclick, 'MAC')));
		$tipoeqs = implode(',',array_unique(array_column($dataclick, 'TipoEquipo')));
		$planprod = implode(',',array_unique(array_column($dataclick, 'UNEPlanProducto')));

		$pos = strpos($planprod, 'TO');

		if ($pos !== FALSE) {
			$datoscolaunique = implode(',',array_unique(array_column($dataclick, 'DatosCola1')));
			$datoscolaexplode = explode('*', $datoscolaunique);
			foreach ($datoscolaexplode as $key => $value) {
				$posnum = strpos($value, 'Numero');
				if ($posnum !== FALSE) {
					$planprod .= $value;
					break;
				}
			}
		}
		$planprod = trim($planprod);

		$datasoportegpon = $this->Modelosoportegpon->getsoportegponbytask($tarea, $fecha);

		$arrayResult = NULL;
		if ($datasoportegpon == 0) {
			// INSERT

			$ressoportegpon = $this->Modelosoportegpon->postsoportegpon($tarea,$arpon,$nap,$hilo,$internet_port1,$internet_port2,$internet_port3,$internet_port4,$tv_port1,$tv_port2,$tv_port3,$tv_port4,$numero_contaco,$nombre_contaco,$observacion,$user_id,$request_id,$user_identification,$fecha_solicitud,$unepedido,$tasktypecategory,$unemunicipio,$uneproductos,$engineer_id,$engineer_name,$mobile_phone,$velocidad_navegacion,$serials,$macs,$tipoeqs,$planprod);

			if ($ressoportegpon == 1 || $ressoportegpon == 0) {
				$arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con exito.');
			} else {
				$arrayResult = array('type' => 'error', 'message' => 'Error al registrar solcitud.', 'error' => $ressoportegpon);
			}

		} else {
			// UPDATE

			$ressoportegpon = $this->Modelosoportegpon->putsoportegpon($tarea,$arpon,$nap,$hilo,$internet_port1,$internet_port2,$internet_port3,$internet_port4,$tv_port1,$tv_port2,$tv_port3,$tv_port4,$numero_contaco,$nombre_contaco,$observacion,$user_id,$request_id,$user_identification,$fecha_solicitud,$unepedido,$tasktypecategory,$unemunicipio,$uneproductos,$engineer_id,$engineer_name,$mobile_phone,$velocidad_navegacion,$serials,$macs,$tipoeqs,$planprod,$datasoportegpon['id_soporte']);

			if ($ressoportegpon == 1 || $ressoportegpon == 0) {
				$arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con exito.');
			} else {
				$arrayResult = array('type' => 'error', 'message' => 'Error al registrar solcitud.', 'error' => $ressoportegpon);
			}
		}
	
		echo json_encode($arrayResult);

	}

	public function getsoportegponbyuser()
	{
		$fecha = date('Y-m-d');
		$fecha = $fecha.' 00:00:00';
		
		$jwt = $this->input->get_request_header('x-token', TRUE);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}
		
		$user_id = $payload->login;
		$user_identification = $payload->iduser;

		$datasoportegpon = $this->Modelosoportegpon->getsoportegponbyuser($user_id, $fecha);

		if ($datasoportegpon != 0) {
			$arrayResult = array('type' => 'success', 'message' => $datasoportegpon);
		} else {
			$arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
		}
	
		echo json_encode($arrayResult);

	}
}
