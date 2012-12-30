<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * LICENSE:
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @categories	Games/Entertainment, Systems Administration
 * @package		Bright Game Panel
 * @author		warhawk3407 <warhawk3407@gmail.com> @NOSPAM
 * @copyleft	2013
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 5
 * @link		http://www.bgpanel.net/
 */



$title = 'Live Game Server List';
$page = 'serverlgsl';
$tab = 2;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$serverid = $_GET['id'];
}
else
{
	exit('Error: ServerID error.');
}
###
$return = 'serverlgsl.php?id='.urlencode($serverid);


require("../configuration.php");
require("./include.php");
require("../libs/lgsl/lgsl_class.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `gameid`, `name`, `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1");


include("./bootstrap/header.php");


/**
 * Notifications
 */
if (isset($_SESSION['msg1']) && isset($_SESSION['msg2']) && isset($_SESSION['msg-type']))
{
?>
			<div class="alert alert-<?php
	switch ($_SESSION['msg-type'])
	{
		case 'block':
			echo 'block';
			break;

		case 'error':
			echo 'error';
			break;

		case 'success':
			echo 'success';
			break;

		case 'info':
			echo 'info';
			break;
	}
?>">
				<a class="close" data-dismiss="alert">&times;</a>
				<h4 class="alert-heading"><?php echo $_SESSION['msg1']; ?></h4>
				<?php echo $_SESSION['msg2']; ?>
			</div>
<?php
	unset($_SESSION['msg1']);
	unset($_SESSION['msg2']);
	unset($_SESSION['msg-type']);
}
/**
 *
 */


?>
			<ul class="nav nav-tabs">
				<li><a href="serversummary.php?id=<?php echo $serverid; ?>">Summary</a></li>
				<li><a href="serverprofile.php?id=<?php echo $serverid; ?>">Profile</a></li>
				<li><a href="servermanage.php?id=<?php echo $serverid; ?>">Manage</a></li>
				<li class="active"><a href="serverlgsl.php?id=<?php echo $serverid; ?>">LGSL</a></li>
<?php

if ($rows['panelstatus'] == 'Started')
{
	echo "\t\t\t\t<li><a href=\"utilitiesrcontool.php?serverid=".$serverid."\">RCON Tool</a></li>";
}

?>

				<li><a href="serverlog.php?id=<?php echo $serverid; ?>">Activity Logs</a></li>
			</ul>
<?php

if ($type['querytype'] != 'none')
{

 /*----------------------------------------------------------------------------------------------------------\
 |                                                                                                            |
 |                      [ LIVE GAME SERVER LIST ] [ © RICHARD PERRY FROM GREYCUBE.COM ]                       |
 |                                                                                                            |
 |    Released under the terms and conditions of the GNU General Public License Version 3 (http://gnu.org)    |
 |                                                                                                            |
 \-----------------------------------------------------------------------------------------------------------*/

//------------------------------------------------------------------------------------------------------------+
// THIS CONTROLS HOW THE PLAYER FIELDS ARE DISPLAYED

  $fields_show  = array("name", "score", "kills", "deaths", "team", "ping", "bot", "time"); // ORDERED FIRST
  $fields_hide  = array("teamindex", "pid", "pbguid"); // REMOVED
  $fields_other = TRUE; // FALSE TO ONLY SHOW FIELDS IN $fields_show

//------------------------------------------------------------------------------------------------------------+
// GET THE SERVER DETAILS AND PREPARE IT FOR DISPLAY

  global $output;
  $output = "";
  global $lgsl_server_id;
  $lgsl_server_id = $serverid;

  $server = lgsl_query_cached("", "", "", "", "", "sep", $lgsl_server_id);

  //if (!$server) { $output .= "<div style='margin:auto; text-align:center'> {$lgsl_config['text']['mid']} </div>"; return; }

  $fields = lgsl_sort_fields($server, $fields_show, $fields_hide, $fields_other);
  $server = lgsl_sort_players($server);
  $server = lgsl_sort_extras($server);
  $misc   = lgsl_server_misc($server);
  $server = lgsl_server_html($server);

//------------------------------------------------------------------------------------------------------------+
// SHOW THE STANDARD INFO

$output .= "
			<table class='table table-bordered table-striped'>
				<thead>
					<tr>
						<th colspan='6' style='text-align: center;'> {$server['s']['name']} </th>
						<th colspan='2' style='text-align: center;'> <a href='{$misc['software_link']}'>{$lgsl_config['text']['slk']}</a> </th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><b> {$lgsl_config['text']['sts']} </b></td>
						<td><b> {$lgsl_config['text']['adr']} </b></td>
						<td><b> {$lgsl_config['text']['cpt']} </b></td>
						<td><b> {$lgsl_config['text']['qpt']} </b></td>
						<td><b> {$lgsl_config['text']['typ']} </b></td>
						<td><b> {$lgsl_config['text']['gme']} </b></td>
						<td><b> {$lgsl_config['text']['map']} </b></td>
						<td><b> {$lgsl_config['text']['plr']} </b></td>
					</tr>
					<tr>
						<td> {$misc['text_status']} </td>
						<td> {$server['b']['ip']} </td>
						<td> {$server['b']['c_port']} </td>
						<td> {$server['b']['q_port']} </td>
						<td> {$server['b']['type']} </td>
						<td> {$server['s']['game']} </td>
						<td> {$server['s']['map']} </td>
						<td> {$server['s']['players']} / {$server['s']['playersmax']} </td>
					</tr>
				</tbody>
			</table>";

//------------------------------------------------------------------------------------------------------------+

$output .= "\r\n\r\n\t\t\t<hr>\r\n";

//------------------------------------------------------------------------------------------------------------+
// SHOW THE PLAYERS

if (empty($server['p']) || !is_array($server['p']))
{
	$output .= "\r\n\t\t\t<div style='text-align: center;'><span class='label'> {$lgsl_config['text']['npi']} </span></div>";
}
else
{
	$output .= "
			<table class='table table-striped'>
				<thead>
					<tr>";

	foreach ($fields as $field)
	{
		$field = ucfirst($field);
		$output .= "
						<td><b> {$field} </b></td>\r\n";
	}

	$output .= "
					</tr>
				</thead>
				<tbody>";

	foreach ($server['p'] as $player_key => $player)
	{
		$output .= "
					<tr>";

		foreach ($fields as $field)
		{
			$output .= "
						<td> {$player[$field]} </td>";
		}

		$output .= "
					</tr>";
	}

	$output .= "
				</tbody>
			</table>";
}

//------------------------------------------------------------------------------------------------------------+

$output .= "\r\n\r\n\t\t\t<hr>\r\n";

//------------------------------------------------------------------------------------------------------------+
// SHOW THE SETTINGS

if (empty($server['e']) || !is_array($server['e']))
{
	$output .= "\r\n\t\t\t<div style='text-align: center;'><span class='label'> {$lgsl_config['text']['nei']} </span></div>";
}
else
{
	$output .= "
			<table class='table table-striped'>
				<thead>
					<tr>
						<td><b> {$lgsl_config['text']['ehs']} </b></td>
						<td><b> {$lgsl_config['text']['ehv']} </b></td>
					</tr>
				</thead>
				<tbody>";

	foreach ($server['e'] as $field => $value)
	{
		$output .= "
					<tr>
						<td> {$field} </td>
						<td> {$value} </td>
					</tr>";
	}

	$output .= "
				</tbody>
			</table>";
}

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//------ PLEASE MAKE A DONATION OR SIGN THE GUESTBOOK AT GREYCUBE.COM IF YOU REMOVE THIS CREDIT ----------------------------------------------------------------------------------------------------+
$output .= "

			<div style='margin-top: 19px;'>
				<table class='table table-bordered'>
					<tr>
						<td style='text-align: center;'>
							Powered by <a href='http://www.greycube.com' target='_blank'>".lgsl_version()."</a>
						</td>
					</tr>
				</table>
			</div>

";
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

	echo $output;

	unset($output);

}


include("./bootstrap/footer.php");
?>