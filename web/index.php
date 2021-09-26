<?php
require('config.php');


//FAN 
const FAN_SPEED_MIN=1000;
const FAN_SPEED_MAX=6000;

require_once("Diematic.class.php");

//get values for parameters
if (isset($_POST['submit'])) $action=$_POST['submit']; else $action=null;
if (isset($_POST['circuit'])) $circuit=$_POST['circuit']; else if (isset($_GET['circuit'])) $circuit=$_GET['circuit']; else $circuit=$circuit_defaut;
if (isset($_POST['mode_chauffage'])) $mode_chauffage=intval($_POST['mode_chauffage']); else $mode_chauffage=null;
if (isset($_POST['mode_ecs'])) $mode_ecs=intval($_POST['mode_ecs']);else $mode_ecs=null;
if (isset($_POST['nb_jour_antigel'])) $nb_jour_antigel=intval($_POST['nb_jour_antigel']); else $nb_jour_antigel=null;

if (isset($_POST['cons_jour_a'])) $cons_jour_a=round($_POST['cons_jour_a'],1); else $cons_jour_a=null;
if (isset($_POST['cons_nuit_a'])) $cons_nuit_a=round($_POST['cons_nuit_a'],1); else $cons_nuit_a=null;
if (isset($_POST['cons_antigel_a'])) $cons_antigel_a=round($_POST['cons_antigel_a'],1); else $cons_antigel_a=null;

if (isset($_POST['cons_jour_b'])) $cons_jour_b=round($_POST['cons_jour_b'],1); else $cons_jour_b=null;
if (isset($_POST['cons_nuit_b'])) $cons_nuit_b=round($_POST['cons_nuit_b'],1); else $cons_nuit_b=null;
if (isset($_POST['cons_antigel_b'])) $cons_antigel_b=round($_POST['cons_antigel_b'],1); else $cons_antigel_b=null;

if (isset($_POST['cons_ecs'])) $cons_ecs=round($_POST['cons_ecs'],1); else $cons_ecs=null;
if (isset($_POST['cons_ecs_nuit'])) $cons_ecs_nuit=round($_POST['cons_ecs_nuit'],1); else $cons_ecs_nuit=null;

if (isset($_GET['log'])) $log=intval($_GET['log']); else $log=null;
if (isset($_GET['view'])) $view=$_GET['view']; else $view=null;


// function used to generate html content
function get_include_contents($filename,$data=NULL) {

	ob_start();
	require ($filename);
	$contents = ob_get_contents();
	
	ob_end_clean();
	return $contents;
}

//Creation of regulator access
$regulator=new Diematic($modbus_ip,$modbus_port);

//update mode if necessary
if ( ($action=='OK') && ($mode_chauffage !=0) && ($circuit=="A") ) {
	$regulator->setModeA($mode_chauffage,$nb_jour_antigel,$mode_ecs);
} 

if ( ($action=='OK') && ($mode_chauffage !=0) && ($circuit=="B") ) {
	$regulator->setModeB($mode_chauffage,$nb_jour_antigel,$mode_ecs);
} 
//set time if necessary
else if ($action=='Synchro Heure') {
	$regulator->setTime();
}
else if (($action=='Valider Temp') && ($circuit=="A")) {
	$regulator->setTempA($cons_jour_a,$cons_nuit_a,$cons_antigel_a);
	$regulator->setEcsTemp($cons_ecs,$cons_ecs_nuit);
}
else if (($action=='Valider Temp') && ($circuit=="B")) {
	$regulator->setTempB($cons_jour_b,$cons_nuit_b,$cons_antigel_b);
	$regulator->setEcsTemp($cons_ecs,$cons_ecs_nuit);
}

//request data synchro
$regulator->synchro();

//get boiler_mode for circuit A 
//if ECS pump is on  OR pump power=100, burner off, and fan on) (workaround bug on BASE_ECS bit 5 (pump ecs) which is ot always set to 1) 
//mode is water heater
if ((($regulator->diematicReg['BASE_ECS']->value & 0x20) !=0) || (($regulator->diematicReg['FAN_SPEED']->value > FAN_SPEED_MIN ) && (($regulator->diematicReg['BASE_ECS']->value & 0x08)== 0))) $boiler_mode_A=2;
//else if PUMP_A is ON, boiler mode is heater
elseif (($regulator->diematicReg['BASE_ECS']->value & 0x10) !=0)  $boiler_mode_A=1;
else $boiler_mode_A=0;

//get boiler_mode for circuit B
//if pump ECS is ON
if (($regulator->diematicReg['BASE_ECS']->value & 0x20) !=0)  $boiler_mode_B=2;
//else if PUMP_B is ON
else if (($regulator->diematicReg['OPTIONS_B_C']->value & 0x10) !=0) $boiler_mode_B=1;
else $boiler_mode_B=0;

//estimate burner power from fan speed
if ($regulator->diematicReg['FAN_SPEED']->value > FAN_SPEED_MIN ) $burner_power=10*round (($regulator->diematicReg['FAN_SPEED']->value / FAN_SPEED_MAX)*10);
else $burner_power=0;

//add parameters to the regulator object
$boiler=new StdClass();
$boiler->boiler_mode_A=$boiler_mode_A;
$boiler->boiler_mode_B=$boiler_mode_B;
$boiler->burner_power=$burner_power;
$boiler->reg=$regulator->diematicReg;

if ($circuit=="A") {
	if ($view=="set") echo get_include_contents("$language/settings_A.ihm.php",$boiler);
	elseif ($view=="param") echo get_include_contents("$language/params.ihm.php",$boiler);
	else echo get_include_contents("$language/ctrl_A.ihm.php",$boiler);
}

if ($circuit=="B") {
	if ($view=="set") echo get_include_contents("$language/settings_B.ihm.php",$boiler);
	elseif ($view=="param") echo get_include_contents("$language/params.ihm.php",$boiler);
	else echo get_include_contents("$language/ctrl_B.ihm.php",$boiler);
}

if ($log==1) echo "<PRE>",$regulator->log,"</PRE>";		
		
unset($regulator);

//
?>
