<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Modelobb8 extends CI_Model
{

	public function contador($modulo)
	{

		try {
			$fecha = date('Y-m-d');
			//$modulo = 'bb8 aplicación mobil';
			$sql = "SELECT * FROM Contador WHERE Fecha = ? AND Modulo = ?";
			$query = $this->db->query($sql, array($fecha, $modulo));
			$res = ($query->num_rows() > 0) ? $query->row_array() : 0 ;


			if ($res > 0) {
				$sql = "UPDATE Contador SET Contador = Contador+1 WHERE Fecha = ? AND Modulo = ?";
				$query = $this->db->query($sql, array($fecha, $modulo));
				//$res = ($query->num_rows() > 0) ? $query->row_array() : 0 ;
			} else {
				$sql = "INSERT INTO Contador (Modulo,Fecha,Contador) VALUES (?,?,1)";
				$query = $this->db->query($sql, array($modulo, $fecha));
				//$res = ($query->num_rows() > 0) ? $query->row_array() : 0 ;
			}
			$this->db->close();
		} catch (\Throwable $th) {
			$error = $this->db->error();
			die($error);
		}

	}

}
