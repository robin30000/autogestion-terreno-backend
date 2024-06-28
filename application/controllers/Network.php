<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(1);
ini_set('display_errors', 1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Network extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('ModeloNetwork');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'Network';
	}

	public function postNetwork()
	{
		$jwt = $this->input->get_request_header('x-token', true);
		$reqjson = json_decode($this->input->raw_input_stream, true);
		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
			echo json_encode($arrayResult);
			die();
		}


		$numero_ticket = trim(htmlentities($reqjson['numero_ticket'], ENT_QUOTES));
		$tecnologia = $reqjson['tecnologia'];
		$direccion = $reqjson['direccion'];
		$observacion = $reqjson['observacion'];
		$region = $reqjson['region'];
		$clasificador = $reqjson['clasificador'];

		$numero_contacto = $payload->celular;
		$nombre_contacto = $payload->nombre;
		$cc_tecnico = $payload->iduser;

		$validacion = $this->ModeloNetwork->validacionesContingecias();
		$click = $validacion[9]['valida'];
		$validaEstado = $validacion[10]['valida'];

		$dataNetwork = $this->ModeloNetwork->getTask($numero_ticket);

		if (!$dataNetwork) {
			$resNetwork = $this->ModeloNetwork->postPedidoNetwork(
				$numero_ticket,
				$tecnologia,
				$direccion,
				$observacion,
				$nombre_contacto,
				$cc_tecnico,
				$numero_contacto,
				$region,
				$clasificador
				);

			if ($resNetwork) {
				$arrayResult = ['type' => 'success', 'message' => 'Se registro solicitud con éxito.'];
			} else {
				$arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $resNetwork];
			}
		} else {
			$arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para esta tarea, valide e intenta nuevamente.'];
		}

		echo json_encode($arrayResult);
	}

	public function getNetworkByUserMass()
	{


		$jwt = $this->input->get_request_header('x-token', true);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
			echo json_encode($arrayResult);
			die();
		}

		$cc_tecnico = $payload->iduser;

		$data = $this->ModeloNetwork->getNetworkByUserMass($cc_tecnico);

		if ($data) {
			$arrayResult = ['type' => 'success', 'message' => $data];
		} else {
			$arrayResult = ['type' => 'error', 'message' => 'No hay datos para listar'];
		}

		echo json_encode($arrayResult);

	}

	public function getNetworkByUserIndividual()
	{

		$jwt = $this->input->get_request_header('x-token', true);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
			echo json_encode($arrayResult);
			die();
		}

		$cc_tecnico = $payload->iduser;

		$data = $this->ModeloNetwork->getNetworkByUserIndividual($cc_tecnico);

		if ($data) {
			$arrayResult = ['type' => 'success', 'message' => $data];
		} else {
			$arrayResult = ['type' => 'error', 'message' => 'No hay datos para listar'];
		}

		echo json_encode($arrayResult);

	}

	public function regiones()
	{

		$regions = [
			"CO-Antioquia Centro",
			"CO-Antioquia Municipios",
			"CO-Antioquia Norte",
			"CO-Antioquia Oriente",
			"CO-Antioquia Sur",
			"CO-Antioquia_Edatel",
			"CO-Atlantico",
			"CO-Bolivar",
			"CO-Bolivar_Edatel",
			"CO-Boyaca",
			"CO-Boyaca_Edatel",
			"CO-Caldas",
			"CO-Caldas_Edatel",
			"CO-Casanare",
			"CO-Cauca",
			"CO-Cesar",
			"CO-Cesar_Edatel",
			"CO-Cordoba",
			"CO-Cordoba_Edatel",
			"CO-Cundinamarca Municipios",
			"CO-Cundinamarca Norte",
			"CO-Cundinamarca Sur",
			"CO-Guajira",
			"CO-Huila",
			"CO-Magdalena",
			"CO-Meta",
			"CO-Nariño",
			"CO-Norte de Santander",
			"CO-Otros_Municipios_Centro",
			"CO-Otros_Municipios_Eje cafetero",
			"CO-Otros_Municipios_Noroccidente",
			"CO-Otros_Municipios_Norte",
			"CO-Otros_Municipios_Oriente",
			"CO-Otros_Municipios_Sur",
			"CO-Quindio",
			"CO-Risaralda",
			"CO-Santander",
			"CO-Santander_Edatel",
			"CO-Sucre",
			"CO-Sucre_Edatel",
			"CO-Tolima",
			"CO-Valle",
			"CO-Valle Quindío"
		];

		return $regions;
	}

}
