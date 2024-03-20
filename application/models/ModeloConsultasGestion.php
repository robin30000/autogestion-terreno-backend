<?php

defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'ConnectionGestion.php';
require_once 'connection.php';

class ModeloConsultasGestion
{
	private $_DB;
	private $_DB2;

	public function __construct()
	{
		$this->_DB = new ConnectionGestion();
		$this->_DB2 = new Conection();
	}

	public function getDataTareaSA($tarea)
	{
		try {

			$stmt = $this->_DB->prepare("SELECT * FROM [TmpSFSPedidos_Ingresado_csv] WHERE [Número de cita] = :tarea");
			$stmt->execute(array(':tarea' => $tarea));
			if ($stmt->rowCount()) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                /**
                ["Id de cita de servicio"]=> "08pQO000004H5CI"
				["Id de orden de trabajo"]=> "0WOQO000004jWxV"
				["Número de orden de trabajo"]=> "01645233"
				*!["Número de cita"]=> "SA-2062915"
				*!["External ID"]=> "1-65272156498046"
				["Estado_orden"]=> "Completo"
				*!["Estado_cita"]=> "Finalizado"
				["Área Operativa"]=> "CO-B2C"
				*!["Tipo de Trabajo"]=> "Reparacion FTTH Internet"
				*!["Categoria de Trabajo"]=> "Reparacion Internet"
				*!["Tipo de trabajo local"]=> "Reparacion GPON Internet"
				["Departamiento"]=> "Antioquia"
				*!["Ciudad"]=> "Sabaneta"
				["Zona o Ramal"]=> NULL
				["Teléfono de Contacto"]=> "3005412530 3014556920"
				["Latitud"]=> "6,152250000000000"
				["Longitud"]=> "-75,601868000000000"
				*!["Comentario de CRM"]=> "POE"
				["Cantidad de Decos"]=> "0"
				["Número de Equipos"]=> "0"
				["Número de Equipos a recuperar"]=> "0"
				["Motivo de Apertura"]=> "UNECodNaturaleza: T122 UNENaturaleza: Servicio intermitente"
				["Motivo de Diagnóstico"]=> "VELOCIDAD CONTRATADA INSUFICIENTE"
				["Motivo de Solución"]=> "CONFIGURACION EQUIPO TERMINAL"
				["Motivo de Fallida"]=> "SIN_INFORMACION"
				["Motivo de Cancelación"]=> NULL
				["Comentario del Técnico"]=> "Se reubica ap para garantizar servicio queda ok"
				["Comentario"]=> NULL
				["Cantidad de Extensión"]=> "0"
				["Token de Fallida con Visita Status"]=> "VN9PZ"
				["Email de Contacto"]=> NULL
				["Duración"]=> "75,00"
				["Datos Incompletos"]=> "0"
				["Tipo de registro de orden de trabajo Id de tipo de registro"]=> "0125f000000W0ch"
				["Estado de Confirmación"]=> "No contactado"
				["Dirección"]=> "CL 56 SUR # 38 - 112 IN 1812"
				["Servicio relacionado Anclado"]=> "0"
				["Codigo de cliente"]=> "COB2C1042455435"
				["Nombre de la cuenta"]=> "ANGIE PAOLA HERRERA MONTAÑEZ ."
				["Última modificación por Nombre completo"]=> "Juan Esteban Henao Gutierrez"
				["Descripción"]=>   "Nombre: OLT * Id: 001301,SABANTCOL * DireccionElementoRed: Nombre: CENTRAL * Id: SABANETA *
									DireccionElementoRed: Nombre: BASTIDOR * Id: 1 * DireccionElementoRed: Nombre: SHELF * Id: 0 * DireccionElementoRed:
									Nombre: ARPON * Id: 42923,CUBIERTA * DireccionElementoRed: CL 56 SUR CR 38Nombre: SPLITTER * Id: 3,PLC *
									DireccionElementoRed: Nombre: HILOSPLITTER * Id: 6 * DireccionElementoRed: Nombre: NAP * Id: 22,INTERNO *
									DireccionElementoRed: CL 56 SUR CR 38 -112Nombre: HILO * Id: 8 * DireccionElementoRed: Nombre: TARJETA * Id:
									025KFD10MC100439,GPFD_16 * DireccionElementoRed: Nombre: SLOTTARJETA * Id: 12 * DireccionElementoRed: Nombre:
									PUERTOFISICO * Id: 8 * DireccionElementoRed: Nombre: PUERTOLOGICO * Id: 13 * DireccionElementoRed: Nombre: STBOX * Id:
									376933022312104877 * DireccionElementoRed: CL 56 SUR # 38 - 112 INTERIOR 1812 Sabaneta AntioquiaNombre: STBOX * Id:
									376933022416103271 * DireccionElementoRed: CL 56 SUR # 38 - 112 INTERIOR 1812 Sabaneta AntioquiaNombre: CPE * Id:
									FHTT9B9B12F0 * DireccionElementoRed: CL 56 SUR # 38 - 112 INTERIOR 1812 Sabaneta Antioquia"
				["Territorio de servicio"]=> "CO-Sabaneta"
				["Territorio de servicio Territorio principal Nombre"]=> "CO-Antioquia Sur"
				["Territorio de servicio Territorio principal Territorio principal Nombre"]=> "CO-Noroccidente"
				["AA Hora Confirmado Cliente"]=> NULL
				["AA Hora Cancelado Cliente"]=> NULL
				["Inicio real"]=> NULL
				["Finalización real"]=> NULL
				["Fecha Última Localización del Técnico"]=> "12/03/2024, 9:27 a. m."
				["Fecha de la última modificación"]=> "12/03/2024"
				["Fecha de la última modificación1"]=> "12/03/2024"
				["Fecha de inicio"]=> "12/03/2024, 12:00 a. m."
				["Fecha de vencimiento"]=> "12/03/2024, 11:59 p. m."
				["Fecha de Creación OT"]=> "11/03/2024"
				["Fecha de Creación Cita"]=> "11/03/2024"
				["Fecha de finalización"]=> "12/03/2024, 11:59 p. m."
				["Inicio de Cita"]=> "12/03/2024, 7:00 a. m."
				["Fin de Cita"]=> "12/03/2024, 12:00 p. m."
				["Inicio Agendado"]=> "12/03/2024, 12:37 p. m."
				["Fin Agendado"]=> "12/03/2024, 1:36 p. m."
				["Fecha de estado final"]=> "12/03/2024, 1:37 p. m."
				["Hora Inicio"]=> "12"
				*!["Contratista"]=> "Emtelco_Tecnicos"
				["Nombre Contratista y Recurso Asignado"]=> "Emtelco_Tecnicos - Henao Gutierrez Juan Esteban"
				["Tecnico Nombre"]=> "Henao Gutierrez Juan Esteban"
				["Reagendación sin cita"]=> "0"
				["Rango de Cita acordada con cliente"]=> "1"
				["No materiales o equipos consumidos"]=> "1"
				["Turno de agendamiento"]=> "PM"
				["Duración programada"]=> "59.0"
				["Duración real (minutos)"]=> NULL
				["Código de Finalización (Token)"]=> "Q92OD"
				["FECHA_CARGA"]=> "2024-03-18 17:59:00"
				["PEDIDO_CITA"]=> "01645233|SA-2062915"
				["PRODUCTO"]=> " Internet"
				["COD_FUNCIONARIO"]=> "1152692392"
				["ID_ORDEN"]=> "0WOQO000004jWxV4AU"
				["FECHA_CREACION_ORDEN"]=> "2024-03-11 15:53:00"
				["FECHA_ULTMODIFICACION_ORDEN"]=> "2024-03-12 13:37:00"
				["ID_CITA"]=> "08pQO000004H5CIYA0"
				["FECHA_CREACION_CITA"]=> "2024-03-11 15:53:00"
				["FECHA_ULTMODIFICACION_CITA"]=> "2024-03-12 13:37:00"
				["FUENTE"]=> "SALESFORCE"
				["DIRECCION_COMPLETA"]=> "1440115472 * CL 56 SUR # 38 - 112 IN 1812 * * no * CL 56 SUR # 38 - 112 INTERIOR 1812 * Y Barrio: MarÃ­a Auxiliadora"
				["ESTADO_PREVIO"]=> "En Sitio"
				["NODO"]=> NULL*/
				$data = $res[0];


				var_dump($res[0]);exit();
			} else {
				$res[0] = 0;
			}

			$this->_DB = null;
		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}

		return $res[0];
	}

}
