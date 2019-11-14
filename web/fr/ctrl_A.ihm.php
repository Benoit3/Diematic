<!DOCTYPE html>
<html>
<head>
<title>Chauffage Circuit A</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" type="text/css">
</head>


<body>

<h1>Chauffage Circuit A</h1>
<form method="post" action="">
<input type="hidden" name="circuit" value="A">
<?php $tableJours=array(1 =>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'); ?>
<?php $boilerMode=array('Veille','Chauffage','Chauffe Eau'); ?>
<table>
	<tr><td>Date</td><td><?=$tableJours[$data->reg['JOUR_SEMAINE']->value]?> <?=$data->reg['JOUR']->value?>/<?=sprintf("%02d",$data->reg['MOIS']->value)?>/<?=sprintf("%02d",$data->reg['ANNEE']->value)?> <?=sprintf("%02d",$data->reg['HEURE']->value)?>:<?=sprintf("%02d",$data->reg['MINUTE']->value)?></td></tr>
	<tr><td>Temp Int</td><td><?=$data->reg['TEMP_AMB_A']->value?> °C</td></tr>
	<tr><td>Temp Ext</td><td><?=$data->reg['TEMP_EXT']->value?> °C</td></tr>
	<tr><td>Temp ECS</td><td><?=$data->reg['TEMP_ECS']->value?> °C</td></tr>
	<tr><td>Etat</td><td><?=$boilerMode[$data->boiler_mode_A]?></td></tr>
	<tr><td>Puiss Bruleur</td><td><?=$data->burner_power?> %</td></tr>
	<tr><td>Temp Chaud Mesure/Cible</td><td><?=$data->reg['TEMP_CHAUD']->value?> °C / <?=$data->reg['TCALC_A']->value?> °C</td></tr>
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

	<tr><td>Mode Chauffage</td><td>
			<select name="mode_chauffage">
				<option value="0"></option>
				<option value="8"  <?= ((($data->reg['MODE_A']->value&0x2F)==8) ? "SELECTED" : "")  ?> >AUTO</option>
				<option value="36" <?= ((($data->reg['MODE_A']->value&0x2F)==36) ? "SELECTED" : "") ?> >DEROG JOUR</option>
				<option value="34" <?= ((($data->reg['MODE_A']->value&0x2F)==34) ? "SELECTED" : "") ?> >DEROG NUIT</option>
				<option value="4"  <?= ((($data->reg['MODE_A']->value&0x2F)==4) ? "SELECTED" : "") ?> >PERM JOUR</option>
				<option value="2"  <?= ((($data->reg['MODE_A']->value&0x2F)==2) ? "SELECTED" : "") ?> >PERM NUIT</option>
				<option value="1"  <?= ((($data->reg['MODE_A']->value&0x2F)==1) ? "SELECTED" : "") ?> >ANTIGEL</option>
			</select>
	</td></tr>
	<?= (($data->reg['MODE_A']->value&0x2F)==1 &&  ($data->reg['NB_JOUR_ANTIGEL']->value > 0) ?  '<tr><td>Durée Antigel</td><td>'.$data->reg['NB_JOUR_ANTIGEL']->value.'</td></tr>': "")  ?>
	
	<tr><td>Mode ECS</td><td>
			<select name="mode_ecs">
				<option value="0"  <?= ((($data->reg['MODE_A']->value&0x50)==0) ? "SELECTED" : "")  ?> >AUTO</option>
				<option value="80" <?= ((($data->reg['MODE_A']->value&0x50)==0x50) ? "SELECTED" : "") ?> >TEMP</option>
				<option value="16" <?= ((($data->reg['MODE_A']->value&0x50)==0x10) ? "SELECTED" : "") ?> >PERM</option>
			</select>
	</td></tr>
	
	<tr><td>
	<div style="text-align:left"><input type="submit" name="submit" value="Refresh"></div>
	</td>
	<td>
	<div style="text-align:right" ><input type="submit" name="submit" value="OK"></div>
	</td></tr>
</table>
</form>
<p><a href="index.php?view=set&circuit=A">Réglages</a> <a href="index.php?view=param">Paramètres</a></p>
</body>
</html>


