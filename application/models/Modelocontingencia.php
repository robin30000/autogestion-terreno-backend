<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

class Modelocontingencia extends CI_Model
{

	public function getcontingenciabypedido($pedido, $producto, $fecha)
	{
		try {

			$sql = "SELECT id, accion, ciudad, correo, macEntra, macSale, motivo, observacion, paquetes, pedido, proceso, producto, remite, tecnologia, tipoEquipo, uen, contrato, perfil, grupo, logindepacho, logincontingencia, horagestion, horacontingencia, observContingencia, acepta, ingresoEquipos, tipificacion, engestion, finalizado, fechaClickMarca, loginContingenciaPortafolio, horaContingenciaPortafolio, observContingenciaPortafolio, aceptaPortafolio, ingresoEquiposPortafolio, tipificacionPortafolio, enGestionPortafolio, finalizadoPortafolio, fechaClickMarcaPortafolio, id_terreno, generarcr 
			FROM contingencias 
			WHERE tarea = ? and producto = ? AND finalizado IS NULL;";
			$query = $this->db->query($sql, array($pedido, $producto));

			$res = ($query->num_rows() > 0) ? $query->row_array() : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

	public function getcontingenciabyuser($user_id, $fecha)
	{
		try {

			$sql = "SELECT id, accion, ciudad, macEntra, macSale, observacion, pedido, producto, horagestion, horacontingencia, observContingencia, tipificacion, finalizado, engestion, acepta
			FROM contingencias
			WHERE logindepacho = ? AND horagestion >= ?;";
			$query = $this->db->query($sql, array($user_id, $fecha));

			$res = ($query->num_rows() > 0) ? $query->result_array() : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

	public function postcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes, $pedido, $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification, $fecha_solicitud, $engestion,  $tAREA_ID, $sistema, $typeTask, $region)
	{
		try {

			$sql = "INSERT INTO contingencias (accion, ciudad, correo, macEntra, macSale, motivo, observacion, paquetes, pedido, proceso, producto, remite, tecnologia, tipoEquipo, uen, contrato, perfil, grupo, logindepacho, id_terreno, horagestion, engestion, tarea, uneSourceSystem, taskType, region) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
			$query = $this->db->query($sql,
				array(
					$tipocontingencia,
					$uNEMunicipio,
					$correo,
					$macentra,
					$macsale,
					$motivo,
					$observacion,
					$paquetes,
					$pedido,
					$TaskType,
					$tipoproducto,
					$remite,
					$uNETecnologias,
					$tipoEquipo,
					$uNEUENcalculada,
					$uNEProvisioner,
					$perfil,
					$grupo,
					$user_id,
					$user_identification,
					$fecha_solicitud,
					$engestion,
                    $tAREA_ID, $sistema, $typeTask, $region
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

	public function putcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes, $pedido, $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification, $fecha_solicitud, $engestion, $id_contingencia)
	{
		try {

			$sql = "UPDATE contingencias SET accion = ?, ciudad = ?, correo = ?, macEntra = ?, macSale = ?, motivo = ?, observacion = ?, paquetes = ?, pedido = ?, proceso = ?, producto = ?, remite = ?, tecnologia = ?, tipoEquipo = ?, uen = ?, contrato = ?, perfil = ?, grupo = ?, logindepacho = ?, id_terreno = ?, horagestion = ?, engestion = ? WHERE id = ?;";
			$query = $this->db->query($sql,
				array(
					$tipocontingencia,
					$uNEMunicipio,
					$correo,
					$macentra,
					$macsale,
					$motivo,
					$observacion,
					$paquetes,
					$pedido,
					$TaskType,
					$tipoproducto,
					$remite,
					$uNETecnologias,
					$tipoEquipo,
					$uNEUENcalculada,
					$uNEProvisioner,
					$perfil,
					$grupo,
					$user_id,
					$user_identification,
					$fecha_solicitud,
					$engestion,
					$id_contingencia
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

	public function getdatosgpon($tarea){
		try {
			$sql = "SELECT c.tarea,
				       CASE WHEN c.horagestion IS NULL THEN '-' ELSE c.horagestion END           AS horagestion,
				       CASE WHEN c.horacontingencia IS NULL THEN '-' ELSE c.horacontingencia END AS horacontingencia,
				       case c.acepta WHEN 'Rechaza' THEN 'Rechazado' when 'Acepta' then 'Ok' ELSE 'En progreso' END AS finalizado,
				       CASE WHEN c.observContingencia IS NULL THEN '' ELSE c.observContingencia END AS observacion
				FROM contingencias c
				WHERE tarea = ?";
			$query = $this->db->query($sql, array($tarea));

			$res = ($query->num_rows() > 0) ? $query->result_array() : 0 ;

			return $res;

			$this->db->close();
		}catch (\Throwable $th){
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
