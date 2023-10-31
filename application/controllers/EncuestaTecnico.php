<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class EncuestaTecnico extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('ModeloEncuestaTecnico');
		$this->load->library('nativesession');
		$this->load->library('validarjwt');
		$this->load->helper('url');
	}

	public function index()
	{
		echo 'EncuestaTecnico';
	}

	public function getEncuesta()
	{
		$jwt = $this->input->get_request_header('x-token', true);

		$payload = $this->validarjwt->verificarjwtlocal($jwt);

		$user_identification = $payload->iduser;


		$res = $this->ModeloEncuestaTecnico->getEncuesta($user_identification);

		if ($res) {
			$response = 'no';
		} else {
			$response = 'si';
		}

		$superV = $this->ModeloEncuestaTecnico->validacionesContingecias();

		if ($superV[4]['valida'] != 'activa'){
			$response = 'no';
		}

		$arrayResult = ['type' => 'success', 'alert' => $response];
		echo json_encode($arrayResult);
	}

	public function validaEncuesta()
	{
		try {

			$jwt = $this->input->get_request_header('x-token', true);

			$payload = $this->validarjwt->verificarjwtlocal($jwt);

			$user_identification = $payload->iduser;

			$res = $this->ModeloEncuestaTecnico->validaEncuesta($user_identification);

			if ($res) {
				$response = 'no';
			} else {
				$response = 'si';
			}

			$arrayResult = ['type' => 'success', 'alert' => $response];
			echo json_encode($arrayResult);

		} catch (\Exception $e) {
			var_dump($e->getMessage());
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
		} catch (Exception $e) {
			var_dump($e->getMessage());
		}
	}

}
