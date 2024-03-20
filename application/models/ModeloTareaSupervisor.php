<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use jwt\src\JWT;
use jwt\src\Key;

require_once 'connection.php';

class ModeloTareaSupervisor
{
	private $_DB;

	public function __construct()
	{
		$this->_DB = new Conection();
	}

	public function tarea_supervisor($data)
	{

		try {

			$response = [];
			$stmt = $this->_DB->prepare("select 'Contingencia' as modulo,
													 a.logincontingencia,
													 a.horagestion as fecha_ingreso,
													 a.horacontingencia as fecha_fin,
													 a.observacion as observacion,
													 a.observContingencia as observacion_asesor,
													 case a.engestion when 1 then 'Finalizado' else 'Sin gestión' end gestion,
													 b.nombre
												from contingencias a INNER JOIN tecnicos b ON a.id_terreno = b.identificacion
												where tarea = :tarea");
			$stmt->execute(array(':tarea' => $data));

			if ($stmt->rowCount() > 0) {
				array_push($response, $stmt->fetchAll(PDO::FETCH_ASSOC));
			}

			$stmt = $this->_DB->prepare("select 'ETP' as modulo,
                                                       login_gestion as logincontingencia,
                                                       fecha_crea as fecha_ingreso,
                                                       fecha_gestion as fecha_fin,
                                                       observacion_terreno as observacion,
                                                       observacionesGestion as observacion_asesor,
                                                       case status_soporte when '1' then 'En gestión' when '0' then 'Sin gestión' else 'Finalizado' end gestion,
                                                       engineer_name as nombre
                                                from etp
                                                where tarea = :tarea");
			$stmt->execute(array(':tarea' => $data));

			if ($stmt->rowCount() > 0) {
				array_push($response, $stmt->fetchAll(PDO::FETCH_ASSOC));
			}

			$stmt = $this->_DB->prepare("select 'Soporte GPON' as modulo,
                                                       login as logincontingencia,
                                                       fecha_creado as fecha_ingreso,
                                                       fecha_respuesta as fecha_fin,
                                                       observacion_terreno as observacion,
                                                       observacion as observacion_asesor,
                                                       -- case status_soporte when '1' then 'En gestión' when '0' then 'Sin gestión' else 'Finalizado' end gestion
                                                       -- case status_soporte when '1' then 'Finalizado' WHEN '2' then 'En gestión' else  'Sin gestión' end gestion
                                                        case status_soporte when 1 then 'Finalizado' WHEN 2 then 'En gestión' else 'Sin gestión' end gestion,
                                                        engineer_name as nombre
                                                from soporte_gpon
                                                where tarea = :tarea");
			$stmt->execute(array(':tarea' => $data));

			if ($stmt->rowCount() > 0) {
				array_push($response, $stmt->fetchAll(PDO::FETCH_ASSOC));
			}

			$stmt = $this->_DB->prepare("select 'TOIP' as modulo,
                                                                   login_gestion as logincontingencia,
                                                                   hora_ingreso as fecha_ingreso,
                                                                   hora_gestion as fecha_fin,
                                                                   'N/A' as observacion,
                                                                   observacion as observacion_asesor,
                                                                   case en_gestion when '1' then 'En gestión' when '0' then 'Sin gestión' else 'Finalizado' end gestion,
                                                                   nombre_tecnico as nombre
                                                            from activacion_toip
                                                            where tarea = :tarea");
			$stmt->execute(array(':tarea' => $data));

			if ($stmt->rowCount() > 0) {
				array_push($response, $stmt->fetchAll(PDO::FETCH_ASSOC));
			}

			$stmt = $this->_DB->prepare("select 'Mesas nacionales' as modulo,
                                                                   login_gestion as logincontingencia,
                                                                   hora_ingreso as fecha_ingreso,
                                                                   hora_gestion as fecha_fin,
                                                                   observacion_tecnico as observacion,
                                                                   observacion_gestion as observacion_asesor,
                                                                   case estado when 'Gestionado' then 'Finalizado' else estado end gestion,
                                                                   nombre_tecnico as nombre
                                                            from mesas_nacionales
                                                            where tarea = :tarea");
			$stmt->execute(array(':tarea' => $data));

			if ($stmt->rowCount() > 0) {
				array_push($response, $stmt->fetchAll(PDO::FETCH_ASSOC));
			}

			$stmt = $this->_DB->prepare("select 'Código incompleto' as modulo,
                                                                   'N/A' as logincontingencia,
                                                                   fecha_creado as fecha_ingreso,
                                                                   fecha_respuesta as fecha_fin,
                                                                   observacion as observacion,
                                                                   codigo as observacion_asesor,
                                                                   case status_soporte when '1' then 'Finalizado' else 'En gestión' end gestion,
                                                                   engineer_name as nombre
                                                            from gestion_codigo_incompleto
                                                            where tarea = :tarea");
			$stmt->execute(array(':tarea' => $data));

			if ($stmt->rowCount() > 0) {
				array_push($response, $stmt->fetchAll(PDO::FETCH_ASSOC));
			}

			$resultado_final = call_user_func_array('array_merge', $response);
			$this->_DB = null;
			return $resultado_final;

		} catch (PDOException $th) {
			var_dump($th->getMessage());
		}
	}
}
