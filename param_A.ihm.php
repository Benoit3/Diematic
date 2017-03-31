<!DOCTYPE html>
<html>
<head>
<title>Chauffage</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" type="text/css">
</head>


<body>

<h1>Chauffage</h1>
<form method="post" action="">
<?php $tableJours=array(1 =>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'); ?>
<table>
	<tr><td>Date</td><td><?=$tableJours[$data['JOUR_SEMAINE']->value]?> <?=$data['JOUR']->value?>/<?=sprintf("%02d",$data['MOIS']->value)?>/<?=sprintf("%02d",$data['ANNEE']->value)?> <?=sprintf("%02d",$data['HEURE']->value)?>:<?=sprintf("%02d",$data['MINUTE']->value)?></td></tr>
	<tr><td>Cons. Jour</td><td><input type="text" name="cons_jour_a" maxlength="4" size="4" value="<?=$data['CONS_JOUR_A']->value?>"> °C</td></tr>
	<tr><td>Cons. Nuit</td><td><input type="text" name="cons_nuit_a" maxlength="4" size="4" value="<?=$data['CONS_NUIT_A']->value?>"> °C</td></tr>
	<tr><td>Cons. Antigel</td><td><input type="text" name="cons_antigel_a" maxlength="4" size="4" value="<?=$data['CONS_ANTIGEL_A']->value?>"> °C</td></tr>
	<tr><td>Cons. ECS Jour</td><td><input type="text" name="cons_ecs" maxlength="4" size="4" value="<?=$data['CONS_ECS']->value?>"> °C</td></tr>
	<tr><td>Cons. ECS Nuit</td><td><input type="text" name="cons_ecs_nuit" maxlength="4" size="4" value="<?=$data['CONS_ECS_NUIT']->value?>"> °C</td></tr>
	<tr><td>CTRL (Version Soft)</td><td><?=$data['CTRL']->value?></td></tr>
	<tr>
		<td><div style="text-align:left"><input type="submit" name="submit" value="Synchro Heure"></div></td>
		<td><div style="text-align:left"><input type="submit" name="submit" value="Refresh"></div></td>
	</tr>
	<tr>
		<td></td>
		<td><div style="text-align:left"><input type="submit" name="submit" value="Valider Temp"></div></td>
	</tr>
</table>

</form>
<p><a href="index.php?view=page1">Panneau de Contrôle</a></p>
</body>
</html>


