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

        $tarea = trim(htmlentities($this->input->get('tarea'), ENT_QUOTES));

        //$tarea   = '37801731';
        $payload = $this->validarjwt->verificarjwtlocal($jwt);

        if (!$payload) {
            $arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
            echo json_encode($arrayResult);
            die();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/BuscarCodInc/$tarea");
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

        $to_time   = strtotime(date('Y-m-d H:i:s'));
        $from_time = strtotime($datacodinc[0]['TimeCreated']);
        $minutes   = round(abs($to_time - $from_time) / 60, 0);

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

        $validacion = $this->Modelocodigoincompleto->validacionesContingecias();

        $valCodigoIncompleto = $validacion[3];

        if ($valCodigoIncompleto['valida'] == 'inactiva'){
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
                    $codigo
                );
            }
            $arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
            echo json_encode($arrayResult);
            die();
        }

        $mystringpend = ($datacodinc[0]['ticket'] != null) ? $datacodinc[0]['ticket'] : '';
        $findmepend   = '010';
        $pospend      = strpos($mystringpend, $findmepend);

        if ($pospend === 0) {

            if ($datacodinc[0]['Quantity'] == '81') {
                $codigo            = 'SARA ya te entregó una respuesta.';
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
                        $codigo
                    );
                }

                $arrayResult = array('type' => 'error', 'message' => 'SARA ya te entregó una respuesta.');
                echo json_encode($arrayResult);
                die();
            }

            $to_time   = strtotime(date('Y-m-d H:i:s'));
            $from_time = strtotime($datacodinc[0]['TimeCreated']);
            $minutes   = round(abs($to_time - $from_time) / 60, 0);

            if (($datacodinc[0]['Quantity'] == '70' || $datacodinc[0]['Quantity'] == '0') && $minutes <= 10) {

                $codigo            = 'Debes esperar 10 min, antes de realizar solicitud.';
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
                        $codigo
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
                    $codigo
                );
            }
            $arrayResult = array('type' => 'success', 'message' => $datacodinc[0]['UNEIncompletionAC']);
            echo json_encode($arrayResult);
            die();
        } else {
            $codigo            = 'Pendiente no es imputable al cliente.';
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
                    $codigo
                );
            }
            $arrayResult = array('type' => 'error', 'message' => 'Pendiente no es imputable al cliente.');
            echo json_encode($arrayResult);
            die();
        }
    }
}
