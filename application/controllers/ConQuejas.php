<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ConQuejas extends CI_Controller {
    function __construct() {
        parent::__construct();
	    $this->load->model('Modeloquejasgo');
        $this->load->library('nativesession');
        $this->load->library('validarjwt');
        $this->load->helper('url');
    }

    public function index()
    {
        echo 'Quejas';
    }

    public function getQuejas(){
        $jwt = $this->input->get_request_header('x-token', TRUE);
        $reqjson = json_decode($this->input->raw_input_stream, true);
        $pedido = trim(htmlentities($this->input->get('pedido'),ENT_QUOTES));

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
            $arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
            echo json_encode($arrayResult);
            die();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/HCHV_DEV/ConsultaQueja/$pedido");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $dataquejas = json_decode($data,true);

        if ($dataquejas != 0) {
            $arrayResult = array('type' => 'success', 'message' => array($dataquejas));
        } else {
            $arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
        }

        echo json_encode($arrayResult);
    }

    public function postquejago(){

        $jwt = $this->input->get_request_header('x-token', TRUE);
        $reqjson = json_decode($this->input->raw_input_stream, true);

        $data = json_decode(file_get_contents("php://input"),true);

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
            $arrayResult = array('type' => 'errorAuth', 'message' => 'Token no valido.');
            echo json_encode($arrayResult);
            die();
        }

        $validacion = $this->Modeloquejasgo->postsquejasgo($data);

        $arrayResult = NULL;
        if ($validacion == 1) {
            $arrayResult = array('type' => 'success', 'message' => 'Se registro solicitud con éxito.');
        }elseif($validacion == 0) {
            $arrayResult = array('type' => 'error', 'message' => 'Error al registrar solicitud.', 'error' => $validacion);
        }elseif ($validacion == 3){
            $arrayResult = array('type' => 'error', 'message' => 'Ya se encuentra en gestión una solicitud con el numero SS ingresado');
        }

        echo json_encode($arrayResult);
    }

    public function getquejasgobyuser()
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

        $datasoportegpon = $this->Modeloquejasgo->getquejasgobyuser($user_identification);


        if ($datasoportegpon != 0) {
            $arrayResult = array('type' => 'success', 'message' => $datasoportegpon);
        } else {
            $arrayResult = array('type' => 'error', 'message' => 'No hay datos para listar');
        }



        echo json_encode($arrayResult);

    }
}
