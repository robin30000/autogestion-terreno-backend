<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

class Modeloconsultagpon extends CI_Model
{

	public function getgestioncodigoincompletotarea($tarea)
	{
		try {

			$sql = "SELECT * FROM soporte_gpon WHERE tarea = ?;";
			$query = $this->db->query($sql, array($tarea));

			$res = ($query->num_rows() > 0) ? $query->row_array() : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

}
