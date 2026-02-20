{{-- @extends('layouts.guest')

@section('guest-content')

		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->

@endsection --}}
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
	<link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">
	<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/electro/bootstrap/css/bootstrap.min.css') }}"/>
	<style>
		/* Sticky footer styles 
		-------------------------------------------------- */
		html { position: relative; min-height: 100%; }
		body { font-family: Georgia, 'Times New Roman', Times, serif; margin-bottom: 60px; /* Margin bottom by footer height */ }
		.footer { position: absolute; bottom: 0; width: 100%; 
					height: 60px; /* Set the fixed height of the footer here */
					line-height: 60px; /* Vertically center the text there */
					background-color: #f5f5f5; }
		@media screen and (min-width: 500px) {
			.footer .copyright { display: inline-block; }
			.footer .desined-by { display: inline-block; margin-left: 100px; }
		}
		@media screen and (max-width: 500px) {
			.footer .desined-by { display: block; }
		}
		/* Custom page CSS
		-------------------------------------------------- */
		/* Not required for template or sticky footer method. */
		.container { padding: 0 15px; }
	</style>
	<title>Bibliothèque numérique</title>
</head>
<body>
	<main role="main" class="container my-5" style="background-image: url({{ asset('assets/img/drc-map.png') }}); background-size: cover; background-repeat: no-repeat; background-attachment: fixed;">
		<div class="row">
			<div class="col-lg-2"></div>
			<div class="col-lg-8">
				<div style="width: 555px; margin: 0 auto;">
					<img src="{{ asset('assets/img/brand-reborn.png') }}" alt="" width="300">
					<img src="{{ asset('assets/img/animated-flag-drc.gif') }}" alt="" width="150" style="margin-left: 100px;">
				</div>
				<h1 class="mt-5 text-center">Annonce du lancement d’une Bibliothèque Numérique au standard industriel</h1>
				<p style="text-align: justify;">Nous avons le plaisir de vous annoncer le lancement officiel de notre <strong>bibliothèque numérique de nouvelle génération, conçue selon les standards industriels les plus avancés. Cette plateforme innovante a pour vocation de rendre accessibles, à un large public, des contenus de haute valeur documentaire dans un objectif de <span class="text-danger">vulgarisation des données</span></strong>.</p>
				<p style="text-align: justify;">Notre bibliothèque propose un vaste catalogue comprenant :</p>
				<ul class="list-group-flush">
					<li class="list-group-item"><strong>Ouvrages</strong> de référence et de culture générale</li>
					<li class="list-group-item"><strong>Médias</strong> audios, vidéos et photos</li>
					<li class="list-group-item"><strong>Journaux</strong> historiques et contemporains</li>
					<li class="list-group-item"><strong>Magazines</strong> spécialisés et grand public</li>
					<li class="list-group-item"><strong>Revues</strong> scientifiques et techniques</li>
					<li class="list-group-item"><strong>Cartes</strong> géographiques et thématiques</li>
					<li class="list-group-item">Et bien d’autres ressources encore</li>
				</ul>
				<p style="text-align: justify;">Grâce à une interface intuitive, une recherche optimisée et une compatibilité multi-supports, cette bibliothèque numérique s’adresse aussi bien aux chercheurs, enseignants, étudiants, qu’aux curieux désireux d’approfondir leurs connaissances.</p>
				<p style="text-align: justify;">Nous vous invitons à découvrir dès maintenant cet espace de savoir, pensé pour démocratiser l’accès à l’information et valoriser la richesse documentaire à travers un traitement numérique moderne et respectueux des normes internationales.</p>
				<p style="text-align: justify;">Rejoignez-nous pour faire de la connaissance un bien partagé.</p>
				<hr class="my-3">
				<p><u>Contact</u> : <strong>+243 855 273 394</strong></p>
				<p><u>E-mail</u> : <strong><a href="mailto:contact@boongo7.com">contact@boongo7.com</a></strong></p>
				<p><u>Adresse Physique</u> : <strong>Silikin Village - Concession COTEX / 63 Avenue Colonel Mondjiba - Kinshasa/Gombe</strong></p>
				<hr>
				{{-- <h3 class="mb-4"><a href="{{ asset('assets/apps/boongo-0_0_1.apk') }}" class="text-decoration-underline">Télécharger ici la version <strong>OVERVIEW</strong> de l'appli Android</a></h3> --}}
			</div>
		</div>
	</main>

	<footer class="footer">
		<div class="container" style="text-align: center;">
			<span class="copyright text-muted">&copy; {{ date('Y') }} Reborn Tous droits réservés.</span> <span class="desined-by text-muted">Designed by <a style="display: inline;" href="https://xsamtech.com">Xsam Technologies</a></span>
		</div>
	</footer>
</body>
</html>