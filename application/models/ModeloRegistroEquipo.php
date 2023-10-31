<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ModeloRegistroEquipo extends CI_Model
{

    public function postregistroequipos($data)
    {

        try {

            $observacion = $data['observacion'];
            $pedido      = $data['pedido'];
            //$mac_entra      = $data['macentra'];
            $identificacion = $data['doc'];
            $tecnico        = $data['user'];
            $cliente        = $data['cliente'];
            $gis            = $data['gis'];
            $direccion      = $data['direccion'];
            $municipio      = $data['municipio'];
            $sistema        = $data['sistema'];
            $mac            = explode('-', $data['macentra']);

            $mac = array_unique($mac);

            foreach ($mac as $m) {

                $sql   = "INSERT INTO registro_equipo (pedido, tecnico, cc_tecnico, observacion, mac_entra, cliente, gis, direccion, municipio,sistema) VALUES (?,?,?,?,?,?,?,?,?,?);";
                $query = $this->db->query(
                    $sql,
                    array(
                        $pedido,
                        $tecnico,
                        $identificacion,
                        $observacion,
                        $m,
                        $cliente,
                        $gis,
                        $direccion,
                        $municipio,
                        $sistema,
                    )
                );
            }

            $res = ($this->db->affected_rows() > 0) ? 1 : 0;

            return $res;

            $this->db->close();
        } catch (\Throwable $th) {

            $error = $this->db->error();

            return $error;

        }
    }


    public function getregistroequiposbyuser($user_id)
    {
        try {
            $fecha = date('Y-m-d');
            $sql   = "SELECT id, observacion, fecha_ingreso, replace(mac_entra,'-',', ') as mac_entra, pedido FROM registro_equipo WHERE tecnico = ? AND fecha_ingreso BETWEEN '$fecha 00:00:00' AND '$fecha 23:59:59'";
            $query = $this->db->query($sql, array($user_id));

            $res = ($query->num_rows() > 0) ? $query->result_array() : 0;

            return $res;
        } catch (\Throwable $th) {

            $error = $this->db->error();

            return $error;

        }
    }

}
