<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RegistroEquipos extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('ModeloRegistroEquipo');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'RegistroEquipos';
	}


	public function postregistroequipos()
	{

		$jwt     = $this->input->get_request_header('x-token', true);
		$reqjson = json_decode($this->input->raw_input_stream, true);

		$data = json_decode(file_get_contents("php://input"), true);

        $hora_actual = date('H:i');

        $hora_inicio = '07:00';
        $hora_fin    = '20:00';

        if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
            $arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 8pm'];
            echo json_encode($arrayResult);
            die();
        }

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		$data['user'] = $payload->login;
		$data['doc']  = $payload->iduser;

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$pedido = trim(htmlentities($data['pedido'], ENT_QUOTES));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/RegistroPedido/$pedido");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$res = curl_exec($ch);
		curl_close($ch);
		$valida = json_decode($res, true);

		if ($valida){

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/DatosRegistroPedido/$pedido");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$res = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($res, true);

            if(empty($result[0]['uNEDireccionComentada'])){
                $data['gis'] = '';
                $data['direccion'] = '';
            }else{
                $gis    = explode('*', $result[0]['uNEDireccionComentada'], 2);
                $data['gis'] = $gis[0];
                $data['direccion'] = $gis[1];
            }


			$data['cliente'] = $result[0]['uNENombreCliente'];
			$data['municipio'] = $result[0]['uNEMunicipio'];
            $data['sistema']=$result[0]['Sistema'];

			$validacion = $this->ModeloRegistroEquipo->postregistroequipos($data);


			if ($validacion == 1) {
				$arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con éxito.');
			} elseif ($validacion == 0) {
				$arrayResult = array('type' => 'error', 'message' => 'Error al registrar pedido no valido.');
			}
		}else{
			$arrayResult = array('type' => 'error', 'message' => 'Pedido no existe favor validar nuevamente.');
		}

		echo json_encode($arrayResult);
	}

	public function getregistroequiposbyuser()
	{
		$fecha = date('Y-m-d');
		$fecha = $fecha . ' 00:00:00';

		$jwt = $this->input->get_request_header('x-token', true);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$user_id             = $payload->login;
		$user_identification = $payload->iduser;

		$datasoportegpon = $this->ModeloRegistroEquipo->getregistroequiposbyuser($user_id);

		if ($datasoportegpon != 0) {
			$arrayResult = array('type' => 'success', 'message' => $datasoportegpon);
		} else {
			$arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
		}

		echo json_encode($arrayResult);

	}

	public function getregistropedido()
	{
		$jwt     = $this->input->get_request_header('x-token', true);
		$reqjson = json_decode($this->input->raw_input_stream, true);

		$pedido = trim(htmlentities($this->input->get('pedido'), ENT_QUOTES));

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
			echo json_encode($arrayResult);
			die();
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/RegistroPedido/$pedido");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);
		$valida = json_decode($data, true);

		if ($valida) {
			$arrayResult = array('type' => 'success', 'message' => 'Pedido valido');
		} else {
			$arrayResult = array('type' => 'error', 'message' => 'Pedido no existe');
		}

		echo json_encode($arrayResult);
	}
}
