<?php
error_reporting(0);
ini_set('display_errors',0);
class Datos
{
	private $Result;
	private $Pedido;
	private $ConsultaB;

	public function __construct($Pedido, $ConsultaB)
	{
		$this->Pedido = $Pedido;
		$this->Result = '';
		$this->ConsultaB = $ConsultaB;
	}

	public function Cablemodem()
	{
		/*verificar que la solicitud sea internet o intenet toip*/

		$validaSara = $this->Pedido[0]['valida'];
		$validaEquipo = $this->Pedido[1]['valida'];
		$this->Pedido = $this->Pedido['pedido1'];

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q2 = "SELECT TOP
                1 wt2.Name 'categoria',
                US.Name 
            FROM
                W6TASKS wt
                LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
                INNER JOIN W6UNESOURCESYSTEMS US ON US.W6Key = wt.UNESourceSystem 
            WHERE
                wt.UNEPedido = '$this->Pedido' 
            ORDER BY
                wt.DispatchDate DESC";

		$resultGpon = $dbc->query($q2);
		$row = array();
		$row = $dbc->fetch_array($resultGpon);

		if ($row['Name'] == 'ETP') {
			return 1510;
		}

		if ($row['categoria'] == 'Aprovisionamiento' || $row['categoria'] == 'Aprovisionamiento BSC' || $row['categoria'] == 'Aseguramiento') {

			$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo
                FROM W6TASKS TK
                INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                WHERE  TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";

			$ressql = $dbc->query($sqlTipo);
			$ressql = odbc_fetch_array($ressql);

			if ($ressql['Tipo'] === 'POE' || $ressql['Tipo'] == 'POE'){

				if ($row['categoria'] == 'Aseguramiento') {

				} else {

					if ($validaEquipo == 'activa') {

						/*consulta equipos*/
						$sql = "select top 1 EQ.RTA, EQ.MAC, TK.UNEPedido
                            FROM W6TASKS TK
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                            INNER JOIN W6TASK_STATUSES STA ON TK.Status=STA.W6Key
                            INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                            INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                            INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                            INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key

                            WHERE TTC.Name IN ('Aprovisionamiento','Aprovisionamiento BSC','Aseguramiento')
                            AND EQ.MAC IS NOT NULL
                            AND EQ.RTA IS NOT NULL
                            AND TK.UNEPedido IN ('$this->Pedido') ORDER BY TK.DispatchDate desc";

						$ressql = $dbc->query($sql);
						$num_rows = odbc_num_rows($ressql);
						if (!$num_rows) {
							return 3200;
						}
						$cadena_buscada = 'Politica';
						//Politica seleccionada TC7300E+MTA_VOZ+TECHNICOLOR no reconocible, intente nuevamente.
						while ($linea = odbc_fetch_array($ressql)) {
							$posicion_coincidencia = strpos($linea['RTA'], $cadena_buscada);
							if ($posicion_coincidencia === true) {
								return 2230;
							}
						}
					}
				}

				if ($validaSara == 'activa') {
					/**
					 * consulta sara quantity
					 */
					$sql2 = "SELECT  TOP 1 la.Name AS Ticket,LU.Quantity, lu.Description
                        FROM W6UNELABORSUSED LU
                        INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                        INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                        INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                        INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                        WHERE LU.TimeCreated > CONVERT (DATE, SYSDATETIME())
                        AND LU.Quantity IN ('81')
                        AND la.Name LIKE('%009%')
                        AND SS.Name = 'POE'
                        AND TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";

					$resultSara = $dbc->query($sql2);
					$num_rows = odbc_num_rows($resultSara);

					if ($num_rows) {

						$sqlValidacion1 = "SELECT TOP 1 la.Name AS Ticket,LU.Quantity, lu.Description
                            FROM W6UNELABORSUSED LU
                            INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                            INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                            INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            WHERE LU.TimeCreated> CONVERT (DATE, SYSDATETIME())
                            AND LU.Quantity IN ('81')
                            AND la.Name LIKE('%009%')
                            AND SS.Name = 'POE'
                            AND lu.Description LIKE ('%Se ejecut%') AND lu.Description NOT LIKE('%pero no se encontr%')
                            AND TK.UNEPedido='$this->Pedido' ORDER BY TK.DispatchDate desc";

						$sqlValidacion2 = "SELECT  TOP 1 la.Name AS Ticket,LU.Quantity, lu.Description
                            FROM W6UNELABORSUSED LU
                            INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                            INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                            INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            WHERE TK.UNEPedido='$this->Pedido' AND LU.TimeCreated> CONVERT (DATE, SYSDATETIME())
                            AND LU.Quantity IN ('81')
                            AND la.Name LIKE('%009%')
                            AND SS.Name = 'POE' AND lu.Description LIKE('%Se ejecut%') AND lu.Description LIKE('%pero no se encontr%')  ORDER BY TK.DispatchDate desc;";

						$sqlValidacion3 = "SELECT  TOP 1 la.Name AS Ticket,LU.Quantity, lu.Description
                            FROM W6UNELABORSUSED LU
                            INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                            INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                            INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            WHERE TK.UNEPedido='$this->Pedido' AND LU.TimeCreated> CONVERT (DATE, SYSDATETIME())
                            AND LU.Quantity IN ('81')
                            AND la.Name LIKE('%009%')
                            AND SS.Name = 'POE'
                            AND (lu.Description LIKE('%El estado actual del Cable%') AND lu.Description LIKE('%con direcci%') AND lu.Description LIKE('%no requiere ser forzado%'))
                            ORDER BY TK.DispatchDate desc";

						$sqlValidacion4 = "SELECT TOP 1 la.Name AS Ticket,LU.Quantity, lu.Description
                            FROM W6UNELABORSUSED LU
                            INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                            INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                            INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            WHERE TK.UNEPedido='$this->Pedido' AND LU.TimeCreated> CONVERT (DATE, SYSDATETIME()) AND LU.Quantity IN ('81') AND la.Name LIKE('%009%') AND SS.Name = 'POE' AND (lu.Description LIKE('%El equipo consultado en el inventario no cumple las condiciones mínimas para ejecutar el Forzar CableModem, Transacción Cancelada.%')
                            OR (lu.Description LIKE('%En la consulta del portafolio no se encontr%') AND lu.Description LIKE ('% producto de internet o telefon%') AND lu.Description LIKE ('%a, o el pedido tiene una PSR cancelada. Comun%'))
                            OR lu.Description LIKE('%La consulta en el inventario arroja un valor vacio para la MAC, comun%')
                            OR lu.Description LIKE('%La consulta en Metasolv con el Pedido/Gis%')
                            OR lu.Description LIKE('%La consulta en Metasolv con IdService no arroja resultados. Comun%')
                            OR (lu.Description LIKE('%Los Equipos del Pedido no cumplen las condiciones m%') AND lu.Description LIKE ('%nimas para forzar cable Modem%'))
                            OR lu.Description LIKE('%Los siguientes elementos no cumplen con los filtros establecidos Status (Finalizada),%'))
                            ORDER BY TK.DispatchDate DESC";

						$sqlValidacion5 = "SELECT TOP 1 la.Name AS Ticket,LU.Quantity, lu.Description
                            FROM W6UNELABORSUSED LU
                            INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                            INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                            INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            WHERE TK.UNEPedido='$this->Pedido' AND LU.TimeCreated> CONVERT (DATE, SYSDATETIME()) AND LU.Quantity IN ('81') AND la.Name LIKE('%009%') AND SS.Name = 'POE' AND lu.Description LIKE('%La tarea con el LaborType: 009 | Forzar Cablemodem ya existe y se encuentra en estado pendiente por procesar%')";

						$queryValidacion1 = $dbc->query($sqlValidacion1);
						$queryValidacion2 = $dbc->query($sqlValidacion2);
						$queryValidacion3 = $dbc->query($sqlValidacion3);
						$queryValidacion4 = $dbc->query($sqlValidacion4);
						$queryValidacion5 = $dbc->query($sqlValidacion5);
						$num_rows1 = odbc_num_rows($queryValidacion1);
						$num_rows2 = odbc_num_rows($queryValidacion2);
						$num_rows3 = odbc_num_rows($queryValidacion3);
						$num_rows4 = odbc_num_rows($queryValidacion4);
						$num_rows5 = odbc_num_rows($queryValidacion5);

						if ($num_rows1) {
							return 401;
						} elseif ($num_rows2) {

						}
						if ($num_rows3) {
							return 403;
						} elseif ($num_rows4) {
							return 404;
						} elseif ($num_rows5) {
							return 405;
						} else {

						}
					} else {
						return 3300;
					}
				}
			} else {
				return 407;
			}

			$q = "
                use [Service Optimization]
                SELECT DISTINCT  top 1
                ENT.Name engineer_Type,
                TK.EngineerID engineerID,
                TK.EngineerName engineerName,
                TK.UNEPedido pEDIDO_UNE,
                TK.CallID tAREA_ID,
                TK.W6Key tASK_ID,
                TK.UNEActividades uNEActividades,
                TK.UNEBarrio uNEBarrio,
                TK.UNECelularContacto uNECelularContacto,
                TK.UNEDepartamento uNEDepartamento,
                TK.UNEDireccion uNEDireccion,
                TK.UNEDireccionComentada uNEDireccionComentada,
                TK.UNEFechaCita uNEFechaCita,
                TK.UNEHoraCita uNEHoraCita,
                TK.UNEFechaIngreso uNEFechaIngreso,
                TK.UNEIdCliente uNEIdCliente,
                TK.UNEMunicipio uNEMunicipio,
                TK.UNENombreCliente uNENombreCliente,
                TK.UNENombreContacto uNENombreContacto,
                TK.UNEPedido uNEPedido,
                TK.UNEProductos uNEProductos,
                TK.UNEProvisioner uNEProvisioner,
                TK.UNETecnologias uNETecnologias,
                Tk.UNEUENcalculada uNEUENcalculada,
                TK.OBS_UNERutaTrabajo uNERutaTrabajo,
                RG.Name uNEUen,
                CASE WHEN TTC.Name LIKE '%Aprovisionamiento%' THEN 'Instalación'
                WHEN TTC.Name LIKE '%Aseguramiento%' THEN 'Reparacion' ELSE 'Otros'
                END AS TaskType,
                TK.DispatchDate,
                LU.Description,
                LU.LaborType,
                EQ.Type,
                EQ.MAC,
                EQ.RTA,
                EQ.RTA3,
                TS.Name Estado,
                TE.LoginName

                FROM W6TASKS TK
                    INNER JOIN W6TASKTYPECATEGORY AS TTC ON TK.tasktypecategory=TTC.W6Key
                    INNER JOIN W6TASK_STATUSES AS TS ON TK.Status = TS.W6Key
                    INNER JOIN W6AREA AS TA ON TK.Area = TA.W6Key
                    INNER JOIN W6TASK_TYPES AS TT ON TK.TaskType = TT.W6Key
                    INNER JOIN W6DISTRICTS TDT ON TK.District=TDT.W6Key
                    INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                    INNER JOIN W6ENGINEERS TE ON TK.EngineerID = TE.ID
                    INNER JOIN W6CALENDARS AS TC ON TE.Calendar = TC.W6Key
                    /*INNER JOIN W6ENGINEERS_SKILLS ENSK ON ENSK.W6Key = TE.W6Key
                    INNER JOIN W6SKILLS SK ON ENSK.SkillKey = SK.W6Key*/
                    LEFT JOIN W6ENGINEER_TYPES ENT ON ENT.W6Key=TE.EngineerType
                    LEFT JOIN W6UNELABORSUSED AS LU ON LU.TaskCallID = TK.CallID OR LU.TaskCallID LIKE '%TK.UNEPedido%'
                    LEFT JOIN W6UNEEQUIPMENTUSED AS EQ ON EQ.TaskCallID = TK.CallID
                WHERE TK.UNEPedido='$this->Pedido'
                /*and TK.DispatchDate > DATEADD(day, -1, CONVERT (date, SYSDATETIME()))
                and TK.DispatchDate < DATEADD(day, +1, CONVERT (date, SYSDATETIME()))*/
                order by TK.DispatchDate desc;";


			$resultset = $dbc->query($q);
			$resultados = array();
			$i = 0;
			while ($linea = $dbc->fetch_array($resultset)) {
				$resultados[] = $linea;

				array_walk_recursive($resultados, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
			}
			$this->Result = $resultados;
			return ($resultados);
		}
	}

	public function GetClick()
	{
		$validaSara = $this->Pedido[0]['valida'];
		$validaEquipo = $this->Pedido[1]['valida'];
		$this->Pedido = $this->Pedido['pedido'];



		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q2 = "SELECT TOP
            1 wt2.Name 'categoria',
            US.Name 
        FROM
            W6TASKS wt
            LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
            INNER JOIN W6UNESOURCESYSTEMS US ON US.W6Key = wt.UNESourceSystem 
        WHERE
            wt.UNEPedido = '$this->Pedido' 
        ORDER BY
            wt.DispatchDate DESC";

		$resultGpon = $dbc->query($q2);
		$row = array();
		$row = $dbc->fetch_array($resultGpon);

		if ($row['Name'] == 'ETP') {
			return 1510;
		}

		if ($row['categoria'] == 'Aprovisionamiento' || $row['categoria'] == 'Aprovisionamiento BSC' || $row['categoria'] == 'Aseguramiento') {

			$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo
                FROM W6TASKS TK
                INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                WHERE  TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";

			$ressql = $dbc->query($sqlTipo);
			$ressql = odbc_fetch_array($ressql);

			if ($ressql['Tipo'] === 'ELT' || $ressql['Tipo'] == 'ELT') {


				$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo, TT.name AS TaskType
                    FROM W6TASKS TK
                    INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                    INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                    WHERE  TK.UNEPedido = '$this->Pedido' AND (TT.Name LIKE ('%Cambio_Domicilio%') OR TT.Name LIKE ('%Extension HFC%')) ORDER BY TK.DispatchDate desc";

				$ressql = $dbc->query($sqlTipo);
				$num_rows = odbc_num_rows($ressql);

				if ($num_rows || $row['categoria'] == 'Aseguramiento') {

				} else {
					if ($validaEquipo == 'activa') {

						/*consulta equipos*/
						$sql = "select top 1 EQ.RTA, EQ.MAC, TK.UNEPedido
                            FROM W6TASKS TK
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                            INNER JOIN W6TASK_STATUSES STA ON TK.Status=STA.W6Key
                            INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                            INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                            INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                            INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
    
                            WHERE TTC.Name IN ('Aprovisionamiento','Aprovisionamiento BSC')
                            AND EQ.MAC IS NOT NULL
                            AND EQ.RTA IS NOT NULL
                            AND TK.UNEPedido IN ('$this->Pedido') ORDER BY TK.DispatchDate desc";

						$ressql = $dbc->query($sql);
						$num_rows = odbc_num_rows($ressql);
						if (!$num_rows) {
							return 3200;
						}
						$cadena_buscada = 'Politica';
						//Politica seleccionada TC7300E+MTA_VOZ+TECHNICOLOR no reconocible, intente nuevamente.
						while ($linea = odbc_fetch_array($ressql)) {
							$posicion_coincidencia = strpos($linea['RTA'], $cadena_buscada);
							if ($posicion_coincidencia === true) {
								return 2230;
							}
						}
					}
				}

			} else {
				$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo, TT.name AS TaskType
                    FROM W6TASKS TK
                    INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                    INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                    WHERE  TK.UNEPedido = '$this->Pedido' AND (TT.Name LIKE ('%Cambio_Domicilio%') OR TT.Name LIKE ('%Extension HFC%')) AND (TK.UNEActividades = 'Movimiento Interno Red') ORDER BY TK.DispatchDate desc";

				$ressql = $dbc->query($sqlTipo);
				$num_rows = odbc_num_rows($ressql);

				if ($num_rows || $row['categoria'] == 'Aseguramiento') {

				} else {
					if ($validaEquipo == 'activa') {

						/*consulta equipos*/
						$sql = "select top 1 EQ.RTA, EQ.MAC, TK.UNEPedido
                            FROM W6TASKS TK
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                            INNER JOIN W6TASK_STATUSES STA ON TK.Status=STA.W6Key
                            INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                            INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                            INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                            INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key

                            WHERE TTC.Name IN ('Aprovisionamiento','Aprovisionamiento BSC')
                            AND EQ.MAC IS NOT NULL
                            AND EQ.RTA IS NOT NULL
                            AND TK.UNEPedido IN ('$this->Pedido') ORDER BY TK.DispatchDate desc";

						$ressql = $dbc->query($sql);
						$num_rows = odbc_num_rows($ressql);
						if (!$num_rows) {
							return 3200;
						}
						$cadena_buscada = 'Politica';
						//Politica seleccionada TC7300E+MTA_VOZ+TECHNICOLOR no reconocible, intente nuevamente.
						while ($linea = odbc_fetch_array($ressql)) {
							$posicion_coincidencia = strpos($linea['RTA'], $cadena_buscada);
							if ($posicion_coincidencia === true) {
								return 2230;
							}
						}

					}
				}

				if ($validaSara == 'activa' && $row['categoria'] != 'Aseguramiento') {
					//if ($validaSara == 'activa') {
					/**
					 * consulta sara quantity
					 */

					$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo
                        FROM W6TASKS TK
                        INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                        WHERE  TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";

					$ressql = $dbc->query($sqlTipo);
					$ressql = odbc_fetch_array($ressql);

					if ($ressql['Tipo'] === 'POE' || $ressql['Tipo'] == 'POE') {

						$sql2 = "SELECT TOP 1 la.Name AS Ticket,LU.Quantity, la.Name, lu.Description
                            FROM W6UNELABORSUSED LU
                            INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                            INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                            INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key=TK.TaskTypeCategory
                            INNER JOIN W6REGIONS RG ON RG.W6Key=TK.Region
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            -- INNER JOIN W6UNELABORTYPE_TASKTYPECATEGORY LA_T ON
                            -- INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key=LA_T.TaskTypeCategoryItem
                            WHERE LU.TimeCreated> CONVERT (DATE, SYSDATETIME())
                            -- AND TTC.NAME = 'Aseguramiento'
                            AND LU.Quantity IN ('81')
                            AND (la.Name LIKE ('%003%')
                            OR la.Name LIKE('%004%')
                            OR la.Name LIKE('%001%')
                            OR la.Name LIKE('%009%'))
                            AND SS.Name = 'POE'
                            AND TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";


						$resultSara = $dbc->query($sql2);
						$row = $dbc->fetch_array($resultSara);
						$num_rows = odbc_num_rows($resultSara);
						if ($num_rows) {
							if (intval($row['Quantity']) != 81) {
								return 3300;
							}
						} else {
							return 3300;
						}

						$sqlTicket = "SELECT TOP 1 la.Name
                            FROM W6UNELABORSUSED LU
                            INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                            INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                            INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key=TK.TaskTypeCategory
                            INNER JOIN W6REGIONS RG ON RG.W6Key=TK.Region
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            WHERE LU.TimeCreated> CONVERT (DATE, SYSDATETIME())
                            AND LU.Quantity IN ('81')
                            AND (la.Name LIKE ('%003%')
                            OR la.Name LIKE('%004%')
                            OR la.Name LIKE('%001%')
                            OR la.Name LIKE('%009%'))
                            AND SS.Name = 'POE'
                            AND TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";

						$resultSara = $dbc->query($sqlTicket);
						$row = $dbc->num_rows($resultSara);

						if (!$row) {
							return 3300;
						}
					}
				}
			}

			$q = "
                use [Service Optimization]
                SELECT DISTINCT  top 1
                ENT.Name engineer_Type,
                TK.EngineerID engineerID,
                TK.EngineerName engineerName,
                TK.UNEPedido pEDIDO_UNE,
                TK.CallID tAREA_ID,
                TK.W6Key tASK_ID,
                TK.UNEActividades uNEActividades,
                TK.UNEBarrio uNEBarrio,
                TK.UNECelularContacto uNECelularContacto,
                TK.UNEDepartamento uNEDepartamento,
                TK.UNEDireccion uNEDireccion,
                TK.UNEDireccionComentada uNEDireccionComentada,
                TK.UNEFechaCita uNEFechaCita,
                TK.UNEHoraCita uNEHoraCita,
                TK.UNEFechaIngreso uNEFechaIngreso,
                TK.UNEIdCliente uNEIdCliente,
                TK.UNEMunicipio uNEMunicipio,
                TK.UNENombreCliente uNENombreCliente,
                TK.UNENombreContacto uNENombreContacto,
                TK.UNEPedido uNEPedido,
                TK.UNEProductos uNEProductos,
                TK.UNEProvisioner uNEProvisioner,
                TK.UNETecnologias uNETecnologias,
                Tk.UNEUENcalculada uNEUENcalculada,
                TK.OBS_UNERutaTrabajo uNERutaTrabajo,
                RG.Name uNEUen,
                CASE WHEN TTC.Name LIKE '%Aprovisionamiento%' THEN 'Instalación'
                WHEN TTC.Name LIKE '%Aseguramiento%' THEN 'Reparacion' ELSE 'Otros'
                END AS TaskType,
                TK.DispatchDate,
                LU.Description,
                LU.LaborType,
                EQ.Type,
                EQ.MAC,
                EQ.RTA,
                EQ.RTA3,
                TS.Name Estado,
                TE.LoginName,
                SS.Name SitemaOrigen

                FROM W6TASKS TK
                    INNER JOIN W6TASKTYPECATEGORY AS TTC ON TK.tasktypecategory=TTC.W6Key
                    INNER JOIN W6TASK_STATUSES AS TS ON TK.Status = TS.W6Key
                    INNER JOIN W6AREA AS TA ON TK.Area = TA.W6Key
                    INNER JOIN W6TASK_TYPES AS TT ON TK.TaskType = TT.W6Key
                    INNER JOIN W6DISTRICTS TDT ON TK.District=TDT.W6Key
                    INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                    INNER JOIN W6ENGINEERS TE ON TK.EngineerID = TE.ID
                    INNER JOIN W6CALENDARS AS TC ON TE.Calendar = TC.W6Key
                    /*INNER JOIN W6ENGINEERS_SKILLS ENSK ON ENSK.W6Key = TE.W6Key
                    INNER JOIN W6SKILLS SK ON ENSK.SkillKey = SK.W6Key*/
                    INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                    LEFT JOIN W6ENGINEER_TYPES ENT ON ENT.W6Key=TE.EngineerType
                    LEFT JOIN W6UNELABORSUSED AS LU ON LU.TaskCallID = TK.CallID OR LU.TaskCallID LIKE '%TK.UNEPedido%'
                    LEFT JOIN W6UNEEQUIPMENTUSED AS EQ ON EQ.TaskCallID = TK.CallID
                WHERE TK.UNEPedido='$this->Pedido'
                /*and TK.DispatchDate > DATEADD(day, -1, CONVERT (date, SYSDATETIME()))
                and TK.DispatchDate < DATEADD(day, +1, CONVERT (date, SYSDATETIME()))*/
                order by TK.DispatchDate desc;";


			$resultset = $dbc->query($q);
			$resultados = array();
			$i = 0;
			while ($linea = $dbc->fetch_array($resultset)) {
				$resultados[] = $linea;

				array_walk_recursive($resultados, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
			}
			$this->Result = $resultados;
			return ($resultados);
		} else {
			return 3001;
		}
	}

	public function cambioEquipos(){

		$validaSara = $this->Pedido[0]['valida'];
		$validaEquipo = $this->Pedido[1]['valida'];
		$tipoProducto = $this->Pedido['tipoProducto'];
		$this->Pedido = $this->Pedido['pedido2'];




		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q2 = "SELECT TOP 1
		wt2.Name 'categoria'
        FROM W6TASKS wt
        LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        WHERE wt.UNEPedido = '$this->Pedido' order by wt.DispatchDate desc";

		$resultGpon = $dbc->query($q2);
		$row = array();
		$row = $dbc->fetch_array($resultGpon);

		$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo
                FROM W6TASKS TK
                INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                WHERE  TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";

		$ressql = $dbc->query($sqlTipo);
		$ressql = odbc_fetch_array($ressql);

		if ($ressql['Tipo'] === 'ELT' || $ressql['Tipo'] == 'ELT') {


			$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo, TT.name AS TaskType
                    FROM W6TASKS TK
                    INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                    INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                    WHERE  TK.UNEPedido = '$this->Pedido' AND (TT.Name LIKE ('%Cambio_Domicilio%') OR TT.Name LIKE ('%Extension HFC%')) ORDER BY TK.DispatchDate desc";

			$ressql = $dbc->query($sqlTipo);
			$num_rows = odbc_num_rows($ressql);

			if ($num_rows) {

			} else {
				if ($validaEquipo == 'activa') {

					/*consulta equipos*/
					$sql = "select top 1 EQ.RTA3, EQ.MACReal,EQ.MACReal2, TK.UNEPedido
                            FROM W6TASKS TK
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                            INNER JOIN W6TASK_STATUSES STA ON TK.Status=STA.W6Key
                            INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                            INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                            INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                            INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
                            WHERE TTC.Name IN ('Aseguramiento')
                            AND (EQ.MACReal IS NOT NULL
                            AND EQ.MACReal2 IS NOT NULL
                            AND EQ.RTA3 IS NOT NULL) OR TK.UNEMarked = 'Amarilla'
                            AND TK.UNEPedido IN ('$this->Pedido') ORDER BY TK.DispatchDate desc";

					$ressql = $dbc->query($sql);
					$num_rows = odbc_num_rows($ressql);
					if (!$num_rows) {
						return 3201;
					}
					$cadena_buscada = 'Politica';
					//Politica seleccionada TC7300E+MTA_VOZ+TECHNICOLOR no reconocible, intente nuevamente.
					while ($linea = odbc_fetch_array($ressql)) {
						$posicion_coincidencia = strpos($linea['RTA3'], $cadena_buscada);
						if ($posicion_coincidencia === true) {
							return 2230;
						}
					}
				}
			}

		} else {
			$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo, TT.name AS TaskType
                    FROM W6TASKS TK
                    INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                    INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                    WHERE  TK.UNEPedido = '$this->Pedido' AND (TT.Name LIKE ('%Cambio_Domicilio%') OR TT.Name LIKE ('%Extension HFC%')) AND (TK.UNEActividades = 'Movimiento Interno Red') ORDER BY TK.DispatchDate desc";

			$ressql = $dbc->query($sqlTipo);
			$num_rows = odbc_num_rows($ressql);

			if ($num_rows) {

			} else {
				if ($validaEquipo == 'activa') {

					/*consulta equipos*/
					$sql = "select top 1 EQ.RTA3, EQ.MACReal,EQ.MACReal2, TK.UNEPedido
                            FROM W6TASKS TK
                            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                            INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                            INNER JOIN W6TASK_STATUSES STA ON TK.Status=STA.W6Key
                            INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                            INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
                            INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                            INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key

                            WHERE TTC.Name IN ('Aseguramiento')
                            AND (EQ.MACReal IS NOT NULL
                            AND EQ.MACReal2 IS NOT NULL
                            AND EQ.RTA3 IS NOT NULL) OR TK.UNEMarked = 'Amarilla'
                            AND TK.UNEPedido IN ('$this->Pedido') ORDER BY TK.DispatchDate desc";

					$ressql = $dbc->query($sql);

					$num_rows = odbc_num_rows($ressql);
					if (!$num_rows) {
						return 3201;
					}
					$cadena_buscada = 'Politica';
					//Politica seleccionada TC7300E+MTA_VOZ+TECHNICOLOR no reconocible, intente nuevamente.
					while ($linea = odbc_fetch_array($ressql)) {
						$posicion_coincidencia = strpos($linea['RTA3'], $cadena_buscada);
						if ($posicion_coincidencia === true) {
							return 2230;
						}
					}

				}
			}

			if ($validaSara == 'activa') {
				/**
				 * consulta sara quantity
				 */

				$sqlTipo = "SELECT TOP 1 SS.Name AS Tipo
                        FROM W6TASKS TK
                        INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                        WHERE  TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";

				$ressql = $dbc->query($sqlTipo);
				$ressql = odbc_fetch_array($ressql);

				if ($ressql['Tipo'] === 'POE' || $ressql['Tipo'] == 'POE') {

					if($tipoProducto == 'TV'){
						$sql2 = "SELECT TOP 1 la.Name AS Ticket,LU.Quantity, la.Name, lu.Description
                                FROM W6UNELABORSUSED LU
                                INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                                INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                                INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                                INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key=TK.TaskTypeCategory
                                INNER JOIN W6REGIONS RG ON RG.W6Key=TK.Region
                                INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                                WHERE LU.TimeCreated> CONVERT (DATE, SYSDATETIME())
                                AND LU.Quantity IN ('81')
                                AND (la.Name LIKE('%004%'))
                                AND SS.Name = 'POE'
                                AND TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";
					}else if($tipoProducto == 'Internet' || $tipoProducto == 'Internet+ToIP'|| $tipoProducto == 'Internet+Toip' || $tipoProducto == 'Toip' || $tipoProducto == 'ToIP'){
						$sql2 = "SELECT TOP 1 la.Name AS Ticket,LU.Quantity, la.Name, lu.Description
                                FROM W6UNELABORSUSED LU
                                INNER JOIN W6TASKS_UNELABORSUSED T_L ON LU.W6Key=T_L.LaborsUsed
                                INNER JOIN W6UNELABORTYPE la ON LU.LaborType=la.W6Key
                                INNER JOIN W6TASKS TK ON T_L.W6Key=TK.W6Key
                                INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key=TK.TaskTypeCategory
                                INNER JOIN W6REGIONS RG ON RG.W6Key=TK.Region
                                INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                                WHERE LU.TimeCreated> CONVERT (DATE, SYSDATETIME())
                                AND LU.Quantity IN ('81')
                                AND (la.Name LIKE ('%003%'))
                                AND SS.Name = 'POE'
                                AND TK.UNEPedido = '$this->Pedido' ORDER BY TK.DispatchDate desc";
					}



					$resultSara = $dbc->query($sql2);
					$row = $dbc->fetch_array($resultSara);
					$num_rows = odbc_num_rows($resultSara);
					if ($num_rows) {
						if (intval($row['Quantity']) != 81) {
							return 3300;
						}
					} else {
						return 3300;
					}

				}
			}
		}

		$q = "
                use [Service Optimization]
                SELECT DISTINCT  top 1
                ENT.Name engineer_Type,
                TK.EngineerID engineerID,
                TK.EngineerName engineerName,
                TK.UNEPedido pEDIDO_UNE,
                TK.CallID tAREA_ID,
                TK.W6Key tASK_ID,
                TK.UNEActividades uNEActividades,
                TK.UNEBarrio uNEBarrio,
                TK.UNECelularContacto uNECelularContacto,
                TK.UNEDepartamento uNEDepartamento,
                TK.UNEDireccion uNEDireccion,
                TK.UNEDireccionComentada uNEDireccionComentada,
                TK.UNEFechaCita uNEFechaCita,
                TK.UNEHoraCita uNEHoraCita,
                TK.UNEFechaIngreso uNEFechaIngreso,
                TK.UNEIdCliente uNEIdCliente,
                TK.UNEMunicipio uNEMunicipio,
                TK.UNENombreCliente uNENombreCliente,
                TK.UNENombreContacto uNENombreContacto,
                TK.UNEPedido uNEPedido,
                TK.UNEProductos uNEProductos,
                TK.UNEProvisioner uNEProvisioner,
                TK.UNETecnologias uNETecnologias,
                Tk.UNEUENcalculada uNEUENcalculada,
                TK.OBS_UNERutaTrabajo uNERutaTrabajo,
                RG.Name uNEUen,
                CASE WHEN TTC.Name LIKE '%Aprovisionamiento%' THEN 'Instalación'
                WHEN TTC.Name LIKE '%Aseguramiento%' THEN 'Reparacion' ELSE 'Otros'
                END AS TaskType,
                TK.DispatchDate,
                LU.Description,
                LU.LaborType,
                EQ.Type,
                EQ.MAC,
                EQ.RTA,
                EQ.RTA3,
                TS.Name Estado,
                TE.LoginName,
                SS.Name SitemaOrigen

                FROM W6TASKS TK
                    INNER JOIN W6TASKTYPECATEGORY AS TTC ON TK.tasktypecategory=TTC.W6Key
                    INNER JOIN W6TASK_STATUSES AS TS ON TK.Status = TS.W6Key
                    INNER JOIN W6AREA AS TA ON TK.Area = TA.W6Key
                    INNER JOIN W6TASK_TYPES AS TT ON TK.TaskType = TT.W6Key
                    INNER JOIN W6DISTRICTS TDT ON TK.District=TDT.W6Key
                    INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                    INNER JOIN W6ENGINEERS TE ON TK.EngineerID = TE.ID
                    INNER JOIN W6CALENDARS AS TC ON TE.Calendar = TC.W6Key
                    /*INNER JOIN W6ENGINEERS_SKILLS ENSK ON ENSK.W6Key = TE.W6Key
                    INNER JOIN W6SKILLS SK ON ENSK.SkillKey = SK.W6Key*/
                    INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
                    LEFT JOIN W6ENGINEER_TYPES ENT ON ENT.W6Key=TE.EngineerType
                    LEFT JOIN W6UNELABORSUSED AS LU ON LU.TaskCallID = TK.CallID OR LU.TaskCallID LIKE '%TK.UNEPedido%'
                    LEFT JOIN W6UNEEQUIPMENTUSED AS EQ ON EQ.TaskCallID = TK.CallID
                WHERE TK.UNEPedido='$this->Pedido'
                /*and TK.DispatchDate > DATEADD(day, -1, CONVERT (date, SYSDATETIME()))
                and TK.DispatchDate < DATEADD(day, +1, CONVERT (date, SYSDATETIME()))*/
                order by TK.DispatchDate desc;";


		$resultset = $dbc->query($q);
		$resultados = array();
		$i = 0;
		while ($linea = $dbc->fetch_array($resultset)) {
			$resultados[] = $linea;

			array_walk_recursive($resultados, function (&$item, $key) {
				if (!mb_detect_encoding($item, 'utf-8', true)) {
					$item = utf8_encode($item);
				}
			});
		}
		$this->Result = $resultados;
		return ($resultados);
	}

	private function utf8_converter($array){
		array_walk_recursive($array, function (&$item, $key) {
			if (!mb_detect_encoding($item, 'utf-8', true)) {
				$item = utf8_encode($item);
			}
		});
		return $array;
	}

	public function GetClickSoporteGpon($task)
	{
		$validaInfraestructura = $task[2]['valida'];
		$infra = $task['infra'];
		$task = $task['tarea'];

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q2 = "SELECT
		wt2.Name 'categoria', wts.Name 'status'
        FROM W6TASKS wt
        INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
        LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        WHERE wt.CallID = '$task'";

		$resultGpon = $dbc->query($q2);
		$row = $dbc->fetch_array($resultGpon);

		$pedidoCont = substr($task,0,4);
		//$resultados = array();

		if(!$row){
			return 3145;
		}

		if($row['status'] != 'En Sitio'){
			return 3552;
		}

		if ($row['categoria'] == 'Aprovisionamiento' || $row['categoria'] == 'Aprovisionamiento BSC' || $row['categoria'] == 'Aseguramiento' || $row['categoria'] == 'Aseguramiento B2B') {
			$tecnologia = "SELECT wt.UNETecnologias
            FROM W6TASKS wt
            WHERE wt.CallID = '$task' AND wt.UNETecnologias LIKE '%GPON%'";

			$resTecnologia = $dbc->query($tecnologia);
			$num_rows = odbc_num_rows($resTecnologia);

			if ($num_rows) {

				if ($infra == '1'){
					$num_rows1 = 1;
				}else{
					if($row['categoria'] == 'Aseguramiento'){
						$query = "SELECT  TOP 1 TK.UNEPedido, RS.Name as estado_equipo
                        FROM W6TASKS TK
                        FULL JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                        FULL JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
                        FULL JOIN W6UNEEQREPAIRSTATUS RS ON EQ.RepairStatus=RS.W6Key
                        WHERE TK.CallID IN ('$task') ORDER BY TK.DispatchDate DESC";

						$resTecnologia = $dbc->query($query);
						$result = $dbc->fetch_array($resTecnologia);

						if($result['estado_equipo'] === 'Reemplazado'){
							$query = "SELECT TOP 1 TK.UNEPedido,  EQ.SerialNo, EQ.MAC, EQ.SerialNoReal, EQ.MACReal, EQ.SerialNoReal2, EQ.MACReal2, EQ.SSID, EQ.UNEPassword, EQ.RTA, EQ.RTA2, EQ.RTA3, EQ.EQProducto
                            FROM W6TASKS TK
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                            INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                            INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
                            
                            WHERE EQ.SerialNoReal IS NOT NULL
                            AND EQ.MACReal IS NOT NULL
                            AND EQ.SerialNoReal2 IS NOT NULL
                            AND EQ.MACReal2 IS NOT NULL
                            AND TK.CallID = '$task'  ORDER BY TK.DispatchDate DESC;";
							$resTecnologia = $dbc->query($query);
							$num_rows1 = odbc_num_rows($resTecnologia);
						}else{
							$num_rows1 = 1;
						}

					}else{

						$query = "SELECT TOP 1 EQ.RTA, EQ.MAC, TK.UNEPedido
                        FROM W6TASKS TK
                        INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                        INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                        INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
                        WHERE EQ.MAC IS NOT NULL
                        AND EQ.RTA IS NOT NULL
                        AND TK.CallID = '$task'  ORDER BY TK.DispatchDate DESC;";
						$resTecnologia = $dbc->query($query);
						$num_rows1 = odbc_num_rows($resTecnologia);
					}
				}

				if(!$num_rows1){
					return 1010;
				}

				$q = "use [Service Optimization]
                SELECT
                wt.UNEPedido,
                wt2.Name 'categoria',
                wt.UNEMunicipio,
                wt.UNEProductos,
                wt.EngineerID,
                wt.EngineerName,
                we.MobilePhone,
                wu2.SerialNo,
                wu2.MAC,
                wu2.TipoEquipo,
                wu3.VelocidadNavegacion,
                wts.Name 'status',
                wu.UNEPlanProducto,
                wu.DatosCola1,
                US.Name,
                CASE
                    AR.Name 
                    WHEN 'Noroccidente' THEN
                    'Andina' 
                    WHEN 'Oriente' THEN
                    'Andina' 
                    WHEN 'Eje cafetero' THEN
                    'Occidente' 
                    WHEN 'Sur' THEN
                    'Occidente' ELSE AR.Name 
                END AS 'Area',
                TT.Name TaskType 
                FROM W6TASKS wt
                INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
                LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
                LEFT JOIN W6UNESERVICES wu ON wu.ParentTaskCallID = wt.CallID
                LEFT JOIN W6ENGINEERS we ON we.ID = wt.EngineerID
                LEFT JOIN W6UNEEQUIPMENTUSED wu2 ON wu2.TaskCallID = wt.CallID
                LEFT JOIN W6UNESERVICES wu3 ON wu3.ParentTaskCallID = wt.CallID
                INNER JOIN W6UNESOURCESYSTEMS US ON US.W6Key=wt.UNESourceSystem
                INNER JOIN W6TASK_TYPES TT ON wt.TaskType= TT.W6Key
                        INNER JOIN W6AREA AS AR ON AR.W6Key = wt.Area 
                WHERE wt.CallID = '$task'  AND wts.Name = 'En Sitio' /*AND wu2.TipoEquipo IS NOT NULL AND wu3.VelocidadNavegacion IS NOT NULL*/
                GROUP BY wt.UNEPedido, wt2.Name, wt.UNEMunicipio, wt.UNEProductos, wt.EngineerID, wt.EngineerName, we.MobilePhone, wu2.SerialNo, wu2.MAC, wu2.TipoEquipo, wu3.VelocidadNavegacion, wts.Name, wu.UNEPlanProducto, wu.DatosCola1, US.Name, AR.Name,
                TT.Name;";
				$resultset = $dbc->query($q);
				$i = 0;
				$data = [];
				while ($row = odbc_fetch_array($resultset)) {
					if ($row['Name'] != 'POE') {
						return 2387;;
					}
					$data[] = $row;
				}
				$resultados = $this->utf8_converter($data);
			} else {

				if ($validaInfraestructura != 'activa') {
					$num_rows = 1;
				}else{

					if($pedidoCont=='CONT'){
						$tecnologia2 = "SELECT wt.CallID, wt.UNEPedido, wt.UNEFechaCita
                        FROM W6TASKS wt
                        -- INNER JOIN W6TASKS_UNESERVICES wu ON wt.W6Key = wu.W6Key
                        -- INNER JOIN W6UNESERVICES SERV ON wu.UNEService = SERV.W6Key
                        WHERE wt.CallID = '$task'";
						$resTecnologia = $dbc->query($tecnologia2);
						$num_rows = odbc_num_rows($resTecnologia);
					}else{
						$fecha = date('Y-m-d');
						$tecnologia2 = "SELECT wt.CallID, wt.UNEPedido, wt.UNEFechaCita, SERV.Infraestructura1
                        FROM W6TASKS wt
                        INNER JOIN W6TASKS_UNESERVICES wu ON wt.W6Key = wu.W6Key
                        INNER JOIN W6UNESERVICES SERV ON wu.UNEService = SERV.W6Key
                        WHERE wt.CallID = '$task' AND ((wt.UNETecnologias LIKE ('%REDCO%') OR (wt.UNETecnologias LIKE ('%HFC%'))) AND (SERV.Infraestructura1 LIKE ('%ARPON%') OR SERV.Infraestructura1 LIKE ('%OLT%'))) OR wt.UNEMarked = 'Amarilla' AND wt.UNEFechaCita = '$fecha' ";

						$resTecnologia = $dbc->query($tecnologia2);
						$num_rows = odbc_num_rows($resTecnologia);
					}
				}

				if ($num_rows > 0) {
					$query = "SELECT
                    wt.UNEPedido,
                    wt2.Name 'categoria',
                    wt.UNEMunicipio,
                    wt.UNEProductos,
                    wt.EngineerID,
                    wt.EngineerName,
                    we.MobilePhone,
                    wu2.SerialNo,
                    wu2.MAC,
                    wu2.TipoEquipo,
                    wu3.VelocidadNavegacion,
                    wts.Name 'status',
                    wu.UNEPlanProducto,
                    wu.DatosCola1,
                    US.Name,
                    CASE
                        AR.Name 
                        WHEN 'Noroccidente' THEN
                        'Andina' 
                        WHEN 'Oriente' THEN
                        'Andina' 
                        WHEN 'Eje cafetero' THEN
                        'Occidente' 
                        WHEN 'Sur' THEN
                        'Occidente' ELSE AR.Name 
                    END AS 'Area',
                    TT.Name TaskType 
                FROM
                    W6TASKS wt
                    INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
                    LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
                    LEFT JOIN W6UNESERVICES wu ON wu.ParentTaskCallID = wt.CallID
                    LEFT JOIN W6ENGINEERS we ON we.ID = wt.EngineerID
                    LEFT JOIN W6UNEEQUIPMENTUSED wu2 ON wu2.TaskCallID = wt.CallID
                    LEFT JOIN W6UNESERVICES wu3 ON wu3.ParentTaskCallID = wt.CallID
                    INNER JOIN W6UNESOURCESYSTEMS US ON US.W6Key= wt.UNESourceSystem
                    INNER JOIN W6TASK_TYPES TT ON wt.TaskType= TT.W6Key
                    INNER JOIN W6AREA AS AR ON AR.W6Key = wt.Area 
                WHERE
                    wt.CallID = '$task' 
                    AND wts.Name = 'En Sitio' 
                GROUP BY
                    wt.UNEPedido,
                    wt2.Name,
                    wt.UNEMunicipio,
                    wt.UNEProductos,
                    wt.EngineerID,
                    wt.EngineerName,
                    we.MobilePhone,
                    wu2.SerialNo,
                    wu2.MAC,
                    wu2.TipoEquipo,
                    wu3.VelocidadNavegacion,
                    wts.Name,
                    wu.UNEPlanProducto,
                    wu.DatosCola1,
                    US.Name,
                    AR.Name,
                    TT.Name;";
					$resultset = $dbc->query($query);
					$data = [];
					while ($row = odbc_fetch_array($resultset)) {
						if ($row['Name'] != 'POE') {
							return 2387;;
						}
						$data[] = $row;
					}
					$resultados = $this->utf8_converter($data);
				}else {
					return 6562;
				}

				if ($infra == '1'){
					$num_rows = 1;
				}else{
					if($row['categoria'] == 'Aseguramiento'){
						$query = "SELECT  TOP 1 TK.UNEPedido, RS.Name as estado_equipo
                        FROM W6TASKS TK
                        FULL JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                        FULL JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
                        FULL JOIN W6UNEEQREPAIRSTATUS RS ON EQ.RepairStatus=RS.W6Key
                        WHERE TK.CallID IN ('$task') ORDER BY TK.DispatchDate DESC";

						$resTecnologia = $dbc->query($query);
						$result = $dbc->fetch_array($resTecnologia);

						if($result['estado_equipo'] == 'Reemplazado'){
							$query = "SELECT TOP 1 TK.UNEPedido,  EQ.SerialNo, EQ.MAC, EQ.SerialNoReal, EQ.MACReal, EQ.SerialNoReal2, EQ.MACReal2, EQ.SSID, EQ.UNEPassword, EQ.RTA, EQ.RTA2, EQ.RTA3, EQ.EQProducto
                            FROM W6TASKS TK
                            INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                            INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                            INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
                            
                            WHERE EQ.SerialNoReal IS NOT NULL
                            AND EQ.MACReal IS NOT NULL
                            AND EQ.SerialNoReal2 IS NOT NULL
                            AND EQ.MACReal2 IS NOT NULL
                            AND TK.CallID = '$task'  ORDER BY TK.DispatchDate DESC;";
							$resTecnologia = $dbc->query($query);
							$num_rows = odbc_num_rows($resTecnologia);
						}
					}else{
						$query = "SELECT TOP 1 EQ.RTA, EQ.MAC, TK.UNEPedido
                        FROM W6TASKS TK
                        INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
                        INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
                        INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
                        WHERE EQ.MAC IS NOT NULL
                        AND EQ.RTA IS NOT NULL
                        AND TK.CallID = '$task'  ORDER BY TK.DispatchDate DESC;";
						$resTecnologia = $dbc->query($query);
						$num_rows = odbc_num_rows($resTecnologia);
					}
				}
				if($num_rows > 0){

				}else{
					return 1010;
				}
			}
		} else {
			return 0154;
		}

		$this->Result = $resultados;
		return ($resultados);
	}


	public function GetClickCodigoIncompleto($task)
	{

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q = "use [Service Optimization]
        SELECT task.CallID, task.Status, w6status.Name AS Estado, w6labor.Quantity, w6labor.TimeCreated, task.UNEIncompletionAC, la.Name AS 'ticket', task.AppointmentStart, wtt.Name AS 'tasktype', w6labor.Description, task.UNEPedido, task.UNEMunicipio, task.UNEProductos, task.EngineerID, task.EngineerName, task.UNENombreContacto, task.UNETelefonoContacto, wt.Name 'tasktypecategory', we.MobilePhone
        FROM W6TASKS AS task
        INNER JOIN W6TASK_STATUSES AS w6status ON w6status.W6Key = task.Status
        LEFT JOIN W6UNELABORSUSED AS w6labor ON w6labor.TaskCallID = task.CallID
        LEFT JOIN W6UNELABORTYPE la ON w6labor.LaborType = la.W6Key
        INNER JOIN W6TASK_TYPES wtt ON wtt.W6Key = task.TaskType
        LEFT JOIN W6TASKTYPECATEGORY wt ON wt.W6Key = task.TaskTypeCategory
        LEFT JOIN W6ENGINEERS we ON we.ID = task.EngineerID
        WHERE task.CallID = '$task'
        ORDER BY w6labor.TimeCreated DESC;";

		$resultset = $dbc->query($q);
		$resultados = array();
		$i = 0;
		while ($linea = $dbc->fetch_array($resultset)) {
			$resultados[] = $linea;

			array_walk_recursive($resultados, function (&$item, $key) {
				if (!mb_detect_encoding($item, 'utf-8', true)) {
					$item = utf8_encode($item);
				}
			});
		}
		$this->Result = $resultados;
		return ($resultados);
	}

	public function GetTareasClickTecnico($cedtec)
	{

		$fecha_actual = date('Y-m-d');

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q = "use [Service Optimization]
        SELECT wt.CallID,
        wt.UNEPedido,
        wa.Name 'area',
        wr.Name 'region',
        wd.Name 'district',
        wt.EngineerID,
        wt.EngineerName,
        wt.Engineer_Type,
        wts.Name 'status',
        wt2.Name 'tasktypecategory',
        wt.UNEDepartamento,
        wt.UNEMunicipio,
        wt.UNEDireccion,
        wt.UNENombreCliente,
        wtt.Name 'tasktype',
        wt.UNEActividades,
        wt.UNEProductos,
        wt.UNETecnologias,
        wt.UNEUENcalculada,
        wt.UNEProvisioner,
        wt.OpenDate,
        wt.DispatchDate,
        wt.OnSiteDate,
        wt.CompletionDate,
        wt.CancellationDate,
        wt.UNEPreviousStatusTimeStamp,
        wt.UNEFechaCita,
        wt.UNEHoraCita,
        wt.DueDate,
        wt.EarlyStart,
        wt.LateStart
        FROM W6TASKS wt
        INNER JOIN W6AREA wa ON wa.W6Key = wt.Area
        INNER JOIN W6REGIONS wr ON wr.W6Key = wt.Region
        INNER JOIN W6DISTRICTS wd on wd.W6Key = wt.District
        INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status AND wts.W6Key NOT IN (124135424, 124151808, 124151809, 124151810, 124153856)
        INNER JOIN W6TASK_TYPES wtt on wtt.W6Key = wt.TaskType
        INNER JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        WHERE wt.EngineerID = '$cedtec' AND wt.EarlyStart >= '$fecha_actual 00:00:00' AND wt.LateStart <= '$fecha_actual 23:59:59'
        ORDER BY wt.UNEFechaCita ASC, wt.UNEHoraCita ASC, wt.UNEPreviousStatusTimeStamp ASC;";

		$resultset = $dbc->query($q);
		$resultados = array();
		$i = 0;
		while ($linea = $dbc->fetch_array($resultset)) {
			$resultados[] = $linea;

			array_walk_recursive($resultados, function (&$item, $key) {
				if (!mb_detect_encoding($item, 'utf-8', true)) {
					$item = utf8_encode($item);
				}
			});
		}
		$this->Result = $resultados;
		return ($resultados);
	}

	public function GetTask($task)
	{

		$fecha_actual = date('Y-m-d');

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q = "SELECT wt.TimeCreated, wt.UNEpedido, wt2.Name 'tasktypecategory', wr.Name 'region', wd.Name 'district', wt.EngineerID, wt.EngineerName, wts.Name 'status', wt.unefechacita
        FROM W6TASKS wt
        INNER JOIN W6REGIONS wr ON wr.W6Key = wt.Region
        INNER JOIN W6DISTRICTS wd on wd.W6Key = wt.District
        INNER JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
        WHERE wt.CallID = '$task'";

		$resultset = $dbc->query($q);
		$num_rows = odbc_num_rows($resultset);
		if ($num_rows) {
			$resultados = array();
			$resultados = ['state' => 1];
			$i = 0;
			while ($linea = $dbc->fetch_array($resultset)) {
				$resultados[] = $linea;

				array_walk_recursive($resultados, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
			}
		} else {
			$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
		}

		$this->Result = $resultados;
		return ($resultados);
	}

	public function verSiTieneNuevaTarea($task)
	{

		$fecha_actual = date('Y-m-d');

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q = "SELECT wt.TimeCreated, wt.UNEpedido, wt2.Name 'tasktypecategory', wr.Name 'region', wd.Name 'district', wt.EngineerID, wt.EngineerName, wts.Name 'status', wt.unefechacita
        FROM W6TASKS wt
        INNER JOIN W6REGIONS wr ON wr.W6Key = wt.Region
        INNER JOIN W6DISTRICTS wd on wd.W6Key = wt.District
        INNER JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
        WHERE wt.CallID = '$task'";

		$resultset = $dbc->query($q);
		$num_rows = odbc_num_rows($resultset);
		if ($num_rows) {
			$resultados = array();
			$pedido = $dbc->fetch_array($resultset);

			$resPedido = $pedido['UNEpedido'];
			$q1 = "SELECT TOP 1 CALLID, UNEfechacita, wts.Name 'status'
            FROM W6TASKS wt
            INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
            WHERE UNEpedido = '$resPedido' AND wts.name != 'Cancelado' ORDER BY UNEfechacita DESC";

			$resultsetPedido = $dbc->query($q1);
			$num_rows1 = odbc_num_rows($resultsetPedido);
			if ($num_rows1) {
				while ($row = $dbc->fetch_array($resultsetPedido)) {
					$resultados1[] = $row;

					array_walk_recursive($resultados1, function (&$item1, $key) {
						$item1 = utf8_encode($item1);
					});
				}
				$res = [];
				foreach ($resultados1 as $dato) {
					if ($dato['CALLID'] > $task) {
						$res[] = $dato['CALLID'];
					}
				}

				if (isset($res[0]) and $res[0] > $task) {
					$resultados = ['state' => 1, 'data' => 'Ya existe una nueva tarea con fecha futura'];
				} else {
					$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
				}
			} else {
				$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
			}

		} else {
			$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
		}

		$this->Result = $resultados;
		return ($resultados);
	}

	public function pedidoNuevo($task)
	{
		$fecha_actual = date('Y-m-d');

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q = "SELECT wt.TimeCreated, wt.UNEpedido, wt2.Name 'tasktypecategory', wr.Name 'region', wd.Name 'district', wt.EngineerID, wt.EngineerName, wts.Name 'status', wt.unefechacita
        FROM W6TASKS wt
        INNER JOIN W6REGIONS wr ON wr.W6Key = wt.Region
        INNER JOIN W6DISTRICTS wd on wd.W6Key = wt.District
        INNER JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
        WHERE wt.CallID = '$task'";

		$resultset = $dbc->query($q);
		$num_rows = odbc_num_rows($resultset);
		if ($num_rows) {
			$resultados = array();
			$pedido = $dbc->fetch_array($resultset);

			$resPedido = $pedido['UNEpedido'];
			$q1 = "SELECT TOP 1 CALLID, UNEfechacita, wts.Name 'status'
            FROM W6TASKS wt
            INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
            WHERE UNEpedido = '$resPedido' AND wts.name <> 'Cancelado' ORDER BY UNEfechacita DESC";

			$resultsetPedido = $dbc->query($q1);
			$num_rows1 = odbc_num_rows($resultsetPedido);
			if ($num_rows1) {
				while ($row = $dbc->fetch_array($resultsetPedido)) {
					$resultados1[] = $row;

					array_walk_recursive($resultados1, function (&$item1, $key) {
						$item1 = utf8_encode($item1);
					});
				}
				$res = [];
				if ($resultados1['UNEfechacita'] != date('Y-m-d h:i:s')) {
					$res[] = $resultados1['UNEfechacita'];
				}

				if ($res[0] != date("Y-m-d")) {
					$resultados = ['state' => 1, 'data' => 'La fecha de la tarea es diferente a hoy'];
				} else {
					$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
				}
			} else {
				$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
			}

		} else {
			$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
		}

		$this->Result = $resultados;
		return ($resultados);
	}

	public function pedidoNuevo2($task)
	{
		$fecha_actual = date('Y-m-d');

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q = "SELECT wt.TimeCreated, wt.UNEpedido, wt2.Name 'tasktypecategory', wr.Name 'region', wd.Name 'district', wt.EngineerID, wt.EngineerName, wts.Name 'status', wt.unefechacita
        FROM W6TASKS wt
        INNER JOIN W6REGIONS wr ON wr.W6Key = wt.Region
        INNER JOIN W6DISTRICTS wd on wd.W6Key = wt.District
        INNER JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
        WHERE wt.CallID = '$task'";

		$resultset = $dbc->query($q);
		$num_rows = odbc_num_rows($resultset);
		if ($num_rows) {
			$resultados = array();
			$pedido = $dbc->fetch_array($resultset);

			$resPedido = $pedido['UNEpedido'];
			$q1 = "SELECT CALLID, UNEfechacita, wts.Name 'status'
            FROM W6TASKS wt
            INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
            WHERE UNEpedido = '$resPedido'";

			$resultsetPedido = $dbc->query($q1);
			$num_rows1 = odbc_num_rows($resultsetPedido);
			if ($num_rows1) {
				while ($row = $dbc->fetch_array($resultsetPedido)) {
					$resultados1[] = $row;

					array_walk_recursive($resultados1, function (&$item1, $key) {
						$item1 = utf8_encode($item1);
					});
				}
				$res = [];
				foreach ($resultados1 as $dato) {
					if ($dato['status'] != 'Cancelado') {
						if ($dato['UNEfechacita'] < date('Y-m-d h:i:s')) {
							$res[] = $dato['UNEfechacita'];
						}
						if ($dato['CALLID'] > $task) {
							$res[] = $dato['CALLID'];
						}
					}
				}

				if ($res[0] > date("Y-m-d") && $res[1] > $task) {
					$resultados = ['state' => 1, 'data' => 'Ya existe una nueva tarea con fecha futura'];
				}
			} else {
				$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
			}

		} else {
			$resultados = ['state' => 0, 'data' => 'No se encontraron datos'];
		}

		$this->Result = $resultados;
		return ($resultados);
	}

	public function GetResult()
	{
		return $this->Result;
	}


	public function GetMicrozona($microzona)
	{
		set_time_limit(240);
		try {
			//echo $microzona;exit();
			//$objetos = explode(",", $microzona);
			//$total = count($objetos);

			$Driver = 'SQL Server';
			$Host = 'NETV-PSQL09-05';
			$DataBase = 'Service Optimization';
			$Name = 'ClickStandBy';
			$User = 'BI_Clicksoftware';
			$Password = '6n`Vue8yYK7Os4D-y';

			$conn = odbc_connect("Driver={" . $Driver . "};Server=" . $Host . ";Database=" . $DataBase . ";", $User, $Password);

			$sql = "SELECT DISTINCT(TK.CallID)
            FROM W6TASKS TK
            INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
            INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
            INNER JOIN W6TASKTYPECATEGORY CAT ON TK.TaskTypeCategory=CAT.W6Key
            INNER JOIN W6TASK_STATUSES ES ON TK.Status=ES.W6Key
            INNER JOIN W6TASKS_UNESERVICES TS ON TK.W6Key=TS.W6Key
            INNER JOIN W6UNESERVICES SER ON (TS.UNEService=SER.W6Key)
            WHERE TK.AppointmentStart BETWEEN ('$microzona 00:00:00') AND ('$microzona 23:59:59') AND CAT.Name IN ('Aseguramiento', 'Aprovisionamiento', 'Aprovisionamiento BSC') AND
            ES.Name IN ('Abierto', 'Asignado', 'Despachado', 'En Camino', 'En Sitio', 'Suspendido')";

			$result = odbc_exec($conn, $sql);

			$task = array();
			while ($row = odbc_fetch_array($result)) {
				$task[] = $row;
			}
			$total = count($task);
			$resultados = array();
			for ($i = 0; $i < $total; $i++) {

				/*$resultset = odbc_prepare(
				$connection,
				"SELECT * FROM user WHERE location = ? AND dateofbirth <= ?"
				);
				$success = odbc_execute($resultset, array($location, $mindateofbirth));*/

				$q = odbc_prepare(
					$conn,
					"SELECT TK.CallID, TK.UNEPedido, CAT.Name Categoria, RG.Name Region, DIS.Name Distrito,
                TK.UNEDireccion, TK.UNEMunicipio, TK.UNEBarrio, CONCAT(TK.Latitude*1.0/1000000,',',TK.Longitude*1.0/1000000) Coordenadas,
                SUBSTRING(TK.UNEDireccionComentada, CHARINDEX('Barrio: ',TK.UNEDireccionComentada)-2,1) TipoGeo,
                SER.Infraestructura1,
                CASE
                    WHEN CHARINDEX('NOE * Id: NOE',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('NOE * Id: NOE',SER.Infraestructura1)+LEN('NOE * Id: NOE'),5)
                    WHEN CHARINDEX('NOE * Id: ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('NOE * Id: ',SER.Infraestructura1)+LEN('NOE * Id: ')+1,5)
                    WHEN CHARINDEX('* arpon ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('* arpon ',SER.Infraestructura1)+LEN('* arpon ')+1,5)
                    WHEN CHARINDEX('ARPON * Id: ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('ARPON * Id: ',SER.Infraestructura1)+LEN('ARPON * Id: ')+1,5)
                    WHEN CHARINDEX('Armario * Id: ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('Armario * Id: ',SER.Infraestructura1)+LEN('Armario * Id: ')+1,5)
                    WHEN CHARINDEX('arm ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('arm ',SER.Infraestructura1)+LEN('arm ')+1,5)
                END Nodo_Arpon_Armario
                FROM W6TASKS TK
                INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
                INNER JOIN W6TASKTYPECATEGORY CAT ON TK.TaskTypeCategory=CAT.W6Key
                INNER JOIN W6TASK_STATUSES ES ON TK.Status=ES.W6Key
                INNER JOIN W6TASKS_UNESERVICES TS ON TK.W6Key=TS.W6Key
                INNER JOIN W6UNESERVICES SER ON (TS.UNEService=SER.W6Key)
                WHERE TK.CallID = ?");

				$success = odbc_execute($q, array($task[$i]['CallID']));

				/*var_dump($success);exit();
				$pstmt=odbc_prepare($odb_con,"select * from configured where param_name=?");
				$res=odbc_execute($pstmt,array(" 'version'"));
				var_dump($res);  //bool(true)
				$row = odbc_fetch_array($pstmt);
				var_dump($row);  //bool(false)*/

				$rowarray = odbc_fetch_array($q);
				//$rowarray = odbc_fetch_object($q);
				$resultados[] = $rowarray;

				//var_dump($resultados);exit();
			}

			function utf8_converter($array)
			{
				array_walk_recursive($array, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
				return $array;
			}

			$result = utf8_converter($resultados);

		} catch (\Throwable$th) {
			var_dump($th);
		}
		odbc_close($conn);
		return ($result);
	}

	public function GetMicrozonaTarea($microzona)
	{
		set_time_limit(240);
		try {
			$objetos = explode(",", $microzona);
			$total = count($objetos);

			$Driver = 'SQL Server';
			$Host = 'NETV-PSQL09-05';
			$DataBase = 'Service Optimization';
			$Name = 'ClickStandBy';
			$User = 'BI_Clicksoftware';
			$Password = '6n`Vue8yYK7Os4D-y';

			$conn = odbc_connect("Driver={" . $Driver . "};Server=" . $Host . ";Database=" . $DataBase . ";", $User, $Password);

			for ($i = 0; $i < $total; $i++) {
				$q = odbc_prepare(
					$conn,
					"SELECT TK.CallID, TK.UNEPedido, CAT.Name Categoria, RG.Name Region, DIS.Name Distrito,
                TK.UNEDireccion, TK.UNEMunicipio, TK.UNEBarrio, CONCAT(TK.Latitude*1.0/1000000,',',TK.Longitude*1.0/1000000) Coordenadas,
                SUBSTRING(TK.UNEDireccionComentada, CHARINDEX('Barrio: ',TK.UNEDireccionComentada)-2,1) TipoGeo,
                SER.Infraestructura1,
                CASE
                    WHEN CHARINDEX('NOE * Id: NOE',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('NOE * Id: NOE',SER.Infraestructura1)+LEN('NOE * Id: NOE'),5)
                    WHEN CHARINDEX('NOE * Id: ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('NOE * Id: ',SER.Infraestructura1)+LEN('NOE * Id: ')+1,5)
                    WHEN CHARINDEX('* arpon ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('* arpon ',SER.Infraestructura1)+LEN('* arpon ')+1,5)
                    WHEN CHARINDEX('ARPON * Id: ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('ARPON * Id: ',SER.Infraestructura1)+LEN('ARPON * Id: ')+1,5)
                    WHEN CHARINDEX('Armario * Id: ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('Armario * Id: ',SER.Infraestructura1)+LEN('Armario * Id: ')+1,5)
                    WHEN CHARINDEX('arm ',SER.Infraestructura1)>1 THEN SUBSTRING(SER.Infraestructura1,CHARINDEX('arm ',SER.Infraestructura1)+LEN('arm ')+1,5)
                END Nodo_Arpon_Armario
                FROM W6TASKS TK
                INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
                INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
                INNER JOIN W6TASKTYPECATEGORY CAT ON TK.TaskTypeCategory=CAT.W6Key
                INNER JOIN W6TASK_STATUSES ES ON TK.Status=ES.W6Key
                INNER JOIN W6TASKS_UNESERVICES TS ON TK.W6Key=TS.W6Key
                INNER JOIN W6UNESERVICES SER ON (TS.UNEService=SER.W6Key)
                WHERE TK.CallID = ?");

				$success = odbc_execute($q, array($objetos[$i]));
				$rowarray = odbc_fetch_array($q);
				$resultados[] = $rowarray;
			}

			function utf8_converter($array)
			{
				array_walk_recursive($array, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
				return $array;
			}

			$result = utf8_converter($resultados);

		} catch (\Throwable$th) {
			var_dump($th);
		}
		odbc_close($conn);
		return ($result);
	}

	public function tecnicos()
	{
		try {

			$Driver = 'SQL Server';
			$Host = 'NETV-PSQL09-05';
			$DataBase = 'Service Optimization';
			$Name = 'ClickStandBy';
			$User = 'BI_Clicksoftware';
			$Password = '6n`Vue8yYK7Os4D-y';

			/*php > $conn = odbc_connect(
			"DRIVER={MySQL ODBC 3.51 Driver};Server=localhost;Database=phpodbcdb",
			"username", "password");
			php > $sql = "SELECT 1 as test";
			php > $rs = odbc_exec($conn,$sql);
			php > odbc_fetch_row($rs);
			php > echo "\nTest\n—--\n” . odbc_result($rs,"test") . "\n";*/

			$conn = odbc_connect("Driver={" . $Driver . "};Server=" . $Host . ";Database=" . $DataBase . ";", $User, $Password);

			$sql = "select ENG.Name nombre, ENG.ID, RG.Name region, ENG.MobilePhone, ENG.UNEProvisioner contrato, ENG.LoginName login, RG.Name ciudad
            FROM W6ENGINEERS ENG
            INNER JOIN W6REGIONS RG ON ENG.Region = RG.W6Key
            INNER JOIN W6AREA AR ON ENG.Area = AR.W6Key
            LEFT JOIN W6ENGINEER_TYPES ENTY ON ENG.EngineerType=ENTY.W6Key
            FULL JOIN W6DISPATCHPOLICY POL ON ENG.DispatchPolicy=POL.W6Key
            WHERE ENG.SOLicenseInactive = ('0')
            ORDER BY eng.Name";

			$result = odbc_exec($conn, $sql);

			$resultados = array();

			/*print_r($row);exit();*/
			while ($row = odbc_fetch_array($result)) {
				$resultados[] = $row;
				array_walk_recursive($resultados, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
			}

			///var_dump($resultados);exit();

		} catch (\Throwable$th) {
			var_dump($th);
		}
		odbc_close($conn);
		//$this->Result=$resultados;
		return ($resultados);
		//echo ($resultados);
		//echo json_encode($resultados);
	}

	public function Quejas(){
		$conn = oci_connect("SIEBDPD122719", "Diciem30+", "netvm-bora12:1521/siebstby");

		if (!$conn) {

			$m = oci_error();

			echo $m['message'], "\n";

			exit;
		}
		//exit();
		$query = "select B.SR_NUM SS,E.NAME NOMBRE_CUENTA,E.DUNS_NUM IDENTIFICACION,CO.CELL_PH_NUM CELULAR,CO.HOME_PH_NUM FIJO,B.SR_CST_NUM AS NUMERO_CUN,CO.EMAIL_ADDR EMAIL,E1.ADDR_NAME DIRECCION,B.X_DESCRIPCION DESCRIPCION
        FROM SIEBEL.S_SRV_REQ B
        LEFT JOIN SIEBEL.S_ORG_EXT E ON B.CST_OU_ID=E.ROW_ID
        LEFT JOIN SIEBEL.S_ADDR_PER E1 ON E.PR_ADDR_ID=E1.ROW_ID
        LEFT JOIN SIEBEL.S_CONTACT CO ON B.CST_CON_ID = CO.ROW_ID
        where B.SR_NUM IN ('$this->Pedido')";

		$stid = oci_parse($conn,$query);
		oci_execute($stid);

		return oci_fetch_assoc($stid);
	}

	public function registroPedido($Pedido){
		try {

			$dbc = new DataBase(
				APP_CONEXION['Click2']
			);

			if (!$dbc->connect()) {
				die('No fue posible Conectarse a la base de datos');
			}

			$q = "SELECT TOP 1
            wt2.Name 'categoria'
            FROM W6TASKS wt
            LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
            WHERE wt.UNEPedido = '$Pedido' order by wt.DispatchDate desc";

			$resultset = $dbc->query($q);
			$num_rows = odbc_num_rows($resultset);


		} catch (\Throwable $th) {
			//throw $th;
		}
		$this->Result = $num_rows;
		return $num_rows;
	}

	public function datosregistroPedido($Pedido){
		try {

			$dbc = new DataBase(
				APP_CONEXION['Click2']
			);

			if (!$dbc->connect()) {
				die('No fue posible Conectarse a la base de datos');
			}

			$q = "SELECT DISTINCT
                    TOP 1 TK.UNEDireccionComentada uNEDireccionComentada,
                    TK.UNEMunicipio uNEMunicipio,
                    TK.UNENombreCliente uNENombreCliente,
                    TK.DispatchDate,
                    WU.Name Sistema
                FROM
                    W6TASKS TK
                    INNER JOIN W6UNESOURCESYSTEMS WU ON WU.W6Key = TK.UNESourceSystem
                WHERE
                    TK.UNEPedido = '$Pedido'
                ORDER BY
                    TK.DispatchDate DESC;";

			$resultset = $dbc->query($q);
			$resultados = array();
			while ($linea = $dbc->fetch_array($resultset)) {
				$resultados[] = $linea;

				array_walk_recursive($resultados, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
			}

		} catch (\Throwable $th) {
			//throw $th;
		}
		$this->Result = $resultados;
		return ($resultados);
	}

	public function ruteo(){

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q = "USE [Service Optimization] SELECT CAST
        ( tk.AppointmentStart AS DATE ) fecha,
        RG.Name zona,
        TTC.Name proceso,
        AR.Name area,
        SYS.Name sistema,
        TYP.Name tasktype,
        SUM ( CASE WHEN tk.status IN ( '124135424', '124151810' ) THEN 1 ELSE 0 END ) 'denominador',
        SUM ( CASE WHEN tk.status IN ( '124135426', '124135428', '124135429', '124145667', '124151808' ) THEN 1 ELSE 0 END ) 'numerador' 
        FROM
            dbo.W6TASKS tk
            INNER JOIN W6REGIONS RG ON tk.Region= RG.W6Key
            INNER JOIN W6TASKTYPECATEGORY TTC ON tk.TaskTypeCategory= TTC.W6Key
            INNER JOIN W6AREA AR ON tk.Area= AR.W6Key
            INNER JOIN W6UNESOURCESYSTEMS SYS ON tk.UNESourceSystem= SYS.W6Key
            INNER JOIN W6TASK_TYPES TYP ON tk.TaskType= TYP.W6Key 
        WHERE
            CAST ( tk.AppointmentStart AS DATE ) = CAST ( GETDATE( ) AS DATE ) 
            AND tk.status IN ( '124135424', '124135426', '124135428', '124135429', '124145667', '124151808', '124151810' ) 
            AND tk.TaskTypeCategory NOT IN ( '128063493' ) 
        GROUP BY
            CAST ( tk.AppointmentStart AS DATE ),
            RG.Name,
            TTC.Name,
            AR.Name,
            SYS.Name,
            TYP.Name;";

		$resultset = $dbc->query($q);
		$resultados = array();
		while ($linea = $dbc->fetch_array($resultset)) {
			$resultados[] = $linea;

			array_walk_recursive($resultados, function (&$item, $key) {
				if (!mb_detect_encoding($item, 'utf-8', true)) {
					$item = utf8_encode($item);
				}
			});
		}

		$this->Result = $resultados;
		return ($resultados);

	}

	public function toip()
	{

		set_time_limit(60);
		ini_set("memory_limit","-1");
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

		// $dbc=new DataBase(
		//     APP_CONEXION['Click2']
		// );
		// if(!$dbc->connect()){
		//     die ('No fue posible Conectarse a la base de datos');
		// }

		// $Driver = 'SQL Server';
		// $Host = 'NETV-PSQL09-05';
		// $DataBase = 'Service Optimization';
		// $Name = 'ClickStandBy';
		// $User = 'BI_Clicksoftware';
		// $Password = '6n`Vue8yYK7Os4D-y';

		// $conn = odbc_connect("Driver={" . $Driver . "};Server=" . $Host . ";Database=" . $DataBase . ";", $User, $Password);

		// if (!$conn) {
		//     echo "No se pudo conectar a la base de datos";
		//     return [];
		// }

		$dbc = new DataBase(
			APP_CONEXION['Click']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		//$db = new Base(APP_CONEXION['Click3']);

		print_r($dbc);
		die();



		$query = "SELECT DISTINCT TK.UNEPedido, TK.CallID,EQ.TimeModified AS Hora_Respuesta, 
        EQ.RTA AS RespuestaAprov,EQ.EQProducto, EQ.SerialNo,EQ.MAC,RG.Name AS Region,
        TTC.Name AS Categoria,TT.Name AS TaskType, EQ.EquipmentID,EQ.TipoEquipo,
        SUBSTRING(SER.DatosCola1, CHARINDEX('Teléfono * Valor:', SER.DatosCola1)+18,10) as NumeroToIP,TK.EngineerName,TK.EngineerID
        
        FROM W6TASKS TK with(nolock)
        INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
        INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
        INNER JOIN W6TASK_STATUSES STA ON TK.Status=STA.W6Key
        INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
        INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
        INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
        INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
        INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
        INNER JOIN W6UNESERVICES SER ON EQ.EQIdentificadorServicio=SER.IdentificadorServicio
        
        WHERE EQ.TimeModified >= DATEADD(dd,DATEDIFF(dd,0,GETDATE()),0)
        AND TTC.Name IN ('Aprovisionamiento')
        AND EQ.Type IN ('Install','Traslado')
        AND SS.Name IN ('POE')
        AND EQ.RTA IN ('NA','N')
        AND (TT.Name LIKE '%HFC%'
        OR TT.Name LIKE '%GPON%')
        AND EQ.EQProducto IN ('Telefonía','TO')
        AND STA.Name IN ('Finalizada')
        AND EQ.TipoEquipo IN ('MTA_VOZ','CPE_CONV','CPE_GPON')
        ORDER BY EQ.TimeModified DESC;";
		//ORDER BY EQ.TimeModified DESC;

		try {

			// $query = "USE [Service Optimization] SELECT CAST
			//     ( tk.AppointmentStart AS DATE ) fecha,
			//     RG.Name zona,
			//     TTC.Name proceso,
			//     AR.Name area,
			//     SYS.Name sistema,
			//     TYP.Name tasktype,
			//     SUM ( CASE WHEN tk.status IN ( '124135424', '124151810' ) THEN 1 ELSE 0 END ) 'denominador',
			//     SUM ( CASE WHEN tk.status IN ( '124135426', '124135428', '124135429', '124145667', '124151808' ) THEN 1 ELSE 0 END ) 'numerador'
			//     FROM
			//         dbo.W6TASKS tk
			//         INNER JOIN W6REGIONS RG ON tk.Region= RG.W6Key
			//         INNER JOIN W6TASKTYPECATEGORY TTC ON tk.TaskTypeCategory= TTC.W6Key
			//         INNER JOIN W6AREA AR ON tk.Area= AR.W6Key
			//         INNER JOIN W6UNESOURCESYSTEMS SYS ON tk.UNESourceSystem= SYS.W6Key
			//         INNER JOIN W6TASK_TYPES TYP ON tk.TaskType= TYP.W6Key
			//     WHERE
			//         CAST ( tk.AppointmentStart AS DATE ) = CAST ( GETDATE( ) AS DATE )
			//         AND tk.status IN ( '124135424', '124135426', '124135428', '124135429', '124145667', '124151808', '124151810' )
			//         AND tk.TaskTypeCategory NOT IN ( '128063493' )
			//     GROUP BY
			//         CAST ( tk.AppointmentStart AS DATE ),
			//         RG.Name,
			//         TTC.Name,
			//         AR.Name,
			//         SYS.Name,
			//         TYP.Name;";

			//odbc_timeout($conn, 60);
			$result = odbc_exec($conn, $query);
			//$result = odbc_prepare($conn, $query);

			odbc_setoption($result, 2, 0, 30);
			//odbc_execute($result);

			if (!$result) {
				echo "Error en la consulta: " . odbc_errormsg($conn);
				exit;
			}

			$data = [];
			while ($row = odbc_fetch_array($result)) {
				//print_r($row);
				$data[] = $row;
			}

			function utf8_converter($array)
			{
				array_walk_recursive($array, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
				return $array;
			}

			$response = utf8_converter($data);
			//$this->Result=$resultados;
			//close();
			return $response;

		} catch (Exception $e) {
			echo "Error: " . $e->getMessage();
			return [];
		}
	}

	public function toip2()
	{
		//set_time_limit(60);
		ini_set("memory_limit","-1");

		// try {
		//     set_time_limit(600);
		//     $dbc = new DataBase(APP_CONEXION['Click2']);

		//     if (!$dbc->connect()) {
		//         die('No fue posible Conectarse a la base de datos');
		//     }

		//     $q = "SELECT DISTINCT
		//         TK.UNEPedido,
		//         TK.CallID,
		//         EQ.TimeModified AS Hora_Respuesta,
		//         EQ.RTA AS RespuestaAprov,
		//         EQ.EQProducto,
		//         EQ.SerialNo,
		//         EQ.MAC,
		//         RG.Name AS Region,
		//         TTC.Name AS Categoria,
		//         TT.Name AS TaskType,
		//         EQ.EquipmentID,
		//         EQ.TipoEquipo,
		//         SUBSTRING ( SER.DatosCola1, CHARINDEX( 'Teléfono * Valor:', SER.DatosCola1 ) + 18, 10 ) AS NumeroToIP
		//     FROM
		//         W6TASKS TK WITH ( nolock )
		//         INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem= SS.W6Key
		//         INNER JOIN W6REGIONS RG ON TK.Region= RG.W6Key
		//         INNER JOIN W6TASK_STATUSES STA ON TK.Status = STA.W6Key
		//         INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory= TTC.W6Key
		//         INNER JOIN W6TASK_TYPES TT ON TK.TaskType= TT.W6Key
		//         INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key= TK_EQ.W6Key
		//         INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey= EQ.W6Key
		//         LEFT JOIN W6UNESERVICES SER ON EQ.EQIdentificadorServicio= SER.IdentificadorServicio
		//     WHERE
		//         EQ.TimeModified > DATEADD( dd, DATEDIFF( dd, 0, GETDATE( ) ), 0 )
		//         AND TTC.Name IN ( 'Aprovisionamiento' )
		//         AND EQ.Type IN ( 'Install' )
		//         AND SS.Name IN ( 'POE' )
		//         AND EQ.RTA IN ( 'NA', 'N' )
		//         AND TT.Name LIKE '%HFC%'
		//         AND EQ.EQProducto = ( 'Telefonía' )
		//         AND STA.Name IN ( 'Finalizada' )
		//         AND EQ.TipoEquipo = 'MTA_VOZ'
		//     ORDER BY
		//         EQ.TimeModified DESC";

		//     $resultset = $dbc->query($q);
		//     $resultados = array();
		//     while ($linea = $dbc->fetch_array($resultset)) {
		//         $resultados[] = $linea;

		//         array_walk_recursive($resultados, function (&$item, $key) {
		//             if (!mb_detect_encoding($item, 'utf-8', true)) {
		//                 $item = utf8_encode($item);
		//             }
		//         });
		//     }

		//     $this->Result = $resultados;
		//     return ($resultados);

		//     /*$resultset = $dbc->query($query);
		//     $resultados = array();

		//     while ($linea = $dbc->fetch_array($resultset)) {
		//         $resultados[] = $linea;
		//     }

		//     function utf8_converter($array)
		//     {
		//         array_walk_recursive($array, function (&$item, $key) {
		//             if (!mb_detect_encoding($item, 'utf-8', true)) {
		//                 $item = utf8_encode($item);
		//             }
		//         });
		//         return $array;
		//     }

		//     var_dump($resultados);exit();

		//     $result = utf8_converter($resultados);*/

		// } catch (\Throwable $th) {
		//     var_dump($th);
		// }

		//return $result;

		// $dbc=new DataBase(
		//     APP_CONEXION['Click2']
		// );
		// if(!$dbc->connect()){
		//     die ('No fue posible Conectarse a la base de datos');
		// }

		$Driver = 'SQL Server';
		$Host = 'NETV-PSQL09-05';
		$DataBase = 'Service Optimization';
		$Name = 'ClickStandBy';
		$User = 'BI_Clicksoftware';
		$Password = '6n`Vue8yYK7Os4D-y';

		/*php > $conn = odbc_connect(
		"DRIVER={MySQL ODBC 3.51 Driver};Server=localhost;Database=phpodbcdb",
		"username", "password");
		php > $sql = "SELECT 1 as test";
		php > $rs = odbc_exec($conn,$sql);
		php > odbc_fetch_row($rs);
		php > echo "\nTest\n—--\n” . odbc_result($rs,"test") . "\n";*/

		$conn = odbc_connect("Driver={" . $Driver . "};Server=" . $Host . ";Database=" . $DataBase . ";", $User, $Password);

		$q = "SELECT DISTINCT TK.UNEPedido, TK.CallID,EQ.TimeModified AS Hora_Respuesta, 
        EQ.RTA AS RespuestaAprov,EQ.EQProducto, EQ.SerialNo,EQ.MAC,RG.Name AS Region,
        TTC.Name AS Categoria,TT.Name AS TaskType, EQ.EquipmentID,EQ.TipoEquipo,
        SUBSTRING(SER.DatosCola1, CHARINDEX('Teléfono * Valor:', SER.DatosCola1)+18,10) as NumeroToIP,TK.EngineerName,TK.EngineerID
        
        FROM W6TASKS TK with(nolock)
        INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
        INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
        INNER JOIN W6TASK_STATUSES STA ON TK.Status=STA.W6Key
        INNER JOIN W6DISTRICTS DIS ON TK.District=DIS.W6Key
        INNER JOIN W6TASKTYPECATEGORY TTC ON TK.TaskTypeCategory=TTC.W6Key
        INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
        INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
        INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
        INNER JOIN W6UNESERVICES SER ON EQ.EQIdentificadorServicio=SER.IdentificadorServicio
        
        WHERE EQ.TimeModified >= DATEADD(dd,DATEDIFF(dd,0,GETDATE()),0)
        AND TTC.Name IN ('Aprovisionamiento')
        AND EQ.Type IN ('Install','Traslado')
        AND SS.Name IN ('POE')
        AND EQ.RTA IN ('NA','N')
        AND (TT.Name LIKE '%HFC%'
        OR TT.Name LIKE '%GPON%')
        AND EQ.EQProducto IN ('Telefonía','TO')
        AND STA.Name IN ('Finalizada')
        AND EQ.TipoEquipo IN ('MTA_VOZ','CPE_CONV','CPE_GPON')
        ORDER BY EQ.TimeModified DESC;";

		$result = odbc_exec($conn, $q);

		$resultados = array();
		$row = odbc_fetch_array($result);
		print_r($row);

		/*print_r($row);exit();*/
		while ($row = odbc_fetch_array($result)) {
			$resultados[] = $row;
			array_walk_recursive($resultados, function (&$item, $key) {
				if (!mb_detect_encoding($item, 'utf-8', true)) {
					$item = utf8_encode($item);
				}
			});
		}

		return $resultados;

		/*$resultset = $dbc->query($q);
		//var_dump($resultset);exit();
		$resultados = array();
		//$resultados = $dbc->fetch_array($resultset);
		//$rowarray = odbc_fetch_array($resultset);
		//var_dump($rowarray);exit();

		$linea = $dbc->fetch_array($resultset);

		var_dump($linea);exit();


		while ($linea = $dbc->fetch_array($resultset)) {
			$resultados[] = $linea;
			var_dump($resultados);exit();
		}

		var_dump($resultados);exit();

		function utf8_converter($array)
		{
			array_walk_recursive($array, function (&$item, $key) {
				if (!mb_detect_encoding($item, 'utf-8', true)) {
					$item = utf8_encode($item);
				}
			});
			return $array;
		}*/

		//var_dump($resultados);exit();

		//$result = utf8_converter($resultados);
		//return $result;

		//     $resultset=$dbc->query($q);

		//     var_dump($resultset);Exit();
		//     $resultados = array();
		//     $i=0;
		//     while ( $linea = $dbc->fetch_array($resultset) ) {
		//         $resultados[] = $linea;
		//         array_walk_recursive($resultados, function(&$item, $key){
		//             if(!mb_detect_encoding($item, 'utf-8', true)){
		//                     $item = utf8_encode($item);
		//             }
		//         });
		//     }
		// $this->Result=$resultados;
		// return ($resultados);

	}

	public function productsEtp($task){
		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}


		$query = "SELECT TOP 1 TK.CallID
        FROM W6TASKS TK
        WHERE TK.CallID IN ('$task') ORDER BY TK.DispatchDate DESC";

		$resultEtp = $dbc->query($query);
		$num_rows = odbc_num_rows($resultEtp);

		if($num_rows){
			$query = "SELECT TOP 1 TK.UNEPedido, TK.UNETecnologias, TKS.Name 'Status', SERV.Infraestructura1, SS.Name AS sistema
            FROM W6TASKS TK
            INNER JOIN W6TASK_STATUSES TKS ON TKS.W6Key = TK.Status
            INNER JOIN W6TASKS_UNESERVICES TKU ON TK.W6Key = TKU.W6Key
            INNER JOIN W6UNESERVICES SERV ON TKU.UNEService = SERV.W6Key
            INNER JOIN W6UNESOURCESYSTEMS SS ON TK.UNESourceSystem=SS.W6Key
            WHERE TK.CallID IN ('$task') ORDER BY TK.DispatchDate DESC";

			$resultset = $dbc->query($query);
			$resultados = array();
			$i = 0;
			while ($linea = $dbc->fetch_array($resultset)) {
				$resultados[] = $linea;

				array_walk_recursive($resultados, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
			}
			$this->Result = $resultados;
			return ($resultados);
		}else{
			return 404;
		}




	}

	public function etp($task){
		$task = $task;

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q2 = "SELECT TOP 1
		wt2.Name 'categoria'
        FROM W6TASKS wt
        LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
        WHERE wt.CallID = '$task' ORDER BY wt.DispatchDate DESC";

		$resultGpon = $dbc->query($q2);
		$row = $dbc->fetch_array($resultGpon);

		if($row['categoria'] == 'Aprovisionamiento' || $row['categoria'] == 'Aprovisionamiento BSC'){
			$query = "SELECT  TK.UNEPedido,TTC.Name AS 'TaskTypeCategory', TK.UNEProductos,TK.EngineerID,TK.EngineerName, WE.MobilePhone,TK.UNEMunicipio,  EQ.SerialNo, EQ.MAC, EQ.SerialNoReal, EQ.MACReal, EQ.SerialNoReal2, EQ.MACReal2, EQ.SSID, EQ.UNEPassword, EQ.RTA, EQ.RTA2, EQ.RTA3, EQ.EQProducto, TKS.Name AS 'Status', WS.Name AS 'UneSourceSystem',WU.TipoEquipo,TT.Name AS 'TaskType', TK.UNETecnologias
            FROM W6TASKS TK
            -- INNER JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
            -- INNER JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
            FULL JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
            FULL JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
            
            INNER JOIN W6UNESOURCESYSTEMS WS ON WS.W6Key = TK.UNESourceSystem
            INNER JOIN W6TASK_STATUSES TKS ON TKS.W6Key = TK.Status
            INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
            INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key = TK.TaskTypeCategory
            LEFT JOIN W6UNEEQUIPMENTUSED WU ON WU.TaskCallID = TK.CallID
            INNER JOIN W6ENGINEERS WE ON WE.ID = TK.EngineerID
            WHERE TK.CallID IN ('$task') ORDER BY TK.DispatchDate DESC";
		}else{
			$query = "SELECT  TK.UNEPedido,TTC.Name AS 'TaskTypeCategory', TK.UNEProductos,TK.EngineerID,TK.EngineerName, WE.MobilePhone,TK.UNEMunicipio,  EQ.SerialNo, EQ.MAC, EQ.SerialNoReal, EQ.MACReal, EQ.SerialNoReal2, EQ.MACReal2, EQ.SSID, EQ.UNEPassword, EQ.RTA, EQ.RTA2, EQ.RTA3, EQ.EQProducto, RS.Name as estado_equipo,TKS.Name AS 'Status', WS.Name AS 'UneSourceSystem',WU.TipoEquipo,TT.Name AS 'TaskType', TK.UNETecnologias
            FROM W6TASKS TK
            FULL JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key=TK_EQ.W6Key
            FULL JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey=EQ.W6Key
            FULL JOIN W6UNEEQREPAIRSTATUS RS ON EQ.RepairStatus=RS.W6Key
            INNER JOIN W6UNESOURCESYSTEMS WS ON WS.W6Key = TK.UNESourceSystem
            INNER JOIN W6TASK_STATUSES TKS ON TKS.W6Key = TK.Status
            INNER JOIN W6TASK_TYPES TT ON TK.TaskType=TT.W6Key
            INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key = TK.TaskTypeCategory
            LEFT JOIN W6UNEEQUIPMENTUSED WU ON WU.TaskCallID = TK.CallID
            INNER JOIN W6ENGINEERS WE ON WE.ID = TK.EngineerID
            WHERE TK.CallID IN ('$task') ORDER BY TK.DispatchDate DESC";
		}


		$resultset = $dbc->query($query);
		$num_rows = odbc_num_rows($resultset);
		if($num_rows){
			$resultados = array();
			$i = 0;
			while ($linea = $dbc->fetch_array($resultset)) {
				$resultados[] = $linea;

				array_walk_recursive($resultados, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
			}
			$this->Result = $resultados;
			return ($resultados);
		}else{
			return 500;
		}


	}

	public function validaPedidoMn($task){

		$dbc = new DataBase(
			APP_CONEXION['Click2']
		);

		if (!$dbc->connect()) {
			die('No fue posible Conectarse a la base de datos');
		}

		$q1 = "SELECT TOP
                1 TKS.Name AS 'Estado' 
            FROM
                W6TASKS TK
                INNER JOIN W6TASK_STATUSES TKS ON TKS.W6Key = TK.Status 
            WHERE
                tk.CallID IN ( '$task' ) 
            ORDER BY
                tk.DispatchDate DESC";

		$res = $dbc->query($q1);
		$row = array();
		$row = $dbc->fetch_array($res);

		if ($row['Estado'] != 'En Sitio') {
			return 5555;
		}

		$q = "SELECT TOP 1
        TK.UNEPedido,
        TTC.Name AS 'TaskTypeCategory',
        TK.UNEProductos,
        TK.EngineerID,
        TK.EngineerName,
        WE.MobilePhone,
        TK.UNEMunicipio,
        EQ.SerialNo,
        EQ.MAC,
        EQ.SerialNoReal,
        EQ.MACReal,
        EQ.SerialNoReal2,
        EQ.MACReal2,
        EQ.SSID,
        EQ.UNEPassword,
        EQ.RTA,
        EQ.RTA2,
        EQ.RTA3,
        EQ.EQProducto,
        TKS.Name AS 'Status',
        WS.Name as 'UneSourceSystem',
        WU.TipoEquipo,
        TT.Name as 'TaskType',
        TK.UNETecnologias,
        TKS.Name AS 'Estado',
        RG.Name AS 'region'
        FROM W6TASKS TK
        
        FULL JOIN W6TASKS_UNEEQUIPMENTSUSED TK_EQ ON TK.W6Key = TK_EQ.W6Key
        FULL JOIN W6UNEEQUIPMENTUSED EQ ON TK_EQ.UNEEquipmentUsedKey = EQ.W6Key
        INNER JOIN W6UNESOURCESYSTEMS WS ON WS.W6Key = TK.UNESourceSystem
        INNER JOIN W6TASK_STATUSES TKS ON TKS.W6Key = TK.Status
        INNER JOIN W6TASK_TYPES TT ON TK.TaskType = TT.W6Key
        INNER JOIN W6TASKTYPECATEGORY TTC ON TTC.W6Key = TK.TaskTypeCategory
        INNER JOIN W6REGIONS RG ON TK.Region=RG.W6Key
        LEFT JOIN W6UNEEQUIPMENTUSED WU ON WU.TaskCallID = TK.CallID
        INNER JOIN W6ENGINEERS WE ON WE.ID = TK.EngineerID
        WHERE tk.CallID in ('$task')
        ORDER BY tk.DispatchDate DESC";

		$resultset = $dbc->query($q);

		$resultados = array();
		$i = 0;
		while ($linea = $dbc->fetch_array($resultset)) {
			$resultados[] = $linea;

			array_walk_recursive($resultados, function (&$item, $key) {
				if (!mb_detect_encoding($item, 'utf-8', true)) {
					$item = utf8_encode($item);
				}
			});
		}
		// $this->Result = $resultados;
		return $resultados;
	}
}
