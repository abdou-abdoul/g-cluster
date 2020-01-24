<?php
class Cluster{
	public  $id; // id int 
	private $taille=0; // taille int
	private $indexClubs=[]; // index {}
	private $matrice ; // matrice des donnees [[]]

	public function __construct($matrice,$id){
		$this->id = $id;
		$this->matrice = $matrice;

	}
	// getteur 
	public function getTaille(){return $this->taille;}
	public function getId(){return $this->id;}
	public function getIndexClubs(){return $this->indexClubs;}
	public function getMatrice(){return $this->matrice;}
	//setteur
	
	//methode
	public function evaluer(){
		// on caclule le max de la somme des distance 
		// d'un point i vers tous les autres points 
		$matrice = $this->matrice;
		$max=0;
		$somme=0;
		foreach ($this->indexClubs as $i) {
			//$somme=0;
			foreach ($this->indexClubs as $j ) {
				$somme+=$matrice[$i][$j];
				if($somme>$max){
					$max=$somme;

				}
			}
			
		}
		return $max/$this->taille;
	}



	public function addClub($index){
		if(!in_array($index, $this->indexClubs)){
			$this->taille++;
			$this->indexClubs[]=$index;
		}

	}
	
	public function removeClub($index){
		$indexClubs=$this->indexClubs;
		if(in_array($index, $indexClubs)){
			$i=array_search($index,$indexClubs);
			unset($indexClubs[$i]);
			$this->indexClubs=array_values($indexClubs);
			$this->taille-- ;
		}	
	}
}
?>