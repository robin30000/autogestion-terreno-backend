<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autogestión Terreno</title>

    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified CSS -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css"> -->
	<link rel="stylesheet" href="<?= base_url() ?>public/assets/css/materialize.css">
	<link rel="stylesheet" href="<?= base_url() ?>public/assets/css/input_custom.css">
</head>

<body>

    <div class="navbar-fixed">
        <nav>
            <!-- navbar content here  -->
            <!-- <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a> -->
            <div class="nav-wrapper blue darken-4">
                <a href="#!" class="brand-logo center-align hide-on-small-only">
                    <img src="<?= base_url() ?>public/assets/img/logo-white.png" alt="logo" style="width: 80%;">
                </a>
                <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li><a class="waves-effect" href="<?= base_url() ?>master">Inicio</a></li>
                    <li><a class="waves-effect" href="#!" onclick="salir()">Cerrar Sesión</a></li>
                </ul>
            </div>
			<div class="progress blue lighten-3" style="margin-top: 0px !important; display:none;" id="loader">
				<div class="indeterminate blue darken-4"></div>
			</div>
        </nav>
    </div>


    <ul id="slide-out" class="sidenav">
        <li>
            <div class="user-view">
                <div class="background blue darken-4">
                    <!-- <img src="images/office.jpg"> -->
                </div>
                <a href="<?= base_url() ?>master"><img src="<?= base_url() ?>public/assets/img/logo-white.png"></a>
                <a href="#"><span class="white-text name"></span></a>
                <a href="#"><span class="white-text login"></span></a>
            </div>
        </li>
        <li><a href="<?= base_url() ?>master">Inicio</a></li>
        <li><a href="<?= base_url() ?>contingencia">Contingencia</a></li>
        <li><a href="<?= base_url() ?>soportegpon">Soporte GPON</a></li>
        <li>
            <div class="divider"></div>
        </li>
        <li><a href="#!" onclick="salir()">Cerrar Sesión</a></li>
    </ul>



    <!-- Page Layout here -->
	<div class="container">
		<h4 class="center-align blue-text text-darken-4">Estado de solicitudes.</h4>
		<div class="row center-align">
			<a class="waves-effect waves-light blue darken-1 btn-small" href="#" onclick="getsoportegpon()"><i class="material-icons">refresh</i></a>
		</div>
		<hr style="width: 50%; border-color: black;">
		
		<div id="contenttable">
		</div>

		<!-- Modal Structure -->
		<div id="modal1" class="modal bottom-sheet">
			<div class="modal-content">
				<h4>Detalle de solciitud</h4>
				<div id="contentmodal"></div>
			</div>
			<div class="modal-footer">
				<a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
			</div>
		</div>
		
    </div>


    <!-- Compiled and minified JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script> -->
	<script src="<?= base_url() ?>public/assets/js/materialize.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
			
			await validarjwt();
			await getsoportegpon();

			M.AutoInit();
			document.querySelector('.name').innerHTML = sessionStorage.getItem('nombre');
			document.querySelector('.login').innerHTML = sessionStorage.getItem('login_click');
        });

		async function getsoportegpon() {

			$('#loader').removeAttr('style');
			$('#loader').attr('style', 'margin-top: 0px !important;');
			

			const token = sessionStorage.getItem('token') || '';

			let base_url = "<?= base_url() ?>getsoportegponbyuser";
			let options = {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'x-token': token,
				},
			}
			
			let fetchRes = await fetch(base_url, options).catch(error => console.error(error));
			let resdata = await fetchRes.json();
			console.log(resdata);

			if (resdata.type == 'errorAuth') {
				sessionStorage.clear();
				window.location = '<?= base_url() ?>';
				throw Error(resdata.message);
			}

			if (resdata.type == 'error') {
				$('#loader').attr('style','margin-top: 0px !important; display:none');
				$('#contenttable').html(`<p>${resdata.message}</p>`);
				return false;
			}

			let content = `<table class="highlight">
							<thead>
								<tr>
									<th>Tarea</th>
									<th>Fecha Sol.</th>
									<th>Tipificación</th>
									<th>Estado</th>
								</tr>
							</thead>
							<tbody>`;
							
			resdata.message.forEach(val => {

				let estado = (val.status_soporte != '1') ? '<span class="badge grey white-text" style="border-radius: 30px;">Pendiente</span>' : '<span class="badge blue darken-4 white-text" style="border-radius: 30px;">Atendido</span>' ;
				let data = JSON.stringify(val);
				let dataencode = data.replaceAll('\"','|')

				content += `<tr onclick="detallesolicitud('${dataencode}')">
						<td>${val.tarea}</td>
						<td>${val.fecha_solicitud_firebase}</td>
						<td>${val.respuesta_soporte || ''}</td>
						<td>${estado}</td>
					</tr>`;
			});

			content += `</tbody></table>`;

			$('#contenttable').html(content);

			$('#loader').attr('style','margin-top: 0px !important; display:none');


		}

		async function detallesolicitud(data) {
			let modalinst =  $('#modal1').modal();
			let instance = M.Modal.getInstance(modalinst)

			let datadecode = data.replaceAll('|','\"')
			let dataformat = JSON.parse(datadecode);

			let content = `
				<p><strong>Tarea:</strong> ${dataformat.tarea}</p>
				<p><strong>Pedido:</strong> ${dataformat.unepedido}</p>
				<p><strong>Categoría:</strong> ${dataformat.tasktypecategory}</p>
				<p><strong>Tipificación:</strong> ${dataformat.respuesta_soporte || ''}</p>
				<p><strong>Fecha Solicitud:</strong> ${dataformat.fecha_solicitud_firebase}</p>
				<p><strong>Fecha Respuesta: </strong> ${dataformat.fecha_respuesta || ''}</p>
				<p><strong>Observación Terreno:</strong></p>
				<p>${dataformat.observacion_terreno || ''}</p>
				<p><strong>Observación Despacho:</strong></p>
				<p>${dataformat.observacion || ''}</p>
			`;

			$('#contentmodal').html(content);
			instance.open();
		}

		async function validarjwt() {
			const token = sessionStorage.getItem('token') || '';

			if (token.length <= 10) {
				sessionStorage.clear();
				window.location = '<?= base_url() ?>';
				throw Error('No hay token en el servidor');
			}

			let base_url = "<?= base_url() ?>validarjwt";
			let options = {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'x-token': token,
				},
			}
			
			let fetchRes = await fetch(base_url, options).catch(error => console.error(error));
			let resdata = await fetchRes.json();
			console.log(resdata);

			if (resdata.type == 'error') {
				sessionStorage.clear();
				window.location = '<?= base_url() ?>';
				throw Error('No hay token en el servidor');
			}
		}

		function salir() {
			sessionStorage.clear();
			window.location = '<?= base_url() ?>';
		}

    </script>
</body>

</html>
