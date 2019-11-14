<!DOCTYPE html>
<html>
<head>
<title>Chauffage Circuit B</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css" type="text/css">
</head>


<body>

<h1>Chauffage Circuit B</h1>
<form method="post" action="">
<?php $tableJours=array(1 =>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'); ?>
<table>
	<tr><td>Date</td><td><?=$tableJours[$data->reg['JOUR_SEMAINE']->value]?> <?=$data->reg['JOUR']->value?>/<?=sprintf("%02d",$data->reg['MOIS']->value)?>/<?=sprintf("%02d",$data->reg['ANNEE']->value)?> <?=sprintf("%02d",$data->reg['HEURE']->value)?>:<?=sprintf("%02d",$data->reg['MINUTE']->value)?></td></tr>
	<tr><td>Cons. Jour</td><td><input type="text" name="cons_jour_b" maxlength="4" size="4" value="<?=$data->reg['CONS_JOUR_B']->value?>"> °C</td></tr>
	<tr><td>Cons. Nuit</td><td><input type="text" name="cons_nuit_b" maxlength="4" size="4" value="<?=$data->reg['CONS_NUIT_B']->value?>"> °C</td></tr>
	<tr><td>Cons. Antigel</td><td><input type="text" name="cons_antigel_b" maxlength="4" size="4" value="<?=$data->reg['CONS_ANTIGEL_B']->value?>"> °C</td></tr>
	<tr><td>Cons. ECS Jour</td><td><input type="text" name="cons_ecs" maxlength="4" size="4" value="<?=$data->reg['CONS_ECS']->value?>"> °C</td></tr>
	<tr><td>Cons. ECS Nuit</td><td><input type="text" name="cons_ecs_nuit" maxlength="4" size="4" value="<?=$data->reg['CONS_ECS_NUIT']->value?>"> °C</td></tr>
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
<p><a href="index.php?view=page1&circuit=B">Panneau de Contrôle</a> <a href="index.php?view=param">Paramètres</a></p>
</body>
</html>


