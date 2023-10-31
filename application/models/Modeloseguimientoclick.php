<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

class Modeloseguimientoclick extends CI_Model
{

	public function gettecnicosbysupervisor($cedula_supervisor)
	{
		try {

			$sql = "SELECT id, cedula_tecnico, nombre_tecnico, celular_corporativo, segmento_tecnico, cedula_supervisor, nombre_supervisor, estado 
			FROM seguimientopedidos.front_recursos 
			WHERE cedula_supervisor = ? AND estado = 1;";
			$query = $this->db->query($sql, array($cedula_supervisor));

			$res = ($query->num_rows() > 0) ? $query->result_array() : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

	//TareasTec cedtec


}
