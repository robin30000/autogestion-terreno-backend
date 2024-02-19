<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Concontingencia extends CI_Controller
{

    function __construct()
    {
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

        //echo 200;exit();
        $fecha = date('Y-m-d');
        $fecha = $fecha . ' 00:00:00';

        $jwt     = $this->input->get_request_header('x-token', true);
        $reqjson = json_decode($this->input->raw_input_stream, true);


        $payload    = $this->validarjwt->verificarjwtlocal($jwt);

        if (!$payload) {
            $arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
            echo json_encode($arrayResult);
            die();
        }

        $hora_actual = date('H:i');

        $hora_inicio = '07:00';
        $hora_fin    = '19:00';

        if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
            $arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 7pm'];
            echo json_encode($arrayResult);
            die();
        }

        $pedido           = trim(htmlentities($reqjson['pedido'], ENT_QUOTES));
        $tipoproducto     = trim(htmlentities($reqjson['tipoproducto'], ENT_QUOTES));
        $tipocontingencia = trim(htmlentities($reqjson['tipocontingencia'], ENT_QUOTES));
        $observacion      = $reqjson['observacion'];
        $macentra         = strtoupper(trim(htmlentities($reqjson['macentra'], ENT_QUOTES)));
        $macsale          = '';

	    $user_id             = $payload->login;
	    $request_id          = null;
	    $user_identification = $payload->iduser;
	    $fecha_solicitud     = date('Y-m-d H:i:s');

        $validacion = $this->Modelocontingencia->validacionesContingecias();
        $click = $validacion[9]['valida'];

        if (stripos($pedido, 'sa') !== false || stripos($pedido, 'w') !== false || $click === 'inactiva') {

            $datasoportegpon = $this->Modelocontingencia->getcontingenciabypedido($pedido, $tipoproducto, $fecha);
            if ($datasoportegpon == 0) {
                if ($tipocontingencia == 'Cambio de Equipo') {
                    $macsale = strtoupper(trim(htmlentities($reqjson['macsale'], ENT_QUOTES)));
                }
                $uNEMunicipio    = '';
                $correo          = '';
                $motivo          = '';
                $paquetes        = '';
                $TaskType        = '';
                $remite     = 'Terreno';
                $uNETecnologias  = '';
                $tipoEquipo      = '';
                $uNEUENcalculada = '';
                $uNEProvisioner  = '';
                $perfil          = '';
                $engestion  = '0';
                $grupo = ($tipoproducto == 'Internet' || $tipoproducto == 'ToIP' || $tipoproducto == 'Internet+Toip') ? 'INTER' : 'TV';
                $tAREA_ID            = $pedido;
                $sistema             = 'POE-SA';
                $typeTask = '';

                $ressoportegpon = $this->Modelocontingencia->postcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes,
                    $pedido,
                    $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification,
                    $fecha_solicitud, $engestion, $tAREA_ID, $sistema, $typeTask);

                if ($ressoportegpon == 1 || $ressoportegpon == 0) {
                    $arrayResult = ['type' => 'success', 'message' => 'Se registro solicitud con éxito.'];
                } else {
                    $arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $ressoportegpon];
                }

            } else {
                $arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para este pedido, valide e intenta nuevamente.'];
            }

            echo json_encode($arrayResult);
            die();
        }

        if ($tipocontingencia == 'Cambio de Equipo') {
            $macsale                    = strtoupper(trim(htmlentities($reqjson['macsale'], ENT_QUOTES)));
            $validacion['pedido2']      = $pedido;
            $validacion['tipoProducto'] = $tipoproducto;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/CambioEquipo/$validacion");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($validacion));
            $data = curl_exec($ch);
            curl_close($ch);
            $dataclick1 = json_decode($data, true);
        } elseif ($tipocontingencia == 'Forzar Cable Modem') {
            $validacion['pedido1'] = $pedido;
            $ch                    = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/CableModem/$validacion");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($validacion));
            $data = curl_exec($ch);
            curl_close($ch);
            $dataclick1 = json_decode($data, true);
        } else {
            $validacion['pedido'] = $pedido;
            $ch                   = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarB/$validacion");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($validacion));
            $data = curl_exec($ch);
            curl_close($ch);
            $dataclick1 = json_decode($data, true);
        }

        $dataclick = (array)$dataclick1;


        if (count($dataclick) == 0) {
            $arrayResult = array('type' => 'error', 'message' => 'Validar pedido e intentar nuevamente en unos minutos.');
            echo json_encode($arrayResult);
            die();
        }

        if (count($dataclick) > 0) {
            $dataclick = $dataclick[0];

            if (($dataclick['TaskTypeCategory'] == 'Aseguramiento') && (strpos($dataclick['typeTask'], 'Bronce') !== false)) {
                $arrayResult = array('type' => 'error', 'message' => 'Es una tarea de BSC, Debes escalar por el modulo mesas nacionales');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 3587) {
                $arrayResult = array('type' => 'error', 'message' => 'Es una tarea de BSC, Debes escalar por el modulo mesas nacionales');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 1587) {
                $arrayResult = array('type' => 'error', 'message' => 'Se debe escalar por la mesa GPON');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 3001) {
                $arrayResult = array('type' => 'error', 'message' => 'Las reparaciones se deben tramitar por despacho.');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 1510) {
                $arrayResult = array('type' => 'error', 'message' => 'Esta solicitud se debe tramitar a través del modulo ETP');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 3200) {
                $arrayResult = array('type' => 'error', 'message' => 'Antes de solicitar contingencias debes solicitar aprovisionamiento por click.');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 3201) {
                $arrayResult = array('type' => 'error', 'message' => 'Antes de solicitar este cambio debes intentarlo desde Click.');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 2230) {
                $arrayResult = array('type' => 'error', 'message' => 'Política de equipo errada, use los equipos correctos.');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 3300) {
                $arrayResult = array('type' => 'error', 'message' => 'Antes de solicitar contingencias debes utilizar SARA.');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 401) {
                $arrayResult = array('type' => 'error', 'message' => ' Ya se ejecuto un forzado de cable Modem.');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 403) {
                $arrayResult = array('type' => 'error', 'message' => 'El cable modem no requiere ser forzado.');
                echo json_encode($arrayResult);
                die();
            }
            if ($dataclick === 404) {
                $arrayResult = array('type' => 'error', 'message' => 'La solicitud no cumple con las condiciones mínimas. Validar con despacho');
                echo json_encode($arrayResult);
                die();
            }
            if ($dataclick === 405) {
                $arrayResult = array('type' => 'error', 'message' => 'Ya existe una solicitud pendiente.');
                echo json_encode($arrayResult);
                die();
            }
            if ($dataclick === 406) {
                $arrayResult = array('type' => 'error', 'message' => 'La solicitud esta en tramite en el bot de SARA');
                echo json_encode($arrayResult);
                die();
            }

            if ($dataclick === 407) {
                $arrayResult = array('type' => 'error', 'message' => 'forzar cablemodem es solo para POE');
                echo json_encode($arrayResult);
                die();
            }


            /*   if ($tipocontingencia == 'Forzar Cable Modem') {
                   $validate = 0;
                   if ($dataclick['TaskType'] == 'Instalación') {
                       $validate = 1;
                   }elseif ($dataclick['TaskType'] == 'Reparacion'){
                       $validate = 1;
                   }

                   if (!$validate) {
                       $arrayResult = array('type' => 'error', 'message' => 'Este pedido se debe tramitar por despacho.');
                       echo json_encode($arrayResult);
                       die();
                   }
               } else {

                   if ($dataclick['TaskType'] != 'Instalación') {
                       $arrayResult = array('type' => 'error', 'message' => 'Las reparaciones se deben tramitar por despacho.');
                       echo json_encode($arrayResult);
                       die();
                   }
               }*/

            if ($dataclick['Estado'] != 'En Sitio') {
                $arrayResult = array('type' => 'error', 'message' => 'El pedido debe estar En Sitio.');
                echo json_encode($arrayResult);
                die();
            }

            /* if ($dataclick['SitemaOrigen'] === 'ETP') {
                 $arrayResult = array('type' => 'error', 'message' => 'Las contingencias de ETP deben ser solicitadas a despacho.');
                 echo json_encode($arrayResult);
                 die();
             } */
        }
        //si diferente de pedido solicitud entrante en el boot de sara


        $engineer_Type         = $dataclick['engineer_Type'];
        $engineerID            = $dataclick['engineerID'];
        $engineerName          = $dataclick['engineerName'];
        $pEDIDO_UNE            = $dataclick['pEDIDO_UNE'];
        $tAREA_ID              = $dataclick['tAREA_ID'];
        $sistema               = $dataclick['SitemaOrigen'];
        $tASK_ID               = $dataclick['tASK_ID'];
        $uNEActividades        = $dataclick['uNEActividades'];
        $uNEBarrio             = $dataclick['uNEBarrio'];
        $uNECelularContacto    = $dataclick['uNECelularContacto'];
        $uNEDepartamento       = $dataclick['uNEDepartamento'];
        $uNEDireccion          = $dataclick['uNEDireccion'];
        $uNEDireccionComentada = $dataclick['uNEDireccionComentada'];
        $uNEFechaCita          = $dataclick['uNEFechaCita'];
        $uNEHoraCita           = $dataclick['uNEHoraCita'];
        $uNEFechaIngreso       = $dataclick['uNEFechaIngreso'];
        $uNEIdCliente          = $dataclick['uNEIdCliente'];
        $uNEMunicipio          = $dataclick['uNEMunicipio'];
        $uNENombreCliente      = $dataclick['uNENombreCliente'];
        $uNENombreContacto     = $dataclick['uNENombreContacto'];
        $uNEPedido             = $dataclick['uNEPedido'];

        $uNEProductos    = $dataclick['uNEProductos'];
        $uNEProvisioner  = $dataclick['uNEProvisioner'];
        $uNETecnologias  = $dataclick['uNETecnologias'];
        $uNEUENcalculada = $dataclick['uNEUENcalculada'];
        $uNERutaTrabajo  = $dataclick['uNERutaTrabajo'];
        $uNEUen          = $dataclick['uNEUen'];
        $TaskType        = $dataclick['TaskType'];
        $DispatchDate    = $dataclick['DispatchDate'];
        $Description     = $dataclick['Description'];
        $LaborType       = $dataclick['LaborType'];
        $Type            = $dataclick['Type'];
        $MAC             = $dataclick['MAC'];
        $RTA             = $dataclick['RTA'];
        $RTA3            = $dataclick['RTA3'];
        $LoginName       = $dataclick['LoginName'];
        $Estado          = $dataclick['Estado'];
        $typeTask = $dataclick['typeTask'];

        /*if (stripos($typeTask, 'NUEVO') !== false) {
            $typeTask = 'Nuevo';
        } elseif (stripos($typeTask, 'REPARACION') !== false) {
            $typeTask = 'Reparación';
        } else {
            $typeTask = 'Upgrade';
        }*/

        $correo     = '';
        $motivo     = '';
        $paquetes   = '';
        $tipoEquipo = '';
        $remite     = 'Terreno';
        $perfil     = '';
        $engestion  = '0';

        $grupo = ($tipoproducto == 'Internet' || $tipoproducto == 'ToIP' || $tipoproducto == 'Internet+Toip') ? 'INTER' : 'TV';

        $datasoportegpon = $this->Modelocontingencia->getcontingenciabypedido($pedido, $tipoproducto, $fecha);

        $arrayResult = null;
        if ($datasoportegpon == 0) {
            // INSERT

            $ressoportegpon = $this->Modelocontingencia->postcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes, $pEDIDO_UNE,
                $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification,
                $fecha_solicitud, $engestion, $tAREA_ID, $sistema, $typeTask);

            if ($ressoportegpon == 1 || $ressoportegpon == 0) {
                $arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con éxito.');
            } else {
                $arrayResult = array('type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $ressoportegpon);
            }

        } else {
            // UPDATE

            /* $ressoportegpon = $this->Modelocontingencia->putcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes, $pedido, $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification, $fecha_solicitud, $engestion, $datasoportegpon['id']);

            if ($ressoportegpon == 1 || $ressoportegpon == 0) {
                $arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con exito.');
            } else {
                $arrayResult = array('type' => 'error', 'message' => 'Error al registrar solcitud.', 'error' => $ressoportegpon);
            } */

            $arrayResult = array('type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para este pedido, valide e intenta nuevamente.');
        }

        // TODO: Notificacion push firebase

        echo json_encode($arrayResult);

    }

    public function getcontingenciabyuser()
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

        $datasoportegpon = $this->Modelocontingencia->getcontingenciabyuser($user_id, $fecha);

        if ($datasoportegpon != 0) {
            $arrayResult = array('type' => 'success', 'message' => $datasoportegpon);
        } else {
            $arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
        }

        echo json_encode($arrayResult);

    }

    public function gettareagpon()
    {

        $jwt = $this->input->get_request_header('x-token', true);

        $tarea = trim(htmlentities($this->input->get('tarea'), ENT_QUOTES));

        $payload = $this->validarjwt->verificarjwtlocal($jwt);

        if (!$payload) {
            $arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
            echo json_encode($arrayResult);
            die();
        }

        $datasoportegpon = $this->Modelocontingencia->getdatosgpon($tarea);

        if ($datasoportegpon != 0) {
            $arrayResult = array('type' => 'success', 'message' => $datasoportegpon);
        } else {
            $arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
        }

        echo json_encode($arrayResult);
    }


}
