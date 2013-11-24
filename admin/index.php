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



$page = 'index';
$tab = 0;
$return = 'index.php';


require("../configuration.php");
require("./include.php");

$title = T_('Home');

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
//Servers :
$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `status` = 'Active' && `panelstatus` = 'Started' ORDER BY `name`" );

//---------------------------------------------------------+
//Boxes :
$boxes = mysql_query( "SELECT `boxid`, `name`, `cache` FROM `".DBPREFIX."box` ORDER BY `name`" );
$cron = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'lastcronrun' LIMIT 1" );

//---------------------------------------------------------+
//Last 15 Actions :
$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` ORDER BY `logid` DESC LIMIT 15" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="row-fluid">
				<div class="span12">
					<div id="charts">
						<div id="players">
<?php

if (query_numrows( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 + CRONDELAY))."'" ) != 0)
{
?>
							<script>
							$(document).ready(function() {
								$.getJSON('api.boxdata.json.php?api_key=<?php echo API_KEY; ?>&task=players', function(data) {
									// Create the chart
									players = new Highcharts.StockChart({
										chart : {
											renderTo : 'players'
										},

										title : {
											text : 'Players'
										},

										xAxis: {
											gapGridLineWidth: 0
										},

										rangeSelector : {
											buttons : [{
												type : 'day',
												count : 1,
												text : '1D'
											}, {
												type : 'week',
												count : 1,
												text : '1W'
											}, {
												type : 'month',
												count : 1,
												text : '1M'
											}, {
												type : 'all',
												count : 1,
												text : 'All'
											}],
											selected : 0,
											inputEnabled : false
										},

										series : [{
											name : 'Players',
											type : 'area',
											data : data,
											threshold : null,
											gapSize: 5,
											tooltip : {
												valueDecimals : 2
											},
											fillColor : {
												linearGradient : {
													x1: 0,
													y1: 0,
													x2: 0,
													y2: 1
												},
												stops : [[0, Highcharts.getOptions().colors[0]], [1, 'rgba(254,254,254,254)']]
											}
										}]
									});
								});
							});
							</script>
							<script src="../bootstrap/js/highstock.js"></script>
							<script src="../bootstrap/js/modules/exporting.js"></script>
							<div id="players"></div>
<?php
}
else
{
?>
							<img class="playersChart" data-original="../bootstrap/img/nodata.png" src="../bootstrap/img/wait.gif" style="display: inline; padding-left: 20px;">
							<script>
							// delayed rendering
							$(document).ready(function() {
								$("img.playersChart").lazyload();
							});
							</script>
<?php
}

?>
						</div>
					</div><!-- /charts -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<hr>
			<div class="row-fluid">
				<div class="span4">
					<div id="twitter">
						<legend>Twitter</legend>
						<blockquote>
							<p>An easy way to get the latest updates, announcements and blog articles of the BPanel project</p>
						</blockquote>
						<a href="http://www.twitter.com/BrightGamePanel" class="btn btn-block" type="button" style="margin-bottom: 10px;">Follow @BrightGamePanel</a>
					</div><!-- /twitter -->
				</div><!-- /span -->
				<div class="span3">
					<div id="usersonline">
						<legend><?php echo T_('Online Users'); ?></legend>
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th> <?php echo T_('Privilege'); ?> </th>
										<th> <?php echo T_('Username'); ?> </th>
									</tr>
								</thead>
								<tbody>
<?php

while ($rowsonlineClients = mysql_fetch_assoc($onlineClients))
{
?>
									<tr>
										<td> <?php echo T_('Client'); ?> </td>
										<td> <a href="clientsummary.php?id=<?php echo $rowsonlineClients['clientid']; ?>"><?php echo htmlspecialchars($rowsonlineClients['username'], ENT_QUOTES); ?></a> </td>
									</tr>
<?php
}
unset($onlineClients);

while ($rowsonlineAdmins = mysql_fetch_assoc($onlineAdmins))
{
?>
									<tr>
										<td> <?php echo T_('Admin'); ?> </td>
										<td> <?php echo htmlspecialchars($rowsonlineAdmins['username'], ENT_QUOTES); ?> </td>
									</tr>
<?php
}
unset($onlineAdmins);

?>
								</tbody>
							</table>
					</div><!-- /usersonline -->
				</div><!-- /span -->
				<div class="span5">
					<div id="notes">
						<legend><?php echo T_('Personal Notes'); ?></legend>
							<form method="post" action="process.php">
								<input type="hidden" name="task" value="personalnotes" />
								<input type="hidden" name="adminid" value="<?php echo $_SESSION['adminid']; ?>" />
								<div style="text-align: center;">
									<textarea name="notes" class="textarea span11"><?php echo htmlspecialchars($rows['notes'], ENT_QUOTES); ?></textarea>
								</div>
								<div style="text-align: center; margin-top: 18px;">
									<button type="submit" class="btn"><?php echo T_('Save'); ?></button>
								</div>
							</form>
					</div><!-- /notes -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="row-fluid">
				<div class="span4">
					<div id="game">
						<legend><?php echo T_('Active Game Servers'); ?></legend>
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th> <?php echo T_('Server Name'); ?> </th>
										<th> <?php echo T_('Net Status'); ?> </th>
									</tr>
								</thead>
								<tbody>
<?php

while ($rowsServers = mysql_fetch_assoc($servers))
{
	$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rowsServers['ipid']."' LIMIT 1" );
	$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1");
	###
	//---------------------------------------------------------+
	//Querying the server
	include_once("../libs/lgsl/lgsl_class.php");
	###
	$lgsl = lgsl_query_live($type['querytype'], $serverIp['ip'], NULL, $rowsServers['queryport'], NULL, 's');
	###
	if (@$lgsl['b']['status'] == '1')
	{
?>
									<tr>
										<td> <a href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></a> </td>
										<td> <span class="label label-success"><?php echo T_('Online'); ?></span> </td>
									</tr>
<?php
	}
	else
	{
?>
									<tr>
										<td> <a href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></a> </td>
										<td> <span class="label label-important"><?php echo T_('Offline'); ?></span> </td>
									</tr>
<?php
	}
	unset($lgsl, $serverIp, $type);
}
unset($servers);

?>
								</tbody>
							</table>
					</div><!-- /game -->
				</div><!-- /span -->
				<div class="span8">
					<div id="game">
						<legend><?php echo T_('Boxes'); ?></legend>
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th> <?php echo T_('Box Name'); ?> </th>
										<th> <?php echo T_('Load Average'); ?> </th>
										<th> <?php echo T_('HDD Usage'); ?> </th>
										<th colspan="2"> <?php echo T_('Bandwith Usage'); ?> </th>
										<th> <?php echo T_('Uptime'); ?> </th>
									</tr>
								</thead>
								<tbody>
<?php

/**
 * BOXES
 */

while ($rowsBoxes = mysql_fetch_assoc($boxes))
{
	$cache = unserialize(gzuncompress($rowsBoxes['cache']));
?>
									<tr>
										<td> <a href="boxsummary.php?id=<?php echo $rowsBoxes['boxid']; ?>"><?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></a> </td>
										<td> <span class="badge badge-<?php

										if (substr($cache["{$rowsBoxes['boxid']}"]['loadavg']['loadavg'], 0, -3) < $cache["{$rowsBoxes['boxid']}"]['cpu']['cores']) {
											echo 'info';
										} else if (substr($cache["{$rowsBoxes['boxid']}"]['loadavg']['loadavg'], 0, -3) == $cache["{$rowsBoxes['boxid']}"]['cpu']['cores']) {
											echo 'warning';
										} else { echo 'important'; }

										?>"><?php echo $cache["{$rowsBoxes['boxid']}"]['loadavg']['loadavg']; ?></span> </td>
										<td> <span class="badge badge-<?php

										if ($cache["{$rowsBoxes['boxid']}"]['hdd']['usage'] < 65) {
											echo 'info';
										} else if ($cache["{$rowsBoxes['boxid']}"]['hdd']['usage'] < 85) {
											echo 'warning';
										} else { echo 'important'; }

										?>"><?php echo $cache["{$rowsBoxes['boxid']}"]['hdd']['usage']; ?>&nbsp;%</span> </td>
										<td> RX:&nbsp;<?php echo bytesToSize($cache["{$rowsBoxes['boxid']}"]['bandwidth']['rx_usage']); ?>/s </td>
										<td> TX:&nbsp;<?php echo bytesToSize($cache["{$rowsBoxes['boxid']}"]['bandwidth']['tx_usage']); ?>/s </td>
										<td> <?php echo $cache["{$rowsBoxes['boxid']}"]['uptime']['uptime']; ?> </td>
									</tr>
<?php
	unset($cache);
}
unset($boxes);

?>
								</tbody>
							</table>
							<div class="well"><?php echo T_('Last Update'); ?> : <span class="label"><?php echo formatDate($cron['value']); ?></span><?php

if ($cron['value'] == 'Never')
{
	echo "\t\t\t<br />".T_('Setup the cron job to enable box monitoring!');
}
unset($cron);

?></div>
					</div><!-- /game -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="row-fluid">
				<div class="span12">
					<div id="logs">
						<legend><?php echo T_('Last 15 Actions'); ?></legend>
							<div style="text-align: center; margin-bottom: 5px;">
								<a href="utilitieslog.php" class="btn btn-primary"><?php echo T_('View All'); ?></a>
							</div>
							<table id="logstable" class="zebra-striped">
								<thead>
									<tr>
										<th><?php echo T_('ID'); ?></th>
										<th><?php echo T_('Message'); ?></th>
										<th><?php echo T_('Name'); ?></th>
										<th><?php echo T_('IP'); ?></th>
										<th><?php echo T_('Timestamp'); ?></th>
									</tr>
								</thead>
								<tbody>
<?php

if (mysql_num_rows($logs) == 0)
{
?>
									<tr>
										<td colspan="5"><div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Logs Found'); ?></span></div></td>
									</tr>
<?php
}
while ($rowsLogs = mysql_fetch_assoc($logs))
{
?>
									<tr>
										<td><?php echo $rowsLogs['logid']; ?></td>
										<td><?php echo htmlspecialchars($rowsLogs['message'], ENT_QUOTES); ?></td>
										<td><?php echo htmlspecialchars($rowsLogs['name'], ENT_QUOTES); ?></td>
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
							<script>
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
					</div><!-- /logs -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
<?php


include("./bootstrap/footer.php");
?>