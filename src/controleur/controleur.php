<?php
ini_set('max_execution_time', 1000);
include("modele/function.php");
include("modele/club.php");
include("modele/cluster.php");
//return la liste contenant les objets club
$data=lire_csv("ressource/data.csv",',','');
$clubList=[];

if(isset($_FILES['club'])){
	verification_fichier($_FILES,$data);
    //print_r($_FILES['url']['tmp_name']) ;
}

if(isset($_POST['typeCluster']) && isset($_POST['nbCluster'])){
	$nbCluster=$_POST['nbCluster'];
	$typeCluster=$_POST['typeCluster'];
	$newClub=$_POST['newClub'];
	//$nbCluster=json_encode($nbCluster);
	$data=lire_csv("ressource/data.csv",',','');
	$newClub=json_decode($newClub,true);
	$clubList=[];
	foreach ($newClub as $nom => $adresse) {
		$clubList[]= new Club($nom,$adresse,$data);
	}

	$donnee=calculDatas($clubList);

	switch ($typeCluster) {
		case 'randomCluster':
			$groupes=randomCluster($clubList,$nbCluster);
			vueCluster($groupes);

			break;
		case 'distanceCluster':
			$groupes=Gclustering($clubList,$nbCluster,$donnee[0]);
			vueCluster($groupes);
			 // echo "<pre>";
			 // 	print_r($groupes);
			 // echo "</pre>";

			break;
		case 'durationCluster':
			$groupes=Gclustering($clubList,$nbCluster,$donnee[1]);
			 // echo "<pre>";
			 // 	print_r($groupes);
			 // echo "</pre>";
			vueCluster($groupes);

			break;
		case 'bestCluster':
			$groupes=Gclustering($clubList,$nbCluster,$donnee[2]);
			  // echo "<pre>";
			  // 	print_r($groupes[0]);
			  // echo "</pre>";
			vueCluster($groupes);
			break;

		default:
			echo $clubList;
	}
}


?>
