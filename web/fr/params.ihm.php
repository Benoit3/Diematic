<!DOCTYPE html>
<html>
<head>
<title>Paramètres</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" type="text/css">
</head>


<body>

<h1>Paramètres</h1>
<form method="post" action="">
<input type="hidden" name="circuit" value="A">
<?php $tableJours=array(1 =>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'); ?>
<?php $boilerMode=array('Veille','Chauffage','Chauffe Eau'); ?>
<table>
	<tr><td>Date</td><td><?=$tableJours[$data->reg['JOUR_SEMAINE']->value]?> <?=$data->reg['JOUR']->value?>/<?=sprintf("%02d",$data->reg['MOIS']->value)?>/<?=sprintf("%02d",$data->reg['ANNEE']->value)?> <?=sprintf("%02d",$data->reg['HEURE']->value)?>:<?=sprintf("%02d",$data->reg['MINUTE']->value)?></td></tr>
	<tr><td>Type Chaudière</td><td><?=$data->reg['BOILER_TYPE']->value?></td></tr>
	<tr><td>CTRL (Version Soft)</td><td><?=$data->reg['CTRL']->value?></td></tr>
	<tr><td>Temp Int</td><td><?=$data->reg['TEMP_AMB_A']->value?> °C</td></tr>
	<tr><td>Temp Ext</td><td><?=$data->reg['TEMP_EXT']->value?> °C</td></tr>
	<tr><td>Temp ECS</td><td><?=$data->reg['TEMP_ECS']->value?> °C</td></tr>
	<tr><td>Pompe A</td><td><?=($data->reg['BASE_ECS']->value & 0x10) >> 4 ?></td></tr>
	<tr><td>Pompe B</td><td><?=($data->reg['OPTIONS_B_C']->value & 0x10) >> 4 ?></td></tr>
	<tr><td>Pompe ECS</td><td><?=($data->reg['BASE_ECS']->value & 0x20) >> 5 ?></td></tr>
	<tr><td>Puiss Pompe</td><td><?=$data->reg['PUMP_POWER']->value ?> %</td></tr>
	<tr><td>Bruleur</td><td><?=($data->reg['BASE_ECS']->value & 0x08) >> 3 ?></td></tr>
	<tr><td>Vitesse Ventilateur</td><td><?=$data->reg['FAN_SPEED']->value?> tr/mn </td></tr>
	<tr><td>Etat A</td><td><?=$boilerMode[$data->boiler_mode_A]?></td></tr>
	<tr><td>Etat B</td><td><?=$boilerMode[$data->boiler_mode_B]?></td></tr>
	<tr><td>Puiss Bruleur</td><td><?=$data->burner_power?> %</td></tr>
	<tr><td>Temp Chaud Mesure/Cible</td><td><?=$data->reg['TEMP_CHAUD']->value?> °C / <?=$data->reg['TCALC_A']->value?> °C</td></tr>
	<tr><td>Temp Retour</td><td><?=$data->reg['RETURN_TEMP']->value?> °C</td></tr>
	<tr><td>Temp Fumées</td><td><?=$data->reg['SMOKE_TEMP']->value?> °C</td></tr>
	<tr><td>Press Eau</td><td><?=$data->reg['PRESSION_EAU']->value?> Bar</td></tr>

	<tr><td>Alarme</td>
		<?= (($data->reg['ALARME']->value!=0) ? '<td style="color:red" >' : '<td>')  ?>
		<?=$data->reg['ALARME']->value?>
		<?= (($data->reg['ALARME']->value==10) ? '<br/> Défaut Sonde Retour' : '')  ?>
		<?= (($data->reg['ALARME']->value==21) ? '<br/> Pression d\'eau basse' : '')  ?>
		<?= (($data->reg['ALARME']->value==26) ? '<br/> Défaut Allumage' : '')  ?>
		<?= (($data->reg['ALARME']->value==28) ? '<br/> STB Chaudière' : '')  ?>
		<?= (($data->reg['ALARME']->value==30) ? '<br/> Rearm. Coffret' : '')  ?>
		<?= (($data->reg['ALARME']->value==31) ? '<br/> Défaut Sonde Fumée' : '')  ?>
		</td>
	</tr>
	
	<tr><td>
	<div style="text-align:left"><input type="submit" name="submit" value="Refresh"></div>
	</td>
	<td>
	</td></tr>
</table>
</form>
<p><a href="index.php?view=page1">Panneau de Contrôle</a> <a href="index.php?view=set">Réglages</a></p>
</body>
</html>


