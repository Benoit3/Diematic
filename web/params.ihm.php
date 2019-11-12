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
<table>
	<tr><td>Date</td><td><?=$tableJours[$data['JOUR_SEMAINE']->value]?> <?=$data['JOUR']->value?>/<?=sprintf("%02d",$data['MOIS']->value)?>/<?=sprintf("%02d",$data['ANNEE']->value)?> <?=sprintf("%02d",$data['HEURE']->value)?>:<?=sprintf("%02d",$data['MINUTE']->value)?></td></tr>
	<tr><td>Type Chaudière</td><td><?=$data['BOILER_TYPE']->value?></td></tr>
	<tr><td>CTRL (Version Soft)</td><td><?=$data['CTRL']->value?></td></tr>
	<tr><td>Temp Int</td><td><?=$data['TEMP_AMB_A']->value?> °C</td></tr>
	<tr><td>Temp Ext</td><td><?=$data['TEMP_EXT']->value?> °C</td></tr>
	<tr><td>Temp ECS</td><td><?=$data['TEMP_ECS']->value?> °C</td></tr>
	<tr><td>Pompe Chauffage</td><td><?=($data['BASE_ECS']->value & 0x10) >> 4 ?></td></tr>
	<tr><td>Puiss Pompe</td><td><?=$data['PUMP_POWER']->value ?></td></tr>
	<tr><td>Bruleur</td><td><?=($data['BASE_ECS']->value & 0x08) >> 3 ?></td></tr>
	<tr><td>Vitesse Ventilateur</td><td><?=$data['FAN_SPEED']->value?></td></tr>
	<tr><td>Temp Chaud Mesure/Cible</td><td><?=$data['TEMP_CHAUD']->value?> °C / <?=$data['TCALC_A']->value?> °C</td></tr>
	<tr><td>Temp Retour</td><td><?=$data['RETURN_TEMP']->value?> °C</td></tr>
	<tr><td>Temp Fumées</td><td><?=$data['SMOKE_TEMP']->value?> °C</td></tr>
	<tr><td>Press Eau</td><td><?=$data['PRESSION_EAU']->value?> Bar</td></tr>

	<tr><td>Alarme</td>
		<?= (($data['ALARME']->value!=0) ? '<td style="color:red" >' : '<td>')  ?>
		<?=$data['ALARME']->value?>
		<?= (($data['ALARME']->value==10) ? '<br/> Défaut Sonde Retour' : '')  ?>
		<?= (($data['ALARME']->value==21) ? '<br/> Pression d\'eau basse' : '')  ?>
		<?= (($data['ALARME']->value==26) ? '<br/> Défaut Allumage' : '')  ?>
		<?= (($data['ALARME']->value==28) ? '<br/> STB Chaudière' : '')  ?>
		<?= (($data['ALARME']->value==30) ? '<br/> Rearm. Coffret' : '')  ?>
		<?= (($data['ALARME']->value==31) ? '<br/> Défaut Sonde Fumée' : '')  ?>
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


