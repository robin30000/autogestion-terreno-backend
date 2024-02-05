<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

	public function validaEncuesta($cc)
	{
		$sqlUser = "SELECT * from encuesta_tecnico where cc_tecnico = ?";
		$query = $this->db->query($sqlUser, array($cc));

		//$res = ($query->num_rows() > 0) ? $query->result_array() : 0;

		return $query->num_rows();

		$this->db->close();

	}

	public function validacionesContingecias()
	{
		try {
			$sql = "SELECT valida, tipo from validaciones_apk";
			$query = $this->db->query($sql);

			$res = ($query->num_rows() > 0) ? $query->result_array() : 0;
			$this->db->close();
			return $res;


		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}
	}

	public function recoverPass($login, $cedula, $celular)
	{
		require_once 'constant.php';
		$sql = "SELECT pass_apk  FROM tecnicos WHERE login_click = ? AND celular = ? AND identificacion = ?";
		$query = $this->db->query($sql, array($login, $celular, $cedula));

		$res = ($query->num_rows() > 0) ? $query->row_array() : 0;
		$this->db->close();
		return $res;

	}

	public function updatePass($pass, $user): int
	{
		try {

			require_once 'constant.php';
			$newpass = md5($pass);

			$sql = "SELECT * FROM cuentasTecnicos WHERE cedula = ?";
			$query = $this->db->query($sql, array($user));
			$res = ($this->db->affected_rows() > 0) ? 1 : 0;

			if (!$res) {
				$sql = "SELECT * FROM tecnicos WHERE identificacion = ?";
				$query = $this->db->query($sql, array($user));
				$res = ($this->db->affected_rows() > 0) ? 1 : 0;

				if ($res) {
					$sql = "INSERT INTO cuentasTecnicos (cedula,password, autogestion_clave, autogestion_clave_encry)   VALUES (?,?, '',AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "'));";
					$query = $this->db->query($sql, array($user, $newpass, $pass));
					$res = ($this->db->affected_rows() > 0) ? 1 : 0;
				}
			}

			$sql = "UPDATE cuentasTecnicos SET autogestion_clave = ?, 
                           					autogestion_clave_encry = AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "')  WHERE cedula = ?";
			$query = $this->db->query($sql, array($newpass, $pass, $user));
			$res = ($this->db->affected_rows() > 0) ? 1 : 0;

			if ($res) {
				$sql = "UPDATE tecnicos SET password = ?, 
                           					pass_apk = ?  WHERE identificacion = ?";
				$query = $this->db->query($sql, array($newpass, $pass, $user));
				$res = ($this->db->affected_rows() > 0) ? 1 : 0;
			}

			$this->db->close();
			return $res;

		} catch (\Throwable $th) {
			$error = $this->db->error();
			die($error);
		}
	}
}
