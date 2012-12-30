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



$title = 'Server Control Panel';
$page = 'servermanage';
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
$return = 'servermanage.php?id='.urlencode($serverid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1");
$box = query_fetch_assoc( "SELECT `ip`, `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rows['groupid']."' LIMIT 1" );


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
				<li class="active"><a href="servermanage.php?id=<?php echo $serverid; ?>">Manage</a></li>
<?php

if ($type['querytype'] != 'none')
{
	echo "\t\t\t\t<li><a href=\"serverlgsl.php?id=".$serverid."\">LGSL</a></li>";
}

?>

<?php

if ($rows['panelstatus'] == 'Started')
{
	echo "\t\t\t\t<li><a href=\"utilitiesrcontool.php?serverid=".$serverid."\">RCON Tool</a></li>";
}

?>

				<li><a href="serverlog.php?id=<?php echo $serverid; ?>">Activity Logs</a></li>
			</ul>
<?php
if ($rows['status'] == 'Pending')
{
?>
			<div class="alert alert-info">
				<h4 class="alert-heading">Server not validated !</h4>
				<p>
					You must validate the server in order to use it.
				</p>
				<p>
					<a class="btn btn-primary" href="serverprocess.php?task=servervalidation&serverid=<?php echo $serverid; ?>">Validate</a>
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
	//Querying the server
	include_once("../libs/lgsl/lgsl_class.php");

	$server = lgsl_query_live($type['querytype'], $box['ip'], NULL, $rows['queryport'], NULL, 's');

?>
			<div class="well">
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
				<div style="text-align: center;">
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
				</div>
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

	//---------------------------------------------------------+

}


include("./bootstrap/footer.php");
?>