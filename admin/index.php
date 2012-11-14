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
 * @copyleft	2012
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 4
 * @link		http://sourceforge.net/projects/brightgamepanel/
 */



$title = 'Home';
$page = 'index';
$tab = 0;
$return = 'index.php';


require("../configuration.php");
require("./include.php");


//---------------------------------------------------------+
//Personal Notes :

$rows = query_fetch_assoc( "SELECT `adminid`, `notes` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$_SESSION['adminid']."' LIMIT 1" );

//---------------------------------------------------------+
//Online Users :

$unixLastMin = time() - 1 * 60;
$onlineClients = mysql_query( "SELECT `clientid`, `username` FROM `".DBPREFIX."client` WHERE `lastactivity` >= '".$unixLastMin."'" ); //We select all clients active in the last minute (based on unix timestamp)
$onlineAdmins = mysql_query( "SELECT `adminid`, `username` FROM `".DBPREFIX."admin` WHERE `lastactivity` >= '".$unixLastMin."'" ); //Same 4 admins
unset($unixLastMin);

//---------------------------------------------------------+
//Boxes :

$boxes = mysql_query( "SELECT `ip`, `sshport` FROM `".DBPREFIX."box`" );

$x = 0;
$y = 0;
while ($rowsBoxes = mysql_fetch_assoc($boxes))
{
	$boxNetworkStatus = getStatus($rowsBoxes['ip'], $rowsBoxes['sshport']);
	if ($boxNetworkStatus == 'Online')
	{
		$x++; //Num Online
	}
	else
	{
		$y++; //Num Offline
	}
}
unset($boxes);

//---------------------------------------------------------+
//Last 15 Actions :

$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` ORDER BY `logid` DESC LIMIT 15" );


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
			<div class="row-fluid">
				<div class="span12">
					<div class="well" id="pchart">
						<legend>Charts</legend>
							<ul id="tab" class="nav nav-pills">
								<li class="dropdown active">
									<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									Players
									<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<li class="active">
											<a data-toggle="tab" href="#playersday">Past 24H</a>
										</li>
										<li>
											<a data-toggle="tab" href="#playersweek">Past Week</a>
										</li>
									</ul>
								</li>
								<li class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									CPU
									<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a data-toggle="tab" href="#cpuday">Past 24H</a>
										</li>
										<li>
											<a data-toggle="tab" href="#cpuweek">Past Week</a>
										</li>
									</ul>
								</li>
								<li class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									RAM
									<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a data-toggle="tab" href="#ramday">Past 24H</a>
										</li>
										<li>
											<a data-toggle="tab" href="#ramweek">Past Week</a>
										</li>
									</ul>
								</li>
								<li class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									Load Average
									<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a data-toggle="tab" href="#loadavgday">Past 24H</a>
										</li>
										<li>
											<a data-toggle="tab" href="#loadavgweek">Past Week</a>
										</li>
									</ul>
								</li>
							</ul>
							<div id="myTabContent" class="tab-content">
								<div class="tab-pane fade in active" id="playersday">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.day.players.multiple">
								</div>
								<div class="tab-pane fade" id="playersweek">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.week.players.multiple">
								</div>
								<div class="tab-pane fade" id="cpuday">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.day.cpu">
								</div>
								<div class="tab-pane fade" id="cpuweek">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.week.cpu">
								</div>
								<div class="tab-pane fade" id="ramday">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.day.ram">
								</div>
								<div class="tab-pane fade" id="ramweek">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.week.ram">
								</div>
								<div class="tab-pane fade" id="loadavgday">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.day.loadavg">
								</div>
								<div class="tab-pane fade" id="loadavgweek">
									<img class="pChart" src="../bootstrap/img/wait.gif" data-original="pchart.php?task=box.week.loadavg">
								</div>
							</div>
					</div><!-- /div pchart -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="row-fluid">
				<div class="span4">
					<div class="well" id="twitter">
						<legend>Twitter</legend>
							<!--
							<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
							<script>
							new TWTR.Widget({
							  version: 2,
							  type: 'profile',
							  rpp: 2,
							  interval: 40000,
							  width: 330,
							  height: 165,
							  theme: {
								shell: {
								  background: '#0088cc',
								  color: '#f9f9f9'
								},
								tweets: {
								  background: '#f9f9f9',
								  color: '#000000',
								  links: '#0072d6'
								}
							  },
							  features: {
								scrollbar: false,
								loop: false,
								live: false,
								behavior: 'all'
							  }
							}).render().setUser('BrightGamePanel').start();
							</script>
							-->
					</div><!-- /accordion twitter -->
				</div><!-- /span -->
				<div class="span4">
					<div class="accordion" id="usersonline">
						<div class="accordion-group">
							<div class="accordion-heading" style="text-align: center;">
								<a class="accordion-toggle" href="#collapseThree" data-parent="#usersonline" data-toggle="collapse">Online Users</a>
							</div>
							<div id="collapseThree" class="accordion-body collapse">
								<div class="accordion-inner">
									<table class="table table-striped table-bordered table-condensed">
<?php

while ($rowsonlineClients = mysql_fetch_assoc($onlineClients))
{
?>
										<tr>
											<td>#<?php echo $rowsonlineClients['clientid']; ?> - [Client] <a href="clientsummary.php?id=<?php echo $rowsonlineClients['clientid']; ?>"><?php echo $rowsonlineClients['username']; ?></a></td>
										</tr>
<?php
}
unset($onlineClients);

while ($rowsonlineAdmins = mysql_fetch_assoc($onlineAdmins))
{
?>
										<tr>
											<td>#<?php echo $rowsonlineAdmins['adminid']; ?> - [Admin] <?php echo $rowsonlineAdmins['username']; ?></td>
										</tr>
<?php
}
unset($onlineAdmins);

?>
									</table>
								</div><!-- /accordion-inner -->
							</div><!-- /accordion-body collapseThree -->
						</div><!-- /accordion-group -->
					</div><!-- /accordion usersonline -->
				</div><!-- /span -->
				<div class="span4">
					<div class="accordion" id="boxes">
						<div class="accordion-group">
							<div class="accordion-heading" style="text-align: center;">
								<a class="accordion-toggle" href="#collapseFour" data-parent="#boxes" data-toggle="collapse">Boxes</a>
							</div>
							<div id="collapseFour" class="accordion-body collapse">
								<div class="accordion-inner">
									<table class="table table-striped table-bordered table-condensed">
										<tr>
											<td>Online</td>
											<td style="text-align: center;"><?php

if ($x != 0)
{
?><span class="badge badge-success"><?php echo $x; ?></span><?php
}
else
{
?><span class="badge badge-warning"><?php echo $x; ?></span><?php
}

?></td>
										</tr>
										<tr>
											<td>Offline</td>
											<td style="text-align: center;"><?php

if ($y != 0)
{
?><span class="badge badge-important"><?php echo $y; ?></span><?php
}
else
{
?><span class="badge"><?php echo $y; ?></span><?php
}

?></td>
										</tr>
									</table>
								</div><!-- /accordion-inner -->
							</div><!-- /accordion-body collapseFour-->
						</div><!-- /accordion-group -->
					</div><!-- /accordion boxes -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="row-fluid">
				<div class="span6">
					<div class="accordion" id="voice">
						<div class="accordion-group">
							<div class="accordion-heading" style="text-align: center;">
								<a class="accordion-toggle" href="#collapseFive" data-parent="#voice" data-toggle="collapse">Active Voice Servers - WIP</a>
							</div>
							<div id="collapseFive" class="accordion-body collapse">
								<div class="accordion-inner">
									<table class="table table-striped table-bordered table-condensed">
										<tr>
											<td></td>
										</tr>
									</table>
								</div><!-- /accordion-inner -->
							</div><!-- /accordion-body collapseFive-->
						</div><!-- /accordion-group -->
					</div><!-- /accordion voice -->
				</div><!-- /span -->
				<div class="span6">
					<div class="accordion" id="game">
						<div class="accordion-group">
							<div class="accordion-heading" style="text-align: center;">
								<a class="accordion-toggle" href="#collapseSix" data-parent="#game" data-toggle="collapse">Active Game Servers</a>
							</div>
							<div id="collapseSix" class="accordion-body collapse">
								<div class="accordion-inner">
									<div style="margin-bottom: 5px;">
										<span class="label label-info">Online Servers:</span>
									</div>
									<table class="table table-striped table-bordered table-condensed">
<?php

$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `status` = 'Active' && `panelstatus` = 'Started' ORDER BY `name`" );
###
while ($rowsServers = mysql_fetch_assoc($servers))
{
	$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rowsServers['boxid']."' LIMIT 1" );
	$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1");
	###
	//---------------------------------------------------------+
	//Querying the server
	include_once("../libs/lgsl/lgsl_class.php");
	###
	$lgsl = lgsl_query_live($type['querytype'], $serverIp['ip'], NULL, $rowsServers['queryport'], NULL, 's');
	###
	if (@$lgsl['b']['status']  == '1') //Online
	{
?>
										<tr>
											<td>#<?php echo $rowsServers['serverid']; ?> - <a href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><?php echo $rowsServers['name']; ?></a> <span class="label label-success"><i class="icon-ok <?php echo formatIcon('icon-white', TEMPLATE); ?>"></i></span></td>
										</tr>
<?php
	}
	unset($lgsl, $serverIp, $type);
}
unset($servers);

?>
									</table>
									<hr>
									<div style="margin-bottom: 5px;">
										<span class="label label-warning">Offline Servers:</span>
									</div>
									<table class="table table-striped table-bordered table-condensed">
<?php

$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `status` = 'Active' && `panelstatus` = 'Started' ORDER BY `name`" );
###
while ($rowsServers = mysql_fetch_assoc($servers))
{
	$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rowsServers['boxid']."' LIMIT 1" );
	$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1");
	###
	//---------------------------------------------------------+
	//Querying the server
	include_once("../libs/lgsl/lgsl_class.php");
	###
	$lgsl = lgsl_query_live($type['querytype'], $serverIp['ip'], NULL, $rowsServers['queryport'], NULL, 's');
	###
	if (@$lgsl['b']['status'] == '0') //Offline
	{
?>
										<tr>
											<td>#<?php echo $rowsServers['serverid']; ?> - <a href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><?php echo $rowsServers['name']; ?></a> <span class="label label-important"><i class="icon-warning-sign <?php echo formatIcon('icon-white', TEMPLATE); ?>"></i></span></td>
										</tr>
<?php
	}
	unset($lgsl, $serverIp, $type);
}
unset($servers);

?>
									</table>
								</div><!-- /accordion-inner -->
							</div><!-- /accordion-body collapseSix-->
						</div><!-- /accordion-group -->
					</div><!-- /accordion game -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="accordion" id="notes">
				<div class="accordion-group">
					<div class="accordion-heading" style="text-align: center;">
						<a class="accordion-toggle" href="#collapseSeven" data-parent="#notes" data-toggle="collapse">Personal Notes</a>
					</div>
					<div id="collapseSeven" class="accordion-body collapse">
						<div class="accordion-inner">
							<form method="post" action="process.php">
								<input type="hidden" name="task" value="personalnotes" />
								<input type="hidden" name="adminid" value="<?php echo $rows['adminid']; ?>" />
								<div style="text-align: center;">
									<textarea name="notes" class="textarea span11"><?php echo htmlspecialchars($rows['notes']); ?></textarea>
								</div>
								<div style="text-align: center; margin-top: 18px;">
									<button type="submit" class="btn">Save</button>
								</div>
							</form>
						</div><!-- /accordion-inner -->
					</div><!-- /accordion-body collapseSeven-->
				</div><!-- /accordion-group -->
			</div><!-- /accordion notes -->
			<div class="accordion" id="logs">
				<div class="accordion-group">
					<div class="accordion-heading" style="text-align: center;">
						<a class="accordion-toggle" href="#collapseEight" data-parent="#logs" data-toggle="collapse">Last 15 Actions</a>
					</div>
					<div id="collapseEight" class="accordion-body collapse">
						<div class="accordion-inner">
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label label-info"><?php echo mysql_num_rows($logs); ?> Record(s) Found</span> (<a href="utilitieslog.php">View All</a>)
							</div>
							<table id="logstable" class="tablesorter">
								<thead>
									<tr>
										<th>ID</th>
										<th>Message</th>
										<th>Name</th>
										<th>IP</th>
										<th>Timestamp</th>
									</tr>
								</thead>
								<tbody>
<?php

if (mysql_num_rows($logs) == 0)
{
?>
									<tr>
										<td colspan="5"><div style="text-align: center;"><span class="label label-warning">No Logs Found</span></div></td>
									</tr>
<?php
}
while ($rowsLogs = mysql_fetch_assoc($logs))
{
?>
									<tr>
										<td><?php echo $rowsLogs['logid']; ?></td>
										<td><?php echo $rowsLogs['message']; ?></td>
										<td><?php echo $rowsLogs['name']; ?></td>
										<td><?php echo $rowsLogs['ip']; ?></td>
										<td><?php echo formatDate($rowsLogs['timestamp']); ?></td>
									</tr>
<?php
}

?>
								</tbody>
							</table>
<?php

if (mysql_num_rows($logs) != 0)
{
?>
							<script type="text/javascript">
							$(document).ready(function() {
								// call the tablesorter plugin
								$("#logstable").tablesorter({
									// sort on the first column
									sortList: [[0,1]]
								});
							});
							</script>
<?php
}
unset($logs);

?>
						</div><!-- /accordion-inner -->
					</div><!-- /accordion-body collapseEight-->
				</div><!-- /accordion-group -->
			</div><!-- /accordion logs -->
			<script src="../bootstrap/js/home.js"></script>
			<script>
			// pchart delayed rendering
			$(function() {
				$("img.pChart").show().lazyload();
			});
			$('a[data-toggle="tab"]').on('shown', function (e) {
				$("img.pChart").show().lazyload();
			});
			</script>
<?php


include("./bootstrap/footer.php");
?>