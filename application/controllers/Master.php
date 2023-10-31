<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

class Master extends CI_Controller {

	function __construct() {
		parent::__construct();
		//$this->load->model('ModeloAutenticacion');
		$this->load->library('nativesession');
		$this->load->helper('url');
	}


	public function index()
	{
		$this->load->view('master');
	}

	public function viewcontingencia()
	{
		$this->load->view('contingencia');
	}

	public function viewsoportegpon()
	{
		$this->load->view('soporte_gpon');
	}

	public function viewlistasoportegpon()
	{
		$this->load->view('lista_soporte_gpon');
	}

	public function viewlistacontingencia()
	{
		$this->load->view('lista_contingencia');
	}


}
