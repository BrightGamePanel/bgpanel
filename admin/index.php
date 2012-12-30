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
//Servers :
$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `status` = 'Active' && `panelstatus` = 'Started' ORDER BY `name`" );

//---------------------------------------------------------+
//Boxes :
$boxes = mysql_query( "SELECT `boxid`, `name`, `bw_rx`, `bw_tx`, `cpu`, `loadavg`, `hdd`, `uptime` FROM `".DBPREFIX."box` ORDER BY `name`" );
$boxData = query_fetch_assoc( "SELECT `boxids`, `bw_rx`, `bw_tx` FROM `".DBPREFIX."boxData` ORDER BY `id` DESC LIMIT 1, 1" ); // Next to last cron data
$cron = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'lastcronrun' LIMIT 1" );

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
					</div><!-- /well pchart -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="row-fluid">
				<div class="span4">
					<div id="twitter">
						<legend>Twitter</legend>
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th>
											<h4>&nbsp;&nbsp;Tweets</h4>
											&nbsp;&nbsp;<a href="http://www.twitter.com/BrightGamePanel" class="btn btn-mini" type="button" style="margin-bottom: 10px;">Follow @BrightGamePanel</a>
										</th>
									</tr>
								</thead>
								<tbody>
<?php

$screen_name = "BrightGamePanel";
$count = "4";

$request = "https://api.twitter.com/1/statuses/user_timeline.json?exclude_replies=true&screen_name={$screen_name}&count={$count}";

$rdata = json_decode(file_get_contents($request));

foreach ($rdata as $ritem)
{
	$showtext = $ritem->text;
	$showtext = utf8_decode($showtext);
	$status_timestamp = strtotime($ritem->created_at);
	$status_locdate = date("Y-m-d (H:i:s)",$status_timestamp);
?>
									<tr>
										<td>
											<a href="http://twitter.com/<?php echo $ritem->user->screen_name; ?>" title="<?php echo $ritem->user->name; ?>" target="_blank">
												<img src="<?php echo $ritem->user->profile_image_url; ?>" alt="<?php echo $ritem->user->name; ?>" align="left" width="48" height="48" border="0" style="padding:10px 8px 2px 0px;" />
											</a>
											<a href="http://twitter.com/<?php echo $ritem->user->screen_name; ?>" title="<?php echo $ritem->user->name; ?>" target="_blank">
												<strong><?php echo $ritem->user->screen_name; ?></strong>
											</a><span class="label"><?php echo $status_locdate; ?></span><br />
											<?php echo $showtext."\r\n"; ?>
										</td>
									</tr>
<?php
	unset($showtext, $status_timestamp, $status_locdate);
}

unset($screen_name, $count, $request, $twitter);

?>
								</tbody>
							</table>
					</div><!-- /twitter -->
				</div><!-- /span -->
				<div class="span2">
					<div id="usersonline">
						<legend>Online Users</legend>
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th> Privilege </th>
										<th> Username </th>
									</tr>
								</thead>
								<tbody>
<?php

while ($rowsonlineClients = mysql_fetch_assoc($onlineClients))
{
?>
									<tr>
										<td> Client </td>
										<td> <a href="clientsummary.php?id=<?php echo $rowsonlineClients['clientid']; ?>"><?php echo htmlspecialchars($rowsonlineClients['username'], ENT_QUOTES); ?></a> </td>
									</tr>
<?php
}
unset($onlineClients);

while ($rowsonlineAdmins = mysql_fetch_assoc($onlineAdmins))
{
?>
									<tr>
										<td> Admin </td>
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
				<div class="span6">
					<div id="notes">
						<legend>Personal Notes</legend>
							<form method="post" action="process.php">
								<input type="hidden" name="task" value="personalnotes" />
								<input type="hidden" name="adminid" value="<?php echo $_SESSION['adminid']; ?>" />
								<div style="text-align: center;">
									<textarea name="notes" class="textarea span11"><?php echo htmlspecialchars($rows['notes'], ENT_QUOTES); ?></textarea>
								</div>
								<div style="text-align: center; margin-top: 18px;">
									<button type="submit" class="btn">Save</button>
								</div>
							</form>
					</div><!-- /notes -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="row-fluid">
				<div class="span4">
					<div id="game">
						<legend>Active Game Servers</legend>
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th> Server Name </th>
										<th> Net Status </th>
									</tr>
								</thead>
								<tbody>
<?php

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
	if (@$lgsl['b']['status'] == '1')
	{
?>
									<tr>
										<td> <a href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></a> </td>
										<td> <span class="label label-success">Online</span> </td>
									</tr>
<?php
	}
	else
	{
?>
									<tr>
										<td> <a href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></a> </td>
										<td> <span class="label label-important">Offline</span> </td>
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
						<legend>Boxes</legend>
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th> Box Name </th>
										<th> Load Average </th>
										<th> HDD Usage </th>
										<th colspan="2"> Bandwith Usage </th>
										<th> Uptime </th>
									</tr>
								</thead>
								<tbody>
<?php

/**
 * BOXES
 */

// Retrieve bandwidth details from the next to last cron
$boxids = explode(';', $boxData['boxids']);
$next2LastBwRx = explode(';', $boxData['bw_rx']);
$next2LastBwTx = explode(';', $boxData['bw_tx']);
unset($boxData);

while ($rowsBoxes = mysql_fetch_assoc($boxes))
{
	$cpu = explode(';', $rowsBoxes['cpu']);
	$hdd = explode(';', $rowsBoxes['hdd']);

	/**
	 * Bandwidth Process
	 */

	// Vars Init
	$bwRxAvg = 0;
	$bwTxAvg = 0;

	// We have to retrieve the box rank from data
	foreach($boxids as $key => $value)
	{
		if ($rowsBoxes['boxid'] == $value) // Box data are the values at the rank $key
		{
			if (array_key_exists($key, $next2LastBwRx) && array_key_exists($key, $next2LastBwTx)) // Is there bandwidth data ?
			{
				$bwRxAvg = round(( $rowsBoxes['bw_rx'] - $next2LastBwRx[$key] ) / ( 60 * 10 ), 2); // Average bandwidth usage for the 10 past minutes
				$bwTxAvg = round(( $rowsBoxes['bw_tx'] - $next2LastBwTx[$key] ) / ( 60 * 10 ), 2);
			}
		}
	}

	// Case where stats have been reset or the box rebooted
	if ( ($bwRxAvg < 0) || ($bwTxAvg < 0) )
	{
		$bwRxAvg = 0;
		$bwTxAvg = 0;
	}
?>
									<tr>
										<td> <a href="boxsummary.php?id=<?php echo $rowsBoxes['boxid']; ?>"><?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></a> </td>
										<td> <span class="badge badge-<?php if ($rowsBoxes['loadavg'] < $cpu[1]) { echo 'info'; } else if ($rowsBoxes['loadavg'] == $cpu[1]) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $rowsBoxes['loadavg']; ?></span> </td>
										<td> <span class="badge badge-<?php if ($hdd[3] < 65) { echo 'info'; } else if ($hdd[3] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $hdd[3].' %'; ?></span> </td>
										<td> RX:&nbsp;<?php echo bytesToSize($bwRxAvg); ?>/s </td>
										<td> TX:&nbsp;<?php echo bytesToSize($bwTxAvg); ?>/s </td>
										<td> <?php echo $rowsBoxes['uptime']; ?> </td>
									</tr>
<?php
	unset($cpu, $hdd, $bwRxAvg, $bwTxAvg);
}
unset($boxes, $boxids, $next2LastBwRx, $next2LastBwTx);

?>
								</tbody>
							</table>
							<div class="well">Last Update : <span class="label"><?php echo formatDate($cron['value']); ?></span><?php

if ($cron['value'] == 'Never')
{
	echo "\t\t\t<br />Setup the cron job to enable box monitoring!";
}
unset($cron);

?></div>
					</div><!-- /game -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
			<div class="row-fluid">
				<div class="span12">
					<div id="logs">
						<legend>Last 15 Actions</legend>
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label label-info"><?php echo mysql_num_rows($logs); ?> Record(s) Found</span> (<a href="utilitieslog.php">View All</a>)
							</div>
							<table id="logstable" class="zebra-striped">
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
					</div><!-- /logs -->
				</div><!-- /span -->
			</div><!-- /row-fluid -->
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