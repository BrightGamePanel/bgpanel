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



$title = 'Script Summary';
$page = 'scriptsummary';
$tab = 4;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$scriptid = $_GET['id'];
}
else
{
	exit('Error:ScriptID error.');
}
###
$return = 'scriptsummary.php?id='.urlencode($scriptid);


require("./configuration.php");
require("./include.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
{
	exit('Error: ScriptID is invalid.');
}

$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
$cat = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."scriptCat` WHERE `id` = '".$rows['catid']."' LIMIT 1" );
$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rows['groupid']."' LIMIT 1" );

//---------------------------------------------------------+

$checkGroup = checkClientGroup($rows['groupid'], $_SESSION['clientid']);

if ($checkGroup == FALSE)
{
	$_SESSION['msg1'] = 'Error!';
	$_SESSION['msg2'] = 'This is not your script!';
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
			<ul class="nav nav-tabs">
				<li class="active"><a href="scriptsummary.php?id=<?php echo $scriptid; ?>">Summary</a></li>
<?php

if ($rows['status'] == 'Active')
{
	echo "\t\t\t\t<li><a href=\"scriptconsole.php?id=".$scriptid."\">Console</a></li>";
}

?>
			</ul>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Script Information</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Name</td>
								<td><?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Category</td>
								<td><?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Status</td>
								<td><?php echo formatStatus($rows['status']); ?></td>
							</tr>
							<tr>
								<td>Owner Group</td>
								<td><?php if (!empty($group['name'])) { echo htmlspecialchars($group['name'], ENT_QUOTES); } else { echo "<span class=\"label\"><em>None</em></span>"; } ?></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Script Description</span>
						</div>
						<table class="table table-bordered table-condensed">
							<tr>
								<td style="text-align: center;"><?php echo htmlspecialchars($rows['description'], ENT_QUOTES); ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Script Monitoring</span>
						</div>
						<table class="table table-bordered table-condensed">
							<tr>
								<td style="text-align: center;">Panel Status: <?php

if (!empty($rows['panelstatus']))
{
	$pstatus = formatStatus($rows['panelstatus']);
}
else
{
	$pstatus = "<span class=\"label\"><em>None</em></span>";
}

echo $pstatus;

?></td>
							</tr>
						</table>
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Script Configuration</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Exec Mode</td>
								<td><?php if ($rows['type'] == '0') { echo 'Non-Interactive'; } else { echo 'Interactive'; }; ?></td>
							</tr>
							<tr>
								<td>File Name</td>
								<td><?php echo htmlspecialchars($rows['filename'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Start Command</td>
								<td><?php echo htmlspecialchars($rows['startline'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Home Directory</td>
								<td><?php echo htmlspecialchars($rows['homedir'], ENT_QUOTES); ?></td>
							</tr>
<?php

if (!empty($rows['panelstatus']))
{
?>
							<tr>
								<td>Screen Name</td>
								<td><?php echo htmlspecialchars($rows['screen'], ENT_QUOTES); ?></td>
							</tr>
<?php
}

?>
						</table>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Script Control Panel</span>
						</div>
<?php

if ($rows['status'] == 'Pending')
{
?>
						<div class="alert alert-info">
							<h4 class="alert-heading">Script not validated !</h4>
							<p>
								An administrator must validate the script in order to use it.
							</p>
						</div>
<?php
}
else
{
	if ($rows['type'] == '0')
	{
?>
							<div style="text-align: center;">
								<a class="btn btn-primary" href="scriptprocess.php?task=scriptstart&scriptid=<?php echo $scriptid; ?>">Launch</a>
							</div>
<?php
	}
	else if ($rows['status'] == 'Inactive')
	{
	?>
							<div class="alert alert-block" style="text-align: center;">
								<h4 class="alert-heading">The script has been disabled !</h4>
							</div>
	<?php
	}
	else if ($rows['panelstatus'] == 'Stopped') //The script has been validated and is marked as offline
	{
	?>
							<div style="text-align: center;">
								<a class="btn btn-primary" href="scriptprocess.php?task=scriptstart&scriptid=<?php echo $scriptid; ?>">Start</a>
							</div>
	<?php
	}
	else if ($rows['panelstatus'] == 'Started') //The script has been validated and is marked as online
	{
	?>
							<div style="text-align: center;">
								<a class="btn btn-warning" href="scriptprocess.php?task=scriptstop&scriptid=<?php echo $scriptid; ?>">Stop</a>
							</div>
	<?php
	}
}

?>
					</div>
				</div>
			</div>
<?php


include("./bootstrap/footer.php");
?>