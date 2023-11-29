<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Consoportegpon extends CI_Controller
{

	function __construct()
	{
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
		$fecha = $fecha . ' 00:00:00';

		$jwt     = $this->input->get_request_header('x-token', true);
		$reqjson = json_decode($this->input->raw_input_stream, true);

        $hora_actual = date('H:i');

        $hora_inicio = '07:00';
        $hora_fin    = '20:00';

        if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
            $arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 8pm'];
            echo json_encode($arrayResult);
            die();
        }

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
			echo json_encode($arrayResult);
			die();
		}

		$validacion = $this->Modelosoportegpon->validacionesContingecias();

		$tarea          = trim(htmlentities($reqjson['tarea'], ENT_QUOTES));
		$arpon          = trim(htmlentities($reqjson['arpon'], ENT_QUOTES));
		$nap            = trim(htmlentities($reqjson['nap'], ENT_QUOTES));
		$hilo           = trim(htmlentities($reqjson['hilo'], ENT_QUOTES));
		$internet_port1 = trim(htmlentities($reqjson['internet_port1'], ENT_QUOTES));
		$internet_port2 = trim(htmlentities($reqjson['internet_port2'], ENT_QUOTES));
		$internet_port3 = trim(htmlentities($reqjson['internet_port3'], ENT_QUOTES));
		$internet_port4 = trim(htmlentities($reqjson['internet_port4'], ENT_QUOTES));
		$tv_port1       = trim(htmlentities($reqjson['tv_port1'], ENT_QUOTES));
		$tv_port2       = trim(htmlentities($reqjson['tv_port2'], ENT_QUOTES));
		$tv_port3       = trim(htmlentities($reqjson['tv_port3'], ENT_QUOTES));
		$tv_port4       = trim(htmlentities($reqjson['tv_port4'], ENT_QUOTES));

		$numero_contacto     = $payload->celular;
		$nombre_contacto     = $payload->nombre;
		$user_identification = $payload->iduser;
		$login               = $payload->login;
		$observacion         = $reqjson['observacion'];
		$infra               = $reqjson['infraestructura'];

/*        'iduser'  => $valUserQuery['identificacion'],
        'login'   => $valUserQuery['login_click'],
        'celular' => $celular,
        'nombre'  => $nombre,*/

        $val['infra'] = $infra;
        $val['tarea'] = $tarea;
        $val['infraestructura'] = $validacion[2]['valida'];
        $val['equipo'] = $validacion[5]['valida'];

        if (stripos($tarea, 'sa') === 0) {
			$datasoportegpon = $this->Modelosoportegpon->getsoportegponbytask($tarea, $fecha);
			if (!$datasoportegpon) {

				$fecha_solicitud      = date('Y-m-d H:i:s');
				$request_id           = null;
				$unepedido            = '';
				$tasktypecategory     = '';
				$unemunicipio         = '';
				$uneproductos         = '';
				$engineer_id          = $user_identification;
				$engineer_name        = $nombre_contacto;
				$mobile_phone         = $numero_contacto;
				$velocidad_navegacion = '';
				$serials              = '';
				$macs                 = '';
				$tipoeqs              = '';
				$planprod             = '';
				$Tipo                 = '';
				$taskType             = '';
				$area                 = '';
				$user_id              = $login;

				$ressoportegpon = $this->Modelosoportegpon->postsoportegpon(strtoupper($tarea), $arpon, $nap, $hilo, $internet_port1, $internet_port2, $internet_port3, $internet_port4,
					$tv_port1,
					$tv_port2, $tv_port3, $tv_port4, $numero_contacto, $nombre_contacto, $observacion, $user_id, $request_id, $user_identification, $fecha_solicitud, $unepedido,
					$tasktypecategory, $unemunicipio, $uneproductos, $engineer_id, $engineer_name, $mobile_phone, $velocidad_navegacion, $serials, $macs, $tipoeqs, $planprod,
					$Tipo,
					$taskType, $area);

				if ($ressoportegpon == 1) {
					$arrayResult = ['type' => 'success', 'message' => 'Se registro solicitud con éxito.'];
				} else {
					$arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $ressoportegpon];
				}

			} else {

				$arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para esta tarea, valide e intenta nuevamente.'];
			}

			echo json_encode($arrayResult);
			die();
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarTaskGpon/ss");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($val));
		$data = curl_exec($ch);
        //var_dump($data);exit();

		curl_close($ch);
		$dataclick = json_decode($data, true);


		if ($dataclick === 3145) {
			$arrayResult = ['type' => 'error', 'message' => 'Tarea no existe en click, por favor valide nuevamente.'];
			echo json_encode($arrayResult);
			die();
		}
		if ($dataclick === 6562) {
			$arrayResult = ['type' => 'error', 'message' => 'La tarea no es GPON.'];
			echo json_encode($arrayResult);
			die();
		}

		if ($dataclick === 3552) {
			$arrayResult = ['type' => 'error', 'message' => 'La tarea no se encuentra en sitio.'];
			echo json_encode($arrayResult);
			die();
		}

		if ($dataclick === 0154) {
			$arrayResult = ['type' => 'error', 'message' => 'No aplica categoría'];
			echo json_encode($arrayResult);
			die();
		}

		if ($dataclick === 2387) {
			$arrayResult = [
				'type'    => 'error',
				'message' => 'Soporte de GPON solo se atiende para POE, para los demas sistemas debes escalar al soporte definido por la operación',
			];
			echo json_encode($arrayResult);
			die();
		}

		if ($dataclick === 1010) {
			$arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene equipos asignados.'];
			echo json_encode($arrayResult);
			die();
		}

		//$user_id = $payload->nombre;

		$user_id    = $payload->login;
		$request_id = null;
		//$user_identification = $payload->iduser;
		$fecha_solicitud = date('Y-m-d H:i:s');

		$unepedido            = $dataclick[0]['UNEPedido'];
		$tasktypecategory     = $dataclick[0]['categoria'];
		$unemunicipio         = $dataclick[0]['UNEMunicipio'];
		$uneproductos         = $dataclick[0]['UNEProductos'];
		$engineer_id          = $dataclick[0]['EngineerID'];
		$engineer_name        = $dataclick[0]['EngineerName'];
		$mobile_phone         = $dataclick[0]['MobilePhone'];
		$velocidad_navegacion = $dataclick[0]['VelocidadNavegacion'];
		$Tipo                 = $dataclick[0]['Name'];
		$taskType             = $dataclick[0]['TaskType'];
		$area                 = $dataclick[0]['Area'];

		$serials  = implode(',', array_unique(array_column($dataclick, 'SerialNo')));
		$macs     = implode(',', array_unique(array_column($dataclick, 'MAC')));
		$tipoeqs  = implode(',', array_unique(array_column($dataclick, 'TipoEquipo')));
		$planprod = implode(',', array_unique(array_column($dataclick, 'UNEPlanProducto')));

		$pos = strpos($planprod, 'TO');

		if ($pos !== false) {
			$datoscolaunique  = implode(',', array_unique(array_column($dataclick, 'DatosCola1')));
			$datoscolaexplode = explode('*', $datoscolaunique);
			foreach ($datoscolaexplode as $key => $value) {
				$posnum = strpos($value, 'Numero');
				if ($posnum !== false) {
					$planprod .= $value;
					break;
				}
			}
		}
		$planprod = trim($planprod);

		$datasoportegpon = $this->Modelosoportegpon->getsoportegponbytask($tarea, $fecha);
		$arrayResult     = null;
		if ($datasoportegpon == 0) {

			$ressoportegpon = $this->Modelosoportegpon->postsoportegpon($tarea, $arpon, $nap, $hilo, $internet_port1, $internet_port2, $internet_port3, $internet_port4, $tv_port1,
				$tv_port2, $tv_port3, $tv_port4, $numero_contacto, $nombre_contacto, $observacion, $user_id, $request_id, $user_identification, $fecha_solicitud, $unepedido,
				$tasktypecategory, $unemunicipio, $uneproductos, $engineer_id, $engineer_name, $mobile_phone, $velocidad_navegacion, $serials, $macs, $tipoeqs, $planprod, $Tipo,
				$taskType, $area);

			if ($ressoportegpon == 1) {
				$arrayResult = ['type' => 'success', 'message' => 'Se registro solicitud con éxito.'];
			} else {
				$arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $ressoportegpon];
			}

		} else {

			$arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para esta tarea, valide e intenta nuevamente.'];
		}

		echo json_encode($arrayResult);

	}

	public function getsoportegponbyuser()
	{
		$fecha = date('Y-m-d');
		$fecha = $fecha . ' 00:00:00';

		$jwt = $this->input->get_request_header('x-token', true);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		if (!$payload) {
			$arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
			echo json_encode($arrayResult);
			die();
		}

		$user_id             = $payload->login;
		$user_identification = $payload->iduser;
		$nombre              = $payload->nombre;

		$datasoportegpon = $this->Modelosoportegpon->getsoportegponbyuser($user_id);

		if ($datasoportegpon != 0) {
			$arrayResult = ['type' => 'success', 'message' => $datasoportegpon];
		} else {
			$arrayResult = ['type' => 'error', 'message' => 'No hay datos para listar'];
		}

		echo json_encode($arrayResult);

	}
}

