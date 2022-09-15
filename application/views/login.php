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
	<link rel="stylesheet" href="<?= base_url() ?>public/assets/css/style.css">
	<link rel="stylesheet" href="<?= base_url() ?>public/assets/css/input_custom.css">
</head>

<body>
	
	<div id="login-page" class="container">
		<div class="col s12 z-depth-6 card-panel">
			<form class="login-form">
				<div class="row"></div>
				<h4 class="center-align">Autogestión Terreno</h4>
				<div class="row">
					<div class="input-field col s12">
						<i class="material-icons prefix">account_circle</i>
						<input class="validate" id="user" type="text">
						<label for="user" data-error="wrong" data-success="right">Login</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12">
						<i class="material-icons prefix">lock_outline</i>
						<input id="password" type="password" autocomplete="off">
						<label for="password">Password</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12">
						<a href="#" class="btn waves-effect waves-light blue darken-4 col s12" id="btnIngresar" onclick="ingresar()">Ingresar</a>
					</div>
				</div>
				<div class="row center-align" style="display:none" id="loader">
					<div class="preloader-wrapper small active">
						<div class="spinner-layer spinner-green-only">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div>
							<div class="gap-patch">
								<div class="circle"></div>
							</div>
							<div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12 m12 l12">
						<p class="margin right-align medium-small">
							<a href="#" class="blue-text text-darken-4">¿Aún no tiene un usuario asignado o tiene problemas para acceder?</a>
						</p>
					</div>
				</div>

			</form>
		</div>
	</div>


    <!-- Compiled and minified JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script> -->
	<script src="<?= base_url() ?>public/assets/js/materialize.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {

			await validarjwt();
			M.AutoInit();

        });

		async function validarjwt() {
			const token = sessionStorage.getItem('token') || '';

			if (token.length <= 10) {
				sessionStorage.clear();
				return false;
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
			} else {
				window.location = '<?= base_url() ?>master';
			}
		}

        async function ingresar() {

			let btnIngresar = document.querySelector('#btnIngresar');
			btnIngresar.className += ' disabled';
			document.querySelector('#loader').removeAttribute('style');

			let user = document.querySelector('#user').value;
			let password = document.querySelector('#password').value;

			let base_url = "<?= base_url() ?>ingresar";
			let data = {
				user: user,
				password: password,
			}
			
			let options = {
			   method: 'POST',
			   headers: {
				   'Content-Type': 'application/json',
				   //'Content-Type': 'application/x-www-form-urlencoded',
			   },
			   body: JSON.stringify(data)
			}
			
			let fetchRes = await fetch(base_url, options).catch(error => console.error(error));
			let resdata = await fetchRes.json();
			console.log(resdata);

			if(resdata.type == 'error') {
				console.log(resdata.message);
				btnIngresar.classList.remove('disabled');
				document.querySelector('#loader').setAttribute('style','display:none');
				M.toast({html: resdata.message});
				return false;
			}

			sessionStorage.setItem('nombre', resdata.message.nombre);
			sessionStorage.setItem('login_click', resdata.message.login_click);
			sessionStorage.setItem('identificacion', resdata.message.identificacion);
			sessionStorage.setItem('token', resdata.token);

			window.location.href = '<?= base_url() ?>master'

		}

    </script>
</body>

</html>
