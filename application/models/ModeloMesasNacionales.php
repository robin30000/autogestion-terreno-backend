<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ModeloMesasNacionales extends CI_Model
{

	public function getMnByTask($tarea)
	{
		$fecha = date('Y-m-d');
		$fecha = $fecha . ' 00:00:00';
		try {
			$sql   = "SELECT * FROM mesas_nacionales
                    WHERE tarea = ? AND hora_ingreso >= ? AND estado in ('Sin gestión', 'En gestión')";
			$query = $this->db->query($sql, [$tarea, $fecha]);
			$res   = ($query->num_rows() > 0) ? $query->row_array() : 0;

			return $res;
		} catch (\Throwable $th) {
			$error = $this->db->error();
			die($error);
		}
	}

	public function postPedidoMn(
		$nombre_contacto,
		$numero_contacto,
		$cc_tecnico,
		$observacion,
		$tarea,
		$unepedido,
		$TaskTypeCategory,
		$UneSourceSystem,
		$mesa,
		$accion,
        $region,
        $area,
		$ata
	) {
		try {
			$sql   = "insert into mesas_nacionales(hora_ingreso, estado, nombre_tecnico, num_contacto_tecnico, cc_tecnico,
                             observacion_tecnico, tarea, pedido, TaskTypeCategory, UneSourceSystem, mesa, accion_tecnico, region, area, activacion_ata)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
			$query = $this->db->query($sql,
				[
					date('Y-m-d H:i:s'),
					'Sin gestión',
					$nombre_contacto,
					$numero_contacto,
					$cc_tecnico,
					$observacion,
					$tarea,
					$unepedido,
					$TaskTypeCategory,
					$UneSourceSystem,
					$mesa,
					$accion,
                    $region,
                    $area,
					$ata
				]
			);


			$res = ($this->db->affected_rows() > 0) ? 1 : 0;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();

			return $error;

		}


	}


	public function getsoporteMnpbyuser($cc_tecnico)
	{
		try {
			$fecha = date('Y-m-d');
			$sql   = "SELECT
						tarea,
						pedido,
						tasktypecategory,
						estado,
						observacion_tecnico,
						hora_ingreso,
						hora_gestion,
						COALESCE ( observacion_gestion, '' ) AS observacion_gestion,
						COALESCE ( tipificacion, '' ) AS tipificacion,
						COALESCE ( tipificacion_2, '' ) AS tipificacion_2
					FROM
						mesas_nacionales  
			WHERE cc_tecnico = ? AND hora_ingreso BETWEEN '$fecha 00:00:00' AND '$fecha 23:59:59'";
			$query = $this->db->query($sql, [$cc_tecnico]);

			$res = ($query->num_rows() > 0) ? $query->result_array() : 0;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

	public function validacionesContingecias()
	{
		try {
			$sql   = "SELECT valida, tipo from validaciones_apk";
			$query = $this->db->query($sql);

			$res = ($query->num_rows() > 0) ? $query->result_array() : 0;

			return $res;

			$this->db->close();
		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}
	}

}
