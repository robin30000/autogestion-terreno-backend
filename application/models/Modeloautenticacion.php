<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Modeloautenticacion extends CI_Model
{

	public function consultauser($username, $password)
	{
		$sqlUser = "SELECT login_click FROM tecnicos WHERE login_click = ?;";
		$valUser = $this->db->query($sqlUser, array($username));
		if ($valUser->num_rows() > 0) {
			$sqlEstado = "SELECT estado FROM tecnicos WHERE login_click = ? AND estado = ?;";
			$valEstado = $this->db->query($sqlEstado, array($username, 1));
			if ($valEstado->num_rows() > 0) { 
				$sqlUserPass = "SELECT login_click FROM tecnicos WHERE login_click = ? AND password = ?;";
				$valUserPass = $this->db->query($sqlUserPass, array($username, $password));
				if ($valUserPass->num_rows() == 1) {
					$sql = "SELECT id, identificacion, nombre, empresa, ciudad, celular, contrato, region, login_click, password, estado FROM tecnicos WHERE login_click = ? AND password = ? AND estado = ?;";
					return $this->db->query($sql, array($username, $password, 1))->row_array();
				} else {
					return 2;
				}
			} else {
				return 3;
			}
		} else {
			return 1;
		}
		$this->db->close();
	}

	public function cambioclave($oldpass, $newpass, $user)
	{
		$sqlCambioClave = $this->db->query("UPDATE Usuario_Usuarios SET Clave = '$newpass' WHERE Usuario = '$user' AND Clave = '$oldpass';");
        $resCambioClave = ($sqlCambioClave === TRUE) ? 1 : 0;
        return $resCambioClave;
        $this->db->close();
	}

	
}
