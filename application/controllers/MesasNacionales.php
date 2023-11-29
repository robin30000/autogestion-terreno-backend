<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MesasNacionales extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('ModeloMesasNacionales');
        $this->load->library('nativesession');
        $this->load->library('validarjwt');
        $this->load->helper('url');
    }

    public function index()
    {
        echo 'Etp';
    }

    public function validaPedidoMn()
    {

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

        $tarea = trim(htmlentities($_GET['tarea'], ENT_QUOTES));

        if (!$tarea) {
            $arrayResult = ['type' => 'error', 'message' => 'Ingrese la tarea'];
            echo json_encode($arrayResult);
            die();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/validaPedidoMn/$tarea");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);

        curl_close($ch);

        if (stripos($tarea, 'sa') === 0) {
            $arrayResult = ['type' => 'success', 'message' => 'ELT-POE'];
            echo json_encode($arrayResult);
            die();
        }

        $dataclick = json_decode($data, true);
        $pattern   = '/\b(BSC|B2B)\b/i';


        if (!$dataclick) {
            $arrayResult = ['type' => 'error', 'message' => 'La tarea no existe. validar tarea e intentar nuevamente.'];
        } elseif ($dataclick == 5555) {
            $arrayResult = ['type' => 'error', 'message' => 'La tarea no se encuentra en sitio'];
        } elseif ($dataclick[0]['Estado'] != 'En Sitio') {
            $arrayResult = ['type' => 'error', 'message' => 'La tarea no se encuentra en sitio'];
        } /*elseif (preg_match($pattern, $dataclick[0]['TaskTypeCategory']) || str_contains($dataclick[0]['TaskType'], 'Bronce')) {
            $arrayResult = ['type' => 'error', 'message' => 'Esta mesa no atiende solicitudes para BSC'];
        }*/ elseif ($dataclick[0]['TaskType'] == 'Reparacion Infraestructura') {
            $arrayResult = ['type' => 'success', 'message' => 'PRE'];
        } elseif ($dataclick[0]['UneSourceSystem'] == 'EDA') {
            $arrayResult = ['type' => 'success', 'message' => 'EDA'];
        } elseif ($dataclick[0]['UneSourceSystem'] == 'ELT' || $dataclick[0]['UneSourceSystem'] == 'POE') {
            $arrayResult = ['type' => 'success', 'message' => 'ELT-POE'];
        } else {
            $arrayResult = ['type' => 'error', 'message' => 'No aplica UneSourceSystem'];
        }

        echo json_encode($arrayResult);
        die();

    }

    public function postPedidoMn()
    {
        $jwt     = $this->input->get_request_header('x-token', true);
        $reqjson = json_decode($this->input->raw_input_stream, true);
        $payload = $this->validarjwt->verificarjwtlocal($jwt);

        if (!$payload) {
            $arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
            echo json_encode($arrayResult);
            die();
        }


        $tarea = trim(htmlentities($reqjson['tarea'], ENT_QUOTES));
        $ch    = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/validaPedidoMn/$tarea");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        $dataclick = json_decode($data, true);


        $numero_contacto = $payload->celular;
        $nombre_contacto = $payload->nombre;
        $cc_tecnico      = $payload->iduser;

        $observacion = $reqjson['observacion'];

        $macSale    = trim(htmlentities($reqjson['macSale'], ENT_QUOTES));
        $macEntra   = trim(htmlentities($reqjson['macEntra'], ENT_QUOTES));
        $accion     = $reqjson['accion'] ?? 'Infraestructura';
        $macSale    = str_replace('-', ',', $macSale);
        $macEntra   = str_replace('-', ',', $macEntra);
        $dataclick1 = $dataclick[0];
        $mesa       = '';

        if (stripos($tarea, 'sa') === 0) {
            $dataMn = $this->ModeloMesasNacionales->getMnByTask($tarea);
            if (!$dataMn) {
                $unepedido        = '';
                $tasktypecategory = '';
                $uneSourceSystem  = '';
                $region           = '';
                $area             = '';
                $mesa             = 'Mesa 1';

                $resMn = $this->ModeloMesasNacionales->postPedidoMn(
                    $nombre_contacto,
                    $numero_contacto,
                    $cc_tecnico,
                    $observacion,
                    $tarea,
                    $unepedido,
                    $tasktypecategory,
                    $uneSourceSystem,
                    $mesa,
                    $accion,
                    $region,
                    strtoupper($area));

                if ($resMn) {
                    $arrayResult = ['type' => 'success', 'message' => 'Se registro solicitud con éxito.'];
                } else {
                    $arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $resMn];
                }
            } else {
                $arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para esta tarea, valide e intenta nuevamente.'];
            }
            echo json_encode($arrayResult);
            die();
        } elseif ($dataclick1['Estado'] != 'En Sitio') {
            $arrayResult = ['type' => 'error', 'message' => 'La tarea ya no se encuentra en sitio'];
            echo json_encode($arrayResult);
            die();
        }

        if ($dataclick1['TaskType'] == 'Reparacion Infraestructura') {
            $mesa = 'Mesa 4';
        } elseif (($dataclick1['UneSourceSystem'] == 'EDA') && ($accion == 'Línea básica' || $accion == 'Cambio de equipo' || $accion == 'Cambio de puerto')) {
            $mesa = 'Mesa 2';
        } elseif (($dataclick1['UneSourceSystem'] == 'EDA' || $dataclick1['UneSourceSystem'] == 'ELT' || $dataclick1['UneSourceSystem'] == 'POE') && ($accion == 'Soporte general')) {
            $mesa = 'Mesa 1';
        } elseif (($dataclick1['UneSourceSystem'] == 'ELT' || $dataclick1['UneSourceSystem'] == 'POE') && ($accion == 'Código de completo' || $accion == 'Código de incompleto' || $accion == 'Validación de parámetros')) {
            $mesa = 'Mesa 3';
        }


        $unepedido        = $dataclick1['UNEPedido'];
        $tasktypecategory = $dataclick1['TaskTypeCategory'];
        $unemunicipio     = $dataclick1['UNEMunicipio'];
        $uneproductos     = $dataclick1['UNEProductos'];
        $engineer_id      = $dataclick1['EngineerID'];
        $engineer_name    = $dataclick1['EngineerName'];
        $mobile_phone     = $dataclick1['MobilePhone'];
        $estado_equipo    = $dataclick1['estado_equipo'];
        $uneSourceSystem  = $dataclick1['UneSourceSystem'];
        $region           = $dataclick1['region'];

        switch ($region) {
            case 'Antioquia Centro':
            case 'Antioquia Norte':
            case 'Antioquia Oriente':
            case 'Antioquia Sur':
            case 'Antioquia_Edatel':
            case 'Boyaca':
            case 'Norte de Santander':
            case 'Santander':
            case 'Boyaca_Edatel':
            case 'Santander_Edatel':
                $area = 'Andina';
                break;
            case 'Atlantico':
            case 'Bolivar':
            case 'Magdalena':
            case 'Cesar':
            case 'Cordoba':
            case 'Sucre':
            case 'Bolivar_Edatel':
            case 'Cesar_Edatel':
            case 'Cordoba_Edatel':
            case 'Guajira':
            case 'Sucre_Edatel':
                $area = 'Costa';
                break;
            case 'Cundinamarca':
            case 'Cundinamarca Sur':
            case 'Cundinamarca Norte':
            case 'Bogota':
                $area = 'Bogota';
                break;
            case 'Meta':
            case 'Valle':
            case 'Cauca':
            case 'Nariño':
            case 'Caldas':
            case 'Quindio':
            case 'Risaralda':
            case 'Valle Quindío':
            case 'Tolima':
                $area = 'Sur';
                break;
        }

        $dataMn = $this->ModeloMesasNacionales->getMnByTask($tarea);

        if (!$dataMn) {
            $resMn = $this->ModeloMesasNacionales->postPedidoMn(
                $nombre_contacto,
                $numero_contacto,
                $cc_tecnico,
                $observacion,
                $tarea,
                $unepedido,
                $tasktypecategory,
                $uneSourceSystem,
                $mesa,
                $accion,
                $region,
                $area);

            if ($resMn) {
                $arrayResult = ['type' => 'success', 'message' => 'Se registro solicitud con éxito.'];
            } else {
                $arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $resMn];
            }
        } else {
            $arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para esta tarea, valide e intenta nuevamente.'];
        }

        echo json_encode($arrayResult);
    }

    public function getsoporteMnbyuser()
    {


        $jwt = $this->input->get_request_header('x-token', true);

        $payload = $this->validarjwt->verificarjwtlocal($jwt);

        if (!$payload) {
            $arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
            echo json_encode($arrayResult);
            die();
        }

        $numero_contacto = $payload->celular;
        $nombre_contacto = $payload->nombre;
        $cc_tecnico      = $payload->iduser;
        //$cc_tecnico      = '1151966070';


        $datasoportegpon = $this->ModeloMesasNacionales->getsoporteMnpbyuser($cc_tecnico);

        //$datasoportegpon = 0;
        if ($datasoportegpon != 0) {
            $arrayResult = ['type' => 'success', 'message' => $datasoportegpon];
        } else {
            $arrayResult = ['type' => 'error', 'message' => 'No hay datos para listar'];
        }

        echo json_encode($arrayResult);

    }

}
