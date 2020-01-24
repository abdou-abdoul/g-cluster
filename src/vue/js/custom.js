
function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}




// Offset for Site Navigation
$('#siteNav').affix({
	offset: {
		top: 100
	}
})


//############################# Variables ##################################################

var nexStep=document.getElementById('nextStep');
var importClub=document.getElementById('importClub');
var selectMethod=document.getElementById('selectMethod');
var clusters=document.getElementById('clusters');
var clusterB=document.getElementById('clusterB');
var liste=document.getElementById('liste');
var selection=document.getElementById('select');
var navBrand=document.getElementsByClassName('navbar-brand')[0]
var nbCluster=document.getElementsByClassName("nbCluster");
var navbarDefault=document.getElementsByClassName('navbar-default')[0]
var main = document.getElementsByTagName('main')[0]


main.style.backgroundImage = "none";
// main.style.marginTop = "50px";
navbarDefault.style.backgroundColor='rgba(0,0,0,0.8)'
navbarDefault.style.borderColor="#fff"
// $('.container').css('width','75em');

$('.non-visible').css('display','block');
$('.dimmer').css('display','none');

//############################# MODIFICATION DES EQUIPES ###################################





//############################# Navigation #################################################

// ETAPE SUIVANTE
nexStep.addEventListener('click', function() {
	var lignes = document.getElementsByTagName("table")[0].getElementsByTagName("tr");
	// initialise objets clubs;
	clubs={};

	var decision = true;
	// initialise la liste des clubs
	var clubs = {}
	// recupere le nom et adresse des équipes
	for(var i=1;i<lignes.length;i++){
		var valid = false
	 	var nom = $(lignes[i]).children().eq(1).html()
	 	var adresse = $(lignes[i]).children().eq(2).html()
	 	clubs[nom] = adresse;
	 	// verification de la validité des adresses
	 	for( var x =0;x<adresse.length;x++){
	 		if( parseInt(adresse.split(' ')[x]) >= 1000){
	 			valid = true
	 		}
	 	}

	 	if(!valid){
	 		$(lignes[i]).children().css('background-color','rgba(255,0,0,0.1)');
	 		$(lignes[i]).parent().prepend($(lignes[i]));
	 		$(lignes[1]).parent().prepend($(lignes[1]));
	 		decision = false;
	 	}
	}
	// si tous les adresses sont valide
	if(decision){
		importClub.style.display='none';
	  	selectMethod.style.display='block';
		nextStep.style.display='none';
		clusters.style.display='none';
	  	navBrand.innerHTML="SELECTION DU CLUSTERING"
	  	clubs=JSON.stringify(clubs);
		loadDoc('updateData',0,clubs);
	//sinon on affiche un message d'erreur.
	}else{alert('les adresses des équipes doivent au moins contenir le code postal de la ville ')}
}, false);


// LISTE DES CLUBS
liste.addEventListener('click', function() {
	selectMethod.style.display='none';
	clusters.style.display='none';
	importClub.style.display='block';
	nextStep.style.display='block';
	navBrand.innerHTML="LISTE DES CLUBS"
	$('#downpdf').css("display","none");

}, false);

// SELECTION DU CLUSTERING
selection.addEventListener('click', function() {
	importClub.style.display='none';
	nextStep.style.display='none';
	clusters.style.display='none';
	selectMethod.style.display='block';
   navBrand.innerHTML="SELECTION DU CLUSTERING";
   $('#downpdf').css("display","none");

},false);



//############################# SELECTION DU CLUSTERING ############################################

var radio = document.getElementsByName('gender');
var navBrandTitle,caseCluster,timeCluster;

// RANDOM CLUSTER

if(radio[0].checked){
	navBrandTitle = "RANDOM CLUSTER";caseCluster="randomCluster";
	$('.optp span').html('Temps de calcul: 2 sec');
}
radio[0].addEventListener('click',function(){
	navBrandTitle = "RANDOM CLUSTER";
	caseCluster="randomCluster";
	$('.optp span').html('Temps de calcul: 2 sec');

},false);

// DISTANCE CLUSTER

if(radio[1].checked){
	navBrandTitle = "DISTANCE CLUSTER";caseCluster="distanceCluster";
	$('.optp span').html('Temps de calcul: 15 sec');
}
radio[1].addEventListener('click',function(){
	navBrandTitle = "DISTANCE CLUSTER";
	caseCluster="distanceCluster";
	$('.optp span').html('Temps de calcul: 15 sec');

},false);

// DURATION CLUSTER

if(radio[2].checked){
	navBrandTitle = "DURATION CLUSTER";caseCluster="durationCluster";
	$('.optp span').html('Temps de calcul: 15 sec');
}
radio[2].addEventListener('click',function(){
	navBrandTitle = "DURATION CLUSTER";
	caseCluster="durationCluster";
	$('.optp span').html('Temps de calcul: 15 sec');

},false);

// BEST CLUSTER

if(radio[3].checked){
	navBrandTitle = "BEST CLUSTER";caseCluster="bestCluster";
	$('.optp span').html('Temps de calcul: 15 sec');
}

radio[3].addEventListener('click',function(){
	navBrandTitle = "BEST CLUSTER";
	caseCluster="bestCluster";
	$('.optp span').html('Temps de calcul: 15 sec');

},false);


//############################# LANCEMENT DU CLUSTERING ########################################

// requete AJAX
 function loadDoc(param,nb,data) {
   var xhttp = new XMLHttpRequest();
  	xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      		if(param == 'updateData'){

      			$('#method').css('display','block');
      			$('.updateData').css('display','none');

      		}
      		else{clusters.innerHTML=xhttp.responseText;}
    }
  };
  xhttp.open('POST', 'controleur.php', true);
  xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhttp.send('nbCluster='+nb+'&typeCluster='+param+'&newClub='+data);
}

// LANCEMENT DU CLUSTERING
clusterB.addEventListener('click',function(){
	var lignes = document.getElementsByTagName("table")[0].getElementsByTagName("tr");
	// initialise objets clubs;
	clubs={};
	// recupere le nom et adresse des équipes
	for(var i=1;i<lignes.length;i++){
		var valid = false
	 	var nom = $(lignes[i]).children().eq(1).html()
	 	var adresse = $(lignes[i]).children().eq(2).html()
	 	clubs[nom] = adresse;
	 }
	// test si nombre de cluster < nombre d'équipes
	if(0 < nbCluster[0].value < lignes.length-1){
		// actualise le titre de la section de la page
		navBrand.innerHTML=navBrandTitle;
		$('#siteNav').css({'border-color':'black'})

		// on affiche le loader
		clusters.innerHTML= ''+
				'<div class="cssload-loader" >'+
					'<div class="cssload-dot"></div>'+
					'<div class="cssload-dot"></div>'+
					'<div class="cssload-dot"></div>'+
					'<div class="cssload-dot"></div>'+
					'<div class="cssload-dot"></div>'+
				'</div>'+
				'<p class="waiting"> G-cluster est entrain de calculer pour vous le cluster'+
				' le plus optimal selon vos critères<br>merci de patienter...</p>';
		selectMethod.style.display='none';clusters.style.display='block';

		// lancement du clustering
		clubs=JSON.stringify(clubs);
		loadDoc(caseCluster,nbCluster[0].value,clubs);

		// affichage du bouton imprimer
		$('#downpdf').css("display","block");

	// sinon on affiche un message d'erreur
	}else{alert("le nombre de cluster doit etre inferieur au nombre d'équipes")}
 },false);

//############################# CHOIX DONNEE MATRICES ET STATISTIQUE


 // VARIABLES DES CHOIX.
 var listTab=['.tabdist','.tabtps'];
 var listTabB=['.tabdistB','.tabtpsB'];
 var listStat=['.statdist','.stattps'];
 var indexTab = 0;

 // CHOIX SUIVANT
 function nextTab(){
 	$(listTab[indexTab]).css({'display':'none'})
 	$(listStat[indexTab]).css({'display':'none'})
 	$(listTabB[indexTab]).css({'background-color':'black'})

 	indexTab=indexTab+1;
 	if(indexTab>listTab.length-1){
 		indexTab=0;
 	}
 	$(listTab[indexTab]).css({'display':'block'})
 	$(listStat[indexTab]).css({'display':'block'})
 	$(listTabB[indexTab]).css({'background-color':'#3DC2C4'})
 }

 // CHOIX PRECEDANT
 function backTab(){
 	$(listTab[indexTab]).css({'display':'none'})
 	$(listStat[indexTab]).css({'display':'none'})
 	$(listTabB[indexTab]).css({'background-color':'black'})
 	indexTab=indexTab-1;
 	if(indexTab<0){
 		indexTab=listTab.length-1;
 	}
 	$(listTab[indexTab]).css({'display':'block'})
 	$(listStat[indexTab]).css({'display':'block'})
 	$(listTabB[indexTab]).css({'background-color':'#3DC2C4'})

 }


// FONCTION IMPRIMER
function imprimer(){
	window.print();
}




// AJOUT EQUIPES
function addTh(e){
	$(e).parent().parent().parent().prepend(
		"<tr onmouseover='showNode(this.firstChild.firstChild)'  onmouseout='hideNode(this.firstChild.firstChild)'>"+
			"<th style='border:0px solid black'>"+
				"<span class='glyphicon glyphicon-plus' onclick='addTh(this)'> </span>"+
			"</th>"+
			"<th>Nom </th>"+
			"<th>Adresse </th>"+
		"</tr>"+
		"<tr onmouseover='showNode(this.firstChild.firstChild)'  onclick='resetbg(this)' "+
		     "onmouseout='hideNode(this.firstChild.firstChild)'>"+
			"<td style='border:0px solid black'>"+
				"<span class='glyphicon glyphicon-remove' onclick='removeNode(this.parentNode.parentNode)'> </span>"+
			"</td>"+
			"<td contenteditable='true' data-placeholder='nom du club' ></td>"+
			"<td contenteditable='true' data-placeholder='son adresse' ></td>"+
		"</tr>"
	);

	$(e).parent().parent().remove()
}

function resetbg(e){
	$(e).css('background-color','rgba(0,0,0,0)');
	$(e).children().css('background-color','rgba(0,0,0,0)');
}
// style modification des clubs

$("tr").mouseenter(function(){
	$(this).find("span").css("opacity","1");
}).mouseleave(function(){
	$(this).find("span").css("opacity","0");
}).click(function() {
	$(this).find("td").css('background-color','rgba(0,0,0,0)')
})

function removeNode(e){
	$(e).remove();
}

function showNode(e){
		$(e).css('opacity','1')
}
function hideNode(e){
		$(e).css('opacity','0')
}



