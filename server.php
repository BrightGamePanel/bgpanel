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



$title = 'Server Summary';
$page = 'server';
$tab = 2;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$serverid = $_GET['id'];
}
else
{
	header( 'Location: index.php' );
	die();
}
###
$return = 'server.php';


require("configuration.php");
require("include.php");
require("./libs/lgsl/lgsl_class.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
$box = query_fetch_assoc( "SELECT `ip`, `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1");
$game = query_fetch_assoc( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1" );
$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rows['groupid']."' LIMIT 1" );
$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` WHERE `serverid` = '".$serverid."' ORDER BY `logid` DESC LIMIT 15" );


//---------------------------------------------------------+


$checkGroup = checkClientGroup($rows['groupid'], $_SESSION['clientid']);

if ($checkGroup == FALSE)
{
	$_SESSION['msg1'] = 'Error!';
	$_SESSION['msg2'] = 'This is not your server!';
	$_SESSION['msg-type'] = 'error';
	header( 'Location: index.php' );
	die();
}


//---------------------------------------------------------+


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
			<div class="tabbable">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#1" data-toggle="tab">Server Summary</a></li>
					<li><a href="#2" data-toggle="tab">Server Control Panel</a></li>
<?php

if ($type['querytype'] != 'none')
{
	echo "\t\t\t\t\t<li><a href=\"#3\" data-toggle=\"tab\">LGSL</a></li>";
}

?>

<?php

if ($rows['panelstatus'] == 'Started')
{
	echo "\t\t\t\t\t<li><a href=\"utilitiesrcontool.php?serverid=".$serverid."\">RCON Tool</a></li>";
}

?>

					<li><a href="#5" data-toggle="tab">Activity Logs</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="1">
						<div class="row-fluid">
							<div class="span6">
								<div class="well">
									<div style="text-align: center; margin-bottom: 5px;">
										<span class="label label-info">Server Information</span>
									</div>
									<table class="table table-striped table-bordered table-condensed">
										<tr>
											<td>Name</td>
											<td><?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?></td>
										</tr>
										<tr>
											<td>Status</td>
											<td><?php echo formatStatus($rows['status']); ?></td>
										</tr>
										<tr>
											<td>Owner Group</td>
											<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
										</tr>
										<tr>
											<td>Game</td>
											<td><?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?></td>
										</tr>
										<tr>
											<td>IP Address</td>
											<td><?php echo $serverIp['ip']; ?></td>
										</tr>
										<tr>
											<td>Port</td>
											<td><?php echo $rows['port']; ?></td>
										</tr>
										<tr>
											<td>Query Port</td>
											<td><?php echo $rows['queryport']; ?></td>
										</tr>
										<tr>
											<td>Slots</td>
											<td><?php echo $rows['slots']; ?></td>
										</tr>
									</table>
								</div>
							</div>
							<div class="span6">
								<div class="well">
									<div style="text-align: center; margin-bottom: 5px;">
										<span class="label label-info">Server Configuration</span>
									</div>
									<table class="table table-striped table-bordered table-condensed">
										<tr>
											<td>Priority</td>
											<td colspan="2"><?php echo $rows['priority']; ?></td>
										</tr>
										<tr>
											<td>Start Command</td>
											<td colspan="2"><?php echo htmlspecialchars($rows['startline'], ENT_QUOTES); ?></td>
										</tr>
										<tr>
											<td>Home Directory</td>
											<td colspan="2"><?php echo htmlspecialchars($rows['homedir'], ENT_QUOTES); ?></td>
										</tr>
										<tr>
											<td>Screen Name</td>
											<td colspan="2"><?php echo $rows['screen']; ?></td>
										</tr>
<?php

$n = 1;
while ($n < 10)
{
	if (!empty($rows['cfg'.$n.'name']) || !empty($rows['cfg'.$n]))
	{
?>
										<tr>
											<td><?php echo htmlspecialchars($rows['cfg'.$n.'name'], ENT_QUOTES); ?></td>
											<td><?php echo htmlspecialchars($rows['cfg'.$n.''], ENT_QUOTES); ?></td>
											<td>{cfg<?php echo $n; ?>}</td>
										</tr>
<?php
	}
	++$n;
}
unset($n);

?>
									</table>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="span6 offset3">
								<div class="well">
									<div style="text-align: center; margin-bottom: 5px;">
										<span class="label label-info">Server Monitoring</span>
									</div>
									<table class="table table-striped table-bordered table-condensed">
										<tr>
											<td>Query Type</td>
											<td><?php echo $type['querytype']; ?></td>
										</tr>
										<tr>
											<td>Panel Status</td>
											<td><?php echo formatStatus($rows['panelstatus']); ?></td>
										</tr>
<?php

if (($rows['status'] == 'Active') && ($rows['panelstatus'] == 'Started'))
{
	//---------------------------------------------------------+
	//Querying the server
	$server = lgsl_query_live($type['querytype'], $serverIp['ip'], NULL, $rows['queryport'], NULL, 's');
	//
	//---------------------------------------------------------+
}

?>
										<tr>
											<td>Net Status</td>
										<td><?php

if (@$server['b']['status'] == '1')
{
	echo formatStatus('Online');
}
else
{
	echo formatStatus('Offline');
}

?></td>
										</tr>
										<tr>
											<td>Map</td>
											<td><?php echo @$server['s']['map']; ?></td>
										</tr>
										<tr>
											<td>Players</td>
											<td><?php echo @$server['s']['players']; ?> / <?php echo @$server['s']['playersmax']; ?></td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="2">
						<div class="row">
							<div class="span8 offset2">
								<div class="well">
									<div style="text-align: center; margin-bottom: 5px;">
										<span class="label label-info">Server Control Panel</span>
									</div>
<?php

if ($rows['status'] == 'Pending')
{
?>
									<div class="alert alert-info">
										<h4 class="alert-heading">Server not validated !</h4>
										<p>
											An administrator must validate the server in order to use it.
										</p>
									</div>
<?php
}
else if ($rows['status'] == 'Inactive')
{
?>
									<div class="alert alert-block" style="text-align: center;">
										<h4 class="alert-heading">The server has been disabled !</h4>
									</div>
<?php
}
else if ($rows['status'] == 'Active')
{

	//---------------------------------------------------------+

?>
									<table class="table">
										<tr>
											<td>Screen Name	</td>
											<td>Owner Group</td>
											<td>Box</td>
											<td>Panel Status</td>
											<td>Net Status</td>
										</tr>
										<tr>
											<td><?php echo $rows['screen']; ?></td>
											<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
											<td><?php echo htmlspecialchars($box['name']); ?> - <?php echo $box['ip'], ENT_QUOTES; ?></td>
											<td><?php echo formatStatus($rows['panelstatus']); ?></td>
											<td><?php

	if (@$server['b']['status'] == '1')
	{
		echo formatStatus('Online');
	}
	else
	{
		echo formatStatus('Offline');
	}

	unset($server);

?></td>
										</tr>
									</table>
<?php

	if ($rows['panelstatus'] == 'Stopped') //The server has been validated and is marked as offline, the only available action is to start it
	{
?>
									<a href="serverprocess.php?task=serverstart&serverid=<?php echo $serverid; ?>" class="btn btn-primary"><i class="icon-play icon-white"></i>&nbsp;Start</a>
<?php
	}
	else if ($rows['panelstatus'] == 'Started') //The server has been validated and is marked as online, the available actions are to restart or to stop it
	{
?>
									<a href="serverprocess.php?task=serverstop&serverid=<?php echo $serverid; ?>" class="btn btn-warning"><i class="icon-stop icon-white"></i>&nbsp;Stop</a>
									<a href="serverprocess.php?task=serverreboot&serverid=<?php echo $serverid; ?>" class="btn btn-primary"><i class="icon-repeat icon-white"></i>&nbsp;Restart</a>
<?php
	}

?>
									<a href="#" class="btn btn-primary" onclick="dlScrLog();return false;"><i class="icon-download-alt icon-white"></i>&nbsp;Download Screenlog</a>
<?php

	//---------------------------------------------------------+

}

?>
								</div>
							</div>
						</div>
						<div style="height:150px"></div>
					</div>
					<div class="tab-pane" id="3">
						<div class="well">
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

?>
						</div>
					</div>
					<div class="tab-pane" id="5">
						<div class="row">
							<div class="span6 offset3">
								<div class="well">
									<div style="text-align: center; margin-bottom: 5px;">
										<span class="label label-info">Last 15 Actions</span>
									</div>
									<table class="table table-bordered">
<?php

if (mysql_num_rows($logs) == 0)
{
?>
										<tr>
											<td>
												<div style="text-align: center;"><span class="label label-warning">No Logs Found</span></div>
											</td>
										</tr>
<?php
}

while ($rowsLogs = mysql_fetch_assoc($logs))
{
?>
										<tr>
											<td>
												<div style="text-align: center;"><?php echo formatDate($rowsLogs['timestamp']); ?> - <?php echo htmlspecialchars($rowsLogs['message']); ?></div>
											</td>
										</tr>
<?php
}
unset($logs);

?>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div style="text-align: center;">
				<ul class="pager">
					<li>
						<a href="index.php">Back to Home</a>
					</li>
				</ul>
			</div>
			<script type="text/javascript">
			function dlScrLog()
			{
				if (confirm("Download SCREENLOG ?"))
				{
					window.location.href='serverprocess.php?task=getserverlog&serverid=<?php echo $serverid; ?>';
				}
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>