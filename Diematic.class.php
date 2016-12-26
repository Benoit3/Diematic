<?php
//*******************************************************************************//
//               This class allow to acces to the regulator Diematic             //
//               of De Dietrich boiler using a TCP/RS485 convertor               //
//                                                                               //
//	Licence :  Creative Commons  Attribution - Pas d’Utilisation Commerciale     //
//              Partage dans les Mêmes Conditions 3.0 France (CC BY-NC-SA 3.0 FR)//
//                                                                               //
//                                                                               //
//   Date     | Version |    Auteur     | Nature de la modification              //
// 12/11/2015 |  1.0.0  | Domip         | Creation                               //
//            |         |               |                                        //
//*******************************************************************************//
require_once("ModBus.class.php");

class Diematic 
{

//Adress
	const regulatorAddress=0x0A;
	const slaveAddress=0;
	
// ModBus Register Types
	const REAL10=0;
	const BIT=1;
	const INTEGER=2;
	
//Diematic Mode
	const AUTO=8;
	const TEMP_DAY=36;
	const TEMP_NIGHT=34;
	const PERM_DAY=4;
	const PERM_NIGHT=2;
	const ANTIICE=1;
	
//log des échanges
public $log="";
// status to remember if the regulator is connected
private $modBus;

// constructor create the object and connect it to the regulator	
function __construct($ipAddr,$port) {
	$this->modBus=new ModBus(self::slaveAddress,'192.168.9.18',20108);
	
	// diematic register definition
	$this->diematicReg= array();
	$this->diematicReg['CTRL']=new StdClass();
	$this->diematicReg['CTRL']->addr=3;
	$this->diematicReg['CTRL']->type=self::INTEGER;
	
	$this->diematicReg['HEURE']=new StdClass();
	$this->diematicReg['HEURE']->addr=4;
	$this->diematicReg['HEURE']->type=self::INTEGER;
	
	$this->diematicReg['MINUTE']=new StdClass();
	$this->diematicReg['MINUTE']->addr=5;
	$this->diematicReg['MINUTE']->type=self::INTEGER;
	
	$this->diematicReg['JOUR_SEMAINE']=new StdClass();
	$this->diematicReg['JOUR_SEMAINE']->addr=6;
	$this->diematicReg['JOUR_SEMAINE']->type=self::INTEGER;
	
	$this->diematicReg['TEMP_EXT']=new StdClass();
	$this->diematicReg['TEMP_EXT']->addr=7;
	$this->diematicReg['TEMP_EXT']->type=self::REAL10;
	
	$this->diematicReg['NB_JOUR_ANTIGEL']=new StdClass();
	$this->diematicReg['NB_JOUR_ANTIGEL']->addr=13;
	$this->diematicReg['NB_JOUR_ANTIGEL']->type=self::INTEGER;
	
	$this->diematicReg['CONS_JOUR_A']=new StdClass();
	$this->diematicReg['CONS_JOUR_A']->addr=14;
	$this->diematicReg['CONS_JOUR_A']->type=self::REAL10;	

	$this->diematicReg['CONS_NUIT_A']=new StdClass();
	$this->diematicReg['CONS_NUIT_A']->addr=15;
	$this->diematicReg['CONS_NUIT_A']->type=self::REAL10;			
	
	$this->diematicReg['CONS_ANTIGEL_A']=new StdClass();
	$this->diematicReg['CONS_ANTIGEL_A']->addr=16;
	$this->diematicReg['CONS_ANTIGEL_A']->type=self::REAL10;
	
	$this->diematicReg['MODE_A']=new StdClass();
	$this->diematicReg['MODE_A']->addr=17;
	$this->diematicReg['MODE_A']->type=self::BIT;
	
	$this->diematicReg['TEMP_AMB_A']=new StdClass();
	$this->diematicReg['TEMP_AMB_A']->addr=18;
	$this->diematicReg['TEMP_AMB_A']->type=self::REAL10;
	
	$this->diematicReg['TCALC_A']=new StdClass();
	$this->diematicReg['TCALC_A']->addr=21;
	$this->diematicReg['TCALC_A']->type=self::REAL10;
	
	$this->diematicReg['CONS_ECS']=new StdClass();
	$this->diematicReg['CONS_ECS']->addr=59;
	$this->diematicReg['CONS_ECS']->type=self::REAL10;
	
	$this->diematicReg['TEMP_ECS']=new StdClass();
	$this->diematicReg['TEMP_ECS']->addr=62;
	$this->diematicReg['TEMP_ECS']->type=self::REAL10;
	
	$this->diematicReg['TEMP_CHAUD']=new StdClass();
	$this->diematicReg['TEMP_CHAUD']->addr=75;
	$this->diematicReg['TEMP_CHAUD']->type=self::REAL10;
	
	$this->diematicReg['BASE_ECS']=new StdClass();
	$this->diematicReg['BASE_ECS']->addr=89;
	$this->diematicReg['BASE_ECS']->type=self::BIT;
	
	$this->diematicReg['CONS_ECS_NUIT']=new StdClass();
	$this->diematicReg['CONS_ECS_NUIT']->addr=96;
	$this->diematicReg['CONS_ECS_NUIT']->type=self::REAL10;
	
	$this->diematicReg['JOUR']=new StdClass();
	$this->diematicReg['JOUR']->addr=108;
	$this->diematicReg['JOUR']->type=self::INTEGER;
	
	$this->diematicReg['MOIS']=new StdClass();
	$this->diematicReg['MOIS']->addr=109;
	$this->diematicReg['MOIS']->type=self::INTEGER;
	
	$this->diematicReg['ANNEE']=new StdClass();
	$this->diematicReg['ANNEE']->addr=110;
	$this->diematicReg['ANNEE']->type=self::INTEGER;
	
	$this->diematicReg['PRESSION_EAU']=new StdClass();
	$this->diematicReg['PRESSION_EAU']->addr=456;
	$this->diematicReg['PRESSION_EAU']->type=self::REAL10;
	
	$this->diematicReg['ALARME']=new StdClass();
	$this->diematicReg['ALARME']->addr=465;
	$this->diematicReg['ALARME']->type=self::BIT;
		
	
	$this->log.="Connexion Status: ".$this->modBus->status."\n";
	
return($this->modBus->status);
}


function dataDecode($modBusReg) {

foreach($modBusReg as $key => $value ) 
	foreach($this->diematicReg as $register) {

		if ($register->addr==$key) {
			
			switch ($register->type) {
				case self::REAL10:
					$register->value=$value*0.1;
					break;
				case self::INTEGER:
					$register->value=$value;
					break;
				case self::BIT:
					$register->value=$value & 0xFFFF;
					break;
			}
			break;
		}
	}
}

// function used to exchange data with the regulator
function synchro() {

$busStatus=0; 			//bus is in slave mode
$silentDetection=-1;	//wait for a frame
$i=0;


while ($i<500){ 
	//slave mode
	if ($busStatus==0) {
	
		//log
		$this->log.="Index:".$i." Bus Status : Slave Silence Detection :".$silentDetection."\n";
		
		//Get data send to me, if available
		$this->modBus->slaveRx();
		$this->log.=$this->modBus->log;
		
		//arm silent detection on first frame received
		if ( ($silentDetection==-1) && ( ($this->modBus->status==0) || ($this->modBus->status==ModBus::NOT_SUPPORTED_FC))) $silentDetection=0;
		//update silent detection following context
		if ($silentDetection>=0) {
			if (($this->modBus->status==ModBus::FRAME_EMPTY) || ($this->modBus->status==ModBus::SOCKET_ERROR)) $silentDetection++; else $silentDetection=0;
		}
	
		//decode register if necessary
		if ($this->modBus->status==0) $this->dataDecode($this->modBus->rxReg);
		
		//update bus status if no traffic during 1s
		if ($silentDetection>=10) $busStatus=1;
		
		//or wait 100 ms
		usleep(100000);
		$i++;
	}
	//master mode
	else {
		//log
		$this->log.="Index:".$i." Bus Status : Master \n";
		
		//if a register need to be updated

		//mode setting
		if (isset($this->diematicReg['MODE_A']->set) ) {
		
			//varring requested mode
			if ($this->diematicReg['MODE_A']->set!=self::ANTIICE) {
				
				//bug work around, to allow remote control update, uncomment 4 next code lines
				//$this->diematicReg['NB_JOUR_ANTIGEL']->set=1;
				//$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);
				//$this->log.=$this->modBus->log;
				//unset($this->diematicReg['NB_JOUR_ANTIGEL']->set);				
				
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_A']);
				$this->log.=$this->modBus->log;
				unset($this->diematicReg['MODE_A']->set);
				
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);
				$this->log.=$this->modBus->log;
				unset($this->diematicReg['NB_JOUR_ANTIGEL']->set);
		
			} else {
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);
				$this->log.=$this->modBus->log;
				unset($this->diematicReg['NB_JOUR_ANTIGEL']->set);		
			}
		}
		
		//time setting
		if (isset($this->diematicReg['HEURE']->set)) {
			$register=array();
			$register[0]=$this->diematicReg['HEURE'];
			$register[1]=$this->diematicReg['MINUTE'];
			$register[2]=$this->diematicReg['JOUR_SEMAINE'];
			$this->modBus->masterTxN(self::regulatorAddress,$register);
			$this->log.=$this->modBus->log;
			unset($this->diematicReg['HEURE']->set);
			unset($this->diematicReg['MINUTE']->set);
			unset($this->diematicReg['JOUR_SEMAINE']->set);
			unset($register);
		}
	
		
		if (isset($this->diematicReg['JOUR']->set)) {
			$register=array();
			$register[0]=$this->diematicReg['JOUR'];
			$register[1]=$this->diematicReg['MOIS'];
			$this->modBus->masterTxN(self::regulatorAddress,$register);
			$this->log.=$this->modBus->log;
			unset($this->diematicReg['JOUR']->set);
			unset($this->diematicReg['MOIS']->set);
			unset($register);
		}
		
		//temperature setting
		if (isset($this->diematicReg['CONS_JOUR_A']->set)) {
			$register=array();
			$register[0]=$this->diematicReg['CONS_JOUR_A'];
			$register[1]=$this->diematicReg['CONS_NUIT_A'];
			$register[2]=$this->diematicReg['CONS_ANTIGEL_A'];
			$this->modBus->masterTxN(self::regulatorAddress,$register);
			$this->log.=$this->modBus->log;
			unset($this->diematicReg['CONS_JOUR_A']->set);
			unset($this->diematicReg['CONS_NUIT_A']->set);
			unset($this->diematicReg['CONS_ANTIGEL_A']->set);
			unset($register);
		}
		
		//ecs temperature setting
		if (isset($this->diematicReg['CONS_ECS']->set)) {
			$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['CONS_ECS']);
			$this->log.=$this->modBus->log;
			unset($this->diematicReg['CONS_ECS']->set);
		}

		//ecs night temperature setting
		if (isset($this->diematicReg['CONS_ECS_NUIT']->set)) {
			$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['CONS_ECS_NUIT']);
			$this->log.=$this->modBus->log;
			unset($this->diematicReg['CONS_ECS_NUIT']->set);
		}	
		
		
		
		//get 20 registers starting at reg 3
		$this->modBus->masterRx(self::regulatorAddress,3,20);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}
		
		//get 4 registers starting at reg 59
		$this->modBus->masterRx(self::regulatorAddress,59,4);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}
		
		//get 2 registers starting at reg 75
		$this->modBus->masterRx(self::regulatorAddress,75,2);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}
		
		//get 8 registers starting at reg 89
		$this->modBus->masterRx(self::regulatorAddress,89,8);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}

		//get 4 registers starting at reg 108
		$this->modBus->masterRx(self::regulatorAddress,108,3);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}	
	
		//get 10 registers starting at reg 456
		$this->modBus->masterRx(self::regulatorAddress,456,15);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}
		
		//set bus status in slave mode and rearm silent detection
		$busStatus=0;
		$silentDetection=-1;
		//end loop
		$i=500;
	}
	
}

}
	
// function used to new value of mode register
function setMode($mode,$nb_jour_antigel,$mode_ecs) {
	//if the mode value is OK, prepare the register to be updated 
	if ( ($mode==self::TEMP_DAY) || ($mode==self::TEMP_NIGHT) || ($mode==self::AUTO) || ($mode==self::PERM_DAY) || ($mode==self::PERM_NIGHT)) {
		$this->diematicReg['MODE_A']->set=($mode & 0x2F | $mode_ecs & 0x50);
		$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;
	}
	//if the selected mode is ANTIICE, if the $nb_jour_antigel value is OK
	else if  ($mode==self::ANTIICE) {
		//set ecs mode
		$this->diematicReg['MODE_A']->set=$mode & 0x2F;
		//if day number not in [1 -> 99] set it to 1
		if ( ($nb_jour_antigel <1) || ($nb_jour_antigel >=99) ) $nb_jour_antigel=1;
		$this->diematicReg['NB_JOUR_ANTIGEL']->set=$nb_jour_antigel;
	}

	$this->log.="Mode :" . $this->diematicReg['MODE_A']->set." Nb Jours Antigel :" .$this->diematicReg['NB_JOUR_ANTIGEL']->set. "\n";
}

//function used to set Temperature
function setTemp($day,$night,$antiIce) {
	//day temperature, between 10 and 30°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_JOUR_A']->set=min(max(intval(2*$day)*5,100),300);	
	//night temperature, between 10 and 30°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_NUIT_A']->set=min(max(intval(2*$night)*5,100),300);
	//anti ice temperature, between 0,5 and 20°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_ANTIGEL_A']->set=min(max(intval(2*$antiIce)*5,5),200);
}

//function used to set Temperature
function setEcsTemp($day,$night) {
	//day temperature, between 10 and 80°C, step is 5°C, unit is 0.1
	$this->diematicReg['CONS_ECS']->set=min(max(intval($day/5)*50,100),800);	
	//night temperature, between 10 and 80°C, step is 5°C, unit is 0.1
	$this->diematicReg['CONS_ECS_NUIT']->set=min(max(intval($night/5)*50,100),800);
}

// function used to set time
function setTime() {
	//if the mode value is OK, prepare the register to be updated 
	$tempsUnix=time();
	
	//initialise l'horloge de la régulation avec l'heure système
	$this->diematicReg['HEURE']->set=0xFF00 | date('H',$tempsUnix);
	$this->diematicReg['MINUTE']->set=0xFF00 | date('i',$tempsUnix);
	$this->diematicReg['JOUR_SEMAINE']->set=0xFF00 | date('N',$tempsUnix);
	
	$this->diematicReg['JOUR']->set=0xFF00 | date('j',$tempsUnix);
	$this->diematicReg['MOIS']->set=0xFF00 | date('n',$tempsUnix);
	$this->diematicReg['ANNEE']->set=0xFF00 | date('y',$tempsUnix);
}



//destructor used to free ressources
function __destruct() {
	unset($this->modBus);
}

}