<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Etp extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('ModeloEtp');
        $this->load->model('ModeloMesasNacionales');
        $this->load->library('nativesession');
        $this->load->library('validarjwt');
        $this->load->helper('url');
    }

    public function index()
    {
        echo 'Etp';
    }

    public function validaPedidoETP()
    {

        $jwt = $this->input->get_request_header('x-token', true);
        $reqjson = json_decode($this->input->raw_input_stream, true);

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
            $arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
            echo json_encode($arrayResult);
            die();
        }


        $tarea = trim(htmlentities($_GET['tarea'], ENT_QUOTES));

        //echo $tarea;exit();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/tipoTecnologiaETP/$tarea");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);


        curl_close($ch);
        $dataclick = json_decode($data, true);
        $dataclick = (array)$dataclick;

        $validacion = $this->ModeloEtp->validacionesContingecias();
        $validaEstado = $validacion[10]['valida'];

        $dataclick = $dataclick[0];
        /*$arrayResult = ['type' => 'success', 'message' => var_dump($dataclick)];
        echo json_encode($arrayResult);
        die();*/


        /*if ($dataclick == 404) {
            $arrayResult = ['type' => 'error', 'message' => 'Pedido no existe.'];
        } elseif ($dataclick['sistema'] != 'ETP') {
            $arrayResult = ['type' => 'error', 'message' => 'Este modulo solo atiende solicitudes de ETP'];
        } else if (strrpos($dataclick['UNETecnologias'], 'GPON')) {
            $arrayResult = ['type' => 'success', 'message' => 'GPON'];
        } elseif (strrpos($dataclick['Infraestructura1'], 'ARPON') || strrpos($dataclick['Infraestructura1'], 'OLT')) {
            $arrayResult = ['type' => 'success', 'message' => 'GPON'];
        } elseif ($dataclick['TaskType'] == 'Cambio_Equipo DTH' || $dataclick['TaskType'] == 'Reparacion DTH') {
            $arrayResult = ['type' => 'success', 'message' => 'ETPLIGHT'];
        } elseif ($dataclick['TaskType'] == 'Precableado' || $dataclick['TaskType'] == 'Modificacion HFC' || $dataclick['TaskType'] == 'Cambio_Equipo HFC' || $dataclick['TaskType'] == 'Extension HFC') {
            $arrayResult = ['type' => 'success', 'message' => 'ETPMEDIO'];
        } else {
            $arrayResult = ['type' => 'success', 'message' => 'OTRO'];
        }*/

        if ($dataclick == 404) {
            $arrayResult = ['type' => 'error', 'message' => 'Pedido no existe.'];
            echo json_encode($arrayResult);
            die();
        }
        //$dataclick['Status'] != 'En Camino'
        if ($validaEstado == 'activa') {
            if ($dataclick['Status'] != 'En Sitio') {
                $arrayResult = ['type' => 'error', 'message' => 'La tarea no se encuentra marcada En Sitio'];
                echo json_encode($arrayResult);
                die();
            }
        }


        if ($dataclick['sistema'] != 'ETP') {
            $arrayResult = ['type' => 'error', 'message' => 'Este modulo solo atiende solicitudes de ETP'];
        } elseif (strrpos($dataclick['UNETecnologias'], 'GPON')) {
            $arrayResult = ['type' => 'success', 'message' => 'GPON'];
        } elseif (strrpos($dataclick['Infraestructura1'], 'ARPON') || strrpos($dataclick['Infraestructura1'], 'OLT')) {
            $arrayResult = ['type' => 'success', 'message' => 'GPON'];
        } elseif ($dataclick['TaskType'] == 'Cambio_Equipo DTH' || $dataclick['TaskType'] == 'Reparacion DTH') {
            $arrayResult = ['type' => 'success', 'message' => 'ETPLIGHT'];
        } elseif ($dataclick['TaskType'] == 'Precableado' || $dataclick['TaskType'] == 'Modificacion HFC' || $dataclick['TaskType'] == 'Cambio_Equipo HFC' || $dataclick['TaskType'] == 'Extension HFC') {
            $arrayResult = ['type' => 'success', 'message' => 'ETPMEDIO'];
        } else {
            $arrayResult = ['type' => 'success', 'message' => 'OTRO'];
        }

        echo json_encode($arrayResult);
        die();

    }

    public function postPedidoETP()
    {
        $fecha = date('Y-m-d');
        $fecha = $fecha . ' 00:00:00';
        $jwt = $this->input->get_request_header('x-token', true);
        $reqjson = json_decode($this->input->raw_input_stream, true);
        $payload = $this->validarjwt->verificarjwtlocal($jwt);

        if (!$payload) {
            $arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
            echo json_encode($arrayResult);
            die();
        }

        $tarea = trim(htmlentities($reqjson['tarea'], ENT_QUOTES));
        //$validacion['tarea'] = $tarea;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/postETP/$tarea");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        $dataclick = json_decode($data, true);
        $dataclick = (array)$dataclick;
        $accion = $reqjson['accion'];
        $arpon = trim(htmlentities($reqjson['arpon'], ENT_QUOTES));
        $nap = trim(htmlentities($reqjson['nap'], ENT_QUOTES));
        $hilo = trim(htmlentities($reqjson['hilo'], ENT_QUOTES));
        $replanteo = trim(htmlentities($reqjson['replanteo'], ENT_QUOTES));
        //$accion    = trim(htmlentities($reqjson['accion'], ENT_QUOTES));


        if ($accion == 'Replanteo') {
            $replanteo = 'SI';
        } else {
            $replanteo = 'NO';
        }
        $superV = 0;
        if (!empty($arpon) && !empty($nap) && $replanteo == 'SI') {
            $superV = 1;
        }

        $tecnico_cc_solicita = $payload->iduser;
        $numero_contacto = $payload->celular;
        $nombre_contacto = $payload->nombre;
        $observacion = $reqjson['observacion'];

        $accionesValidas = array(
            'Requiere escalera (Realizar acometida)',
            'No corresp. a precableado o extensión',
            'Ubicar Usuario',
            'Actividad requiere escalera',
            'Actividad requiere herramientas',
            'Actividad requiere Materiales',
            'No corresponde a cambio de equipo',
            'Cambio de distrito',
            'Nivelar ruta lejana'
        );

        if (in_array($accion, $accionesValidas)) {
            $validacion = $this->ModeloMesasNacionales->validacionesContingecias();
            $validaEstado = $validacion[10]['valida'];
            $mesa = 'Geco';
            $ata = $reqjson['ata'] ?? 'NO';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/validaPedidoMn/$tarea");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);

            $dataclick = json_decode($data, true);
            $dataclick1 = $dataclick[0];

            if (!$dataclick1) {
                $arrayResult = ['type' => 'error', 'message' => 'La tarea no existe. validar tarea e intentar nuevamente.'];
                echo json_encode($arrayResult);
                die();
            }

            if ($validaEstado == 'activa') {
                if ($dataclick1['Estado'] != 'En Sitio') {
                    $arrayResult = ['type' => 'error', 'message' => 'La tarea no se encuentra en sitio'];
                    echo json_encode($arrayResult);
                    die();
                }
            }


            if ($dataclick1['TaskType'] == 'Precableado' || $dataclick1['TaskType'] == 'Extension HFC' || $dataclick1['TaskType'] == 'Modificacion HFC' || $dataclick1['TaskType'] == 'Cambio_Equipo HFC') {
                $tipoSolicitud = 'Medio';
            } else {
                $tipoSolicitud = 'Light';
            }

            $unepedido = $dataclick1['UNEPedido'];
            $tasktypecategory = $dataclick1['TaskTypeCategory'];
            $uneSourceSystem = $dataclick1['UneSourceSystem'];
            $UNETecnologias = $dataclick1['UNETecnologias'];
            $region = $dataclick1['region'];
	        $calendario = $dataclick1['calendario'];
	        $microzona = $dataclick1['microzona'];
			$TaskType = $dataclick1['TaskType'];
            $patron = '/GPON/';
            if (preg_match_all($patron, $UNETecnologias)) {
                $UNETecnologias = 'GPON';
            }

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
                    $tecnico_cc_solicita,
                    $observacion,
                    $tarea,
                    $unepedido,
                    $tasktypecategory,
                    $uneSourceSystem,
                    $mesa,
                    $accion,
                    $region,
                    $area,
                    $ata,
                    $UNETecnologias,
                    $tipoSolicitud,
	                $calendario,
	                $microzona,
	                $TaskType);

                if ($resMn) {
                    $arrayResult = ['type' => 'success', 'message' => 'Se registro su solicitud con éxito. ver la respuesta en mesas nacionales.'];
                } else {
                    $arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $resMn];
                }
            } else {
                $arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para esta tarea, valide e intenta nuevamente.'];
            }

            echo json_encode($arrayResult);
            die();
        }

        $validacion = $this->ModeloEtp->validacionesContingecias();

        $valEquipo = $validacion[6]['valida'];
        $valInfra = $validacion[7]['valida'];
        $validaEstado = $validacion[10]['valida'];

        if ($dataclick[0] == 500) {
            $arrayResult = ['type' => 'error', 'message' => 'Tarea no existe en click, valida por favor mas tarde.'];
            echo json_encode($arrayResult);
            die();
        }

        if ($validaEstado == 'activa') {
            if ($dataclick[0]['Status'] != 'En Sitio') {
                $arrayResult = ['type' => 'error', 'message' => 'La tarea no se encuentra en sitio'];
                echo json_encode($arrayResult);
                die();
            }
        }

        if ($accion == 'Entrega de códigos' || $accion == 'Replanteo') {

        } elseif ($dataclick[0]['TaskTypeCategory'] == 'Aprovisionamiento' || $dataclick[0]['TaskTypeCategory'] == 'Aprovisionamiento BSC') {

            if (!$superV) {
                if (str_contains($dataclick[0]['TaskType'], 'Cambio_Tecnologia')) {

                } elseif (str_contains($dataclick[0]['TaskType'], 'Cambio')) {

                    foreach ($dataclick as $data) {
                        if ($data['Serialreal'] != null || $data['Serialreal2'] != null || $data['MACReal'] != null || $data['MACReal2'] != null || $data['Serialreal'] != 'NO_INSTALADO' || $data['Serialreal2'] != 'NO_INSTALADO' || $data['MACReal'] != 'NO_INSTALADO' || $data['MACReal2'] != 'NO_INSTALADO') {
                            $band += 1;
                        }

                        if ($data['RTA3'] != 'NA' || $data['RTA3'] != null) {
                            $rta3 += 1;
                        }
                    }
                    if ($valEquipo == 'activa') {
                        if ($band < 1) {
                            $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene equipos asignados.'];
                            echo json_encode($arrayResult);
                            die();
                        }


                        if ($rta3 < 1) {
                            $arrayResult = ['type' => 'error', 'message' => 'No se ha intentado activar equipos.'];
                            echo json_encode($arrayResult);
                            die();
                        }
                    }

                    if (str_contains($dataclick[0]['EQProducto'], 'Internet') || str_contains($dataclick[0]['EQProducto'], 'INTERNET')) {

                        if ($dataclick[0]['estado_equipo'] == 'Reemplazado') {
                            foreach ($dataclick as $data) {
                                if ($data['Serialreal'] != null || $data['Serialreal2'] != null || $data['MACReal'] != null || $data['MACReal2'] != null) {
                                    $band += 1;
                                }

                                if ($data['RTA3'] != 'NA' || $data['RTA3'] != null) {
                                    $rta3 += 1;
                                }
                            }
                            if ($valEquipo == 'activa') {
                                if ($band < 1) {
                                    $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene equipos asignados.'];
                                    echo json_encode($arrayResult);
                                    die();
                                }

                                if ($rta3 < 1) {
                                    $arrayResult = ['type' => 'error', 'message' => 'No se ha intentado activar equipos.'];
                                    echo json_encode($arrayResult);
                                    die();
                                }
                            }

                            if (str_contains($dataclick[0]['EQProducto'], 'Internet') || str_contains($dataclick[0]['EQProducto'], 'INTERNET')) {
                                foreach ($dataclick as $data) {
                                    if ($data['UNEPassword'] != null || $data['SSID'] != null) {
                                        $validaPass += 1;
                                    }
                                }
                                if ($valInfra == 'activa') {
                                    if ($validaPass < 1) {
                                        $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene SSID o clave wifi asignada.'];
                                        echo json_encode($arrayResult);
                                        die();
                                    }
                                }
                            } else {
                                $validaPass = 1;
                            }

                            if ($valInfra == 'activa') {
                                if ($validaPass < 1) {
                                    $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene SSID o clave wifi asignada.'];
                                    echo json_encode($arrayResult);
                                    die();
                                }
                            }
                        } elseif ($dataclick[0]['estado_equipo'] == 'Reparado' || $dataclick[0]['estado_equipo'] == null) {
                            //$validaPass = 1;
                        }
                    }


                } else {

                    if ($dataclick[0]['MAC'] == null || $dataclick[0]['SerialNo'] == null || $dataclick[0]['SerialNo'] == 'NO_INSTALADO' || $dataclick[0]['MAC'] == 'NO_INSTALADO') {
                        if ($valEquipo == 'activa') {
                            $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene equipos asignados.'];
                            echo json_encode($arrayResult);
                            die();
                        }
                    }
                    $rta = 0;
                    foreach ($dataclick as $data) {
                        if ($data['RTA'] != 'NA' || $data['RTA'] != null) {
                            $rta += 1;
                        }
                    }
                    if ($valEquipo == 'activa') {
                        if ($rta < 1) {
                            $arrayResult = ['type' => 'error', 'message' => 'No se ha intentado activar equipos.'];
                            echo json_encode($arrayResult);
                            die();
                        }
                    }

                    if (str_contains($dataclick[0]['EQProducto'], 'Internet') || str_contains($dataclick[0]['EQProducto'], 'INTERNET')) {
                        foreach ($dataclick as $data) {
                            if ($data['UNEPassword'] != null || $data['SSID'] != null) {
                                $validaPass += 1;
                            }
                        }
                    } else {
                        $validaPass = 1;
                    }

                    if ($valInfra == 'activa') {
                        if ($validaPass < 1) {
                            $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene SSID o clave wifi asignada.'];
                            echo json_encode($arrayResult);
                            die();
                        }
                    }
                }
            }

        } elseif ($dataclick[0]['TaskTypeCategory'] == 'Aseguramiento') {
            $band = 0;
            $rta3 = 0;

            if ($dataclick[0]['estado_equipo'] == 'Reemplazado') {
                foreach ($dataclick as $data) {
                    if ($data['Serialreal'] != null || $data['Serialreal2'] != null || $data['MACReal'] != null || $data['MACReal2'] != null) {
                        $band += 1;
                    }

                    if ($data['RTA3'] != 'NA' || $data['RTA3'] != null) {
                        $rta3 += 1;
                    }
                }
                if ($band < 1) {
                    $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene equipos asignados.'];
                    echo json_encode($arrayResult);
                    die();
                }

                if ($rta3 < 1) {
                    $arrayResult = ['type' => 'error', 'message' => 'No se ha intentado activar equipos.'];
                    echo json_encode($arrayResult);
                    die();
                }

                if (str_contains($dataclick[0]['EQProducto'], 'Internet') || str_contains($dataclick[0]['EQProducto'], 'INTERNET')) {
                    foreach ($dataclick as $data) {
                        if ($data['UNEPassword'] != null || $data['SSID'] != null) {
                            $validaPass += 1;
                        }
                    }
                } else {
                    $validaPass = 1;
                }


                if ($validaPass < 1) {
                    $arrayResult = ['type' => 'error', 'message' => 'La tarea no tiene SSID o clave wifi asignada.'];
                    echo json_encode($arrayResult);
                    die();
                }
            } elseif ($dataclick[0]['estado_equipo'] == 'Reparado' || $dataclick[0]['estado_equipo'] == null) {

            }
        }


        $internet_port1 = trim(htmlentities($reqjson['internet_port1'], ENT_QUOTES));
        $internet_port2 = trim(htmlentities($reqjson['internet_port2'], ENT_QUOTES));
        $internet_port3 = trim(htmlentities($reqjson['internet_port3'], ENT_QUOTES));
        $internet_port4 = trim(htmlentities($reqjson['internet_port4'], ENT_QUOTES));
        $tv_port1 = trim(htmlentities($reqjson['tv_port1'], ENT_QUOTES));
        $tv_port2 = trim(htmlentities($reqjson['tv_port2'], ENT_QUOTES));
        $tv_port3 = trim(htmlentities($reqjson['tv_port3'], ENT_QUOTES));
        $tv_port4 = trim(htmlentities($reqjson['tv_port4'], ENT_QUOTES));

        $macSale = trim(htmlentities($reqjson['macSale'], ENT_QUOTES));
        $macEntra = trim(htmlentities($reqjson['macEntra'], ENT_QUOTES));
        $macSale = str_replace('-', ',', $macSale);
        $macEntra = str_replace('-', ',', $macEntra);


        $dataclick1 = $dataclick[0];
        $tecnico_login_solicita = $payload->login;

        $unepedido = $dataclick1['UNEPedido'];
        $tasktypecategory = $dataclick1['TaskTypeCategory'];
        $unemunicipio = $dataclick1['UNEMunicipio'];
        $uneproductos = $dataclick1['UNEProductos'];
        $engineer_id = $dataclick1['EngineerID'];
        $engineer_name = $dataclick1['EngineerName'];
        $mobile_phone = $dataclick1['MobilePhone'];
        $tipo = $dataclick1['TipoEquipo'];
        $SSID = $dataclick1['UNEPassword'];
        $RTA = $dataclick1['RTA'];
        $RTA2 = $dataclick1['RTA2'];
        $RTA3 = $dataclick1['RTA3'];
        $estado_equipo = $dataclick1['estado_equipo'];
        //$estado_equipo    = '';
        $uneSourceSystem = $dataclick1['UneSourceSystem'];
        $UNETecnologias = $dataclick1['UNETecnologias'];

        $serials = implode(',', array_unique(array_column($dataclick, 'SerialNo')));
        $macs = implode(',', array_unique(array_column($dataclick, 'MAC')));
        $SerialNoReal = implode(',', array_unique(array_column($dataclick, 'SerialNoReal')));
        $MACReal = implode(',', array_unique(array_column($dataclick, 'MACReal')));
        $SerialNoReal2 = implode(',', array_unique(array_column($dataclick, 'SerialNoReal2')));
        $MACReal2 = implode(',', array_unique(array_column($dataclick, 'MACReal2')));

        $dataEtp = $this->ModeloEtp->getEtpByTask($tarea, $fecha);

        $arrayResult = null;

        if (!$dataEtp) {
            $resEtp = $this->ModeloEtp->postregistroETP(
                $tarea,
                $arpon,
                $nap,
                $hilo,
                $internet_port1,
                $internet_port2,
                $internet_port3,
                $internet_port4,
                $tv_port1,
                $tv_port2,
                $tv_port3,
                $tv_port4,
                $numero_contacto,
                $nombre_contacto,
                $observacion,
                $unepedido,
                $tasktypecategory,
                $unemunicipio,
                $uneproductos,
                $engineer_id,
                $engineer_name,
                $mobile_phone,
                $serials,
                $macs,
                $tipo,
                $SSID,
                $RTA,
                $RTA2,
                $RTA3,
                $estado_equipo,
                $SerialNoReal,
                $SerialNoReal2,
                $MACReal,
                $MACReal2,
                $replanteo,
                $uneSourceSystem,
                $tecnico_cc_solicita,
                $tecnico_login_solicita,
                $accion,
                $macSale,
                $macEntra,
                $UNETecnologias);

            if ($resEtp == 1) {
                $arrayResult = ['type' => 'success', 'message' => 'Se registro solicitud con exito.'];
            } else {
                $arrayResult = ['type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $resEtp];
            }
        } else {
            $arrayResult = ['type' => 'error', 'message' => 'Ya se encuentra una solicitud en proceso para esta tarea, valide e intenta nuevamente.'];
        }

        echo json_encode($arrayResult);
    }

    public function getsoporteetpbyuser()
    {


        $jwt = $this->input->get_request_header('x-token', true);

        $payload = $this->validarjwt->verificarjwtlocal($jwt);

        if (!$payload) {
            $arrayResult = ['type' => 'errorAuth', 'message' => 'Token no valido.'];
            echo json_encode($arrayResult);
            die();
        }

        $user_id = $payload->login;
        $user_identification = $payload->iduser;


        $datasoportegpon = $this->ModeloEtp->getsoporteetpbyuser($user_id);

        if ($datasoportegpon != 0) {
            $arrayResult = ['type' => 'success', 'message' => $datasoportegpon];
        } else {
            $arrayResult = ['type' => 'error', 'message' => 'No hay datos para listar'];
        }

        echo json_encode($arrayResult);

    }

}
