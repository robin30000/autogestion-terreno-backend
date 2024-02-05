<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

class Modelocodigoincompleto extends CI_Model
{

	public function getgestioncodigoincompletotarea($tarea)
	{
		try {

			$sql = "SELECT tarea FROM gestion_codigo_incompleto WHERE tarea = ?;";
			$query = $this->db->query($sql, array($tarea));

			$res = ($query->num_rows() > 0) ? $query->row_array() : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

    public function testgestioncodigoincompleto($object)
    {
        try {
            $o = json_encode($object);
            $sql = "INSERT INTO gestion_codigo_incompleto (put) VALUES (?);";
            $query = $this->db->query($sql,array($o));

            $res = ($this->db->affected_rows() > 0) ? 1 : 0 ;
            return $res;
            $this->db->close();
        } catch (\Throwable $th) {

            $error = $this->db->error();
            return $error;

        }
    }

	public function postgestioncodigoincompleto($unepedido,$unemunicipio,$uneproductos,$engineerid,$engineername,$unenombrecontacto,$unetelefonocontacto,$tasktypecategory,$mobilephone,$tarea,$fecha_inicio,$fecha_respuesta, $respuesta, $observacion,$codigo,$login)
	{
		try {

			$sql = "INSERT INTO gestion_codigo_incompleto (tarea, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, engineer_id, engineer_name, mobile_phone, fecha_solicitud_firebase, fecha_creado, fecha_respuesta,respuesta_gestion, observacion, codigo, status_soporte, login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,1,?);";
			$query = $this->db->query($sql,
				array(
					$tarea,
					$unetelefonocontacto,
					$unenombrecontacto,
					$unepedido,
					$tasktypecategory,
					$unemunicipio,
					$uneproductos,
					$engineerid,
					$engineername,
					$mobilephone,
                    $fecha_respuesta,
                    $fecha_respuesta,
                    $fecha_inicio,
                    $respuesta,
                    $observacion,
                    $codigo,
					$login
				)
			);

			$res = ($this->db->affected_rows() > 0) ? 1 : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			return $error;

		}
	}

	public function putgestioncodigoincompleto($unepedido,$unemunicipio,$uneproductos,$engineerid,$engineername,$unenombrecontacto,$unetelefonocontacto,$tasktypecategory,$mobilephone,$tarea,$fecha_respuesta, $respuesta,$observacion, $codigo)
	{
		try {

			$sql = "UPDATE gestion_codigo_incompleto SET numero_contacto = ?, nombre_contacto = ?, unepedido = ?, tasktypecategory = ?, unemunicipio = ?, uneproductos = ?, engineer_id = ?, engineer_name = ?, mobile_phone = ?, fecha_respuesta = ?, respuesta_gestion = ?, observacion = ?, status_soporte = '1', codigo = ? WHERE tarea = ?;";
			$query = $this->db->query($sql,...
				array(
					$unetelefonocontacto,
					$unenombrecontacto,
					$unepedido,
					$tasktypecategory,
					$unemunicipio,
					$uneproductos,
					$engineerid,
					$engineername,
					$mobilephone,
                    $fecha_respuesta,
                    $respuesta,
                    $observacion,
                    $codigo,
                    $tarea,
				)
			);

			$res = ($this->db->affected_rows() > 0) ? 1 : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			return $error;

		}
	}

    public function validacionesContingecias()
    {
        try {
            $sql = "SELECT valida, tipo from validaciones_apk";
            $query = $this->db->query($sql);

            $res = ($query->num_rows() > 0) ? $query->result_array() : 0;

            return $res;

            $this->db->close();
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
    }

}
