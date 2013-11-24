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



$page = 'serveradd';
$tab = 2;
$return = 'serveradd.php';


require("../configuration.php");
require("./include.php");


$title = T_('Add New Server');


$numBoxes = query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box`" );
$numGroups = query_numrows( "SELECT `groupid` FROM `".DBPREFIX."group`" );
$games = mysql_query( "SELECT `gameid`, `game` FROM `".DBPREFIX."game` WHERE `status` = 'Active' ORDER BY `game`" );


//---------------------------------------------------------+

if ($numBoxes == 0)
{
	$step = 'noboxes';
}
else if ($numGroups == 0)
{
	$step = 'nogroups';
}
else if (isset($_GET['gameid']) && is_numeric($_GET['gameid']))
{
	if (query_numrows( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$_GET['gameid']."'" ) == 0)
	{
		exit('Error: Game is invalid.');
	}
	else
	{
		$gameid = $_GET['gameid'];
		$step = 'form';
	}
}
else
{
	$step = 'selectgame';
}

//---------------------------------------------------------+


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


switch ($step)
{

//------------------------------------------------------------------------------------------------------------+



	case 'noboxes':
?>
			<div class="well">
				<div style="text-align: center;">
					<span class="label label-warning"><?php echo T_('No Boxes Found'); ?></span><br />
					<?php echo T_('No boxes found.'); ?> <a href="boxadd.php"><?php echo T_('Click here'); ?></a> <?php echo T_('to add a new box.'); ?>
				</div>
			</div>
			<div style="text-align: center;">
				<ul class="pager">
					<li>
						<a href="server.php"><?php echo T_('Back to Servers'); ?></a>
					</li>
				</ul>
			</div>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+



	case 'nogroups':
?>
			<div class="well">
				<div style="text-align: center;">
					<span class="label label-warning"><?php echo T_('No Groups Found'); ?></span><br />
					<?php echo T_('No groups found.'); ?> <a href="configgroupadd.php"><?php echo T_('Click here'); ?></a> <?php echo T_('to add a new group.'); ?>
				</div>
			</div>
			<div style="text-align: center;">
				<ul class="pager">
					<li>
						<a href="server.php"><?php echo T_('Back to Servers'); ?></a>
					</li>
				</ul>
			</div>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+



	case 'selectgame':
?>
			<div class="well">
				<form method="get" action="serveradd.php">
					<label><?php echo T_('Game'); ?></label>
						<select name="gameid">
<?php

//---------------------------------------------------------+
while ($rowsGames = mysql_fetch_assoc($games))
{
?>
							<option value="<?php echo $rowsGames['gameid']; ?>"><?php echo htmlspecialchars($rowsGames['game'], ENT_QUOTES); ?></option>
<?php
}
//---------------------------------------------------------+

?>
						</select>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary"><?php echo T_('Submit'); ?></button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="server.php"><?php echo T_('Back to Servers'); ?></a>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+



	case 'form':
		$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."' LIMIT 1" );
		$clients = mysql_query( "SELECT `clientid`, `firstname`, `lastname` FROM `".DBPREFIX."client` WHERE `status` = 'Active' ORDER BY `clientid`" );
		$admins = mysql_query( "SELECT `adminid`, `username` FROM `".DBPREFIX."admin` WHERE `status` = 'Active' ORDER BY `adminid`" );
		$boxes = mysql_query( "SELECT `boxid`, `name` FROM `".DBPREFIX."box` ORDER BY `boxid`" );
		$groups = mysql_query( "SELECT `groupid`, `name` FROM `".DBPREFIX."group` ORDER BY `groupid`" );
		###
?>
			<ul class="breadcrumb">
				<li><a href="serveradd.php"><?php echo T_('Select Game'); ?></a> <span class="divider">/</span></li>
				<li class="active"><?php echo T_('Add New Game Server'); ?></li>
			</ul>
			<div class="well">
				<form method="post" action="serverprocess.php">
					<input type="hidden" name="task" value="serveradd" />
					<input type="hidden" name="gameID" value="<?php echo $gameid; ?>" />
					<label><?php echo T_('Game'); ?></label>
						<input type="text" class="input-xlarge disabled" disabled="" placeholder="<?php echo htmlspecialchars($rows['game'], ENT_QUOTES); ?>">
					<label><?php echo T_('Server Name'); ?></label>
						<input type="text" name="name" class="span5" value="<?php
if (isset($_SESSION['name']))
{
	echo htmlspecialchars($_SESSION['name'], ENT_QUOTES);
	unset($_SESSION['name']);
}
?>">
					<label><?php echo T_('Owner Group'); ?></label>
						<select name="groupID">
<?php
//---------------------------------------------------------+
while ($rowsGroups = mysql_fetch_assoc($groups))
{
	if (isset($_SESSION['groupid']) && $rowsGroups['groupid'] == $_SESSION['groupid'])
	{
?>
							<option value="<?php echo $rowsGroups['groupid']; ?>" selected="selected">#<?php echo $rowsGroups['groupid'].' - '.htmlspecialchars($rowsGroups['name'], ENT_QUOTES); ?></option>
<?php
		unset($_SESSION['groupid']);
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
						<select name="ipID">
<?php
//---------------------------------------------------------+
while ($rowsBoxes = mysql_fetch_assoc($boxes))
{
	$ips = mysql_query( "SELECT `ipid`, `ip` FROM `".DBPREFIX."boxIp` WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

	while ($rowsIps = mysql_fetch_assoc($ips))
	{
		if (isset($_SESSION['ipid']) && $rowsIps['ipid'] == $_SESSION['ipid'])
		{
?>
								<option value="<?php echo $rowsIps['ipid']; ?>" selected="selected"><?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES).' - '.$rowsIps['ip']; ?></option>
<?php
			unset($_SESSION['ipid']);
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
						<span class="help-inline">{ip}</span>
					<label><?php echo T_('Nice Priority'); ?></label>
						<select name="priority">
<?php
//---------------------------------------------------------+
$n = -20;
while ($n < 20)
{
	if (!isset($_SESSION['priority']) && $n == 0)
	{
?>
							<option value="<?php echo $n; ?>" selected="selected"><?php echo $n; ?></option>
<?php
	}
	else if (isset($_SESSION['priority']) && $n == $_SESSION['priority'])
	{
?>
							<option value="<?php echo $n; ?>" selected="selected"><?php echo $n; ?></option>
<?php
		unset($_SESSION['priority']);
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
while ($n < $rows['maxslots'])
{
	++$n;
	if (isset($_SESSION['slots']) && $n == $_SESSION['slots'])
	{
?>
							<option value="<?php echo $n; ?>" selected="selected"><?php echo $n; ?></option>
<?php
		unset($_SESSION['slots']);
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
						<input type="text" name="port" class="span1" value="<?php
if (isset($_SESSION['port']))
{
	echo htmlspecialchars($_SESSION['port'], ENT_QUOTES);
	unset($_SESSION['port']);
}
else
{
	echo $rows['defaultport'];
}
?>">
						<span class="help-inline">{port}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Connection Port)</span>
					<label><?php echo T_('Query Port'); ?></label>
						<input type="text" name="queryPort" class="span1" value="<?php
if (isset($_SESSION['queryport']))
{
	echo htmlspecialchars($_SESSION['queryport'], ENT_QUOTES);
	unset($_SESSION['queryport']);
}
else
{
	echo $rows['queryport'];
}
?>">
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
										<input type="text" name="cfg<?php echo $n; ?>Name" class="span2" style="margin-bottom: 0px;" value="<?php
if (isset($_SESSION['cfg'.$n.'Name']))
{
	echo htmlspecialchars($_SESSION['cfg'.$n.'Name'], ENT_QUOTES);
	unset($_SESSION['cfg'.$n.'Name']);
}
else
{
	echo htmlspecialchars($rows['cfg'.$n.'name'], ENT_QUOTES);
}
?>">
									</td>
									<td>
										<input type="text" name="cfg<?php echo $n; ?>" class="span4" style="margin-bottom: 0px;" value="<?php
if (isset($_SESSION['cfg'.$n]))
{
	echo htmlspecialchars($_SESSION['cfg'.$n], ENT_QUOTES);
	unset($_SESSION['cfg'.$n]);
}
else
{
	echo htmlspecialchars($rows['cfg'.$n], ENT_QUOTES);
}
?>">
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
						<textarea name="startLine" class="textarea span5"><?php
if (isset($_SESSION['startline']))
{
	echo htmlspecialchars($_SESSION['startline'], ENT_QUOTES);
	unset($_SESSION['startline']);
}
else
{
	echo htmlspecialchars($rows['startline'], ENT_QUOTES);
}
?></textarea>
					<hr>
					<h3><?php echo T_('Select an Action to Perform'); ?>:</h3>
					<label class="radio">
						<input type="radio" name="radioAction" value="link" checked>
						<?php echo T_('Link An Existing Game Server'); ?>
					</label>
					<label><?php echo T_('Absolute Path of the Server Executable'); ?></label>
						<input type="text" name="path" class="span6" value="<?php
if (isset($_SESSION['path']))
{
	echo htmlspecialchars($_SESSION['path'], ENT_QUOTES);
	unset($_SESSION['path']);
}
?>">
						<span class="help-inline"><?php echo T_('Example'); ?>:&nbsp;/home/user/game-servers/server1/serverbinary.bin</span>
					<label class="radio">
						<input type="radio" name="radioAction" value="create">
						<?php echo T_('Create A New Game Server'); ?><br />
						<span style="margin: 12px;" class="label label-warning"><?php echo T_('Warning! If a game server already exists in the given directory, all content will be replaced!'); ?></span>
					</label>
					<label><?php echo T_('Absolute Path for the New Game Server'); ?></label>
						<input type="text" name="path2" class="span6" value="<?php
if (isset($_SESSION['path2']))
{
	echo htmlspecialchars($_SESSION['path2'], ENT_QUOTES);
	unset($_SESSION['path2']);
}
?>">
						<span class="help-inline"><?php echo T_('Example'); ?>:&nbsp;/home/user/game-servers/server1/</span>
					<hr>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary"><?php echo T_('Add New Server'); ?></button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="serveradd.php"><?php echo T_('Back'); ?></a>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+

}


include("./bootstrap/footer.php");
?>