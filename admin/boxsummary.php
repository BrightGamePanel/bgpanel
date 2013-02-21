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



$page = 'boxsummary';
$tab = 3;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$boxid = $_GET['id'];
}
else
{
	exit('Error: BoxID error.');
}
###
$return = 'boxsummary.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");


$title = T_('Box Summary');


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
$boxData = query_fetch_assoc( "SELECT `boxids`, `bw_rx`, `bw_tx` FROM `".DBPREFIX."boxData` ORDER BY `id` DESC LIMIT 1, 1" ); // Next to last cron data
$cron = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'lastcronrun' LIMIT 1" );
$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` WHERE `boxid` = '".$boxid."' ORDER BY `logid` DESC LIMIT 5" );


$cpu = explode(';', $rows['cpu']);
$mem = explode(';', $rows['ram']);
$swap = explode(';', $rows['swap']);
$hdd = explode(';', $rows['hdd']);

//---------------------------------------------------------+

/**
 * Bandwidth Process
 */

// Retrieve bandwidth details from the next to last cron
$boxids = explode(';', $boxData['boxids']);
$next2LastBwRx = explode(';', $boxData['bw_rx']);
$next2LastBwTx = explode(';', $boxData['bw_tx']);
unset($boxData);

// Vars Init
$bwRxAvg = 0;
$bwTxAvg = 0;

// We have to retrieve the box rank from data
foreach($boxids as $key => $value)
{
	if ($boxid == $value) // Box data are the values at the rank $key
	{
		if (array_key_exists($key, $next2LastBwRx) && array_key_exists($key, $next2LastBwTx)) // Is there bandwidth data ?
		{
			$bwRxAvg = round(( $rows['bw_rx'] - $next2LastBwRx[$key] ) / ( 60 * 10 ), 2); // Average bandwidth usage for the 10 past minutes
			$bwTxAvg = round(( $rows['bw_tx'] - $next2LastBwTx[$key] ) / ( 60 * 10 ), 2);
		}
	}
}

// Case where stats have been reset or the box rebooted
if ( ($bwRxAvg < 0) || ($bwTxAvg < 0) )
{
	$bwRxAvg = 0;
	$bwTxAvg = 0;
}

unset($boxids, $next2LastBwRx, $next2LastBwTx);
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
				<li class="active"><a href="boxsummary.php?id=<?php echo $boxid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>"><?php echo T_('Charts'); ?></a></li>
				<li><a href="boxgamefile.php?id=<?php echo $boxid; ?>"><?php echo T_('Game File Repositories'); ?></a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Box Information'); ?></span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td><?php echo T_('Name'); ?></td>
								<td><?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('IP Address'); ?></td>
								<td><?php echo $rows['ip']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('OS Type'); ?></td>
								<td><span class="label">Linux</span></td>
							</tr>
						</table>
						<div style="text-align: center;">
							<button onclick="deleteBox();return false;" class="btn btn-danger"><?php echo T_('Delete Box'); ?></button>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Last 5 Actions'); ?></span>
						</div>
						<table class="table table-bordered">
<?php
if (mysql_num_rows($logs) == 0)
{
?>
							<tr>
								<td>
									<div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Logs Found'); ?></span></div>
								</td>
							</tr>
<?php
}

while ($rowsLogs = mysql_fetch_assoc($logs))
{
?>
							<tr>
								<td>
									<div style="text-align: center;"><?php echo formatDate($rowsLogs['timestamp']); ?> - <?php echo htmlspecialchars($rowsLogs['message'], ENT_QUOTES); ?></div>
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
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Box Monitoring'); ?></span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td><?php echo T_('Network Status'); ?></td>
								<td><?php echo formatStatus(getStatus($rows['ip'], $rows['sshport'])); ?>&nbsp;(<?php echo T_('Port'); ?>: <?php echo $rows['sshport']; ?>)</td>
							</tr>
							<tr>
								<td><?php echo T_('CPU Load'); ?> (<a href="#" id="cpu" rel="tooltip" title="<?php echo T_('Shows the percentage of CPU in use by the box (user mode).'); ?>">?</a>)</td>
								<td>
									<span class="badge badge-<?php if ($cpu[2] < 65) { echo 'info'; } else if ($cpu[2] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $cpu[2].' %'; ?></span>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('Bandwidth Usage'); ?> (<a href="#" id="bw" rel="tooltip" title="<?php echo T_('Shows Bandwidth Statistics. RX: receive, incoming data. TX: transmitting, outgoing data.'); ?>">?</a>)</td>
								<td>
									RX Total (<a href="#" id="bw2" rel="tooltip" title="Total incoming data since boot.">?</a>)&nbsp;:&nbsp;
									<span class="badge badge-info"><?php echo bytesToSize($rows['bw_rx']); ?></span>&nbsp;
									RX:&nbsp;<span class="badge"><?php echo bytesToSize($bwRxAvg); ?>/s</span><br />
									TX Total (<a href="#" id="bw3" rel="tooltip" title="Total outgoing data since boot.">?</a>)&nbsp;:&nbsp;
									<span class="badge badge-info"><?php echo bytesToSize($rows['bw_tx']); ?></span>&nbsp;
									TX:&nbsp;<span class="badge"><?php echo bytesToSize($bwTxAvg); ?>/s</span>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('Load Average'); ?> (<a href="#" id="loadavg" rel="tooltip" title="<?php echo T_('Represents the average system load during the last 15 minutes.'); ?>">?</a>) [<a href="http://en.wikipedia.org/wiki/Load_%28computing%29" target="_blank">Wiki</a>]</td>
								<td>
									<span class="badge badge-<?php if ($rows['loadavg'] < $cpu[1]) { echo 'info'; } else if ($rows['loadavg'] == $cpu[1]) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $rows['loadavg']; ?></span>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('RAM Usage'); ?></td>
								<td>
									<div class="progress progress-<?php if ($mem[3] < 65) { echo 'info'; } else if ($mem[3] < 85) { echo 'warning'; } else { echo 'danger'; } ?>">
										<div class="bar" style="width: <?php echo $mem[3]; ?>%;"></div>
									</div>
									<?php echo $mem[0]; ?> <?php echo T_('MB total'); ?>, <?php echo $mem[1]; ?> <?php echo T_('MB used'); ?>, <?php echo $mem[2]; ?> <?php echo T_('MB free'); ?>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('Hostname'); ?></td>
								<td><?php echo $rows['hostname']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('OS'); ?></td>
								<td><?php echo $rows['os']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Date'); ?></td>
								<td><?php echo $rows['date']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Kernel Version - Machine Architecture'); ?></td>
								<td><?php echo $rows['kernel']; echo ' - '; echo $rows['arch']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('CPU Info'); ?></td>
								<td><?php echo $cpu[0]; ?>, <?php echo $cpu[1]; ?> <?php echo T_('cores'); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Uptime'); ?></td>
								<td><?php echo $rows['uptime']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('SWAP Usage'); ?></td>
								<td>
									<div class="progress progress-<?php if ($swap[3] < 10) { echo 'info'; } else if ($swap[3] < 66) { echo 'warning'; } else { echo 'danger'; } ?>">
										<div class="bar" style="width: <?php echo $swap[3]; ?>%;"></div>
									</div>
									<?php echo $swap[0]; ?> <?php echo T_('MB total'); ?>, <?php echo $swap[1]; ?> <?php echo T_('MB used'); ?>, <?php echo $swap[2]; ?> <?php echo T_('MB free'); ?>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('HDD Usage'); ?></td>
								<td>
									<div class="progress progress-<?php if ($hdd[3] < 65) { echo 'info'; } else if ($hdd[3] < 85) { echo 'warning'; } else { echo 'danger'; } ?>">
										<div class="bar" style="width: <?php echo $hdd[3]; ?>%;"></div>
									</div>
									<?php echo $hdd[0]; ?> <?php echo T_('total'); ?>, <?php echo $hdd[1]; ?> <?php echo T_('used'); ?>, <?php echo $hdd[2]; ?> <?php echo T_('free'); ?>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('Last Update'); ?></td>
								<td><span class="label"><?php echo formatDate($cron['value']); ?></span></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="span6">
					<div class="well form-horizontal">
						<form method="post" action="boxprocess.php">
							<input type="hidden" name="task" value="boxnotes" />
							<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label label-info"><?php echo T_('Admin Notes'); ?></span>
							</div>
							<textarea name="notes" class="textarea span12"><?php echo htmlspecialchars($rows['notes'], ENT_QUOTES); ?></textarea>
							<div style="text-align: center; margin-top: 18px;">
								<button type="submit" class="btn"><?php echo T_('Save'); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<script language="javascript" type="text/javascript">
			function deleteBox()
			{
				if (confirm("<?php echo T_('Are you sure you want to delete box:'); ?> <?php echo htmlspecialchars(addslashes($rows['name']), ENT_QUOTES); ?> ?"))
				{
					window.location.href='boxprocess.php?task=boxdelete&id=<?php echo $boxid; ?>';
				}
			}
			<!-- -- -- -->
			$(document).ready(function() {
				$('#bw').tooltip();
				$('#bw2').tooltip();
				$('#bw3').tooltip();
				$('#cpu').tooltip();
				$('#loadavg').tooltip();
			});
			</script>
<?php


include("./bootstrap/footer.php");
?>