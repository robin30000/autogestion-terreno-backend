<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once 'connectionGestion.php';
error_reporting(0);
ini_set('display_errors', 0);

//require_once('/var/www/html/autogestionterreno/application/src/JWT.php');
//require_once('/var/www/html/autogestionterreno/application/src/Key.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Autenticacion extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Modeloautenticacion');
        $this->load->library('nativesession');
        $this->load->helper('url');
        $this->load->library('validarjwt');
    }

    public function index()
    {
        $this->load->view('login');
    }

    public function autenticar()
    {
        $reqjson = json_decode($this->input->raw_input_stream, true);
        //$version = '';
        $username    = htmlentities($reqjson['user'], ENT_QUOTES);
        $password    = md5(htmlentities($reqjson['password'], ENT_QUOTES));
        $version     = htmlentities($reqjson['version'], ENT_QUOTES);
        $hora_actual = date('H:i');

        $hora_inicio = '07:00';
        $hora_fin    = '19:00';

        if ($hora_actual <= $hora_inicio || $hora_actual >= $hora_fin) {
            $arrayResult = ['type' => 'error', 'message' => 'El horario de operación es de 7am a 7pm'];
        } elseif (empty($username) || empty($password)) {
            $arrayResult = ['type' => 'error', 'message' => 'No pueden haber campos vacíos, ingrese usuario y clave.'];
        } elseif (!$version || $version != '23') {
            $arrayResult = ['type' => 'error', 'message' => 'Comunicate con un administrador para obtener la version actualizada de la aplicación.'];
        } else {
            $valUserQuery = $this->Modeloautenticacion->consultauser($username, $password);

            if ($valUserQuery == 1) {
                $arrayResult = ['type' => 'error', 'message' => 'Usuario no existe.'];
            } elseif ($valUserQuery == 2) {
                $arrayResult = ['type' => 'error', 'message' => 'Clave incorrecta.'];
            } elseif ($valUserQuery == 3) {
                $arrayResult = ['type' => 'error', 'message' => 'Usuario se encuentra inactivo, comuníquese con el administrador.'];
            } else {

                /*$pdo = new Connection();
                $cc  = $valUserQuery['identificacion'];

                $query = "SELECT top 1 CELULAR, NOMBRE_FUNCIONARIO from DimWorking WHERE COD_FUNCIONARIO = :cc ORDER BY FECHA_CARGA DESC";
                $stmt  = $pdo->prepare($query);
                $stmt->bindParam(':cc', $cc, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $celular = isset($result['CELULAR']) ? $result['CELULAR'] : '';
                $nombre  = isset($result['NOMBRE_FUNCIONARIO']) ? $result['NOMBRE_FUNCIONARIO'] : '';*/

                $valUserQuery['password'] = '';
                $valUserQuery['logueado'] = 'SI';
                $key                      = SIGNATURE_JWT;
                $payload                  = [
                    'iss'     => 'http://200.13.250.190/autogestionterreno',
                    'sub'     => 'autogestionterreno',
                    'exp'     => time() + 1296000,
                    'iat'     => strtotime(date('Y-m-d H:i:s')),
                    'iduser'  => $valUserQuery['identificacion'],
                    'login'   => $valUserQuery['login_click'],
                    'celular' => $valUserQuery['celular'],
                    'nombre'  => $valUserQuery['nombre'],
                    'perfil' => $valUserQuery['perfil'],
                    /*'celular' => $celular,
                    'nombre'  => $nombre,*/
                ];

                /**
                 * IMPORTANT:
                 * You must specify supported algorithms for your application. See
                 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
                 * for a list of spec-compliant algorithms.
                 */
                $jwt = JWT::encode($payload, $key, 'HS256');

                /* $this->nativesession->set('id', $valUserQuery['id']);
                $this->nativesession->set('identificacion', $valUserQuery['identificacion']);
                $this->nativesession->set('nombre', $valUserQuery['nombre']);
                $this->nativesession->set('empresa', $valUserQuery['empresa']);
                $this->nativesession->set('ciudad', $valUserQuery['ciudad']);
                $this->nativesession->set('celular', $valUserQuery['celular']);
                $this->nativesession->set('contrato', $valUserQuery['contrato']);
                $this->nativesession->set('region', $valUserQuery['region']);
                $this->nativesession->set('login_click', $valUserQuery['login_click']);
                $this->nativesession->set('estado', $valUserQuery['Usuaestadorio']);
                $this->nativesession->set('Logueado', 'SI'); */

                $menu = $this->menuapp1($valUserQuery['perfil']);
                $valUserQuery['menu'] = $menu;

                $cc  = $valUserQuery['identificacion'];
                $val = $this->Modeloautenticacion->validaEncuesta($cc);

                if ($val) {
                    $valUserQuery['alert'] = 'no';
                } else {
                    $valUserQuery['alert'] = 'si';
                }

                $superV = $this->Modeloautenticacion->validacionesContingecias();

                if ($superV[4]['valida'] == 'inactiva') {
                    $valUserQuery['alert'] = 'no';
                }

                $arrayResult = ['type' => 'success', 'message' => $valUserQuery, 'token' => $jwt];
            }
        }
        echo json_encode($arrayResult);
    }

    public function verificarjwt()
    {
        try {

            $jwt     = $this->input->get_request_header('x-token', true);
            $decoded = JWT::decode($jwt, new Key(SIGNATURE_JWT, 'HS256'));

            $arrayResult = ['type' => 'success', 'message' => 'OK'];
            echo json_encode($arrayResult);

        } catch (\Exception $e) {

            $arrayResult = ['type' => 'error', 'message' => 'Token no valido.'];
            echo json_encode($arrayResult);

        }
    }

    public function cambioclave()
    {

        $user = $this->nativesession->get('Usuario');

        $oldPass   = htmlentities(trim($this->input->post('oldPass')));
        $newPass   = htmlentities(trim($this->input->post('newPass')));
        $renewPass = htmlentities(trim($this->input->post('renewPass')));

        $oldPass = md5($oldPass);

        $valOldPass = $this->Modeloautenticacion->ValidarOldPassword($oldPass, $user);
        if ($valOldPass == 0) {
            $arrayResult = ['type' => 'error', 'message' => 'Clave anterior incorrecta, vuelva a intentar'];
            echo json_encode($arrayResult);
            die();
        }

        if ($newPass != $renewPass) {
            $arrayResult = ['type' => 'error', 'message' => 'Clave no son iguales, vuelva a intentar'];
            echo json_encode($arrayResult);
            die();
        }

        $newPass = md5($newPass);
        $data    = $this->Modeloautenticacion->CambioClave($oldPass, $newPass, $user);
        if ($data == 1) {
            $arrayResult = ['type' => 'success', 'message' => 'Se cambio la clave con exito.'];
            echo json_encode($arrayResult);
        }
    }

    public function logout()
    {
        $this->nativesession->destroy();
        $this->index();
    }

    public function validaEncuesta()
    {
        $jwt     = $this->input->get_request_header('x-token', true);
        $payload = $this->validarjwt->verificarjwtlocal($jwt);
        $cc      = $payload->iduser;

        $val = $this->Modeloautenticacion->validaEncuesta($cc);

        if ($val) {
            $valRes = 'no';
        } else {
            $valRes = 'si';
        }

        $arrayResult = ['type' => 'success', 'alert' => $valRes];
        echo json_encode($arrayResult);
    }

    public function menuapp1($perfil)
    {

        if ($perfil === 'supervisor') {
            $menu = [
                [
                    "menuOpt" => 0,
                    "menuName" => "Inicio",
                    "menuIcon" => 61703,
                    "pageName" => "Autogestión Terreno",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 22,
                    "menuName" => "Consulta tarea",
                    "menuIcon" => 60239,
                    "pageName" => "Consulta tarea",
                    "estado" => true,
                ], [
                    "menuOpt" => 20,
                    "menuName" => "Mesas Nacionales",
                    "menuIcon" => 984696,
                    "pageName" => "Mesas Nacionales",
                    "estado" => true,
                ], [
                    "menuOpt" => 5,
                    "menuName" => "BB8",
                    "menuIcon" => 61495,
                    "pageName" => "BB8",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 8,
                    "menuName" => "Consulta Quejas",
                    "menuIcon" => 58311,
                    "pageName" => "Consulta Quejas",
                    "estado" => true,
                ],
            ];
        } else {
            $menu = [
                [
                    "menuOpt" => 0,
                    "menuName" => "Inicio",
                    "menuIcon" => 61703,
                    "pageName" => "Autogestión Terreno",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 1,
                    "menuName" => "Contingencias",
                    "menuIcon" => 984818,
                    "pageName" => "Contingencias",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 2,
                    "menuName" => "Soporte GPON",
                    "menuIcon" => 62245,
                    "pageName" => "Soporte GPON",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 13,
                    "menuName" => "Soporte ETP",
                    "menuIcon" => 62353,
                    "pageName" => "Soporte ETP",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 20,
                    "menuName" => "Mesas Nacionales",
                    "menuIcon" => 984696,
                    "pageName" => "Mesas Nacionales",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 5,
                    "menuName" => "BB8",
                    "menuIcon" => 61495,
                    "pageName" => "BB8",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 6,
                    "menuName" => "Códigos",
                    "menuIcon" => 62122,
                    "pageName" => "Códigos",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 7,
                    "menuName" => "Consulta Contingencias",
                    "menuIcon" => 984035,
                    "pageName" => "Consulta Contingencias",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 8,
                    "menuName" => "Consulta Quejas",
                    "menuIcon" => 58311,
                    "pageName" => "Consulta Quejas",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 11,
                    "menuName" => "Registro equipos",
                    "menuIcon" => 61313,
                    "pageName" => "Registro equipos",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 9,
                    "menuName" => "Tips",
                    "menuIcon" => 60239,
                    "pageName" => "Tips",
                    "estado" => true,
                ],
            ];
        }

        return $menu;
    }

    public function menuapp()
    {


        $jwt = $this->input->get_request_header('x-token', true);
        $payload = $this->validarjwt->verificarjwtlocal($jwt);
        $perfil = $payload->perfil;


        if ($perfil === 'supervisor') {
            $menu = [
                [
                    "menuOpt" => 0,
                    "menuName" => "Inicio",
                    "menuIcon" => 61703,
                    "pageName" => "Autogestión Terreno",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 22,
                    "menuName" => "Consulta tarea",
                    "menuIcon" => 60239,
                    "pageName" => "Consulta tarea",
                    "estado" => true,
                ], [
                    "menuOpt" => 20,
                    "menuName" => "Mesas Nacionales",
                    "menuIcon" => 984696,
                    "pageName" => "Mesas Nacionales",
                    "estado" => true,
                ], [
                    "menuOpt" => 5,
                    "menuName" => "BB8",
                    "menuIcon" => 61495,
                    "pageName" => "BB8",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 8,
                    "menuName" => "Consulta Quejas",
                    "menuIcon" => 58311,
                    "pageName" => "Consulta Quejas",
                    "estado" => true,
                ],
            ];
        } else {
            $menu = [
                [
                    "menuOpt" => 0,
                    "menuName" => "Inicio",
                    "menuIcon" => 61703,
                    "pageName" => "Autogestión Terreno",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 1,
                    "menuName" => "Contingencias",
                    "menuIcon" => 984818,
                    "pageName" => "Contingencias",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 2,
                    "menuName" => "Soporte GPON",
                    "menuIcon" => 62245,
                    "pageName" => "Soporte GPON",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 13,
                    "menuName" => "Soporte ETP",
                    "menuIcon" => 62353,
                    "pageName" => "Soporte ETP",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 20,
                    "menuName" => "Mesas Nacionales",
                    "menuIcon" => 984696,
                    "pageName" => "Mesas Nacionales",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 5,
                    "menuName" => "BB8",
                    "menuIcon" => 61495,
                    "pageName" => "BB8",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 6,
                    "menuName" => "Códigos",
                    "menuIcon" => 62122,
                    "pageName" => "Códigos",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 7,
                    "menuName" => "Consulta Contingencias",
                    "menuIcon" => 984035,
                    "pageName" => "Consulta Contingencias",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 8,
                    "menuName" => "Consulta Quejas",
                    "menuIcon" => 58311,
                    "pageName" => "Consulta Quejas",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 11,
                    "menuName" => "Registro equipos",
                    "menuIcon" => 61313,
                    "pageName" => "Registro equipos",
                    "estado" => true,
                ],
                [
                    "menuOpt" => 9,
                    "menuName" => "Tips",
                    "menuIcon" => 60239,
                    "pageName" => "Tips",
                    "estado" => true,
                ],
            ];
        }

        $arrayResult = ['type' => 'success', 'message' => $menu];
        echo json_encode($arrayResult);
    }


    /*public function menuapp($opt = 0)
    {
        $menu = [
            [
                "menuOpt"  => 0,
                "menuName" => "Inicio",
                "menuIcon" => 61703,
                "pageName" => "Autogestión Terreno",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 1,
                "menuName" => "Contingencias",
                "menuIcon" => 984818,
                "pageName" => "Contingencias",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 2,
                "menuName" => "Soporte GPON",
                "menuIcon" => 62245,
                "pageName" => "Soporte GPON",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 13,
                "menuName" => "Soporte ETP",
                "menuIcon" => 62353,
                "pageName" => "Soporte ETP",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 20,
                "menuName" => "Mesas Nacionales",
                "menuIcon" => 984696,
                "pageName" => "Mesas Nacionales",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 5,
                "menuName" => "BB8",
                "menuIcon" => 61495,
                "pageName" => "BB8",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 6,
                "menuName" => "Códigos",
                "menuIcon" => 62122,
                "pageName" => "Códigos",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 7,
                "menuName" => "Consulta Contingencias",
                "menuIcon" => 984035,
                "pageName" => "Consulta Contingencias",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 8,
                "menuName" => "Consulta Quejas",
                "menuIcon" => 58311,
                "pageName" => "Consulta Quejas",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 11,
                "menuName" => "Registro equipos",
                "menuIcon" => 61313,
                "pageName" => "Registro equipos",
                "estado"   => true,
            ],
            [
                "menuOpt"  => 9,
                "menuName" => "Tips",
                "menuIcon" => 60239,
                "pageName" => "Tips",
                "estado"   => true,
            ],

        ];

        if ($opt == 1) {
            return $menu;
            die();
        }

        $arrayResult = ['type' => 'success', 'message' => $menu];
        echo json_encode($arrayResult);
    }*/
}
