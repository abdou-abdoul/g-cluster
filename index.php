<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="src/vue/images/ico.png" />
    <title>- CLUSTER</title>

    <link href="src/vue/css/bootstrap.min.css" rel="stylesheet">

    <link href="src/vue/css/custom.css" rel="stylesheet">

</head>

<body>

    <!-- Navigation -->
    <nav id="siteNav" class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Logo et barre de menu -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">
                	G-CLUSTER
                </a>
            </div>
            <!-- barre de menu -->
            <div class="collapse navbar-collapse" id="navbar">
                <ul class="nav navbar-nav navbar-right">
                    <li class="active">
                        <a href="/gcluster/index.php">Accueil</a>
                    </li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Services <span class="caret"></span></a>
						<ul class="dropdown-menu" aria-labelledby="about-us">
							<li><a href="">Application</a></li>
							<li><a href="">Comment ça marche ?</a></li>
						</ul>
					</li>
                    <li>
                        <a href="#contact">Contact</a>
                    </li>
                </ul>

            </div><!-- /fin de la barre de menu -->
        </div><!-- /fin du container -->
    </nav>

	<!-- Header -->
    <main>
        <div class="main-content">
            <div class="main-content-inner">
                <h1>G-CLUSTER</h1>
                <!-- <img src="vue/images/a.png" style='width:100px;height:100px;margin-bottom:10px;border-radius:20px;'> -->
                <p>
                     Le cluestering consiste à regrouper un ensemble d'équipe dans des groupes homogènes équilibrés géographiquement.
					 Commancez par importer votre fichier ".csv" contenant sur chaque ligne le nom puis l'adresse d'une équipe séparés par un point virgule.
                </p>
                <form  enctype='multipart/form-data' action="src/application.php" method="POST" id="form_file" class="btn btn-info btn-lg" >
                    <!---- Taille maxi de l'upload en octets ---->
                    <input type="hidden" name="MAX_FILE_SIZE" value="8000000">
                    <input type="file" onchange="document.getElementById('form_file').submit()" name="club" size="30">
                </form>
                <div class="btn btn-info btn-lg">Importer votre fichier</div>

               <!-- <a href="src/application.php" class="btn btn-info btn-lg">Lancer l'application</a> -->
            </div>
            <?php

                    if(isset($_GET['error'])){
                        echo $_GET['error'];
                    }

                ?>
        </div>
    </main>

	<!-- Footer -->
    <footer class="page-footer">

    	<!-- Contacter nous -->
        <div class="contact" id='contact'>
        	<div class="container">
				<h2 class="section-heading">Contacter nous</h2>
				<p><span class="glyphicon glyphicon-envelope"></span><br> Gcluster@live.com</p>
        	</div>
        </div>

        <!-- Copyright  -->
        <div class="small-print">
        	<div class="container">
        		<p>Copyright &copy; 2015</p>
        	</div>
        </div>

    </footer>

    <!-- jQuery -->
    <script src="vue/js/jquery-1.11.3.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vue/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="vue/js/jquery.easing.min.js"></script>



</body>

</html>
