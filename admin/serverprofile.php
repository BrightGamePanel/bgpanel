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
 * @version		(Release 0) DEVELOPER BETA 9
 * @link		http://www.bgpanel.net/
 */



$page = 'serverprofile';
$tab = 2;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: ServerID error.');
}

$serverid = $_GET['id'];
$return = 'serverprofile.php?id='.urlencode($serverid);


require("../configuration.php");
require("./include.php");
require_once("../includes/func.ssh2.inc.php");
require_once("../libs/phpseclib/Crypt/AES.php");
require_once("../libs/gameinstaller/gameinstaller.php");


$title = T_('Server Settings');

$serverid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$box = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
$ip = query_fetch_assoc( "SELECT `ip`, `boxid` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rows['ipid']."' LIMIT 1" );
$game = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1" );
$boxes = mysql_query( "SELECT `boxid`, `name` FROM `".DBPREFIX."box` ORDER BY `boxid`" );
$groups = mysql_query( "SELECT `groupid`, `name` FROM `".DBPREFIX."group` ORDER BY `groupid`" );

$aes = new Crypt_AES();
$aes->setKeyLength(256);
$aes->setKey(CRYPT_KEY);

// Get SSH2 Object OR ERROR String
$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
if (!is_object($ssh))
{
	$_SESSION['msg1'] = T_('Connection Error!');
	$_SESSION['msg2'] = $ssh;
	$_SESSION['msg-type'] = 'error';
}

$gameInstaller = new GameInstaller( $ssh );

$gameCacheInfo =	$gameInstaller->getCacheInfo( dirname($rows['path']) );
$gameExists =		$gameInstaller->gameExists( $game['game'] );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


if ($rows['panelstatus'] == 'Started')
{
?>
			<div class="alert alert-block">
				<h4 class="alert-heading">"<?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?>" <?php echo T_('is currently running!'); ?></h4>
			</div>
<?php
}

?>
			<ul class="nav nav-tabs">
				<li><a href="serversummary.php?id=<?php echo $serverid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li class="active"><a href="serverprofile.php?id=<?php echo $serverid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="servermanage.php?id=<?php echo $serverid; ?>"><?php echo T_('Manage'); ?></a></li>
<?php

if ($game['querytype'] != 'none')
{
	echo "\t\t\t\t<li><a href=\"serverlgsl.php?id=".$serverid."\">LGSL</a></li>";
}

?>

<?php

if ($rows['panelstatus'] == 'Started')
{
	echo "\t\t\t\t<li><a href=\"utilitiesrcontool.php?serverid=".$serverid."\">".T_('RCON Tool')."</a></li>";
}

?>

				<li><a href="#" onclick="ajxp()"><?php echo T_('WebFTP'); ?></a></li>
				<li><a href="serverlog.php?id=<?php echo $serverid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
<?php

// Game Installer Notification
if ( $gameExists != FALSE ) {
	if ( $gameCacheInfo != FALSE ) {
		if ( ($gameCacheInfo['status'] != 'Ready') && ($gameCacheInfo['status'] != 'Aborted') ) {
			// Operation in progress
?>
			<div class="alert alert-info">
				<h4 class="alert-heading"><?php echo T_('Operation In Progress On This Game Server'); ?></h4>
				<br />
				<div class="progress progress-striped active">
					<div class="bar" style="width: 100%;"><?php echo htmlspecialchars($gameCacheInfo['status'], ENT_QUOTES); ?></div>
				</div>
				<p class="text-center">
					<a class="btn btn-warning" href="#" onclick="doGameServerAction('<?php echo $serverid; ?>', 'abortOperation', '<?php echo T_('abort current operation for game server'); ?>', '<?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?>')">
						<i class="icon-stop icon-white"></i>&nbsp;<?php echo T_('Abort Operation'); ?>
					</a>
				</p>
			</div>
<?php
		}
	}
}

if ($rows['panelstatus'] == 'Started')
{
?>
			<div class="alert alert-block">
				<h4 class="alert-heading"><?php echo T_('Server profile edition disabled.'); ?></h4>
				<p><?php echo T_('The server is currently running.'); ?></p>
			</div>
<?php
}
else
{

?>
			<div class="well">
				<form method="post" action="serverprocess.php">
					<input type="hidden" name="task" value="serverprofile" />
					<input type="hidden" name="serverid" value="<?php echo $serverid; ?>" />
					<label><?php echo T_('Game'); ?></label>
						<input type="text" class="input-xlarge disabled" disabled="" placeholder="<?php echo htmlspecialchars($rows['game'], ENT_QUOTES); ?>">
					<label><?php echo T_('Server Name'); ?></label>
						<input type="text" name="name" class="span5" value="<?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?>">
<?php

	//---------------------------------------------------------+

	if ($rows['status'] != 'Pending')
	{
?>
					<label><?php echo T_('Status'); ?></label>
						<div class="btn-group" data-toggle="buttons-radio" style="margin-bottom: 5px;">
							<a class="btn btn-primary <?php
		if ($rows['status']	== 'Active')
		{
			echo 'active';
		}
?>" onclick="switchRadio();return false;"><?php echo T_('Active'); ?></a>
							<a class="btn btn-primary <?php
		if ($rows['status']	== 'Inactive')
		{
			echo 'active';
		}
?>" onclick="switchRadio();return false;"><?php echo T_('Inactive'); ?></a>
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
						<h4 class="alert-heading"><?php echo T_('Server not validated !'); ?></h4>
						<p>
							<?php echo T_('You must validate the server before changing its status.'); ?>
						</p>
						<p>
							<a class="btn btn-primary" href="serverprocess.php?task=servervalidation&serverid=<?php echo $serverid; ?>"><?php echo T_('Validate'); ?></a>
						</p>
					</div>
<?php
	}

	//---------------------------------------------------------+

?>
					<label><?php echo T_('Owner Group'); ?></label>
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
					<label><?php echo T_('Box IP'); ?></label>
<?php

	if ($rows['status'] == 'Pending')
	{
?>
						<select name="ipid">
<?php

		//---------------------------------------------------------+

		while ($rowsBoxes = mysql_fetch_assoc($boxes))
		{
			$ips = mysql_query( "SELECT `ipid`, `ip` FROM `".DBPREFIX."boxIp` WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

			while ($rowsIps = mysql_fetch_assoc($ips))
			{
				if ($rowsIps['ipid'] == $rows['ipid'])
				{
?>
									<option value="<?php echo $rowsIps['ipid']; ?>" selected="selected"><?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES).' - '.$rowsIps['ip']; ?></option>
<?php
				}
				else
				{
?>
									<option value="<?php echo $rowsIps['ipid']; ?>"><?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES).' - '.$rowsIps['ip']; ?></option>
<?php
				}
			}

			unset($ips);
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
			if ($rowsBoxes['boxid'] == $ip['boxid'])
			{
?>
						<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES).' - '.$ip['ip']; ?>">
						<input type="hidden" name="ipid" value="<?php echo $rows['ipid']; ?>">
<?php
			}

		}

		//---------------------------------------------------------+

	}

?>
						<span class="help-inline">{ip}</span>
					<label><?php echo T_('Nice Priority'); ?></label>
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
						<span class="help-inline"><?php echo T_('-20 is the most favorable and 19 the least favorable'); ?></span>
					<label><?php echo T_('Slots'); ?></label>
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
					<label><?php echo T_('Server Port'); ?></label>
						<input type="text" name="port" class="span1" value="<?php echo $rows['port']; ?>">
						<span class="help-inline">{port}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Connection Port)</span>
					<label><?php echo T_('Query Port'); ?></label>
						<input type="text" name="queryPort" class="span1" value="<?php echo htmlspecialchars($rows['queryport'], ENT_QUOTES); ?>">
						<span class="help-inline"><?php echo T_('LGSL Query Port'); ?></span>
					<div class="row">
						<div class="span6">
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label"><?php echo T_('Server Configuration'); ?></span>
							</div>
							<table class="table table-striped table-bordered">
								<tr>
									<td><?php echo T_('Configuration Name'); ?></td>
									<td><?php echo T_('Associated Option'); ?></td>
									<td><?php echo T_('Alias'); ?></td>
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
					<label><?php echo T_('Start Command'); ?></label>
						<textarea name="startLine" class="textarea span5"><?php echo htmlspecialchars($rows['startline'], ENT_QUOTES); ?></textarea>
					<label><?php echo T_('Absolute Path of the Server Executable'); ?></label>
						<input type="text" name="path" class="span6" value="<?php echo htmlspecialchars($rows['path'], ENT_QUOTES); ?>">
						<span class="help-inline"><?php echo T_('Example'); ?>:&nbsp;/home/user/game/server1/serverbinary.bin</span>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary"><?php echo T_('Save Changes'); ?></button>
						<button type="reset" class="btn"><?php echo T_('Cancel Changes'); ?></button>
					</div>
				</form>
			</div>
<?php

}

?>
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
			<!-- -->
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>