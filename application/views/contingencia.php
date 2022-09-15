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
			<div class="progress blue lighten-3" style="margin-top: 0px; display:none;" id="loader">
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
		<h4 class="center-align blue-text text-darken-4">Formulario de solicitud contingencias</h4>
		<div class="row center-align">
			<a href="<?= base_url() ?>listacontingencia" class="waves-effect waves-light grey darken-4 btn-small" style="border-radius: 30px;">
				<i class="material-icons left">subject</i>Ver solicitudes
			</a>
		</div>
		<hr style="width: 50%; border-color: black;">
		<form>


			<div class="row">
				<div class="input-field col s12">
					<input id="pedido" type="text">
					<label for="pedido">Pedido: *</label>
				</div>
			</div>

			<div class="row">
				<div class="input-field col s12">
					<select id="tipoproducto">
						<option value="" disabled selected>Seleccione</option>
						<option value="TV">TV</option>
						<option value="Internet">Internet</option>
						<option value="ToIP">ToIP</option>
						<option value="Internet+ToIP">Internet+ToIP</option>
					</select>
					<label>Tipo de producto: *</label>
				</div>
			</div>

			<div class="row">
				<div class="input-field col s12">
					<select id="tipocontingencia">
						<option value="" disabled selected>Seleccione</option>
						<option value="Contingencia">Contingencia</option>
						<option value="Refresh">Refresh</option>
						<option value="Cambio de Equipo">Cambio de Equipo</option>
					</select>
					<label>Tipo de contingencia: *</label>
				</div>
			</div>

			<div class="row">
				<div class="input-field col s12">
					<textarea id="observacion" class="materialize-textarea"></textarea>
					<label for="observacion">Detalle de la solicitud: *</label>
				</div>
			</div>

			<div class="row">
				<div class="input-field col s12">
					<input id="macentra" type="text">
					<label for="macentra">MAc Entra: *</label>
					<span class="helper-text" data-error="wrong" data-success="right">Más de una MAC debe de ir separadas por coma (,)</span>
				</div>
			</div>

			<div class="row">
				<div class="input-field col s12">
					<input id="macsale" type="text">
					<label for="macsale">Mac Sale:</label>
					<span class="helper-text" data-error="wrong" data-success="right">Más de una MAC debe de ir separadas por coma (,)</span>
				</div>
			</div>

			<!-- <div class="row">
				<div class="chips" id="macentra"></div>
			</div>

			<div class="row">
				<div class="chips" id="macsale"></div>
			</div> -->
			
			<div class="row">
				<div class="col s12">
					<button class="btn waves-effect waves-light blue darken-4 col s12" type="button" id="btnEnviar" onclick="postcontingencia()">
						Enviar solicitud
					</button>
				</div>
			</div>

		</form>
		
    </div>


    <!-- Compiled and minified JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script> -->
    <script src="<?= base_url() ?>public/assets/js/materialize.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
			await validarjwt();

			await M.AutoInit();

			document.querySelector('.name').innerHTML = sessionStorage.getItem('nombre');
			document.querySelector('.login').innerHTML = sessionStorage.getItem('login_click');

			/* $('#macentra').chips({
				placeholder: 'Mac Entra *',
				secondaryPlaceholder: '+Mac Entra',
			});

			$('#macsale').chips({
				placeholder: 'Mac Sale',
				secondaryPlaceholder: '+Mac Sale',
			}); */
        });


		async function postcontingencia() {

			let btnEnviar = document.querySelector('#btnEnviar');
			btnEnviar.className += ' disabled';
			$('#loader').removeAttr('style');
			$('#loader').attr('style', 'margin-top: 0px !important;');

			/* let macentramap = $('#macentra')[0].M_Chips.chipsData.map((val) => val.tag);
			let macsalemap = $('#macsale')[0].M_Chips.chipsData.map((val) => val.tag); */

			let macentramap = $('#macentra').val().replaceAll(' ', '').split(',');
			let macsalemap = $('#macsale').val().replaceAll(' ', '').split(',');

			let macentra = macentramap.join('-');
			let macsale = macsalemap.join('-');

			let pedido = document.getElementById('pedido').value;
			let tipoproducto = document.getElementById('tipoproducto').value;
			let tipocontingencia = document.getElementById('tipocontingencia').value;
			let observacion = document.getElementById('observacion').value;

			if ( pedido == '' || tipoproducto == '' || tipocontingencia == '') {
				btnEnviar.classList.remove('disabled');
				$('#loader').attr('style','margin-top: 0px !important; display:none');
				M.toast({html: 'Debes de dilingenciar los campos obligatorios!'})
				return false;
			}

			if (macentramap.length == 0) {

				btnEnviar.classList.remove('disabled');
				$('#loader').attr('style','margin-top: 0px !important; display:none');
				M.toast({html: 'Mac Entra es obligarorio!'})
				return false;

			}

			if (macsalemap.length == 0 && tipocontingencia == 'Cambio de Equipo') {
				
				btnEnviar.classList.remove('disabled');
				$('#loader').attr('style','margin-top: 0px !important; display:none');
				M.toast({html: 'Mac Sale es obligarorio!'})
				return false;
			
			}

			let base_url = "<?= base_url() ?>postcontingencia";
			let data = {
				pedido,
				tipoproducto,
				tipocontingencia,
				observacion,
				macentra,
				macsale,
			}
			
			let options = {
			   method: 'POST',
			   headers: {
				   'Content-Type': 'application/json',
				   'x-token': sessionStorage.getItem('token') || '',
			   },
			   body: JSON.stringify(data)
			}
			
			let fetchRes = await fetch(base_url, options).catch(error => console.error(error));
			let resdata = await fetchRes.json();
			console.log(resdata);

			if (resdata.type == 'errorAuth') {
				console.log(resdata.message);
				sessionStorage.clear();
				window.location = '<?= base_url() ?>';
				throw Error(resdata.message);
			}

			if (resdata.type == 'error') {
				console.log(resdata.message);
				btnEnviar.classList.remove('disabled');
				$('#loader').attr('style','margin-top: 0px !important; display:none');
				M.toast({html: '<span class="red-text text-darken-4">Error!</span> '+resdata.message})
				return false;
			}

			btnEnviar.classList.remove('disabled');
			$('#loader').attr('style','margin-top: 0px !important; display:none');

			M.toast({html: '<span class="green-text text-darken-4">Exito!</span> '+resdata.message});

			setTimeout(() => {

				location.reload();
				
			}, 1000);
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
