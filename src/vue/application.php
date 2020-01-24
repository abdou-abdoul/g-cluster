<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    	<link rel="icon" href="vue/images/ico.png" />
    <title>- CLUSTER</title>

    <link href="vue/css/bootstrap.min.css" rel="stylesheet">

    <link href="vue/css/custom.css" rel="stylesheet" media="print">
    <link href="vue/css/custom.css" rel="stylesheet" media="screen">
</head>
<body>
    <div class="dimmer" style="position: fixed; top:0; left:0; bottom: 0; right: 0; z-index: 10; background: rgba(255,255,255,1) ">
        <div class="updateData">
            <div class="cssload-loader" >
                <div class="cssload-dot"></div>
                <div class="cssload-dot"></div>
                <div class="cssload-dot"></div>
                <div class="cssload-dot"></div>
                <div class="cssload-dot"></div>
            </div>
            <p class="waiting" style ="left:39%" >
            Importation des équipes, merci de patienter...</p>
        </div>
    </div>
    <!-- Navigation -->
    <nav id="siteNav" class="navbar navbar-default navbar-fixed-top" role="navigation" style="background-color: #555" >
        <div class="container" style="width: 75em">
            <!-- Logo et barre de menu -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">
                	LISTE DES CLUBS
                </a>
            </div>
            <!-- barre de menu -->
            <div class="collapse navbar-collapse" id="navbar">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="../index.php"> Accueil</a>
                    </li>
					<li class="dropdown non-visible" >
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Navigation <span class="caret"></span></a>
						<ul class="dropdown-menu" aria-labelledby="about-us" >
							<li><a id="liste" href="#">LISTE DES CLUBS</a></li>
							<li><a id="select" href="#">SELECTION DU CLUSTERING</a></li>
						</ul>
					</li>
                    <li>
                        <a href="#" id="nextStep" ><span class="glyphicon glyphicon-chevron-right"></span> Etape suivante</a>
                    </li>
                     <li>
                        <a href="#"  id='downpdf' onclick='imprimer()' >
                        <span class='glyphicon glyphicon glyphicon-print'></span> Imprimer</a>
                    </li>
                </ul>

            </div><!-- /fin de la barre de menu -->
        </div><!-- /fin du container -->
    </nav>


	<!-- Header -->
	 <main>
        <section>
            <div id="importClub" class="content" >
            <?php include("controleur/controleur.php");?>
        </div>

        <div id="selectMethod" class="content" >
                <div class="updateData">
                    <div class="cssload-loader" >
                        <div class="cssload-dot"></div>
                        <div class="cssload-dot"></div>
                        <div class="cssload-dot"></div>
                        <div class="cssload-dot"></div>
                        <div class="cssload-dot"></div>
                    </div>
                    <p class="waiting" style ="left:29%" > G-cluster est entrain de calculer la distance entre les différents équipes<br>
                    cette opération peut prendre un certain temps qui varie en fonction du nombre des nouvelles équipes<br>
                    merci de patienter...</p>
                </div>
                <div id="method">
                    <label>
                        <input type="radio" name="gender" value="randomCluster" checked>  RANDOM CLUSTER
                        <p>Répartitionner vos équipes en groupes choisi au hasard.</p>
                    </label>
                    <label>
                        <input type="radio" name="gender" value="distanceCluster" >  DISTANCE CLUSTER
                        <p>Répartitionner vos équipes en minimisant la distance de trajet entre les équipes de chaques groupes.  </P>
                    </label>
                    <label>
                        <input type="radio" name="gender" value="durationCluster" > DURATION CLUSTER
                        <p>Répartitionner vos équipes en minimisant le temps de trajet  entre les équipes de chaques groupes.  </P>
                    </label>
                    <label>
                        <input type="radio" name="gender" value="bestCluster" >  BEST CLUSTER
                        <p>Répartitionner vos équipes en minimisant la distance et le temps de trajet entre les équipes de chaques groupes.  </P>
                    </label>
                    <p class='optp'> Nombre de cluster: <input type="number" class="nbCluster" value="8" min="1" max="99" step="1"><br/>
                       <span>Temps de calcul: 1 sec</span>
                    </p>

                    <a id="clusterB" href="#"  >Lancer la répartition des équipes</a>
                </div>


        </div>

        <div id="dataClub" class="content">


        </div>

        <div id="clusters" class="content">
        </div>
        </section>
    </main>
    <div style='margin-top:50px;'>

    </div>



    <!-- jQuery -->
    <script src="vue/js/jquery-1.11.3.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vue/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="vue/js/jquery.easing.min.js"></script>
    <script src="vue/js/jquery_ui.js"></script>

	<!-- Custom Javascript -->
    <script src="vue/js/custom.js"></script>

</body>

</html>
