﻿<?php
/*
================================================
LEFT 4 DEAD AND LEFT 4 DEAD 2 PLAYER RANK
Copyright (c) 2010 Mikko Andersson
================================================
Common PHP functions and code - "common.php"
================================================
*/

// If its not installed, then head into install.php
if (!$l4dstats_web_installed)
{
	header("Location: install/index.php");
	die();
}

// Allow debug parameter set from URL
$allow_debug = 1;

error_reporting(E_ERROR);

$debug = ($allow_debug && isset($_GET['debug']) && $_GET['debug']);

$get_parameters = '?';

foreach ($_GET as $key => $value)
{
	if ($key == 'template' || $key == 'lang' || $key == 'debug')
	{
		continue;
	}

	$get_parameters .= $key . '=' . $value . '&';
}

$template_properties['get_parameters'] = $get_parameters;

// Include configuration file
require("./config.php");

// Include language
require("./languages.php");

// Include Template engine class
require("./class_template.php");

// IP to Country
require("./ip2country.php");
$ip2c = new ip2country();
$ip2c->set_tableprefix($mysql_ip2c_tableprefix);

// Include template
require("./templates.php");

function php4_scandir($dir, $listDirectories=false, $skipDots=true)
{
	$dirArray = array();

	if ($handle = opendir($dir))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ((($file == "." || $file == "..") && !$skipDots) || ($file != "." && $file != ".."))
			{
				if (!$listDirectories && is_dir($file)) 
					continue;

				array_push($dirArray, basename($file));
			}
		}

		closedir($handle);
	}

	return $dirArray;
}

function getfriendid($pszAuthID)
{
	$iServer = "0";
	$iAuthID = "0";

	$szAuthID = $pszAuthID;

	$szTmp = strtok($szAuthID, ":");

	while(($szTmp = strtok(":")) !== false)
	{
		$szTmp2 = strtok(":");
		if($szTmp2 !== false)
		{
			$iServer = $szTmp;
			$iAuthID = $szTmp2;
		}
	}

	if($iAuthID == "0")
		return "0";

	$i64friendID = bcmul($iAuthID, "2");

	//Friend ID's with even numbers are the 0 auth server.
	//Friend ID's with odd numbers are the 1 auth server.
	$i64friendID = bcadd($i64friendID, bcadd("76561197960265728", $iServer));

	return $i64friendID;
}

function formatage($date) {
	global $language_pack;
	$nametable = array(" " . $language_pack['seconds'], " " . $language_pack['minutes'], " " . $language_pack['hours'], " " . $language_pack['days'], " " . $language_pack['weeks'], " " . $language_pack['months'], " " . $language_pack['years']);
	$agetable = array("60", "60", "24", "7", "4", "12", "10");
	$ndx = 0;

	while ($date > $agetable[$ndx]) {
		$date = $date / $agetable[$ndx];
		$ndx++;
		next($agetable);
	}

	return number_format($date, 2).$nametable[$ndx];
}

function getpopulation($population, $file, $cityonly) {
	$cityarr = array();
	$page = fopen($file, "r");
	while (($data = fgetcsv($page, 1000, ",")) !== FALSE) {
		if ((strstr($data[0], "County") || strstr($data[0], "Combined")) && $cityonly)
			continue;

		$cityarr[$data[1]] = $data[2];
	}

	fclose($page);
	asort($cityarr, SORT_NUMERIC);

	$returncity = "";
	$returncity2 = "";

	foreach ($cityarr as $city => $pop) {
		if ($population > $pop)
			$returncity = $city;
		else {
			$returncity2 = $city;
			break;
		}
	}

	$return = array($returncity,
					$cityarr[$returncity],
					$returncity2,
					$cityarr[$returncity2]);

	return $return;
}

function gettotalpointsraw($row)
{
	$totalpoints = 0;

	if ($game_version != 1)
		$totalpoints = $row['points'] + $row['points_realism'] + $row['points_survivors'] + $row['points_infected'] + $row['points_survival'] + $row['points_scavenge_survivors'] + $row['points_scavenge_infected'] + $row['points_realism_survivors'] + $row['points_realism_infected'] + $row['points_mutations'];
	else
		$totalpoints = $row['points'] + $row['points_survivors'] + $row['points_infected'] + $row['points_survival'];

	return $totalpoints;
}

function gettotalpoints($row)
{
	return number_format(gettotalpointsraw($row));
}

function gettotalplaytimecalc($row)
{
	if ($game_version != 1)
		return $row['playtime'] + $row['playtime_realism'] + $row['playtime_versus'] + $row['playtime_survival'] + $row['playtime_scavenge'] + $row['playtime_realismversus'] + $row['playtime_mutations'];
	else
		return $row['playtime'] + $row['playtime_versus'] + $row['playtime_survival'];
}

function gettotalplaytime($row)
{
	return getplaytime(gettotalplaytimecalc($row));
}

function getplaytime($minutes)
{
	global $language_pack;
	return formatage($minutes * 60) . " (" . number_format($minutes) . " " . $language_pack['minutes'] . ")";
}

function getppm($__points, $__playtime)
{
	//echo "\$__points=" . $__points . "<br>";
	//echo "\$__playtime=" . $__playtime . "<br>";
	if ($__points != 0 && $__playtime != 0)
		return $__points / $__playtime;

	return 0.0;
}

function getserversettingsvalue($name)
{
	global $mysql_tableprefix;

	$q = "SELECT svalue FROM " . $mysql_tableprefix . "server_settings WHERE sname = '" . mysql_real_escape_string($name) . "'";
	$res = mysql_query($q);

	if ($res && mysql_num_rows($res) == 1 && ($r = mysql_fetch_array($res)))
		return $r['svalue'];

	return "";
}

function createtablerowtooltip($row, $i)
{
	return "<tr>";
}

function getplayerinfo($row)
{
	global $showplayerflags, $ip2c;

	$retval['name'] = htmlentities($row['name'], ENT_COMPAT, "UTF-8");
	$retval['ip'] = $row['ip'];
	$retval['flag'] = ($showplayerflags ? $ip2c->get_country_flag($row['ip']) : "");
	$retval['steamid'] = $row['steamid'];

	$retval['points'] = $row['points'];
	$retval['totalpoints'] = gettotalpointsraw($row);
	$retval['points_coop'] = $row['points_coop'];
	$retval['points_realism'] = $row['points_realism'];
	$retval['points_versus'] = $row['points_survivors'] + $row['points_infected'];
	$retval['points_versus_sur'] = $row['points_survivors'];
	$retval['points_versus_inf'] = $row['points_infected'];
	$retval['points_survival'] = $row['points_survival'];
	$retval['points_scavenge'] = $row['points_scavenge_survivors'] + $row['points_scavenge_infected'];
	$retval['points_scavenge_sur'] = $row['points_scavenge_survivors'];
	$retval['points_scavenge_inf'] = $row['points_scavenge_infected'];
	$retval['points_realismversus'] = $row['points_realism_survivors'] + $row['points_realism_infected'];
	$retval['points_realismversus_sur'] = $row['points_realism_survivors'];
	$retval['points_realismversus_inf'] = $row['points_realism_infected'];
	$retval['points_mutations'] = $row['points_mutations'];
	$retval['points_mutations'] = $row['points_mutations'];

	$retval['totalplaytime'] = gettotalplaytime($row);
	$retval['playtime_coop'] = getplaytime($row['playtime']);
	$retval['playtime_realism'] = getplaytime($row['playtime_realism']);
	$retval['playtime_versus'] = getplaytime($row['playtime_versus']);
	$retval['playtime_survival'] = getplaytime($row['playtime_survival']);
	$retval['playtime_scavenge'] = getplaytime($row['playtime_scavenge']);
	$retval['playtime_realismversus'] = getplaytime($row['playtime_realismversus']);
	$retval['playtime_mutations'] = getplaytime($row['playtime_mutations']);

	$retval['ppm_coop'] = getppm($row['points'], $row['playtime']);
	$retval['ppm_versus'] = getppm($row['points_survivors'] + $row['points_infected'], $row['playtime_versus']);
	$retval['ppm_survival'] = getppm($row['points_survival'], $row['playtime_survival']);
	$retval['ppm_realism'] = getppm($row['points_realism'], $row['playtime_realism']);
	$retval['ppm_scavenge'] = getppm($row['points_scavenge_survivors'] + $row['points_scavenge_infected'], $row['playtime_scavenge']);
	$retval['ppm_realismversus'] = getppm($row['points_realism_survivors'] + $row['points_realism_infected'], $row['playtime_realismversus']);
	$retval['ppm_mutations'] = getppm($row['points_mutations'], $row['playtime_mutations']);

	$retval['row'] = $row;

	return $retval;
}

function parseplayersummary($profilexml)
{
	return parseplayerprofile($profilexml, "/profile/summary");
}

function parseplayerheadline($profilexml)
{
	return parseplayerprofile($profilexml, "/profile/headline");
}

function parseplayername($profilexml)
{
	return parseplayerprofile($profilexml, "/profile/steamID");
}

function parseplayerhoursplayed2wk($profilexml)
{
	return parseplayerprofile($profilexml, "/profile/hoursPlayed2Wk");
}

function parseplayersteamrating($profilexml)
{
	return parseplayerprofile($profilexml, "/profile/steamRating");
}

function parseplayermembersince($profilexml)
{
	return parseplayerprofile($profilexml, "/profile/memberSince");
}

function parseplayerprivacystate($profilexml)
{
	return parseplayerprofile($profilexml, "/profile/privacyState");
}

/*
*	Parse player Steam profile
*	Parameters:
*		profilexml	- [SimpleXMLElement] Player Steam profile XML
*		xpathnode		- [String] XML path for the node
*	Returns:
*		[String] XML Node value
*/
function parseplayerprofile($profilexml, $xpathnode)
{
	$arr = $profilexml->xpath($xpathnode);
	
	if (!$arr || count($arr) != 1)
		return "";

	return "" . $arr[0];
}

/*
*	Get player avatar
*	Parameters:
*		profilexml	- [SimpleXMLElement] Player Steam profile XML
*		avatarsize	- [String] icon / medium / full
*	Returns:
*		[String] Image URL
*/
function parseplayeravatar($profilexml, $avatarsize)
{
	if (!$profilexml || !$avatarsize)
	{
		return "";
	}

	$retval = "";
	
	switch (strtolower($avatarsize))
	{
		case "icon":
			if ($profilexml->avatarIcon)
			{
				$retval = $profilexml->avatarIcon;
			}
			break;

		case "medium":
			if ($profilexml->avatarMedium)
			{
				$retval = $profilexml->avatarMedium;
			}
			break;

		case "full":
			if ($profilexml->avatarFull)
			{
				$retval = $profilexml->avatarFull;
			}
			break;
	}
	
	return $retval;
}

/*
*	Get player avatar
*	Parameters:
*		steamid			- [String] Player Steam ID
*		avatarsize	- [String] icon / medium / full
*	Returns:
*		[String] Image URL
*/
function getplayeravatar($steamid, $avatarsize)
{
	if (!$steamid || !$avatarsize)
	{
		return "";
	}

	$xml = getplayersteamprofilexml($steamid);
	return parseplayeravatar($xml, $avatarsize);
}

/*
*	Get player Steam profile XML
*	Parameters:
*		steamid			- [String] Player Steam ID
*	Returns:
*		[SimpleXMLElement] Steam profile XML
*/
function getplayersteamprofilexml($steamid)
{
	global $xml_ply_profile;

	if (!$steamid)
	{
		return;
	}

	if ($xml_ply_profile == true) {
		return simplexml_load_file("http://steamcommunity.com/profiles/" . getfriendid($steamid) . "?xml=1");
	}

}

/*
Database fields
*/

$TOTALPOINTS = "points + points_survivors + points_infected + points_survival" . ($game_version != 1 ? " + points_realism + points_scavenge_survivors + points_scavenge_infected + points_realism_survivors + points_realism_infected + points_mutations" : "");
$TOTALPLAYTIME = "playtime + playtime_versus + playtime_survival" . ($game_version != 1 ? " + playtime_realism + playtime_scavenge + playtime_realismversus + playtime_mutations" : "");

if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data) {
		$f = @fopen($filename, 'w');
		if (!$f) {
			return false;
		} else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

if (basename($_SERVER['PHP_SELF']) !== "update/index.php" && basename($_SERVER['PHP_SELF']) !== "install/index.php") {
	if (file_exists("./install/index.php") && $l4dstats_web_installed) {
		echo "Delete the folder <b>install</b> before running webstats!<br />\n";
		exit;
	}
	elseif (file_exists("./update/index.php")) {
		echo "Delete the folder <b>update.php</b> before running webstats!<br />\n";
		exit;
	}
}

$con_ip2c = 0;
if (strlen($mysql_ip2c_server) > 0)
{
	$con_ip2c = mysql_connect($mysql_ip2c_server, $mysql_ip2c_user, $mysql_ip2c_password);
	mysql_select_db($mysql_ip2c_db, $con_ip2c);
	mysql_query("SET NAMES 'utf8'", $con_ip2c);
}

$con_main = mysql_connect($mysql_server, $mysql_user, $mysql_password);
mysql_select_db($mysql_db, $con_main);
mysql_query("SET NAMES 'utf8'", $con_main);

if (!$con_ip2c)
	$con_ip2c = $con_main;

$ip2c->set_connection($con_ip2c);

$coop_campaigns = array();
$versus_campaigns = array();
$realism_campaigns = array();
$survival_campaigns = array();
$scavenge_campaigns = array();
$realismversus_campaigns = array();
$mutations_campaigns = array();

if ($game_version == 1)
{
	$coop_campaigns = array("l4d_hospital" => "No Mercy",
					   "l4d_airport" => "Dead Air",
					   "l4d_smalltown" => "Death Toll",
					   "l4d_farm" => "Blood Harvest",
					   "l4d_garage" => "Crash Course",
					   "" => "Custom Maps");

	$versus_campaigns = array("l4d_vs_hospital" => "No Mercy",
					   "l4d_vs_airport" => "Dead Air",
					   "l4d_vs_smalltown" => "Death Toll",
					   "l4d_vs_farm" => "Blood Harvest",
					   "l4d_garage" => "Crash Course",
					   "" => "Custom Maps");

	$survival_campaigns = array("l4d_sv_lighthouse" => "Lighthouse",
					   "l4d_hospital" => "No Mercy - Co-op",
					   "l4d_airport" => "Dead Air - Co-op",
					   "l4d_smalltown" => "Death Toll - Co-op",
					   "l4d_farm" => "Blood Harvest - Co-op",
					   "l4d_vs_hospital" => "No Mercy - Versus",
					   "l4d_vs_airport" => "Dead Air - Versus",
					   "l4d_vs_smalltown" => "Death Toll - Versus",
					   "l4d_vs_farm" => "Blood Harvest - Versus",
					   "l4d_garage" => "Crash Course",
					   "" => "Custom Maps");
}
else if ($game_version == 2)
{
	$coop_campaigns = array("c1m" => "Dead Center",
					   "c2m" => "Dark Carnival",
					   "c3m" => "Swamp Fever",
					   "c4m" => "Hard Rain",
					   "c5m" => "The Parish",
					   "c6m" => "The Passing",
					   "c7m" => "The Sacrifice",
					   "c8m" => "No Mercy",
					   "c9m" => "Crash Course",
					   "c10m" => "Death Toll",
					   "c11m" => "Dead Air",
					   "c12m" => "Blood Harvest",
					   "c13m" => "Cold Stream",
					   "l4d_yama_" => "Yama",
					   "l4d2_city17_0" => "City 17",
					   "l4d2_motamap_m" => "A Dam Mission",
					   "l4d_deathaboard0" => "Death A Board 2",
					   "" => "Custom Maps");

	$versus_campaigns = array("c1m" => "Dead Center",
					   "c2m" => "Dark Carnival",
					   "c3m" => "Swamp Fever",
					   "c4m" => "Hard Rain",
					   "c5m" => "The Parish",
					   "c6m" => "The Passing",
					   "c7m" => "The Sacrifice",
					   "c8m" => "No Mercy",
					   "c9m" => "Crash Course",
					   "c10m" => "Death Toll",
					   "c11m" => "Dead Air",
					   "c12m" => "Blood Harvest",
					   "c13m" => "Cold Stream",
					   "l4d_yama_" => "Yama",
					   "" => "Custom Maps");

	$survival_campaigns = array("c1m" => "Dead Center",
					   "c2m" => "Dark Carnival",
					   "c3m" => "Swamp Fever",
					   "c4m" => "Hard Rain",
					   "c5m" => "The Parish",
					   "c6m" => "The Passing",
					   "c7m" => "The Sacrifice",
					   "c8m" => "No Mercy",
					   "c9m" => "Crash Course",
					   "c10m" => "Death Toll",
					   "c11m" => "Dead Air",
					   "c12m" => "Blood Harvest",
					   "c13m" => "Cold Stream",
					   "l4d_yama_" => "Yama",
					   "" => "Custom Maps");

	$scavenge_campaigns = array("c1m" => "Dead Center",
					   "c2m" => "Dark Carnival",
					   "c3m" => "Swamp Fever",
					   "c4m" => "Hard Rain",
					   "c5m" => "The Parish",
					   "c6m" => "The Passing",
					   "c7m" => "The Sacrifice",
					   "c8m" => "No Mercy",
					   "c9m" => "Crash Course",
					   "c10m" => "Death Toll",
					   "c11m" => "Dead Air",
					   "c12m" => "Blood Harvest",
					   "c13m" => "Cold Stream",
					   "l4d_yama_" => "Yama",
					   "" => "Custom Maps");

	$realism_campaigns = array("c1m" => "Dead Center",
					   "c2m" => "Dark Carnival",
					   "c3m" => "Swamp Fever",
					   "c4m" => "Hard Rain",
					   "c5m" => "The Parish",
					   "c6m" => "The Passing",
					   "c7m" => "The Sacrifice",
					   "c8m" => "No Mercy",
					   "c9m" => "Crash Course",
					   "c10m" => "Death Toll",
					   "c11m" => "Dead Air",
					   "c12m" => "Blood Harvest",
					   "c13m" => "Cold Stream",
					   "l4d_yama_" => "Yama",
					   "" => "Custom Maps");

	$realismversus_campaigns = array("c1m" => "Dead Center",
					   "c2m" => "Dark Carnival",
					   "c3m" => "Swamp Fever",
					   "c4m" => "Hard Rain",
					   "c5m" => "The Parish",
					   "c6m" => "The Passing",
					   "c7m" => "The Sacrifice",
					   "c8m" => "No Mercy",
					   "c9m" => "Crash Course",
					   "c10m" => "Death Toll",
					   "c11m" => "Dead Air",
					   "c12m" => "Blood Harvest",
					   "c13m" => "Cold Stream",
					   "l4d_yama_" => "Yama",
					   "" => "Custom Maps");

	$mutations_campaigns = array("c1m" => "Dead Center",
					   "c2m" => "Dark Carnival",
					   "c3m" => "Swamp Fever",
					   "c4m" => "Hard Rain",
					   "c5m" => "The Parish",
					   "c6m" => "The Passing",
					   "c7m" => "The Sacrifice",
					   "c8m" => "No Mercy",
					   "c9m" => "Crash Course",
					   "c10m" => "Death Toll",
					   "c11m" => "Dead Air",
					   "c12m" => "Blood Harvest",
					   "c13m" => "Cold Stream",
					   "l4d_yama_" => "Yama",
					   "" => "Custom Maps");
}
else
{
	$coop_campaigns = array("l4d_hospital" => "No Mercy (L4D1)",
					   "l4d_airport" => "Dead Air (L4D1)",
					   "l4d_smalltown" => "Death Toll (L4D1)",
					   "l4d_farm" => "Blood Harvest (L4D1)",
					   "l4d_garage" => "Crash Course (L4D1)",
					   "c1m" => "Dead Center (L4D2)",
					   "c2m" => "Dark Carnival (L4D2)",
					   "c3m" => "Swamp Fever (L4D2)",
					   "c4m" => "Hard Rain (L4D2)",
					   "c5m" => "The Parish (L4D2)",
					   "c6m" => "The Passing (L4D2)",
					   "c7m" => "The Sacrifice (L4D2)",
					   "c8m" => "No Mercy (L4D2)",
					   "c9m" => "Crash Course (L4D2)",
					   "c10m" => "Death Toll (L4D2)",
					   "c11m" => "Dead Air (L4D2)",
					   "c12m" => "Blood Harvest (L4D2)",
					   "c13m" => "Cold Stream (L4D2)",
					   "l4d_yama_" => "Yama (L4D2)",
					   "" => "Custom Maps");

	$versus_campaigns = array("l4d_vs_hospital" => "No Mercy (L4D1)",
					   "l4d_vs_airport" => "Dead Air (L4D1)",
					   "l4d_vs_smalltown" => "Death Toll (L4D1)",
					   "l4d_vs_farm" => "Blood Harvest (L4D1)",
					   "l4d_garage" => "Crash Course (L4D1)",
					   "c1m" => "Dead Center (L4D2)",
					   "c2m" => "Dark Carnival (L4D2)",
					   "c3m" => "Swamp Fever (L4D2)",
					   "c4m" => "Hard Rain (L4D2)",
					   "c5m" => "The Parish (L4D2)",
					   "c6m" => "The Passing (L4D2)",
					   "c7m" => "The Sacrifice (L4D2)",
					   "c8m" => "No Mercy (L4D2)",
					   "c9m" => "Crash Course (L4D2)",
					   "c10m" => "Death Toll (L4D2)",
					   "c11m" => "Dead Air (L4D2)",
					   "c12m" => "Blood Harvest (L4D2)",
					   "c13m" => "Cold Stream (L4D2)",
					   "l4d_yama_" => "Yama (L4D2)",
					   "" => "Custom Maps");

	$survival_campaigns = array("l4d_sv_lighthouse" => "Lighthouse (L4D1)",
					   "l4d_hospital" => "No Mercy - Co-op (L4D1)",
					   "l4d_airport" => "Dead Air - Co-op (L4D1)",
					   "l4d_smalltown" => "Death Toll - Co-op (L4D1)",
					   "l4d_farm" => "Blood Harvest - Co-op (L4D1)",
					   "l4d_vs_hospital" => "No Mercy - Versus (L4D1)",
					   "l4d_vs_airport" => "Dead Air - Versus (L4D1)",
					   "l4d_vs_smalltown" => "Death Toll - Versus (L4D1)",
					   "l4d_vs_farm" => "Blood Harvest - Versus (L4D1)",
					   "l4d_garage" => "Crash Course (L4D1)",
					   "c1m" => "Dead Center (L4D2)",
					   "c2m" => "Dark Carnival (L4D2)",
					   "c3m" => "Swamp Fever (L4D2)",
					   "c4m" => "Hard Rain (L4D2)",
					   "c5m" => "The Parish (L4D2)",
					   "c6m" => "The Passing (L4D2)",
					   "c7m" => "The Sacrifice (L4D2)",
					   "c8m" => "No Mercy (L4D2)",
					   "c9m" => "Crash Course (L4D2)",
					   "c10m" => "Death Toll (L4D2)",
					   "c11m" => "Dead Air (L4D2)",
					   "c12m" => "Blood Harvest (L4D2)",
					   "c13m" => "Cold Stream (L4D2)",
					   "l4d_yama_" => "Yama (L4D2)",
					   "" => "Custom Maps");

	$scavenge_campaigns = array("c1m" => "Dead Center (L4D2)",
					   "c2m" => "Dark Carnival (L4D2)",
					   "c3m" => "Swamp Fever (L4D2)",
					   "c4m" => "Hard Rain (L4D2)",
					   "c5m" => "The Parish (L4D2)",
					   "c6m" => "The Passing (L4D2)",
					   "c7m" => "The Sacrifice (L4D2)",
					   "c8m" => "No Mercy (L4D2)",
					   "c9m" => "Crash Course (L4D2)",
					   "c10m" => "Death Toll (L4D2)",
					   "c11m" => "Dead Air (L4D2)",
					   "c12m" => "Blood Harvest (L4D2)",
					   "c13m" => "Cold Stream (L4D2)",
					   "l4d_yama_" => "Yama (L4D2)",
					   "" => "Custom Maps (L4D2)");

	$realism_campaigns = array("c1m" => "Dead Center (L4D2)",
					   "c2m" => "Dark Carnival (L4D2)",
					   "c3m" => "Swamp Fever (L4D2)",
					   "c4m" => "Hard Rain (L4D2)",
					   "c5m" => "The Parish (L4D2)",
					   "c6m" => "The Passing (L4D2)",
					   "c7m" => "The Sacrifice (L4D2)",
					   "c8m" => "No Mercy (L4D2)",
					   "c9m" => "Crash Course (L4D2)",
					   "c10m" => "Death Toll (L4D2)",
					   "c11m" => "Dead Air (L4D2)",
					   "c12m" => "Blood Harvest (L4D2)",
					   "c13m" => "Cold Stream (L4D2)",
					   "l4d_yama_" => "Yama (L4D2)",
					   "" => "Custom Maps (L4D2)");

	$realismversus_campaigns = array("c1m" => "Dead Center (L4D2)",
					   "c2m" => "Dark Carnival (L4D2)",
					   "c3m" => "Swamp Fever (L4D2)",
					   "c4m" => "Hard Rain (L4D2)",
					   "c5m" => "The Parish (L4D2)",
					   "c6m" => "The Passing (L4D2)",
					   "c7m" => "The Sacrifice (L4D2)",
					   "c8m" => "No Mercy (L4D2)",
					   "c9m" => "Crash Course (L4D2)",
					   "c10m" => "Death Toll (L4D2)",
					   "c11m" => "Dead Air (L4D2)",
					   "c12m" => "Blood Harvest (L4D2)",
					   "c13m" => "Cold Stream (L4D2)",
					   "l4d_yama_" => "Yama (L4D2)",
					   "" => "Custom Maps (L4D2)");

	$mutations_campaigns = array("c1m" => "Dead Center (L4D2)",
					   "c2m" => "Dark Carnival (L4D2)",
					   "c3m" => "Swamp Fever (L4D2)",
					   "c4m" => "Hard Rain (L4D2)",
					   "c5m" => "The Parish (L4D2)",
					   "c6m" => "The Passing (L4D2)",
					   "c7m" => "The Sacrifice (L4D2)",
					   "c8m" => "No Mercy (L4D2)",
					   "c9m" => "Crash Course (L4D2)",
					   "c10m" => "Death Toll (L4D2)",
					   "c11m" => "Dead Air (L4D2)",
					   "c12m" => "Blood Harvest (L4D2)",
					   "c13m" => "Cold Stream (L4D2)",
					   "l4d_yama_" => "Yama (L4D2)",
					   "" => "Custom Maps (L4D2)");
}

// Fix the site name
$site_name = htmlentities($site_name);
$game_locations = array();
$international = false;
$game_country_code_last = "NULL";

// http://developer.valvesoftware.com/wiki/Steam_browser_protocol
if (isset($game_addresses))
{
	foreach($game_addresses as $game_info)
	{
		if (count($game_info) != 2)
			continue;

		$game_name = htmlentities($game_info[0], ENT_COMPAT, "UTF-8");
		$game_address = $game_info[1];

		if (strlen($game_address) > 0)
		{
			$game_ip = "";
			$game_lon = 0.0;
			$game_lat = 0.0;
			$game_flag = "";
			$game_country_code = "";

			if ($showplayerflags)
			{
				$game_adderss_split = split(":", $game_address);

				$ip_classes = split(".", $game_adderss_split[0]);

				if (count($ip_classes) != 4)
					$game_ip = gethostbyname($game_adderss_split[0]);
				else
				{
					$all_numeric = true;

					foreach ($ip_classes as $ip_class)
					{
						if (!is_numeric($ip_class) || $ip_class < 0 || $ip_class > 255)
						{
							$all_numeric = false;
							break;
						}
					}

					if ($all_numeric)
					{
						$game_ip = $game_adderss_split[0];
					}
					else
					{
						$game_ip = gethostbyname($game_adderss_split[0]);
					}
				}

				$game_country_code = strtolower($ip2c->get_country_code($game_ip));

				$game_lat = $ip2c->get_latitude($game_ip);
				$game_lon = $ip2c->get_longitude($game_ip);

				if ($game_country_code != "" && $game_country_code != "xx" && $game_country_code != "int" && file_exists("./img/flags/" . $game_country_code . ".gif"))
				{
					if (!$international && $game_country_code_last != "NULL" && $game_country_code != $game_country_code_last)
					{
						$international = true;
					}

					$game_country_code_last = $game_country_code;

					$game_flag = $ip2c->get_country_flag($game_ip);
				}
				else
				{
					$international = true;
				}
			}
			else
			{
				$international = true;
			}

			if (!isset($motd_page))
			{
				$link = "steam://connect/" . str_replace(array("\"", "/"), "", $game_address);
				$game_location = array("country_code" => $game_country_code, "flag" => $game_flag, "link" => $link, "title" => $game_flag . "<a href=\"" . $link . "\">" . $game_name . "</a>", "lat" => $game_lat, "lon" => $game_lon);
			}
			else
			{
				$link = "";
				$game_location = array("country_code" => $game_country_code, "flag" => $game_flag, "link" => $link, "title" => $game_flag . $game_name, "lat" => $game_lat, "lon" => $game_lon);
			}

			$game_locations[] = $game_location;
		}
	}
}

$locations = count($game_locations);

if ($locations == 1 || isset($motd_page))
{
	if (!isset($motd_page))
	{
		$site_name = (!$international ? $game_locations[0]["flag"] : "") . "<a href=\"" . $game_locations[0]["link"] . "\">" . $site_name . "</a>";
	}
	else
	{
		$site_name = (!$international ? $game_locations[0]["flag"] : "") . $site_name;
	}
}
else if ($locations > 1)
{
	$game_locations_html = "";

	foreach ($game_locations as $game_location)
	{
		if (strlen($game_locations_html))
		{
			$game_locations_html .= "<br />";
		}
		$game_locations_html .= $game_location["title"];
	}

	$game_locations_html = str_replace("&", "&amp;", $game_locations_html);
	$game_locations_html = str_replace("\"", "&#34;", $game_locations_html);
	$game_locations_html = str_replace("'", "&#92;&#39;", $game_locations_html);
	$game_locations_html = str_replace("\\", "&#92;&#92;", $game_locations_html);

	$site_name = (!$international ? $game_locations[0]["flag"] : "") . "<a href=\"javascript:void();\" onmouseover=\"showcmb(this, '" . $game_locations_html . "');\" onmouseout=\"hidecmb();\">" . $site_name . "</a>";
}

$realismlink = "";
$scavengelink = "";
$realismversuslink = "";
$mutationslink = "";
$realismcmblink = "";
$scavengecmblink = "";
$realismversuscmblink = "";
$mutationscmblink = "";

if ($game_version != 1)
{
	$realismlink = "<a href='maps.php?type=realism'>" . $language_pack['realismstats'] . "</a>";
	$scavengelink = "<a href='maps.php?type=scavenge'>" . $language_pack['scavengestats'] . "</a>";
	$realismversuslink = "<a href='maps.php?type=realismversus'>" . $language_pack['realismversusstats'] . "</a>";
	$mutationslink = "<a href='maps.php?type=mutations'>" . $language_pack['mutationsstats'] . "</a>";

	$realismcmblink = str_replace("'", "&quot;", $realismlink) . "<br>";
	$scavengecmblink = str_replace("'", "&quot;", $scavengelink) . "<br>";
	$realismversuscmblink = str_replace("'", "&quot;", $realismversuslink) . "<br>";
	$mutationscmblink = str_replace("'", "&quot;", $mutationslink) . "<br>";

	$tpl = new Template($templatefiles['navigation_gamemodelink.tpl']);

	$tpl->set("gamemodelink", $realismlink);
	$realismlink = $tpl->fetch($templatefiles['navigation_gamemodelink.tpl']);

	$tpl->set("gamemodelink", $scavengelink);
	$scavengelink = $tpl->fetch($templatefiles['navigation_gamemodelink.tpl']);

	$tpl->set("gamemodelink", $realismversuslink);
	$realismversuslink = $tpl->fetch($templatefiles['navigation_gamemodelink.tpl']);

	$tpl->set("gamemodelink", $mutationslink);
	$mutationslink = $tpl->fetch($templatefiles['navigation_gamemodelink.tpl']);
	// $realismlink = "<li>" . $realismlink . "</li>";
	// $scavengelink = "<li>" . $scavengelink . "</li>";
	// $realismversuslink = "<li>" . $realismversuslink . "</li>";
	// $mutationslink = "<li>" . $mutationslink . "</li>";
}

$timedmapslink = "";

if ($timedmaps_show_all)
{
	$timedmapslink = "<li><a href=\"timedmaps.php\">Timed Maps</a></li>";
}

$header_extra = array();
$header_extra[$language_pack['zombieskilled']] = 0;
$header_extra[$language_pack['playersserved']] = 0;
$result = mysql_query("SELECT COUNT(*) AS players_served, sum(kills) AS total_kills FROM " . $mysql_tableprefix . "players");
if ($result && $row = mysql_fetch_array($result))
{
	$header_extra[$language_pack['zombieskilled']] = $row['total_kills'];
	$header_extra[$language_pack['playersserved']] = $row['players_served'];
}

$i = 1;

$result = mysql_query("SELECT * FROM " . $mysql_tableprefix . "players ORDER BY " . $TOTALPOINTS . " DESC LIMIT 10");
if ($result && mysql_num_rows($result) > 0)
{
	while ($row = mysql_fetch_array($result)) {
		// This character is A PAIN... Find out how to convert it in to a HTML entity!
		// http://www.fileformat.info/info/unicode/char/06d5/index.htm
		// Maybe it's the same with all Arabic characters???? From right to left type of writing.

		$top10players[$i++] = getplayerinfo($row);
		
		/*
		$name = htmlentities($row['name'], ENT_COMPAT, "UTF-8");
		//$name = str_replace("" , "&#1749;", $name);
		//$titlename = str_replace("\"" , "\\\"", $name);

		$avatarimg = "";
		$playerheadline = "";
		
		if ($steam_profile_read && $i <= $top10players_additional_info)
		{
			$playersteamprofile = getplayersteamprofilexml($row['steamid']);

			if ($playersteamprofile)
			{
				if ($players_avatars_show)
				{
					$avatarimgurl = parseplayeravatar($playersteamprofile, "icon");

					if($avatarimgurl)
					{
						$avatarimg = "<img src=\"" . $avatarimgurl . "\" border=\"0\">";
					}
				}
				
				$playerheadline = htmlentities(parseplayerheadline($playersteamprofile), ENT_COMPAT, "UTF-8");
			}
		}
		
		$playername = ($showplayerflags ? $ip2c->get_country_flag($row['ip']) : "") . "<a href=\"player.php?steamid=" . $row['steamid'] . "\">" . $name . "</a>";
		
		if ($playerheadline)
		{
			$playername = "<table border=0 cellspacing=0 cellpadding=0 class=\"top10\"><tr><td rowspan=\"2\">&nbsp;</td><td>" . $playername . "</td></tr><tr><td class=\"summary\">" . $playerheadline . "</td></tr></table>";
		}
		
		if ($avatarimg)
		{
			$playername = "<table border=0 cellspacing=0 cellpadding=0 class=\"top10\"><tr><td>&nbsp;</td><td>" . $avatarimg . "</td>" . ($playerheadline ? "" : "<td>&nbsp;</td>") . "<td>" . $playername . "</td></tr></table>";
		}

		if (!$playerheadline && !$avatarimg)
		{
			if ($i <= $top10players_additional_info)
			{
				$playername = "<table border=0 cellspacing=0 cellpadding=0 class=\"top10\"><tr><td>&nbsp;</td><td>" . $playername . "</td></tr></table>";
			}
			else
			{
				$playername = "&nbsp;" . $playername;
			}
		}

		$top10[] = createtablerowtooltip($row, $i) . "<td><b>" . $i . ".</b></td><td><div style=\"position:relative;min-width:150px;max-width:200px;overflow:hidden;white-space:nowrap;\">" . $playername . "</div></td></tr>";

		if ($top10players_additional_info && $i == $top10players_additional_info)
		{
			$top10[] = "<tr><td colspan=\"3\">&nbsp;</td></tr>";
		}

		$i++;
		*/
	}
}

$template_properties['top10players'] = $top10players;

$motd_message = htmlentities(getserversettingsvalue("motdmessage"), ENT_COMPAT, "UTF-8");
$layout_motd = "";
if ($show_motd && strlen($motd_message) > 0)
{
	$tpl_msg = new Template($templatefiles['layout_motd.tpl']);
	$tpl_msg->set("motd_message", $motd_message);
	$layout_motd = $tpl_msg->fetch($templatefiles['layout_motd.tpl']);
}

// Load top10 template
$tpl = new Template($templatefiles['top10.tpl']);
$template_properties['top10'] = $tpl->fetch($templatefiles['top10.tpl']);

// Load page main navigation template
$tpl = new Template($templatefiles['navigation_main.tpl']);
$template_properties['navigation_main'] = $tpl->fetch($templatefiles['navigation_main.tpl']);

// Load page navigation template
$tpl = new Template($templatefiles['navigation.tpl']);
$template_properties['navigation'] = $tpl->fetch($templatefiles['navigation.tpl']);

?>
