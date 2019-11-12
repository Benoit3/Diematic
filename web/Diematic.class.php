<?php
//*******************************************************************************//
//               This class allow to acces to the regulator Diematic             //
//               of De Dietrich boiler using a TCP/RS485 convertor               //
//                                                                               //
//	Licence :  Creative Commons  Attribution - Pas d'Utilisation Commerciale     //
//              Partage dans les Mêmes Conditions 3.0 France (CC BY-NC-SA 3.0 FR)//
//                                                                               //
//*******************************************************************************//
require_once("ModBus.class.php");

class Diematic 
{

//Adress
	const regulatorAddress=0x0A;
	const slaveAddress=0x00;
	
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
	$this->modBus=new ModBus(self::slaveAddress,$ipAddr,$port);
	
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
	
	$this->diematicReg['CONS_JOUR_B']=new StdClass();
	$this->diematicReg['CONS_JOUR_B']->addr=23;
	$this->diematicReg['CONS_JOUR_B']->type=self::REAL10;	

	$this->diematicReg['CONS_NUIT_B']=new StdClass();
	$this->diematicReg['CONS_NUIT_B']->addr=24;
	$this->diematicReg['CONS_NUIT_B']->type=self::REAL10;			
	
	$this->diematicReg['CONS_ANTIGEL_B']=new StdClass();
	$this->diematicReg['CONS_ANTIGEL_B']->addr=25;
	$this->diematicReg['CONS_ANTIGEL_B']->type=self::REAL10;
	
	$this->diematicReg['MODE_B']=new StdClass();
	$this->diematicReg['MODE_B']->addr=26;
	$this->diematicReg['MODE_B']->type=self::BIT;
	
	$this->diematicReg['TEMP_AMB_B']=new StdClass();
	$this->diematicReg['TEMP_AMB_B']->addr=27;
	$this->diematicReg['TEMP_AMB_B']->type=self::REAL10;
	
	$this->diematicReg['TCALC_B']=new StdClass();
	$this->diematicReg['TCALC_B']->addr=32;
	$this->diematicReg['TCALC_B']->type=self::REAL10;

	$this->diematicReg['CONS_ECS']=new StdClass();
	$this->diematicReg['CONS_ECS']->addr=59;
	$this->diematicReg['CONS_ECS']->type=self::REAL10;
	
	$this->diematicReg['TEMP_ECS']=new StdClass();
	$this->diematicReg['TEMP_ECS']->addr=62;
	$this->diematicReg['TEMP_ECS']->type=self::REAL10;
	
	$this->diematicReg['TEMP_CHAUD']=new StdClass();
	$this->diematicReg['TEMP_CHAUD']->addr=75;
	$this->diematicReg['TEMP_CHAUD']->type=self::REAL10;
	
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
		
	$this->diematicReg['BASE_ECS']=new StdClass();
	$this->diematicReg['BASE_ECS']->addr=427;
	$this->diematicReg['BASE_ECS']->type=self::BIT;

	$this->diematicReg['OPTIONS_B_C']=new StdClass();
	$this->diematicReg['OPTIONS_B_C']->addr=428;
	$this->diematicReg['OPTIONS_B_C']->type=self::BIT;

	$this->diematicReg['RETURN_TEMP']=new StdClass();
	$this->diematicReg['RETURN_TEMP']->addr=453;
	$this->diematicReg['RETURN_TEMP']->type=self::REAL10;
	
	$this->diematicReg['SMOKE_TEMP']=new StdClass();
	$this->diematicReg['SMOKE_TEMP']->addr=454;
	$this->diematicReg['SMOKE_TEMP']->type=self::REAL10;
	
	$this->diematicReg['FAN_SPEED']=new StdClass();
	$this->diematicReg['FAN_SPEED']->addr=455;
	$this->diematicReg['FAN_SPEED']->type=self::INTEGER;
	
	$this->diematicReg['PRESSION_EAU']=new StdClass();
	$this->diematicReg['PRESSION_EAU']->addr=456;
	$this->diematicReg['PRESSION_EAU']->type=self::REAL10;
	
	$this->diematicReg['BOILER_TYPE']=new StdClass();
	$this->diematicReg['BOILER_TYPE']->addr=457;
	$this->diematicReg['BOILER_TYPE']->type=self::INTEGER;
	
	$this->diematicReg['PUMP_POWER']=new StdClass();
	$this->diematicReg['PUMP_POWER']->addr=463;
	$this->diematicReg['PUMP_POWER']->type=self::INTEGER;
	
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

//empty reception buffer
//wait 100 ms
usleep(100000);
do {
	$this->modBus->slaveRx();
	$this->log.=$this->modBus->log; }
while ( !(($this->modBus->status==ModBus::FRAME_EMPTY) || ($this->modBus->status==ModBus::SOCKET_ERROR))) ;
	$this->log.="Buffer empty\n";

while ($i<500){ 
	//slave mode
	if ($busStatus==0) {
	
		//log
		$this->log.="Index:".$i." Bus Status : Slave Silence Detection :".$silentDetection."\n";
		
		//Get data send to me, if available
		$this->modBus->slaveRx();
		$this->log.=$this->modBus->log;
		
		//arm silent detection on first frame received
		if ( ($silentDetection==-1) && ( ($this->modBus->status==0) || ($this->modBus->status==ModBus::NOT_SUPPORTED_FC) || ($this->modBus->status==ModBus::NOT_ADRESSED_TO_ME))) $silentDetection=0;
		//update silent detection following context
		if ($silentDetection>=0) {
			if (($this->modBus->status==ModBus::FRAME_EMPTY) || ($this->modBus->status==ModBus::SOCKET_ERROR)) $silentDetection++; else $silentDetection=0;
		}
	
		//decode register if necessary
		if ($this->modBus->status==0) $this->dataDecode($this->modBus->rxReg);
		
		//update bus status if no traffic during 1s
		if ($silentDetection>=12) $busStatus=1;
		
		//or wait 100 ms
		usleep(100000);
		$i++;
	}
	//master mode
	else {
		//log
		$this->log.="Index:".$i." Bus Status : Master \n";

		//Parameters setting
		//mode A setting
		if (isset($this->diematicReg['MODE_A']->set)) {
			//set the new mode
			if ($this->diematicReg['MODE_A']->set==self::ANTIICE) {
				//workaround to warranty activation of permanent antifreeze
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=1;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
				usleep(500000);
				//set antifreeze
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_A']);$this->log.=$this->modBus->log;
			}
			else {
				//update new mode + workaround for remote control update
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_A']);$this->log.=$this->modBus->log;
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=1;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_A']);$this->log.=$this->modBus->log;
				usleep(500000);
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_A']);$this->log.=$this->modBus->log;
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
			}
		}

		//mode B setting
		if (isset($this->diematicReg['MODE_B']->set)) {
			//set the new mode
			if ($this->diematicReg['MODE_B']->set==self::ANTIICE) {
				//workaround to warranty activation of permanent antifreeze
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=1;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
				usleep(500000);
				//set antifreeze
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_B']);$this->log.=$this->modBus->log;
			}
			else {
				//update new mode + workaround for remote control update
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_B']);$this->log.=$this->modBus->log;
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=1;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_B']);$this->log.=$this->modBus->log;
				usleep(500000);
				$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['MODE_B']);$this->log.=$this->modBus->log;
				$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;$this->modBus->masterTx(self::regulatorAddress,$this->diematicReg['NB_JOUR_ANTIGEL']);$this->log.=$this->modBus->log;
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

		if (isset($this->diematicReg['CONS_JOUR_B']->set)) {
			$register=array();
			$register[0]=$this->diematicReg['CONS_JOUR_B'];
			$register[1]=$this->diematicReg['CONS_NUIT_B'];
			$register[2]=$this->diematicReg['CONS_ANTIGEL_B'];
			$this->modBus->masterTxN(self::regulatorAddress,$register);
			$this->log.=$this->modBus->log;
			unset($this->diematicReg['CONS_JOUR_B']->set);
			unset($this->diematicReg['CONS_NUIT_B']->set);
			unset($this->diematicReg['CONS_ANTIGEL_B']->set);
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
		
		
		
		//get 63 registers starting at reg 1
		$this->modBus->masterRx(self::regulatorAddress,1,63);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}
		
		//get 64 registers starting at reg 64
		$this->modBus->masterRx(self::regulatorAddress,64,64);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}

		//get 64 registers starting at reg 128
		//$this->modBus->masterRx(self::regulatorAddress,128,64);
		//$this->log.=$this->modBus->log;
		//if ($this->modBus->status==0) {
			//$this->dataDecode($this->modBus->rxReg);
		//}

		//get 64 registers starting at reg 196
		//$this->modBus->masterRx(self::regulatorAddress,192,64);
		//$this->log.=$this->modBus->log;
		//if ($this->modBus->status==0) {
			//$this->dataDecode($this->modBus->rxReg);
		//}

		//get 64 registers starting at reg 256
		//$this->modBus->masterRx(self::regulatorAddress,256,64);
		//$this->log.=$this->modBus->log;
		//if ($this->modBus->status==0) {
			//$this->dataDecode($this->modBus->rxReg);
		//}
		
		//get 64 registers starting at reg 320
		//$this->modBus->masterRx(self::regulatorAddress,320,64);
		//$this->log.=$this->modBus->log;
		//if ($this->modBus->status==0) {
			//$this->dataDecode($this->modBus->rxReg);
		//}

		//get 64 registers starting at reg 384
		$this->modBus->masterRx(self::regulatorAddress,384,64);
		$this->log.=$this->modBus->log;
		if ($this->modBus->status==0) {
			$this->dataDecode($this->modBus->rxReg);
		}
		
		//get 23 last registers starting at reg 448
		$this->modBus->masterRx(self::regulatorAddress,448,23);
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
function setModeA($mode,$nb_jour_antigel,$mode_ecs) {
	//if the mode value is OK, prepare the register to be updated 
	if ( ($mode==self::TEMP_DAY) || ($mode==self::TEMP_NIGHT) || ($mode==self::AUTO) || ($mode==self::PERM_DAY) || ($mode==self::PERM_NIGHT)) {
		$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;
		$this->diematicReg['MODE_A']->set=($mode & 0x2F | $mode_ecs & 0x50);
	}
	//if the selected mode is ANTIICE
	else if  ($mode==self::ANTIICE) {
		//set  mode
		$this->diematicReg['MODE_A']->set=($mode & 0x2F | $mode_ecs & 0x50);
	}

	$this->log.="Mode A :" . $this->diematicReg['MODE_A']->set." Nb Jours Antigel :" .$this->diematicReg['NB_JOUR_ANTIGEL']->set. "\n";
}

function setModeB($mode,$nb_jour_antigel,$mode_ecs) {
	//if the mode value is OK, prepare the register to be updated 
	if ( ($mode==self::TEMP_DAY) || ($mode==self::TEMP_NIGHT) || ($mode==self::AUTO) || ($mode==self::PERM_DAY) || ($mode==self::PERM_NIGHT)) {
		$this->diematicReg['MODE_B']->set=($mode & 0x2F | $mode_ecs & 0x50);
		$this->diematicReg['NB_JOUR_ANTIGEL']->set=0;
	}
	//if the selected mode is ANTIICE, if the $nb_jour_antigel value is OK
	else if  ($mode==self::ANTIICE) {
		//set  mode
		$this->diematicReg['MODE_B']->set=($mode & 0x2F | $mode_ecs & 0x50);
	}

	$this->log.="Mode B :" . $this->diematicReg['MODE_B']->set." Nb Jours Antigel :" .$this->diematicReg['NB_JOUR_ANTIGEL']->set. "\n";
}

//function used to set Temperature
function setTempA($day,$night,$antiIce) {
	//day temperature, between 10 and 30°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_JOUR_A']->set=min(max(intval(2*$day)*5,100),300);	
	//night temperature, between 10 and 30°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_NUIT_A']->set=min(max(intval(2*$night)*5,100),300);
	//anti ice temperature, between 0,5 and 20°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_ANTIGEL_A']->set=min(max(intval(2*$antiIce)*5,5),200);
}

function setTempB($day,$night,$antiIce) {
	//day temperature, between 10 and 30°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_JOUR_B']->set=min(max(intval(2*$day)*5,100),300);	
	//night temperature, between 10 and 30°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_NUIT_B']->set=min(max(intval(2*$night)*5,100),300);
	//anti ice temperature, between 0,5 and 20°C, step is 0,5°C, unit is 0.1
	$this->diematicReg['CONS_ANTIGEL_B']->set=min(max(intval(2*$antiIce)*5,5),200);
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
