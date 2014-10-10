﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<?php echo $statspagemeta;?>
	
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>  
	
	<title><?php echo $language_pack['title'];?> :: <?php echo $title;?></title>
	<link href="css.php?file=style" rel="stylesheet" type="text/css" />
	<link href="css.php?file=player" rel="stylesheet" type="text/css" />
	<link href="css.php?file=stats" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="./savi/jquery.js"></script>
	<script type="text/javascript" src="./savi/steamprofile.js"></script>

	<style type="text/css">
		body {
			margin: 0;
			padding: 0;
			background: url(<?php $filepath = '/css/img/stats_bg.jpg'; echo file_exists($template_properties['current_template_path'] . $filepath) ? $template_properties['current_template_path'] . $filepath : './templates/default' . $filepath;?>)  no-repeat fixed center top transparent;
			background-color: rgb(21,21,21);
			font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
			font-size: 13px;
			color: #D4D4D4;
		}
	</style>
<?php echo $template_properties['extra_headers']; ?>

</head>
<body>

<!-- start tooltip control -->
<script type="text/javascript" src="js.php?file=statstooltip"></script>
<!-- end tooltip control -->

<!-- start combobox control -->
<script type="text/javascript" src="js.php?file=statscombobox"></script>
<!-- end combobox control -->

<!-- start header -->
<div id="header">
	<!-- Serach Bar -->
	<div id="search">
		<?php echo $language_pack['searchfor']; ?>
		<form method="post" action="search.php">
			<input type="text" id="s" name="search" value="" />
			<input type="submit" id="x" name="submit" value="<?php echo $language_pack['search']; ?>" />
		</form>
	</div>
	<div id="logo">
		<h2><?php echo $site_name;?></h2>
	</div>
	<!-- Zombies killed, Players Served -->
	<div id="players_served" align="right">
		<?php foreach ($header_extra as $title => $value): ?>
		<h2><?php echo $title;?>: <?php echo number_format($value);?></h2>
		<?php endforeach; ?>
	</div>
</div>
<!--
<div id="motd">
	<?php echo $motd_message;?>
	<h2>Message Of The Day</h2>
	<ul>
		<li style="font-size: 10px; line-height: 12px; color: rgb(255, 102, 51);" >This server is the home of Custom Player Stats. Tip of the day: You can mute Custom Player Stats from Rank Menu. Type RANKMENU to open Rank Menu! </li>
	</ul>
</div>-->
<!-- end header -->

<!-- start page -->
<div id="page">
	<!-- start content -->
	<div id="content">
	<div class="post">
			<h1 class="title" style="background: none; padding: 0; margin-top: -10px;"><?php echo $page_heading;?></h1>
		</div>

		<?php echo $body;?>
	</div>
	<!-- end content -->
<?php echo $template_properties['navigation']; ?>

	<div style="clear: both;">&nbsp;</div>
	<p id="legal">
		<span id="legal1">Copyright &copy; 2013 <a href="http://forums.alliedmods.net/member.php?u=52082" target="_blank" >muukis</a> | Left 4 Dead Stats written for <a href="http://forums.alliedmods.net/showthread.php?t=115965" target="_blank" >SourceMod</a> | Designed by <a href="http://forums.alliedmods.net/member.php?u=178524" target="_blank" >JonnyBoy0719</a></span><br>
	</p>
</div>
<!-- end page -->

</body>
</html>