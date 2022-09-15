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
		<h4 class="center-align blue-text text-darken-4">Formulario de solicitud soporte gpon</h4>
		<div class="row center-align">
			<a href="<?= base_url() ?>listasoportegpon" class="waves-effect waves-light grey darken-4 btn-small" style="border-radius: 30px;">
				<i class="material-icons left">subject</i>Ver solicitudes
			</a>
		</div>
		<hr style="width: 50%; border-color: black;">
		<form>

			<div class="input-field col s12">
				<i class="material-icons prefix">subject</i>
				<input id="tarea" type="text">
				<label for="tarea">Tarea: *</label>
			</div>

			<div class="input-field col s12">
				<i class="material-icons prefix">subject</i>
				<input id="arpon" type="number">
				<label for="arpon">ARPON: *</label>
			</div>

			<div class="input-field col s12">
				<i class="material-icons prefix">subject</i>
				<input id="nap" type="number">
				<label for="nap">NAP: *</label>
			</div>

			<div class="input-field col s12">
				<i class="material-icons prefix">subject</i>
				<input id="hilo" type="number">
				<label for="hilo">HILO: *</label>
			</div>

			<div class="row">
				<p style="font-size: 16px;">Puertos de Internet</p>
				<div class="col s12" style="text-align-last: justify;">
					<label>
						<input type="checkbox" id="internet_port1" class="filled-in" checked/>
						<span>1</span>
					</label>
					<label>
						<input type="checkbox" id="internet_port2" class="filled-in"/>
						<span>2</span>
					</label>
					<label>
						<input type="checkbox" id="internet_port3" class="filled-in"/>
						<span>3</span>
					</label>
					<label>
						<input type="checkbox" id="internet_port4" class="filled-in"/>
						<span>4</span>
					</label>
				</div>
			</div>

			<div class="row">
				<p style="font-size: 16px;">Puertos de TV</p>
				<div class="col s12" style="text-align-last: justify;">
					<label>
						<input type="checkbox" id="tv_port1" class="filled-in"/>
						<span>1</span>
					</label>
					<label>
						<input type="checkbox" id="tv_port2" class="filled-in"/>
						<span>2</span>
					</label>
					<label>
						<input type="checkbox" id="tv_port3" class="filled-in" checked/>
						<span>3</span>
					</label>
					<label>
						<input type="checkbox" id="tv_port4" class="filled-in" checked/>
						<span>4</span>
					</label>
				</div>
			</div>

			<div class="input-field col s12">
				<i class="material-icons prefix">local_phone</i>
				<input id="numero_contaco" type="number">
				<label for="numero_contaco">Numero de contacto: *</label>
			</div>

			<div class="input-field col s12">
				<i class="material-icons prefix">account_circle</i>
				<input id="nombre_contaco" type="text">
				<label for="nombre_contaco">Nombre de contacto: *</label>
			</div>

			<div class="row">
				<div class="input-field col s12">
					<i class="material-icons prefix">subject</i>
					<textarea id="observacion" class="materialize-textarea"></textarea>
					<label for="observacion">Observación: *</label>
				</div>
			</div>
			
			<div class="row">
				<div class="col s12">
					<button class="btn waves-effect waves-light blue darken-4 col s12" type="button" id="btnEnviar" onclick="postsoportegpon()">
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
            
			M.AutoInit();
			document.querySelector('.name').innerHTML = sessionStorage.getItem('nombre');
			document.querySelector('.login').innerHTML = sessionStorage.getItem('login_click');
        });

		document.getElementById('arpon').addEventListener('input',function() {(this.value.length > 6) ? this.value = this.value.slice(0,6) : false});
		document.getElementById('nap').addEventListener('input',function() {(this.value.length > 2) ? this.value = this.value.slice(0,2) : false});
		document.getElementById('hilo').addEventListener('input',function() {(this.value.length > 2) ? this.value = this.value.slice(0,2) : false});


		async function postsoportegpon() {

			let btnEnviar = document.querySelector('#btnEnviar');
			btnEnviar.className += ' disabled';
			$('#loader').removeAttr('style');
			$('#loader').attr('style', 'margin-top: 0px !important;');

			let tarea = document.getElementById('tarea').value;
			let arpon = document.getElementById('arpon').value;
			let nap = document.getElementById('nap').value;
			let hilo = document.getElementById('hilo').value;
			let internet_port1 = (document.getElementById('internet_port1').checked) ? '1' : '0';
			let internet_port2 = (document.getElementById('internet_port2').checked) ? '1' : '0';
			let internet_port3 = (document.getElementById('internet_port3').checked) ? '1' : '0';
			let internet_port4 = (document.getElementById('internet_port4').checked) ? '1' : '0';
			let tv_port1 = (document.getElementById('tv_port1').checked) ? '1' : '0';
			let tv_port2 = (document.getElementById('tv_port2').checked) ? '1' : '0';
			let tv_port3 = (document.getElementById('tv_port3').checked) ? '1' : '0';
			let tv_port4 = (document.getElementById('tv_port4').checked) ? '1' : '0';
			let numero_contaco = document.getElementById('numero_contaco').value;
			let nombre_contaco = document.getElementById('nombre_contaco').value;
			let observacion = document.getElementById('observacion').value;

			if ( tarea == '' || arpon == '' || nap == '' || hilo == '' || numero_contaco == '' || nombre_contaco == '' || observacion == '') {
				btnEnviar.classList.remove('disabled');
				$('#loader').attr('style','margin-top: 0px !important; display:none');
				M.toast({html: 'Debes de dilingenciar los campos obligatorios!'})
				return false;
			}

			let base_url = "<?= base_url() ?>postsoportegpon";
			let data = {
				tarea,
				arpon,
				nap,
				hilo,
				internet_port1,
				internet_port2,
				internet_port3,
				internet_port4,
				tv_port1,
				tv_port2,
				tv_port3,
				tv_port4,
				numero_contaco,
				nombre_contaco,
				observacion
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
