<?php

function POSTredirect($error) {
   	$url ="../index.php?error=".$error;
    header("Location: ".htmlspecialchars_decode($url), true, 303);
    /* NB: il faut interrompre l'exécution du script: la redirection
     * n'est effective qu'une fois que le client a reçu le message
     * HTTP. Inutile (et dangereux d'un point de vue sécurité) d'afficher
     * la vue ! */
    die;
}

function verification_fichier($file,$data){

	if($file['club']['size'] > 200000 ){
		$error = "le fichier ne doit pas dépasser 200ko";
		POSTredirect($error);
		return;
	}
	$nomfichier = $file['club']['name'];
	$fichier = basename($nomfichier);
	$extensions_autorisees = array('csv');
	$infosfichier = pathinfo($file['club']['name']);
	$extension_upload = $infosfichier['extension'];
	if(!in_array($extension_upload, $extensions_autorisees)){
		$error = "le type du fichier n'est pas autorisé !";
		POSTredirect($error);
		return;
	}

	if( preg_match('#[\x00-\x1F\x7F-\x9F/\\\\]#', $nomfichier) ){
		$error = " le nom du fichier est invalide ! ";
		return;
		POSTredirect($error);
	}

	$clubList=lire_csv($file['club']['tmp_name'],";",$data);
	showListClub($clubList);
	$donnee=calculDatas($clubList);
}


/*******************************************************************************************
	FONCTION XAJAX:
		ON INTEROGE L'API DE GOOGLE MAP
			DIRECTION A PARTIR D'UN DEPART VERS UNE DESTINATION
				RETOURNE le resultat en xml

*******************************************************************************************/
function GoogleApi($adresse1,$adresse2){
	// remplace les espaces par des '+'
	$adresse1 = str_replace(" ", "+", $adresse1);
	$adresse2 = str_replace(" ", "+", $adresse2);
	$url='http://maps.google.com/maps/api/directions/xml?language=fr&origin='.$adresse1.'&destination='.$adresse2.'&sensor=false';
	$curl = curl_init();
	$proxy = "https://proxy.unicaen.fr:3128";
	//curl_setopt($curl, CURLOPT_PROXY, $proxy);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$xml = curl_exec($curl);
	$xml;
	$root = simplexml_load_string($xml);
	return $root;
}

/*******************************************************************************************
	FONCTION GETDATA:
		RECUPERER LA DISTANCE ET LA DURATION ENTRE 2 CLUBS
			RETOURNE [distance, duration]

*******************************************************************************************/
function getData($data,$nom1,$adresse1,$nom2,$adresse2){
	$nom1=trim($nom1);$nom2=trim($nom2);
	$adresse1=trim($adresse1);$adresse2=trim($adresse2);
	// si les noms ou les adresses des clubs sont similaires
	if(($nom1==$nom2)or($adresse1==$adresse2)){
		// on retourne la distance et la duration
		return [0,0];
	}

	// si l'adresse contient moins de 5 caractères
	// on le remplace par le nom
	if(strlen($adresse1)<4){return [0,0];}
	if(strlen($adresse2)<4){return [0,0];}

	$array=array();
	// si les données entre les clubs sont
	if(array_key_exists($nom1,$data)and
		array_key_exists($nom2,$data[$nom1])){
			$distance=$data[$nom1][$nom2][0];
			$duration=$data[$nom1][$nom2][1];
			// on retourne la distance et la duration
			array_push($array, $distance, $duration);
			return $array;
	}
	if(array_key_exists($nom2,$data)and
		array_key_exists($nom1,$data[$nom2])){
			$distance=$data[$nom2][$nom1][0];
			$duration=$data[$nom2][$nom1][1];
			// on retourne la distance et la duration
			array_push($array, $distance, $duration);
			return $array;
	}else{

			/*
	Sinon on lance la requte google map pour recuperer la distance et le temps entre l'adress1 et l'adresse2
	Return array(distance, duration)
	*/
	$root = GoogleApi($adresse1,$adresse2);


	if ($root->route->leg != null){
		$distance=$root->route->leg->distance->value;
		$duration=$root->route->leg->duration->value;
	}else {

		$split = explode(' ', $adresse1);
		$adressebis1='';
		foreach ($split as $key) {
		 	if($key == strtoupper($key)
		 	and strlen($key) >=5 ){
		 		$adressebis1=$adressebis1.$key.' ';

		 	}
		}

		$root = GoogleApi($adressebis1,$adresse2);

		if ($root->route->leg != null){
		 	$distance=$root->route->leg->distance->value;
		 	$duration=$root->route->leg->duration->value;
		}else{
			$split = explode(' ', $adresse2);
			$adressebis2='';
			foreach ($split as $key) {
			 	if($key == strtoupper($key)
		 		and strlen($key) >= 5){
				 	$adressebis2=$adressebis2.$key.' ';
			 	}
			}

			$root = GoogleApi($adresse1,$adressebis2);

			if ($root->route->leg != null){
			 	$distance=$root->route->leg->distance->value;
			 	$duration=$root->route->leg->duration->value;
			}else{

				$root = GoogleApi($adressebis1,$adressebis2);
				if ($root->route->leg != null){
				 	$distance=$root->route->leg->distance->value;
				 	$duration=$root->route->leg->duration->value;
				}else{
					$distance = 0;
					$duration = 0;
				}
			}

		}
	}
	if($distance != 0){
	// si la distance entre les clubs non nul
		// conversion ($distance et $duration) en string
		$dist = trim((string)$distance);
		$tps = trim((string)$duration);
		// ecriture dans le fichier data.csv
		ecrire_csv("ressource/data.csv",array($nom1,$nom2,$dist,$tps));
	}
	// on retourne la distance et la duration
	array_push($array, $distance, $duration);
	return $array;

	}

}

/*******************************************************************************************
	FONCTION LIRE_CSV
		PARSER LES FICHIERS CSV:
			SI CONTIENT LE NOM ET L'ADRESSE DES CLUBS
				RETOURNE [liste d'objet clubs]
			SI CONTIENT DISTANCE ET DURATION ENTRE LES CLUBS
				RETOURNE [distance,duration]

*******************************************************************************************/
function lire_csv($nomDuFichier,$sep,$donnees){
	$club=array();
	if (($handle = fopen($nomDuFichier, "r")) != FALSE) {
		$taille=filesize($nomDuFichier)+1;
		if($taille==1){return $club;}
		while (($data = fgetcsv($handle, $taille,$sep)) != FALSE) {
			$nbChamps = count($data);
			if($nbChamps==2){
				$nom=$data[0];
				$adresse=$data[1];
				$club[]=new Club($nom,$adresse,$donnees);
			}
			if($nbChamps==4){
				$club[$data[0]][$data[1]] = [$data[2],$data[3]];
			}
		}
		fclose($handle);

	}
	return $club;
}
/*******************************************************************************************
	FONCTION ECRIRE CSV
		ECRIRE SUR UN FICHIER CSV
*******************************************************************************************/
function ecrire_csv($file,$contentArray){
	if (($fichier = fopen($file, "r+")) != FALSE) {
		$taille=filesize($file)+1;
		while (($data = fgetcsv($fichier, $taille, ",")) != FALSE) {}
		fputcsv($fichier,$contentArray);
		fclose($fichier);
	}
}

/*******************************************************************************************
	FONCTION RANDOMCLUSTER:
		REGROUPER AU HASARD LES EQUIPES EN NOMBRE DE CLUSTER
			RETOURNE une liste de groupe

*******************************************************************************************/
function randomCluster($clubList,$nbCluster){
	$max = sizeof($clubList);
	$maxG = (int)($max/$nbCluster);
	$num = 0;
	$group = [];
	while(sizeof($group)<$nbCluster){
		$rand = rand(0,sizeof($clubList)-1);
		$group[$num][] = $clubList[$rand];
		$clubList[$rand]->setGroupe($num+1);
		unset($clubList[$rand]);
		$clubList=array_values($clubList);
		while(sizeof($group[$num])<$maxG){
			$rand = rand(0,sizeof($clubList)-1);
			$group[$num][] = $clubList[$rand];
			unset($clubList[$rand]);
			$clubList=array_values($clubList);

		}
		$num++;
	}
	for($i=0;$i<sizeof($clubList);$i++) {
		$rand = rand(0,sizeof($group)-1);
		if(sizeof($group[$rand])<=$maxG){
			$group[$rand][] = $clubList[$i];
			unset($clubList[$i]);
			$clubList=array_values($clubList);
		}


	}
	return $group ;
}


function getCentres($nb,$clubs){
	$centres= [];
	// on recupere nb centres
	while(sizeof($centres) < $nb){
		// choisir un centre  au hasard
		$index = rand(0,sizeof($clubs)-1);
		$centres[]=$clubs[$index];
		// actualise $clubs
		unset($clubs[$index]);
		$clubs=array_values($clubs);
	}
	return $centres;
}

function centreProcheClub($index,$indexCentres,$listVal,$ignores){
	$min=9999999999999999;
	$indexCentre=-1;
	foreach ($indexCentres as $key ) {

		if( !in_array($key, $ignores) && $listVal[$key][$index] < $min){
			$min = $listVal[$key][$index];
			$indexCentre=$key;
		}
	}
	// l'index du centre le plus proche du club
	return $indexCentre;
}


function clubProcheGroupe($groupe,$listVal,$ignores){
	$min=9999999999999999;
	$resultat = 0;
	$indexClub=0;
	$indexClubs = [];
	for($i=0;$i<sizeof($listVal);$i++){
		// si pas dans groupe ni dans ignores
		if(!in_array($i,$groupe->getIndexClubs()) && !in_array($i,$ignores)){
			$indexClubs[]=$i;
		}

	}
	// pour chaque point du groupe
	foreach ( $groupe->getIndexClubs() as $indexClubInGroupe) {
		// le club le plus proche de $indexClubInGroupe
		$indexProche=centreProcheClub($indexClubInGroupe,$indexClubs,$listVal,[]);
		if($listVal[$indexClubInGroupe][$indexProche] < $min){
			$min = $listVal[$indexClubInGroupe][$indexProche];
			$resultat = $indexProche;
		}
	}
	return $resultat;
}


function Gclustering2($clubList,$nb,$listVal){
	$clubs = $clubList;
	$resultat = [];
	$groupes = [];
	$tailleMinGroupe = (int)(sizeof($clubs)/$nb);

	$indexClubs=[];
	for($i=0;$i<sizeof($clubs);$i++){$indexClubs[]=$i;}

	// ON RECUPERE LES CENTROISDES AU HASARD
	// ON LES AJOUTES CHACUN DANS DES GROUPES DIFFERENTS
	$centres=getCentres($nb,$clubs);
	$indexCentres=[];
	foreach ($centres as $key) {
		$newgroup = new Cluster($listVal,sizeof($groupes));
		$indexC = array_search($key,$clubs );
		$indexCentres[]=$indexC;
		$newgroup->addClub($indexC);
		$clubs[$indexC]->setGroupe($newgroup->getId());
		$groupes[]=$newgroup;
	}

	// ON AJOUTE CHAQUE CLUB AU GROUPE CONTENANT LE CENTROIDES LE PLUS PROCHE
	for ($indexC = 0; $indexC < sizeof($clubs); $indexC++) {
		// juste pour gerer le cas où plusieurs centres  sont de distance = 0
		if(!in_array($indexC,$indexCentres)){
			$procheCentre = centreProcheClub($indexC,$indexCentres,$listVal,[]);
			$groupeCentre = $clubs[$procheCentre]->getGroupe();
			$clubs[$indexC]->setGroupe($groupeCentre);
			$groupes[$groupeCentre]->addClub($indexC);
		}

	}
	// ON AJOUTE LES CLUBS LE PLUS PROCHE D'UN GROUPE NON COMPLET
	// DANS LE GROUPE NON COMPLET AFIN DE LE COMPLETER
	for ($indexG = 0; $indexG < sizeof($groupes); $indexG++) {
			// tant que nom complet
			$ignores = [];
			while($groupes[$indexG]->getTaille() < $tailleMinGroupe){
				// on recupere lindex du club le plus proche du groupe
				$clubProcheGroupe = clubProcheGroupe($groupes[$indexG],$listVal,$ignores);
				$ignores[]=$clubProcheGroupe;
				// index du groupe du club proche
				$indexGroupe = $clubs[$clubProcheGroupe]->getGroupe();
				// si le groupe hote du du club proche est surcomplet
				if($groupes[$indexGroupe]->getTaille() > $tailleMinGroupe ){
					// on enleve le club de son ancien groupe
					$groupes[$indexGroupe]-> removeClub($clubProcheGroupe);
					// on ajoute le club dans le nouveau groupe
					$clubs[$clubProcheGroupe]->setGroupe($indexG);
					$groupes[$indexG]->addClub($clubProcheGroupe);

				}

			}
	}
	 // REEQUILIBRE LES GROUPES TROP SURCOMPLET
	 for ($indexG = 0; $indexG < sizeof($groupes); $indexG++) {
	 	// tant que la taille du groupe surcomplet
	 	while( $groupes[$indexG]->getTaille() > $tailleMinGroupe + 1){
	 		$min = 99999999;
	 		$ignores= $groupes[$indexG]->getIndexClubs();
	 		$indexGmin=0;
	 		$indexCmin=0;
	 		foreach ($ignores as $indexC) {
	 			// point le plus proche du groupe.

				$clubProche=centreProcheClub($indexC,$indexClubs,$listVal,$ignores);
				$ignores[]=$clubProche;
				// si le groupe peut etre surcomplet
				$indexGroupe = $clubs[$clubProche]->getGroupe();
				if($groupes[$indexGroupe]->getTaille() < $tailleMinGroupe + 1 ){
					if( $listVal[$indexC][$clubProche] < $min ){
						$min = $listVal[$indexC][$clubProche];
						$indexGmin=$indexGroupe;
						$indexCmin= $indexC;
					}
				}

	 		}
	 		// on supprime indexCmin
	 		$groupes[$indexG]->removeClub($indexCmin);
	 		// on ajoute indexCmin à hote(clibMin)
	 		$groupes[$indexGmin]->addClub($indexCmin);
	 		$clubs[$indexCmin]->setGroupe($indexGmin);

	 	}
	 }

	 // ALGO K-MEDOIDES

	$groupes = KMEDOIDES($groupes,$clubs);



	// foreach ($groupes as $key ) {
	// 	$resultat [] = $key->getIndexClubs();
	// }

	$num=0;
	foreach ($groupes as $key ) {
		foreach ($key->getIndexClubs() as $club) {
			$resultat [$num][] = $clubs[$club];
		}
		$num++;

	}

	return $resultat;
}

/*******************************************************************************************
	FONCTION KMEDOIDES:
		PERMUTER DES CLUBS AU HASARD D4UN GROUPE SI CA PERMET D'AMELIORER LE CLUSTERING
			RETOURNE LA LISTE DES GROUPES : liste d'objet cluster

*******************************************************************************************/

function KMEDOIDES($groupes,$clubs){
	//$resultat = [];
	for($i=0;$i<99*sizeof($clubs);$i++){
		$club1  =  rand(0,sizeof($clubs)-1);
		$club2 =   rand(0,sizeof($clubs)-1);
		$G1 = $clubs[$club1]->getGroupe();
		$G2 = $clubs[$club2]->getGroupe();
		$resultat[]= $groupes[$G1];
		$a = 1;
		if( $G1 != $G2){

			$augmentation = false;

			$valeur1 = $groupes[$G1]->evaluer();
	 		$valeur2 = $groupes[$G2]->evaluer();

			$groupes[$G1]->addClub($club2);
			$groupes[$G2]->addClub($club1);

			$groupes[$G1]->removeClub($club1);
			$groupes[$G2]->removeClub($club2);

			$clubs[$club1]->setGroupe($G2);
			$clubs[$club2]->setGroupe($G1);

			$newValeur1 = $groupes[$G1]->evaluer();
	 		$newValeur2 = $groupes[$G2]->evaluer();

	 		$compare1 = abs($valeur1 - $newValeur1);
			$compare2 = abs($valeur2 - $newValeur2);

	 		if( $valeur1 >= $newValeur1 and $valeur2 >= $newValeur2  ){
				$augmentation=true;
	 		}

	 		 if($valeur1 >= $newValeur1 and $valeur2 <= $newValeur2 and $compare1>=$compare2 ){
	 		 	$augmentation=true;
	 		 }

			 if($valeur1 <= $newValeur1 and $valeur2 >= $newValeur2 and $compare1<=$compare2 ){
			 	$augmentation=true;
			 }

			if(!$augmentation){
				$groupes[$G1]->addClub($club1);
				$groupes[$G2]->addClub($club2);

				$groupes[$G1]->removeClub($club2);
				$groupes[$G2]->removeClub($club1);

				$clubs[$club1]->setGroupe($G1);
				$clubs[$club2]->setGroupe($G2);
			}


		}

	}
	return $groupes;

}



/*******************************************************************************************
	FONCTION Gclustering:
		REGROUPER LES EQUIPES EN UN NOMBRE DEFINIT DE GROUPE TOUT EN MINIMISANT SES VALEURS
			RETOURNE LA LISTE DES GROUPES : array(array)

*******************************************************************************************/
function Gclustering($clubList,$nb,$listVal){
	$clubs = $clubList;
	$listvalo =$listVal;
	$nears = [];
	$centres = [];
	$inGroup = [];
	$group = [];
	$max = sizeof($clubList);
	$maxG = (int)($max/$nb);
	$num = 0;
	// tant qu'il reste des groupes à créer
	while(sizeof($group)<$nb){
		// on recupere nb clubs au hasard
		$extremite = getExtremite($listVal);
		// met à jour la liste des valeur
		$listVal = $extremite[1];
		// recupere le premier centoides
		$centre = $extremite[2][0];
		// si le centroides n'a pas encore été ajouter dans un groupes
		if(!in_array($centre, $inGroup)){
			// on ajoute le centroides dans la liste des centroides
			$centres[] = $centre;
			// on ajoute le centroides dans la liste des clubs déja dans un groupe
			$inGroup[] = $centre;
			// ajout du centroide dans un groupe
			$group[$num][]=$clubList[$centre];
			// on recupere les $maxG-1 equipes les plus proche du centroides
			$nearVal = nearValue($listVal[$centre],$maxG-1,$inGroup);//liste index
			$nears[$num][] = $listVal[$centre];
			foreach ($nearVal as $value) {
				$group[$num][] = $clubList[$value];
				$inGroup[] = $value;
			}
			$num++;
		}
		if(sizeof($group)<$nb){
			// recupere le second centoides
			$centre = $extremite[2][1];
			if(!in_array($centre, $inGroup)){
				$centres[] = $centre;
				$inGroup[] = $centre;
				$group[$num][]=$clubList[$centre];
				$nearVal = nearValue($listVal[$centre],$maxG-1,$inGroup);
				$nears[$num][] = $nearVal;
				foreach ($nearVal as $value) {
					$group[$num][] = $clubList[$value];
					$inGroup[] = $value;
				}
				$num++;
			}
		}
	}
	// Ajout des équipes restant dans le groupe contenant le plus proche centroides
	for($i=0;$i<sizeof($clubList);$i++){
		// equipes restant qui ne sont pas dans un groupe
		if(!in_array($i, $inGroup)){
			$min=99999999999999999;
			// initialise l'index du groupe dont le centroides est le plus proche de l'equipes
			$index3;
			for($j=0;$j<sizeof($centres);$j++) {
				$val =$listvalo[$i][$centres[$j]] ;
				// si valeur minimale
				// et au max une seule équipes en plus dans un groupe
				if($val<$min && sizeof($group[$j])<=$maxG){
					$min = $val;
					// index du groupe
					$index3 = $j;
				}

			}
			// ajout de l'equipe dans le groupe.
			$group[$index3][] = $clubList[$i];
		}
	}

	$resultat=[];
	$groupes=[];
	for($i=0;$i<sizeof($group);$i++){
		$groupes[$i]= new Cluster($listvalo,$i);
		for($j=0;$j<sizeof($group[$i]);$j++){
			$index=array_search($group[$i][$j], $clubList);
			$clubList[$index]->setGroupe($i);
			$groupes[$i]->addClub($index);
		}
	}

	// foreach ($groupes as $key ) {
	// 	//$resultat [] = $key->getMatrice();
	//  	$resultat [] = $key->evaluer();
	//  }

	$groupes = KMEDOIDES($groupes,$clubList);

	$num=0;
	foreach ($groupes as $groupe ) {
		foreach ($groupe->getIndexClubs() as $club) {
			$resultat [$num][] = $clubs[$club];
		}
		$num++;

	}

	return $resultat;


	//return $group;
}
/*******************************************************************************************
	FONCTION NEARVALUE:
		RECUPERE UN NOMBRE DEFINIT DES INDEX DES CLUBS LES PLUS PROCHES D'UN CENTROIDES
			RETOURNE LA LISTE DES INDEX :array

*******************************************************************************************/
function nearValue($listVal,$nb,$inGroup){
	// initialise la liste qui va contenir les index des clubs proches
	$nearVal=[];
	// tant tous les n'ont ps été récupéré
	while ( sizeof($nearVal)<$nb) {
		// on recupere la valeur minimal
		$min = minVal($listVal,$inGroup);
		// on l'ajoute dans $nearval
		$nearVal[] = $min;
		$inGroup[] = $min;
	}
	return $nearVal;
}

/*******************************************************************************************
	FONCTION MINVAL:
		RECUPERE L'INDEX DU CLUB LE PLUS PROCHES D'UN CENTROIDES QUI N'A PAS ENCORE ETE RECUPEREE
			RETOURNE L'INDEX  : int

*******************************************************************************************/
function minVal($listVal,$inGroup){
	// initialise la valeur minimal
	$min=99999999999999999;
	// initialise l'index (du club) de la valeur minimal
	$index=0;
	for($i=0;$i<sizeof($listVal);$i++) {
		$val=$listVal[$i];
		if(!in_array($i,$inGroup) && $val != -1  && $val < $min ){
			$min=$val;
			$index=$i;
		}
	}
	if(in_array($index,$inGroup)){
		$index = array_search(-1,$listVal );
	}
	return $index;
}

/*******************************************************************************************
	FONCTION GETEXTREMITE:
		RECUPERER LES INDEX DES 2 CLUBS LES PLUS ELOIGNEES SELON UNE LISTE DE VALEUR
			RETOURNE [la valeur max, nouvelle lise de valeur, [index1,index2]]

*******************************************************************************************/
function getExtremite($listVal){
	// initialise la valeur maximal
	$max=0;
	// initialise la liste des index des clubs extremite
	 $index = [];
	for($i=0;$i<sizeof($listVal);$i++){
		for($j=0;$j<sizeof($listVal);$j++){
			if($listVal[$i][$j]>$max){
				// on recupere la valeur maximal de la liste
		 		$max=$listVal[$i][$j];
		 		// $i et $j correspond aux index des équipes dans clubList
				$index = [$i,$j];
	 		}

		}
	}
	// On remplace la valeur par -1 pour ne plus la recuperer
	$listVal[$index[0]][$index[1]]=-1;
	$listVal[$index[1]][$index[0]]=-1;
	// on l'index des 2 extremité (clubs)
	return [$max,$listVal,$index];
}



function htmlesc($str) {
		return htmlspecialchars($str,
			/* on échappe guillemets _et_ apostrophes : */
			ENT_QUOTES
			/* les séquences UTF-8 invalides sont
			 * remplacées par le caractère �
			 * (au lieu de renvoyer la chaîne vide…) : */
			| ENT_SUBSTITUTE
			/* on utilise les entités HTML5 (en particulier &apos;) : */
			| ENT_HTML5,
			/* encodage */
			'UTF-8');
	}



/*******************************************************************************************
	FONCTION SHOWLISTCLUB:
		AFFICHER LA LISTE DES CLUBS IMPORTEES

*******************************************************************************************/
function showListClub($clubList){
	echo "<table  style='width:100%' title='cliquez pour modifier les équipes'>
			<tr>
				<th style='border:0px solid black'>
					<span class='glyphicon glyphicon-plus' onclick='addTh(this)' ></span>
				</th>
				<th>Nom</th>
				<th>Adresse</th>
			</tr>";
	foreach($clubList as $club ){
	echo "
			<tr>
				<td style='border:0px solid black'>
					<span class='glyphicon glyphicon-remove' onclick='removeNode(this.parentNode.parentNode)'> </span>
				</td>
				<td contenteditable='true'>".htmlesc($club->getNom())." </td>
				<td contenteditable='true'>".htmlesc($club->getAdresse())." </td>
			</tr>
			";
	}
	echo "</table>";
}
/*******************************************************************************************
	FONCTION CALCULDATA:
		RCALCULER LA LISTE DES VALEURS (DISTANCE-DURATION) ENTRES LES EQUIPES
		RETOURNE [LISTE DES VALEURS]

*******************************************************************************************/
function calculData($clubList){
	$notCount=[];$dist=[];$tps=[];
	foreach($clubList as $club1){
		foreach($clubList as $club2){
			if(!in_array($club2,$notCount)){
				if ($club1!=$club2){
					$dist_tps = $club1->dist_tps($club2);
					$dist[] = (int)$dist_tps[0];
					$tps[] = (int)$dist_tps[1];
				}
			}

		}
		$notCount[]=$club1;
	}
	return [$dist,$tps];
}

function mTokm($m){
	return floor($m/1000).'';
}

function secToHeures($sec){
	$h = floor($sec / 3600);
 	$m = floor($sec/60) % 60;
 	$s = $sec % 60;
 	if($h<10){$h='0'.$h;}
 	if($m<10){$m='0'.$m;}
	if($s<10){$s='0'.$s;}
 	return $h.':'.$m.':'.$s;
}
/*******************************************************************************************
	FONCTION CALCULDATA:
		CALCULER LA LISTE DES VALEURS (DISTANCE-DURATION) ENTRES LES EQUIPES
		RETOURNE [[VALEURS POUR L'EQUIPE 0],...,[VALEURS POUR L'EQUIPE DERNIER]]

*******************************************************************************************/
function calculDatas($clubList){
	$distances=[];
	$durations=[];
	$dist_plus_tps=[];
	// couple 2 clubs
	for($i=0;$i<sizeof($clubList);$i++){
		for($j=0;$j<sizeof($clubList);$j++){
			// si le coule symetrique déjà récupéré
			if(isset($distances[$j][$i])){
				// on recupère sa valeur
				$distances[$i][$j] = $distances[$j][$i];
				$durations[$i][$j] = $durations[$j][$i];
				$dist_plus_tps[$i][$j] = $dist_plus_tps[$j][$i];
			}else{
				$dist_tps = $clubList[$i]->dist_tps($clubList[$j]);
				$distances[$i][$j] = (int)$dist_tps[0];
				$durations[$i][$j] = (int)$dist_tps[1];
				$dist_plus_tps[$i][$j]=(int)$dist_tps[0]*(int)$dist_tps[1];
			}
		}
	}
	return [$distances,$durations,$dist_plus_tps];
}

/*******************************************************************************************
	FONCTION CALCULSTAT:
		CALCUL DES STATISTIQUES D'UN GROUPE
		RETOURNE [TOTAL,MOYENNE,MAXIMAL,MINIMAL]

*******************************************************************************************/
function calculStat($listVal){
	$total=0;
	$max=0;
	$min=999999999999;
	foreach ($listVal as $val) {
		if($val>$max){$max = $val;}
		if($val<$min){$min = $val;}
		$total += $val;
	}
	$moy = 0;
	$ecart=0;
	if(sizeof($listVal) > 0){
		$moy = floor($total/sizeof($listVal));
		//calcul ecart-type;
		$moyenne_arith=(1/sizeof($listVal))*$total;
		$ecart_moy = 0;
		foreach ($listVal as $val) {
			$ecart_moy += pow($val-$moyenne_arith,2);
			$ecart=floor(sqrt(1/sizeof($listVal)*$ecart_moy));
		}

	}

	return [$total,$moy,$max,$min,$ecart];
}

/*******************************************************************************************
	FONCTION VUECLUSTER:
		AFFICHER LA LISTE DES GROUPES
		AFFICHER LES STAISTIQUES DE CHAQUE GROUPES
		AFFICHER UN TABLEAU CONTENANT LES VALEURS ENTRE LES EQUIPES

*******************************************************************************************/
function vueCluster($groupes){
	$num=0;
	for($i=0;$i<sizeof($groupes);$i++){
		$num++;$trs='';$th='';$trs2='';$ths='';
		$listDist=calculDatas($groupes[$i]);
		echo "<div class='cluster'>";
		echo "<h4>CLUSTER ".$num."</h4>";
		echo "<p><strong>Liste des clubs:</strong><br>";
		for($x=0;$x<sizeof($groupes[$i]);$x++){

			if(($x%4) == 0 && $x>3){
				echo "</p><p><br>" ;
			}
			// on affiche le nom des clubs pour chaque groupes
			echo "<span draggable='true' ondragstart='drag(event)''>".htmlesc($groupes[$i][$x]->getNom())."</span>";
			if($x==sizeof($groupes[$i])-1){
				echo '.';
			}else{
				echo ' <br> ';
			}
			// on affiche le tableau des distances
			$trs=$trs.'<th>'.htmlesc($groupes[$i][$x]->getNom()).'</th>';
			$trs2=$trs2.'<th>'.htmlesc($groupes[$i][$x]->getNom()).'</th>';
			$ths=$ths.'<th>'.htmlesc($groupes[$i][$x]->getNom()).'</th>';
			for($j=0;$j<sizeof($groupes[$i]);$j++){
				$valdist = mTokm($listDist[0][$x][$j]);
				$valtps=secToHeures($listDist[1][$x][$j]);

			 	$trs =$trs.'<td>'.$valdist.'</td>';
			 	$trs2 =$trs2.'<td>'.$valtps.'</td>';
			}
			$trs= '<tr>'.$trs.'</tr>';
			$trs2= '<tr>'.$trs2.'</tr>';
		}
		$listVal=calculData($groupes[$i]);
		$stat=calculStat($listVal[0]);
		$stat2=calculStat($listVal[1]);
		$total = "distance total: ".mTokm($stat[0])." km<br> ";
		$moy = "distance moyenne: ".mTokm($stat[1])." km<br> ";
		$max = "distance maximal: ".mTokm($stat[2])." km <br> ";
		$min = "distance minimale: ".mTokm($stat[3])." km <br>";
		$ecart = "ecart-type distance: ".mTokm($stat[4])." km <br>";

		$total2 = "total des tenps de trajet: ".secToHeures($stat2[0])."  <br> ";
		$moy2 = "moyenne temps de trajet: ".secToHeures($stat2[1])." <br> ";
		$max2 = "temps du trajet maximal: ".secToHeures($stat2[2])." <br> ";
		$min2 = "temps du trajet minimale: ".secToHeures($stat2[3])." <br>";
		$ecart2 = "ecart-type temps du trajet: ".secToHeures($stat2[4])." <br>";

		echo "</p>
			<p class='statdist'
			       style='float:right;text-align:left;margin-right:0px;color:rgba(0,0,0,0.7);display:block;'>
			    	<strong>Statistique:</strong><br>".$total.$moy.$max.$min.$ecart."
			</p>
			<p class='stattps'
			   style='float:right;text-align:left;margin-right:0px;color:rgba(0,0,0,0.7);display:none;'>
		       <strong>Statistique:</strong><br>".$total2.$moy2.$max2.$min2.$ecart2."
			</p>
			<div class='content-table' >
				<table class='tabdist'>
					<tr><td style='color:#3DC2C4;text-align:left;font-weight:bold;background-color: rgba(250,250,250,0.8)'> DISTANCES_(km)</td>".$ths."</tr>".$trs."
				</table>
				<table class='tabtps'>
					<tr><td style='color:#3DC2C4;text-align:left;font-weight:bold;background-color: rgba(250,250,250,0.8)'> TEMPS </td>".$ths."</tr>".$trs2."
				</table>
			</div>
			<nav>
				<div class='tabBack' onclick='backTab()'><span class='glyphicon glyphicon-arrow-left'></span></div>
				<aside>
						<div class='tabdistB'></div>  <div class='tabtpsB'></div>
				</aside>
				<div class='tabNext' onclick='nextTab()'><span class='glyphicon glyphicon-arrow-right'></span></div>
			</nav>
		</div>";
	}
}

?>