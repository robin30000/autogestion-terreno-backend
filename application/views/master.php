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
    <div class="row">
        <div class="col s12 m6">
			<a href="<?= base_url() ?>contingencia">
				<div class="card-panel hoverable blue darken-4 z-depth-3" style="border-radius: 20px;">
					<h4 class="white-text center-align">
						Contingencias
					</h4>
				</div>
			</a>
        </div>
        <div class="col s12 m6">
			<a href="<?= base_url() ?>soportegpon">
				<div class="card-panel hoverable blue darken-4 z-depth-3" style="border-radius: 20px;">
					<h4 class="white-text center-align">
						Soporte GPON
					</h4>
				</div>
			</a>
        </div>
    </div>


    <!-- Compiled and minified JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script> -->
    <script src="<?= base_url() ?>public/assets/js/materialize.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            await validarjwt()
            M.AutoInit();
			document.querySelector('.name').innerHTML = sessionStorage.getItem('nombre');
			document.querySelector('.login').innerHTML = sessionStorage.getItem('login_click');

        });

        // Initialize collapsible (uncomment the lines below if you use the dropdown variation)
        // var collapsibleElem = document.querySelector('.collapsible');
        // var collapsibleInstance = M.Collapsible.init(collapsibleElem, options);

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
