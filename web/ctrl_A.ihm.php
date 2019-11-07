<!DOCTYPE html>
<html>
<head>
<title>Chauffage Circuit A</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" type="text/css">
</head>


<body>

<h1>Chauffage</h1>
<form method="post" action="">
<input type="hidden" name="circuit" value="A">
<?php $tableJours=array(1 =>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'); ?>
<table>
	<tr><td>Date</td><td><?=$tableJours[$data['JOUR_SEMAINE']->value]?> <?=$data['JOUR']->value?>/<?=sprintf("%02d",$data['MOIS']->value)?>/<?=sprintf("%02d",$data['ANNEE']->value)?> <?=sprintf("%02d",$data['HEURE']->value)?>:<?=sprintf("%02d",$data['MINUTE']->value)?></td></tr>
	<tr><td>Temp Int</td><td><?=$data['TEMP_AMB_A']->value?> °C</td></tr>
	<tr><td>Temp Ext</td><td><?=$data['TEMP_EXT']->value?> °C</td></tr>
	<tr><td>Pompe</td><td><?=($data['BASE_ECS']->value) & 0x10 >> 4 ?></td></tr>
	
	<tr><td>Temp ECS</td><td><?=$data['TEMP_ECS']->value?> °C</td></tr>
	
	<tr><td>Bruleur</td><td><?=($data['BASE_ECS']->value & 0x08) >> 3 ?></td></tr>
	<tr><td>Temp Chaud Mesure/Cible</td><td><?=$data['TEMP_CHAUD']->value?> °C / <?=$data['TCALC_A']->value?> °C</td></tr>
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

	<tr><td>Mode Chauffage</td><td>
			<select name="mode_chauffage">
				<option value="0"></option>
				<option value="8"  <?= ((($data['MODE_A']->value&0x2F)==8) ? "SELECTED" : "")  ?> >AUTO</option>
				<option value="36" <?= ((($data['MODE_A']->value&0x2F)==36) ? "SELECTED" : "") ?> >DEROG JOUR</option>
				<option value="34" <?= ((($data['MODE_A']->value&0x2F)==34) ? "SELECTED" : "") ?> >DEROG NUIT</option>
				<option value="4"  <?= ((($data['MODE_A']->value&0x2F)==4) ? "SELECTED" : "") ?> >PERM JOUR</option>
				<option value="2"  <?= ((($data['MODE_A']->value&0x2F)==2) ? "SELECTED" : "") ?> >PERM NUIT</option>
				<option value="1"  <?= ((($data['MODE_A']->value&0x2F)==1) ? "SELECTED" : "") ?> >ANTIGEL</option>
			</select>
	</td></tr>
	<?= (($data['MODE_A']->value&0x2F)==1 &&  ($data['NB_JOUR_ANTIGEL']->value > 0) ?  '<tr><td>Durée Antigel</td><td>'.$data['NB_JOUR_ANTIGEL']->value.'</td></tr>': "")  ?>
	
	<tr><td>Mode ECS</td><td>
			<select name="mode_ecs">
				<option value="0"  <?= ((($data['MODE_A']->value&0x50)==0) ? "SELECTED" : "")  ?> >AUTO</option>
				<option value="80" <?= ((($data['MODE_A']->value&0x50)==0x50) ? "SELECTED" : "") ?> >TEMP</option>
				<option value="16" <?= ((($data['MODE_A']->value&0x50)==0x10) ? "SELECTED" : "") ?> >PERM</option>
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
<p><a href="index.php?view=param&circuit=A">Paramètres</a></p> 
</body>
</html>


