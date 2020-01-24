<?php
class Club{
	private $nom; //string
	private $adresse; // string
	private $groupe; // int
	private $data; // []
	public function __construct($nom,$adresse,$data){
		$this->nom = $nom;
		$this->adresse = $adresse;
		$this->groupe = 0;
		$this->data = $data;
	}
	// getteur 
	public function getNom(){return $this->nom;}
	public function getAdresse(){return $this->adresse;}
	public function getGroupe(){return $this->groupe;}
	//setteur
	public function setNom($newNom){$this->nom=$newNom;}
	public function setAdresse($newAdresse){$this->adresse=$newAdresse;}
	public function setGroupe($newCluster){$this->groupe=$newCluster;}
	//methode
	public function dist_tps($club2){
		return getData($this->data,$this->nom,$this->adresse,$club2->getNom(),$club2->getAdresse());
	}	
}

?>