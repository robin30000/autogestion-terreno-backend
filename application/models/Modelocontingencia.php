<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Modelocontingencia extends CI_Model
{

	public function getcontingenciabypedido($pedido, $fecha)
	{
		try {
			
			$sql = "SELECT id, accion, ciudad, correo, macEntra, macSale, motivo, observacion, paquetes, pedido, proceso, producto, remite, tecnologia, tipoEquipo, uen, contrato, perfil, grupo, logindepacho, logincontingencia, horagestion, horacontingencia, observContingencia, acepta, ingresoEquipos, tipificacion, engestion, finalizado, fechaClickMarca, loginContingenciaPortafolio, horaContingenciaPortafolio, observContingenciaPortafolio, aceptaPortafolio, ingresoEquiposPortafolio, tipificacionPortafolio, enGestionPortafolio, finalizadoPortafolio, fechaClickMarcaPortafolio, id_terreno, generarcr 
			FROM contingencias 
			WHERE pedido = ? AND horagestion >= ? AND finalizado IS NULL;";
			$query = $this->db->query($sql, array($pedido, $fecha));
			
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

			$sql = "SELECT id, accion, ciudad, macEntra, macSale, observacion, pedido, producto, horagestion, horacontingencia, observContingencia, tipificacion, finalizado, engestion
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

	public function postcontingencia($tipocontingencia, $uNEMunicipio, $correo, $macentra, $macsale, $motivo, $observacion, $paquetes, $pedido, $TaskType, $tipoproducto, $remite, $uNETecnologias, $tipoEquipo, $uNEUENcalculada, $uNEProvisioner, $perfil, $grupo, $user_id, $user_identification, $fecha_solicitud, $engestion)
	{
		try {
			
			$sql = "INSERT INTO contingencias (accion, ciudad, correo, macEntra, macSale, motivo, observacion, paquetes, pedido, proceso, producto, remite, tecnologia, tipoEquipo, uen, contrato, perfil, grupo, logindepacho, id_terreno, horagestion, engestion) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
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
					$engestion
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

	

	
}
