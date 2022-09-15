<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Concontingencia extends CI_Controller {
	
	function __construct() {
		parent::__construct();
		$this->load->model('Modelocontingencia');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'contingencia';
	}

	public function postcontingencia()
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

		$pedido = trim(htmlentities($reqjson['pedido'],ENT_QUOTES));
		$tipoproducto = trim(htmlentities($reqjson['tipoproducto'],ENT_QUOTES));
		$tipocontingencia = trim(htmlentities($reqjson['tipocontingencia'],ENT_QUOTES));
		$observacion = trim(htmlentities($reqjson['observacion'],ENT_QUOTES));
		$macentra = trim(htmlentities($reqjson['macentra'],ENT_QUOTES));
		$macsale = trim(htmlentities($reqjson['macsale'],ENT_QUOTES));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarB/$pedido"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);

		$dataclick = json_decode($data, TRUE);

		if (count($dataclick) == 0) {
			$arrayResult = array('type' => 'error', 'message' => 'Validar pedido e intentar nuevamente.');
			echo json_encode($arrayResult);
			die();
		}
		
		$user_id = $payload->login;
		$request_id = NULL;
		$user_identification = $payload->iduser;
		$fecha_solicitud = date('Y-m-d H:i:s');

		$engineer_Type = $dataclick['engineer_Type'];
		$engineerID = $dataclick['engineerID'];
		$engineerName = $dataclick['engineerName'];
		$pEDIDO_UNE = $dataclick['pEDIDO_UNE'];
		$tAREA_ID = $dataclick['tAREA_ID'];
		$tASK_ID = $dataclick['tASK_ID'];
		$uNEActividades = $dataclick['uNEActividades'];
		$uNEBarrio = $dataclick['uNEBarrio'];
		$uNECelularContacto = $dataclick['uNECelularContacto'];
		$uNEDepartamento = $dataclick['uNEDepartamento'];
		$uNEDireccion = $dataclick['uNEDireccion'];
		$uNEDireccionComentada = $dataclick['uNEDireccionComentada'];
		$uNEFechaCita = $dataclick['uNEFechaCita'];
		$uNEHoraCita = $dataclick['uNEHoraCita'];
		$uNEFechaIngreso = $dataclick['uNEFechaIngreso'];
		$uNEIdCliente = $dataclick['uNEIdCliente'];
		$uNEMunicipio = $dataclick['uNEMunicipio'];
		$uNENombreCliente = $dataclick['uNENombreCliente'];
		$uNENombreContacto = $dataclick['uNENombreContacto'];
		$uNEPedido = $dataclick['uNEPedido'];
		$uNEProductos = $dataclick['uNEProductos'];
		$uNEProvisioner = $dataclick['uNEProvisioner'];
		$uNETecnologias = $dataclick['uNETecnologias'];
		$uNEUENcalculada = $dataclick['uNEUENcalculada'];
		$uNERutaTrabajo = $dataclick['uNERutaTrabajo'];
		$uNEUen = $dataclick['uNEUen'];
		$TaskType = $dataclick['TaskType'];
		$DispatchDate = $dataclick['DispatchDate'];
		$Description = $dataclick['Description'];
		$LaborType = $dataclick['LaborType'];
		$Type = $dataclick['Type'];
		$MAC = $dataclick['MAC'];
		$RTA = $dataclick['RTA'];
		$RTA3 = $dataclick['RTA3'];
		$LoginName = $dataclick['LoginName'];
		$Estado = $dataclick['Estado'];

		$correo = '';
		$motivo = '';
		$paquetes = '';
		$tipoEquipo = '';
		$remite = 'Terreno';
		$perfil = '';
		$engestion = '0';

		$grupo = ($tipoproducto == 'Internet' || $tipoproducto == 'ToIP' || $tipoproducto == 'Internet+ToIP') ? 'INTER' : 'TV';

		$datasoportegpon = $this->Modelocontingencia->getcontingenciabypedido($pedido, $fecha);

		$arrayResult = NULL;
		if ($datasoportegpon == 0) {
			// INSERT

			$ressoportegpon = $this->Modelocontingencia->postcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes, $pedido, $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification, $fecha_solicitud, $engestion);

			if ($ressoportegpon == 1 || $ressoportegpon == 0) {
				$arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con exito.');
			} else {
				$arrayResult = array('type' => 'error', 'message' => 'Error al registrar solcitud.', 'error' => $ressoportegpon);
			}

		} else {
			// UPDATE

			$ressoportegpon = $this->Modelocontingencia->putcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes, $pedido, $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification, $fecha_solicitud, $engestion, $datasoportegpon['id']);

			if ($ressoportegpon == 1 || $ressoportegpon == 0) {
				$arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con exito.');
			} else {
				$arrayResult = array('type' => 'error', 'message' => 'Error al registrar solcitud.', 'error' => $ressoportegpon);
			}
		}
	
		echo json_encode($arrayResult);

	}

	public function getcontingenciabyuser()
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

		$datasoportegpon = $this->Modelocontingencia->getcontingenciabyuser($user_id, $fecha);

		if ($datasoportegpon != 0) {
			$arrayResult = array('type' => 'success', 'message' => $datasoportegpon);
		} else {
			$arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
		}
	
		echo json_encode($arrayResult);

	}


}
