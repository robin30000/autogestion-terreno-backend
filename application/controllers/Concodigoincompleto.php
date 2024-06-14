<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*error_reporting(0);
ini_set('display_errors', 0);*/

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Concodigoincompleto extends CI_Controller
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
		$fecha = date('Y-m-d');

		$jwt = $this->input->get_request_header('x-token', true);
		$reqjson = json_decode($this->input->raw_input_stream, true);

		$tarea = $reqjson['codigo'];

		$hora_actual = date('H:i');

		$hora_inicio = '07:00';
		$hora_fin = '19:00';

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

		$user_id = $payload->login;
		$user_identification = $payload->iduser;
		if (stripos($tarea, 'sa') === 0) {
			$tipo = $reqjson['tipo'];
			if ($tipo === 'incompleto') {

				$tarea = strtoupper($tarea);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarCodIncSa/$tarea");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data = curl_exec($ch);
				curl_close($ch);

				$datacodinc = json_decode($data, true);
				$dataclick = (array)$datacodinc;

				if (count($dataclick) == 0) {
					$arrayResult = array('type' => 'error', 'message' => 'Tarea no existe.');
					echo json_encode($arrayResult);
					die();
				}
				$codigo = $dataclick[0]['UNEIncompletionACENG'];
			} elseif ($tipo === 'completo') {
				$tarea = strtoupper($tarea);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarCodSa/$tarea");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data = curl_exec($ch);
				curl_close($ch);

				$datacodinc = json_decode($data, true);
				$dataclick = (array)$datacodinc;

				if (count($dataclick) == 0) {
					$arrayResult = array('type' => 'error', 'message' => 'Tarea no existe.');
					echo json_encode($arrayResult);
					die();
				}
				$codigo = $dataclick[0]['UNECompletionACENG'];
			}

			$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);
			if (!$datagestioncodinc) {
				$this->Modelocodigoincompleto->postgestioncodigoincompleto(
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					$tarea,
					$fecha,
					date('Y-m-d H:i:s'),
					$codigo,
					'SA',
					$codigo,
					$user_id
				);
			}

			$arrayResult = array('type' => 'success', 'message' => $codigo);
			echo json_encode($arrayResult);
			die();
		} elseif (stripos($tarea, 'w') === 0) {

			$DespuesDeW = substr($tarea, 1);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarCodInc/$DespuesDeW");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$datacodinc = json_decode($data, true);

			if (count($datacodinc) == 0) {
				$arrayResult = array('type' => 'error', 'message' => 'Tarea no existe.');
				echo json_encode($arrayResult);
				die();
			}

			$codigo = $datacodinc[0]['UNEIncompletionAC'];
			$unepedido = $datacodinc[0]['UNEPedido'];
			$unemunicipio = $datacodinc[0]['UNEMunicipio'];
			$uneproductos = $datacodinc[0]['UNEProductos'];
			$engineerid = $datacodinc[0]['EngineerID'];
			$engineername = $datacodinc[0]['EngineerName'];
			$unenombrecontacto = $datacodinc[0]['UNENombreContacto'];
			$unetelefonocontacto = $datacodinc[0]['UNETelefonoContacto'];
			$tasktypecategory = $datacodinc[0]['tasktypecategory'];
			$mobilephone = $datacodinc[0]['MobilePhone'];

			$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($DespuesDeW);
			if (!$datagestioncodinc) {
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
					date('Y-m-d H:i:s'),
					$codigo,
					'W',
					$codigo,
					$user_id
				);
			}
			$arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
			echo json_encode($arrayResult);
			die();

		} else {
			$task = strtoupper($tarea);
			//$posicion = strpos($task, "ret");
			if (stripos($task, 'RET') === 0) {
				$ret = 1;
			} else {
				$ret = 0;
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarCodInc/$task");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data = curl_exec($ch);
			curl_close($ch);

			$datacodinc = json_decode($data, true);

			if (count($datacodinc) == 0) {
				$arrayResult = array('type' => 'error', 'message' => 'Tarea no existe.');
				echo json_encode($arrayResult);
				die();
			}

			$appointmentStart = date('Y-m-d', strtotime($datacodinc[0]['AppointmentStart']));

			$fecha_respuesta = '';
			$unepedido = $datacodinc[0]['UNEPedido'];
			$unemunicipio = $datacodinc[0]['UNEMunicipio'];
			$uneproductos = $datacodinc[0]['UNEProductos'];
			$engineerid = $datacodinc[0]['EngineerID'];
			$engineername = $datacodinc[0]['EngineerName'];
			$unenombrecontacto = $datacodinc[0]['UNENombreContacto'];
			$unetelefonocontacto = $datacodinc[0]['UNETelefonoContacto'];
			$tasktypecategory = $datacodinc[0]['tasktypecategory'];
			$mobilephone = $datacodinc[0]['MobilePhone'];
			$fecha_respuesta = $datacodinc[0]['TimeCreated'];
			$fecha_inicio = $datacodinc[0]['AppointmentStart'];
			$respuesta = $datacodinc[0]['Estado'];
			$Description = $datacodinc[0]['Description'];
			$codigo = $datacodinc[0]['UNEIncompletionAC'];
			$tasktype = $datacodinc[0]['tasktype'];
			//Cambio_Equipo DTH

			$to_time = strtotime(date('Y-m-d H:i:s'));
			$from_time = strtotime($datacodinc[0]['TimeCreated']);
			$minutes = round(abs($to_time - $from_time) / 60, 0);

            $validacion = $this->Modelocodigoincompleto->validacionesContingecias();
            $validaEstado = $validacion[10]['valida'];

			if ($ret){

                if ($validaEstado == 'activa') {
                    if ($datacodinc[0]['Estado'] != 'En Sitio') {
                        $arrayResult = array('type' => 'error', 'message' => 'Debes estar en sitio para continuar.');
                        echo json_encode($arrayResult);
                        die();
                    }
                }

				$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);
				if (!$datagestioncodinc) {
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
						$Description,
						$codigo,
						$user_id
					);
				}
				$arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
				echo json_encode($arrayResult);
				die();

			}

			if ($appointmentStart > $fecha) {
				$arrayResult = array('type' => 'error', 'message' => 'Cliente posee una cita programada posterior.');
				echo json_encode($arrayResult);
				die();
			}

			if ($datacodinc[0]['tasktype'] == 'Cambio_Equipo DTH') {
				$arrayResult = array('type' => 'error', 'message' => 'Se debe escalar por el menu mesas nacionales (Cambio_Equipo DTH)');
				echo json_encode($arrayResult);
				die();
			}

            if ($validaEstado == 'activa') {
                if ($datacodinc[0]['Estado'] != 'En Sitio') {
                    $arrayResult = array('type' => 'error', 'message' => 'Debes estar en sitio para continuar.');
                    echo json_encode($arrayResult);
                    die();
                }
            }

			$validacion = $this->Modelocodigoincompleto->validacionesContingecias();

			$valCodigoIncompleto = $validacion[3];

			if ($valCodigoIncompleto['valida'] == 'inactiva') {
				$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);
				if (!$datagestioncodinc) {
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
						$Description,
						$codigo,
						$user_id
					);
				}
				$arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
				echo json_encode($arrayResult);
				die();
			}

			$mystringpend = ($datacodinc[0]['ticket'] != null) ? $datacodinc[0]['ticket'] : '';
			$findmepend = '010';
			$pospend = strpos($mystringpend, $findmepend);

			if ($pospend === 0) {

				if ($datacodinc[0]['Quantity'] == '81') {
					$codigo = 'SARA ya te entregó una respuesta.';
					$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);
					if (!$datagestioncodinc) {
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
							$Description,
							$codigo,
							$user_id
						);
					}

					$arrayResult = array('type' => 'error', 'message' => 'SARA ya te entregó una respuesta.');
					echo json_encode($arrayResult);
					die();
				}

				$to_time = strtotime(date('Y-m-d H:i:s'));
				$from_time = strtotime($datacodinc[0]['TimeCreated']);
				$minutes = round(abs($to_time - $from_time) / 60, 0);

				if (($datacodinc[0]['Quantity'] == '70' || $datacodinc[0]['Quantity'] == '0') && $minutes <= 10) {

					$codigo = 'Debes esperar 10 min, antes de realizar solicitud.';
					$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);
					if (!$datagestioncodinc) {
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
							$Description,
							$codigo,
							$user_id
						);
					}

					$arrayResult = array('type' => 'error', 'message' => 'Debes esperar 10 min, antes de realizar solicitud.');
					echo json_encode($arrayResult);
					die();
				}

				$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);
				if (!$datagestioncodinc) {
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
						$Description,
						$codigo,
						$user_id
					);
				}
				$arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
				echo json_encode($arrayResult);
				die();
			} else {
				$codigo = 'Pendiente no es imputable al cliente.';
				$datagestioncodinc = $this->Modelocodigoincompleto->getgestioncodigoincompletotarea($tarea);
				if (!$datagestioncodinc) {
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
						$Description,
						$codigo,
						$user_id
					);
				}
				$arrayResult = array('type' => 'error', 'message' => 'Pendiente no es imputable al cliente.');
				echo json_encode($arrayResult);
				die();
			}
		}
	}
}
