<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Modeloquejasgo extends CI_Model
{

    public function postsquejasgo($data)
    {
        $fecha = date('Y-m-d');
        $observacion = $data['observacion'];
        $num_ss = $data['data'][0]['SS'];
        $cliente = $data['data'][0]['NOMBRE_CUENTA'];
        $identificacion = $data['data'][0]['IDENTIFICACION'];
        $ceLular = $data['data'][0]['CELULAR'];
        $fijo = $data['data'][0]['FIJO'];
        $numero_cun = $data['data'][0]['NUMERO_CUN'];
        $email = $data['data'][0]['EMAIL'];
        $descripcion_queja = $data['data'][0]['DESCRIPCION'];
        $direccion = $data['data'][0]['DIRECCION'];
        $identificacion_tecnico = $data['identificacion'];
        $nom_tecnico = $data['nombre'];
        try {

            $sql = "SELECT
						*
					FROM
						quejasgo
					WHERE
						pedido = ?
					AND en_gestion != 2
					AND fecha BETWEEN '$fecha 00:00:00' AND '$fecha 23:59:59'";
            $query = $this->db->query($sql,
                array($num_ss)
            );

            $res = ($query->num_rows() > 0) ? $query->result_array() : 0;

            if ($res){
                $res = 3;
            }else{
                $sql = "INSERT INTO quejasgo ( pedido,
                                                cliente,
                                                cedtecnico,
                                                tecnico,
                                                fecha,
                                                duracion,
                                                region,
                                                idllamada,
                                                telefono_contacto,
                                                descripcion_queja,
                           						observacion,
                           						movil_cliente,
                           						doc_cliente,
                           						email_cliente,
                           						direccion,
                           						en_gestion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
                $query = $this->db->query($sql,
                    array($num_ss, $cliente, $identificacion_tecnico, $nom_tecnico, date('Y-m-d H:i:s'), '20', 'MEDELLIN', 'IDLLAMADA', $fijo, $descripcion_queja, $observacion, $ceLular, $identificacion, $email, $direccion, 0)
                );

                $res = ($this->db->affected_rows() > 0) ? 1 : 0 ;
            }

            return $res;

            $this->db->close();
        } catch (\Throwable $th) {

            $error = $this->db->error();
            return $error;

        }
    }


    public function getquejasgobyuser($user_id){
        try {

            $fecha = date('Y-m-d');
            //$fecha = '2023-05-23';
            $sql   = "SELECT
							en_gestion,
							gestion_asesor,
							accion,
							observacion_gestion,
							pedido,
							fecha_gestion,
							id,
							fecha,
							asesor
						FROM
							quejasgo
						WHERE
							fecha BETWEEN '$fecha 00:00:00'
						AND '$fecha 23:59:00'
						AND cedtecnico = ?";
            $query = $this->db->query($sql, array($user_id));

            $res = ($query->num_rows() > 0) ? $query->result_array() : 0;

            return $res;

            $this->db->close();
        } catch (\Throwable $th) {

            $error = $this->db->error();
            die($error);

        }
    }

}
