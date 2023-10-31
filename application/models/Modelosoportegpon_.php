<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

class Modelosoportegpon extends CI_Model
{

	public function getsoportegponbytask($tarea, $fecha)
	{
		try {

			$sql = "SELECT id_soporte, tarea, arpon, nap, hilo, port_internet_1, port_internet_2, port_internet_3, port_internet_4, port_television_1, port_television_2, port_television_3, port_television_4, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion, user_id_firebase, request_id_firebase, user_identification_firebase, status_soporte, fecha_solicitud_firebase, fecha_creado, respuesta_soporte, observacion, login, fecha_respuesta, observacion_terreno
			FROM soporte_gpon 
			WHERE tarea = ? AND fecha_solicitud_firebase >= ? AND status_soporte = 0;";
			$query = $this->db->query($sql, array($tarea, $fecha));

			$res = ($query->num_rows() > 0) ? $query->row_array() : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

	public function getsoportegponbyuser($user_id, $fecha)
	{
		try {

			$sql = "SELECT id_soporte, tarea, unepedido, tasktypecategory, status_soporte, fecha_solicitud_firebase, respuesta_soporte, observacion, fecha_respuesta, observacion_terreno
			FROM soporte_gpon 
			WHERE user_id_firebase = ? AND fecha_solicitud_firebase >= ?;";
			$query = $this->db->query($sql, array($user_id, $fecha));

			$res = ($query->num_rows() > 0) ? $query->result_array() : 0 ;

			return $res;

			$this->db->close();
		} catch (\Throwable $th) {

			$error = $this->db->error();
			die($error);

		}
	}

	public function quitar_tildes($cadena)
	{
		//echo 'recibi cadena'.$cadena;
		$no_permitidas = [
			"á",
			"é",
			"í",
			"ó",
			"ú",
			"Á",
			"É",
			"Í",
			"Ó",
			"Ú",
			"ñ",
			"À",
			"Ã",
			"Ì",
			"Ò",
			"Ù",
			"Ã™",
			"Ã ",
			"Ã¨",
			"Ã¬",
			"Ã²",
			"Ã¹",
			"ç",
			"Ç",
			"Ã¢",
			"ê",
			"Ã®",
			"Ã´",
			"Ã»",
			"Ã‚",
			"ÃŠ",
			"ÃŽ",
			"Ã”",
			"Ã›",
			"ü",
			"Ã¶",
			"Ã–",
			"Ã¯",
			"Ã¤",
			"«",
			"Ò",
			"Ã",
			"Ã„",
			"Ã‹",
		];
		$permitidas    = [
			"a",
			"e",
			"i",
			"o",
			"u",
			"A",
			"E",
			"I",
			"O",
			"U",
			"n",
			"N",
			"A",
			"E",
			"I",
			"O",
			"U",
			"a",
			"e",
			"i",
			"o",
			"u",
			"c",
			"C",
			"a",
			"e",
			"i",
			"o",
			"u",
			"A",
			"E",
			"I",
			"O",
			"U",
			"u",
			"o",
			"O",
			"i",
			"a",
			"e",
			"U",
			"I",
			"A",
			"E",
		];
		$texto         = str_replace($no_permitidas, $permitidas, $cadena);
		return $texto;
	}

	public function postsoportegpon($tarea,$arpon,$nap,$hilo,$internet_port1,$internet_port2,$internet_port3,$internet_port4,$tv_port1,$tv_port2,$tv_port3,$tv_port4,$numero_contaco,$nombre_contaco,$observacion,$user_id,$request_id,$user_identification,$fecha_solicitud,$unepedido,$tasktypecategory,$unemunicipio,$uneproductos,$engineer_id,$engineer_name,$mobile_phone,$velocidad_navegacion,$serials,$macs,$tipoeqs,$planprod)
	{
		try {

			$sql = "INSERT INTO soporte_gpon (tarea, arpon, nap, hilo, port_internet_1, port_internet_2, port_internet_3, port_internet_4, port_television_1, port_television_2, port_television_3, port_television_4, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion, user_id_firebase, request_id_firebase, user_identification_firebase, status_soporte, fecha_solicitud_firebase, fecha_creado, observacion_terreno) 
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
			$query = $this->db->query($sql,
				array(
					$tarea,
					$arpon,
					$nap,
					$hilo,
					$internet_port1,
					$internet_port2,
					$internet_port3,
					$internet_port4,
					$tv_port1,
					$tv_port2,
					$tv_port3,
					$tv_port4,
					$numero_contaco,
					$nombre_contaco,
					$unepedido,
					$tasktypecategory,
					$unemunicipio,
					$uneproductos,
					$planprod,
					$engineer_id,
					$engineer_name,
					$mobile_phone,
					$serials,
					$macs,
					$tipoeqs,
					$velocidad_navegacion,
					$user_id,
					$request_id,
					$user_identification,
					0,
					$fecha_solicitud,
					$fecha_solicitud,
					$this->quitar_tildes($observacion)
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

	public function putsoportegpon($tarea,$arpon,$nap,$hilo,$internet_port1,$internet_port2,$internet_port3,$internet_port4,$tv_port1,$tv_port2,$tv_port3,$tv_port4,$numero_contaco,$nombre_contaco,$observacion,$user_id,$request_id,$user_identification,$fecha_solicitud,$unepedido,$tasktypecategory,$unemunicipio,$uneproductos,$engineer_id,$engineer_name,$mobile_phone,$velocidad_navegacion,$serials,$macs,$tipoeqs,$planprod,$id_soporte)
	{
		try {

			$sql = "UPDATE soporte_gpon SET tarea = ?, arpon = ?, nap = ?, hilo = ?, port_internet_1 = ?, port_internet_2 = ?, port_internet_3 = ?, port_internet_4 = ?, port_television_1 = ?, port_television_2 = ?, port_television_3 = ?, port_television_4 = ?, numero_contacto = ?, nombre_contacto = ?, unepedido = ?, tasktypecategory = ?, unemunicipio = ?, uneproductos = ?, datoscola = ?, engineer_id = ?, engineer_name = ?, mobile_phone = ?, serial = ?, mac = ?, tipo_equipo = ?, velocidad_navegacion = ?, user_id_firebase = ?, request_id_firebase = ?, user_identification_firebase = ?, status_soporte = ?, fecha_solicitud_firebase = ?, fecha_creado = ?, observacion_terreno = ? WHERE id_soporte = ?;";
			$query = $this->db->query($sql,
				array(
					$tarea,
					$arpon,
					$nap,
					$hilo,
					$internet_port1,
					$internet_port2,
					$internet_port3,
					$internet_port4,
					$tv_port1,
					$tv_port2,
					$tv_port3,
					$tv_port4,
					$numero_contaco,
					$nombre_contaco,
					$unepedido,
					$tasktypecategory,
					$unemunicipio,
					$uneproductos,
					$planprod,
					$engineer_id,
					$engineer_name,
					$mobile_phone,
					$serials,
					$macs,
					$tipoeqs,
					$velocidad_navegacion,
					$user_id,
					$request_id,
					$user_identification,
					0,
					$fecha_solicitud,
					$fecha_solicitud,
					$observacion,
					$id_soporte
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
