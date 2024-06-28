<?php
//defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'connection.php';

class ModeloNetwork
{
	private Conection $_DB;

	public function __construct()
	{
		$this->_DB = new Conection();
	}

	public function getTask($tarea)
	{
		$fecha = date('Y-m-d');
		$fecha = $fecha . ' 00:00:00';

		try {

			$stmt = $this->_DB->prepare("SELECT * FROM network
                    WHERE numero_ticket = :tarea AND fecha_ingreso >= :fecha AND estado != :estado");
			$stmt->execute(array(':tarea' => $tarea, ':fecha' => $fecha, ':estado' => 'Finalizado'));

			return $stmt->rowCount();

		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}
	}


	public function postPedidoNetwork(
		$numero_caso,
		$tecnologia,
		$ubicacion,
		$observacion_tecnico,
		$nombre_tecnico,
		$cc_tecnico,
		$numero_contacto,
		$region,
		$clasificador
	)
	{
		try {
			$sql = "insert into network(fecha_ingreso, numero_ticket, tecnologia, ubicacion, observacion_tecnico, nombre_tecnico, cc_tecnico, clasificador, numero_contacto, region)
			VALUES (?,?,?,?,?,?,?,?,?,?);";
			$stmt = $this->_DB->prepare($sql);
			$stmt->execute([
				date('Y-m-d H:i:s'),
				$numero_caso,
				$tecnologia,
				$ubicacion,
				$observacion_tecnico,
				$nombre_tecnico,
				$cc_tecnico,
				$clasificador,
				$numero_contacto,
				$region
			]);

			return $stmt->rowCount();

		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}
	}

	public function getNetworkByUserMass($cc)
	{
		try {

			$stmt = $this->_DB->prepare("SELECT * FROM network where cc_tecnico = :cc AND clasificador = :clasificador");
			$stmt->execute([':cc' => $cc, ':clasificador' => 'cierre_masivo']);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}
	}

	public function getNetworkByUserIndividual($cc)
	{
		try {

			$stmt = $this->_DB->prepare("SELECT * FROM network where cc_tecnico = :cc AND clasificador = :clasificador");
			$stmt->execute([':cc' => $cc, ':clasificador' => 'cierre_individual']);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}
	}

	public function validacionesContingecias()
	{
		try {
			$sql = "SELECT valida, tipo from validaciones_apk";
			$stmt = $this->_DB->prepare($sql);
			$stmt->execute();

			if ($stmt->rowCount()) {
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return 0;
			}

		} catch (PDOException $e) {
			var_dump($e->getMessage());
		}
	}

}
