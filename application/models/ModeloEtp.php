<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
ini_set('display_errors', 0);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ModeloEtp extends CI_Model
{

    public function getEtpByTask($tarea, $fecha)
    {
        try {
            $sql   = "SELECT * FROM etp
                    WHERE tarea = ? AND etp.fecha_crea >= ? AND status_soporte in ('0','1')";
            $query = $this->db->query($sql, array($tarea, $fecha));
            $res   = ($query->num_rows() > 0) ? $query->row_array() : 0;

            return $res;
        } catch (\Throwable $th) {
            $error = $this->db->error();
            die($error);
        }
    }

    public function postregistroETP(
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
        $numero_contacto,
        $nombre_contacto,
        $observacion,
        $unepedido,
        $tasktypecategory,
        $unemunicipio,
        $uneproductos,
        $engineer_id,
        $engineer_name,
        $mobile_phone,
        $serials,
        $macs,
        $tipo,
        $SSID,
        $RTA,
        $RTA2,
        $RTA3,
        $estado_equipo,
        $SerialNoReal,
        $SerialNoReal2,
        $MACReal,
        $MACReal2,
        $replanteo,
        $uneSourceSystem,
        $tecnico_cc_solicita,
        $tecnico_login_solicita,
        $accion,
        $macSale,
        $macEntra,
        $UNETecnologias
    ) {
        try {
            $sql   = "INSERT INTO etp (tarea, arpon, nap, hilo, port_internet_1, port_internet_2, port_internet_3, port_internet_4, port_television_1, 
                port_television_2, port_television_3, port_television_4, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos,
                engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, fecha_crea,
                observacion_terreno,ssdi,rta,rta2,rta3,estado_equipo,serianoreal,serialnoreal2,macreal,macreal2, replanteo, uneSourceSystem, tecnico_cc_solicita, tecnico_login_solicita, accion, macSale,macEntra, UNETecnologias) 
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
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
                    $numero_contacto,
                    $nombre_contacto,
                    $unepedido,
                    $tasktypecategory,
                    $unemunicipio,
                    $uneproductos,
                    $engineer_id,
                    $engineer_name,
                    $mobile_phone,
                    $serials,
                    $macs,
                    $tipo,
                    date('Y-m-d H:i:s'),
                    $observacion,
                    $SSID,
                    $RTA,
                    $RTA2,
                    $RTA3,
                    $estado_equipo,
                    $SerialNoReal,
                    $SerialNoReal2,
                    $MACReal,
                    $MACReal2,
                    $replanteo,
                    $uneSourceSystem,
                    $tecnico_cc_solicita,
                    $tecnico_login_solicita,
                    $accion,
                    $macSale,
                    $macEntra,
                    $UNETecnologias
                )
            );


            $res = ($this->db->affected_rows() > 0) ? 1 : 0;

            return $res;

            $this->db->close();
        } catch (\Throwable $th) {

            $error = $this->db->error();

            return $error;

        }


    }


    public function getsoporteetpbyuser($user_id)
    {
        try {
            $fecha = date('Y-m-d');

            $sql   = "SELECT
                        id_soporte,
                        tarea,
                        unepedido,
                        tasktypecategory,
                    CASE
                            status_soporte 
                            WHEN '2' THEN
                            'Finalizado' ELSE 'Sin gestion' 
                        END AS status_soporte,
                        observacionesGestion AS observacion,
                        observacion_terreno,
                        fecha_gestion,
                        fecha_crea 
                    FROM
                        etp 
                    WHERE tecnico_login_solicita = ? 
                      AND fecha_crea BETWEEN '$fecha 00:00:00' AND '$fecha 23:59:59'";
            $query = $this->db->query($sql, array($user_id));

            $res = ($query->num_rows() > 0) ? $query->result_array() : 0;

            return $res;

            $this->db->close();
        } catch (\Throwable $th) {

            $error = $this->db->error();
            die($error);

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
