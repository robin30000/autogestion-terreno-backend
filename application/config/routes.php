<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'autenticacion';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['master'] = 'master/index';
$route['contingencia'] = 'master/viewcontingencia';
$route['soportegpon'] = 'master/viewsoportegpon';
$route['listasoportegpon'] = 'master/viewlistasoportegpon';
$route['listacontingencia'] = 'master/viewlistacontingencia';


/* AUTENTICACION */
$route['ingresar'] = 'Autenticacion/autenticar';
$route['validarjwt'] = 'Autenticacion/verificarjwt';
$route['validarmenu'] = 'Autenticacion/menuapp';

/* SOPORTE GPON */
$route['postsoportegpon'] = 'Consoportegpon/postsoportegpon';
$route['getsoportegponbyuser'] = 'Consoportegpon/getsoportegponbyuser';

/* CONTINGENCIA */
$route['postcontingencia'] = 'Concontingencia/postcontingencia';
$route['getcontingenciabyuser'] = 'Concontingencia/getcontingenciabyuser';
$route['gettareagpon'] = 'Concontingencia/gettareagpon';

/* BB8 */
$route['getbb8'] = 'Conbb8/getbb8';
$route['getbb8Puertos'] = 'Conbb8/getbb8Puertos';

/* CODIGO INCOMPLETO */
$route['getcodigoincompleto'] = 'Concodigoincompleto/getcodigoincompleto';

/* SEGUIMIENTO CLICK */
$route['gettecnicosbysupervisor'] = 'Conseguimientoclick/gettecnicosbysupervisor';
$route['gettareasbytecnico'] = 'Conseguimientoclick/gettareasbytecnico';

/* CONSULTA QUEJAS */

$route['getQuejas'] = 'ConQuejas/getQuejas';
$route['postquejago'] = 'ConQuejas/postquejago';
$route['getquejasgobyuser'] = 'ConQuejas/getquejasgobyuser';

/**
 * registro equipos
 */

$route['postregistroequipos'] = 'RegistroEquipos/postregistroequipos';
$route['getregistroequiposbyuser'] = 'RegistroEquipos/getregistroequiposbyuser';
$route['getregistropedido'] = 'RegistroEquipos/getregistropedido';

/**
 * ETP
 */

$route['validapedidoetp'] = 'Etp/validaPedidoETP';
$route['postpedidoetp'] = 'Etp/postPedidoETP';
$route['getsoporteetpbyuser'] = 'Etp/getsoporteetpbyuser';

/**
 * mesas nacionales
 */

$route['validaPedidoMn'] = 'MesasNacionales/validaPedidoMn';
$route['postPedidoMn'] = 'MesasNacionales/postPedidoMn';
$route['getsoporteMnbyuser'] = 'MesasNacionales/getsoporteMnbyuser';

/**
 * encuestas
 */

$route['validaEncuesta'] = 'EncuestaTecnico/validaEncuesta';
$route['getEncuesta'] = 'EncuestaTecnico/getEncuesta';
$route['getCelularTecnico'] = 'EncuestaTecnico/getCelularTecnico';
$route['guardaCelularTecnico'] = 'EncuestaTecnico/guardaCelularTecnico';

/**
 * TAREA SUPERVISOR
 */

$route['tareasupervisor'] = 'TareaSupervisor/tareasupervisor';

/**
 * network
 */

$route['postNetwork'] = 'Network/postNetwork';
$route['getNetworkByUserMass'] = 'Network/getNetworkByUserMass';
$route['getNetworkByUserIndividual'] = 'Network/getNetworkByUserIndividual';


