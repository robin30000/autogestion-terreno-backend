<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ModeloEncuestaTecnico extends CI_Model
{

	public function getEncuesta($cc)
	{
		$sqlUser = "SELECT * from encuesta_tecnico where cc_tecnico = ?";
		$query   = $this->db->query($sqlUser, [$cc]);

		//$res = ($query->num_rows() > 0) ? $query->result_array() : 0;

		return $query->num_rows();

		$this->db->close();

	}

	public function validaEncuesta($cc)
	{
		try {

			$sqlUser = "SELECT * from encuesta_tecnico where cc_tecnico = ?";
			$query   = $this->db->query($sqlUser, [$cc]);

			//$res = ($query->num_rows() > 0) ? $query->result_array() : 0;

			if ($query->num_rows() == 0) {
				$query = "INSERT INTO encuesta_tecnico (cc_tecnico, estado, fecha) VALUES (?,?,?)";
				$stmt  = $this->db->query($query,
					[
						$cc,
						'1',
						date('Y-m-d H:i:s'),
					]);
			}


			$res = ($this->db->affected_rows() > 0) ? 1 : 0;

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
