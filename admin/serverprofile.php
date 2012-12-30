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



$title = 'Server Settings';
$page = 'serverprofile';
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
$return = 'serverprofile.php?id='.urlencode($serverid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$game = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1" );
$boxes = mysql_query( "SELECT `boxid`, `name`, `ip` FROM `".DBPREFIX."box` ORDER BY `boxid`" );
$groups = mysql_query( "SELECT `groupid`, `name` FROM `".DBPREFIX."group` ORDER BY `groupid`" );


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
				<li class="active"><a href="serverprofile.php?id=<?php echo $serverid; ?>">Profile</a></li>
				<li><a href="servermanage.php?id=<?php echo $serverid; ?>">Manage</a></li>
<?php

if ($game['querytype'] != 'none')
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
			<div class="well">
				<form method="post" action="serverprocess.php">
					<input type="hidden" name="task" value="serverprofile" />
					<input type="hidden" name="serverid" value="<?php echo $serverid; ?>" />
					<label>Game</label>
						<input type="text" class="input-xlarge disabled" disabled="" placeholder="<?php echo htmlspecialchars($rows['game'], ENT_QUOTES); ?>">
					<label>Server Name</label>
						<input type="text" name="name" class="span5" value="<?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?>">
<?php

//---------------------------------------------------------+

if ($rows['status'] != 'Pending')
{
?>
					<label>Status</label>
						<div class="btn-group" data-toggle="buttons-radio" style="margin-bottom: 5px;">
							<a class="btn btn-primary <?php
	if ($rows['status']	== 'Active')
	{
		echo 'active';
	}
?>" onclick="switchRadio();return false;">Active</a>
							<a class="btn btn-primary <?php
	if ($rows['status']	== 'Inactive')
	{
		echo 'active';
	}
?>" onclick="switchRadio();return false;">Inactive</a>
						</div>
						<div class="collapse">
							<label class="radio">
								<input id="status0" type="radio" value="Active" name="status" <?php
	if ($rows['status']	== 'Active')
	{
		echo "checked=\"\"";
	}
?>>
							</label>
							<label class="radio">
								<input id="status1" type="radio" value="Inactive" name="status" <?php
	if ($rows['status']	== 'Inactive')
	{
		echo "checked=\"\"";
	}
?>>
							</label>
						</div>
<?php
}
else
{
?>
					<input type="hidden" name="status" value="Pending" />
					<div class="alert alert-info">
						<h4 class="alert-heading">Server not validated !</h4>
						<p>
							You must validate the server before changing its status.
						</p>
						<p>
							<a class="btn btn-primary" href="serverprocess.php?task=servervalidation&serverid=<?php echo $serverid; ?>">Validate</a>
						</p>
					</div>
<?php
}

//---------------------------------------------------------+

?>
					<label>Owner Group</label>
						<select name="groupid">
<?php

//---------------------------------------------------------+

while ($rowsGroups = mysql_fetch_assoc($groups))
{
	if ($rowsGroups['groupid'] == $rows['groupid'])
	{
?>
							<option value="<?php echo $rowsGroups['groupid']; ?>" selected="selected">#<?php echo $rowsGroups['groupid'].' - '.htmlspecialchars($rowsGroups['name'], ENT_QUOTES); ?></option>
<?php
	}
	else
	{
?>
							<option value="<?php echo $rowsGroups['groupid']; ?>">#<?php echo $rowsGroups['groupid'].' - '.htmlspecialchars($rowsGroups['name'], ENT_QUOTES); ?></option>
<?php
	}
}

//---------------------------------------------------------+

?>
						</select>
					<label>Box</label>
<?php

if ($rows['status'] == 'Pending')
{
?>
						<select name="boxid">
<?php

	//---------------------------------------------------------+

	while ($rowsBoxes = mysql_fetch_assoc($boxes))
	{
		if ($rowsBoxes['boxid'] == $rows['boxid'])
		{
?>
							<option value="<?php echo $rowsBoxes['boxid']; ?>" selected="selected"><?php echo $rowsBoxes['ip'].' - '.htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></option>
<?php
		}
		else
		{
?>
							<option value="<?php echo $rowsBoxes['boxid']; ?>"><?php echo $rowsBoxes['ip'].' - '.htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></option>
<?php
		}
	}

	//---------------------------------------------------------+

?>
						</select>
<?php
}
else
{

	//---------------------------------------------------------+

	while ($rowsBoxes = mysql_fetch_assoc($boxes))
	{
		if ($rowsBoxes['boxid'] == $rows['boxid'])
		{
?>
						<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo$rowsBoxes['ip'].' - '.htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?>">
						<input type="hidden" name="boxid" value="<?php echo $rows['boxid']; ?>">
<?php
		}
	}

	//---------------------------------------------------------+

}

?>
						<span class="help-inline">{ip}</span>
					<label>Nice Priority</label>
						<select name="priority">
<?php


//---------------------------------------------------------+

$n = -20;
while ($n < 20)
{
	if ($n == $rows['priority'])
	{
?>
							<option value="<?php echo $n; ?>" selected="selected"><?php echo $n; ?></option>
<?php
	}
	else
	{
?>
							<option value="<?php echo $n; ?>"><?php echo $n; ?></option>
<?php
	}
	++$n;
}

//---------------------------------------------------------+

?>
						</select>
						<span class="help-inline">-20 is the most favorable and 19 the least favorable</span>
					<label>Slots</label>
						<select name="slots">
<?php

//---------------------------------------------------------+

$n = 0;
while ($n < $game['maxslots'])
{
	++$n;
	if ($n == $rows['slots'])
	{
?>
							<option value="<?php echo $n; ?>" selected="selected"><?php echo $n; ?></option>
<?php
	}
	else
	{
?>
							<option value="<?php echo $n; ?>"><?php echo $n; ?></option>
<?php
	}
}

//---------------------------------------------------------+

?>
						</select>
						<span class="help-inline">{slots}</span>
					<label>Server Port</label>
						<input type="text" name="port" class="span1" value="<?php echo $rows['port']; ?>">
						<span class="help-inline">{port}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Connection Port)</span>
					<label>Query Port</label>
						<input type="text" name="queryPort" class="span1" value="<?php echo htmlspecialchars($rows['queryport'], ENT_QUOTES); ?>">
						<span class="help-inline">LGSL Query Port</span>
					<div class="row">
						<div class="span6">
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label">Server Configuration</span>
							</div>
							<table class="table table-striped table-bordered">
								<tr>
									<td>Configuration Name</td>
									<td>Associated Option</td>
									<td>Alias</td>
								</tr>
<?php

//---------------------------------------------------------+

$n = 1;
while ($n < 10)
{
?>
								<tr>
									<td>
										<input type="text" name="cfg<?php echo $n; ?>Name" class="span2" style="margin-bottom: 0px;" value="<?php echo htmlspecialchars($rows['cfg'.$n.'name'], ENT_QUOTES); ?>">
									</td>
									<td>
										<input type="text" name="cfg<?php echo $n; ?>" class="span4" style="margin-bottom: 0px;" value="<?php echo htmlspecialchars($rows['cfg'.$n], ENT_QUOTES); ?>">
									</td>
									<td style="padding-left: 3px;">
										<div style="text-align: center; margin-bottom: 0px;">
											<span class="help-inline" style="padding-top: 5px;">{cfg<?php echo $n; ?>}</span>
										</div>
									</td>
								</tr>
<?php
	++$n;
}
unset ($n);

//---------------------------------------------------------+

?>
							</table>
						</div>
					</div>
					<label>Start Command</label>
						<textarea name="startLine" class="textarea span5"><?php echo htmlspecialchars($rows['startline'], ENT_QUOTES); ?></textarea>
					<label>Home Directory</label>
						<input type="text" name="homeDir" class="span6" value="<?php echo htmlspecialchars($rows['homedir'], ENT_QUOTES); ?>">
						<span class="help-inline">Executable Directory</span>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Save Changes</button>
						<button type="reset" class="btn">Cancel Changes</button>
					</div>
				</form>
			</div>
			<script language="javascript" type="text/javascript">
			function switchRadio()
			{
				var statusActive = document.getElementById('status0');
				var statusInactive = document.getElementById('status1');
				<!-- -->
				var active = statusActive.getAttribute('checked');
				var inactive = statusInactive.getAttribute('checked');
				<!-- -->
				if (active == '') {
					statusActive.removeAttribute('checked');
					statusInactive.setAttribute('checked', '');
				} else if (inactive == '') {
					statusActive.setAttribute('checked', '');
					statusInactive.removeAttribute('checked');
				}
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>