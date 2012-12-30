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



$title = 'Boxes';
$page = 'box';
$tab = 3;
$return = 'box.php';


require("../configuration.php");
require("./include.php");


$cron = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'lastcronrun' LIMIT 1" );
$boxes = mysql_query( "SELECT `boxid`, `name`, `ip`, `sshport`, `bw_rx`, `bw_tx`, `cpu`, `ram`, `loadavg`, `hdd` FROM `".DBPREFIX."box` ORDER BY `boxid`" );
$boxData = query_fetch_assoc( "SELECT `boxids`, `bw_rx`, `bw_tx` FROM `".DBPREFIX."boxData` ORDER BY `id` DESC LIMIT 1, 1" ); // Next to last cron data


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
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info"><?php echo mysql_num_rows($boxes); ?> Record(s) Found</span> (<a href="boxadd.php">Add New Box</a>)
				</div>
				<table id="boxes" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>IP Address</th>
							<th>Servers</th>
							<th>Network Status</th>
							<th colspan="2">Bandwidth Usage (<a href="#" id="bw" rel="tooltip" title="Shows Bandwidth Statistics. RX: receive, incoming data. TX: transmitting, outgoing data.">?</a>)</th>
							<th>CPU (<a href="#" id="cpu" rel="tooltip" title="Shows the percentage of CPU in use by the box (user mode).">?</a>)</th>
							<th>RAM (<a href="#" id="ram" rel="tooltip" title="Shows the percentage of RAM in use by the box.">?</a>)</th>
							<th>Load Average (<a href="#" id="loadavg" rel="tooltip" title="Represents the average system load during the last 15 minutes.">?</a>) [<a href="http://en.wikipedia.org/wiki/Load_%28computing%29" target="_blank">Wiki</a>]</th>
							<th>HDD (<a href="#" id="hdd" rel="tooltip" title="Shows the percentage of HDD usage.">?</a>)</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($boxes) == 0)
{
?>
						<tr>
							<td colspan="12"><div style="text-align: center;"><span class="label label-warning">No Boxes Found</span><br />No boxes found. <a href="boxadd.php">Click here</a> to add a new box.</div></td>
						</tr>
<?php
}
else
{
	// Retrieve bandwidth details from the next to last cron
	$boxids = explode(';', $boxData['boxids']);
	$next2LastBwRx = explode(';', $boxData['bw_rx']);
	$next2LastBwTx = explode(';', $boxData['bw_tx']);
	unset($boxData);
}

while ($rowsBoxes = mysql_fetch_assoc($boxes))
{
	$cpu = explode(';', $rowsBoxes['cpu']);
	$mem = explode(';', $rowsBoxes['ram']);
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
							<td><?php echo $rowsBoxes['boxid']; ?></td>
							<td><?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsBoxes['ip'], ENT_QUOTES); ?></td>
							<td><?php echo query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `boxid` = '".$rowsBoxes['boxid']."'" ); ?></td>
							<td><?php echo formatStatus(getStatus($rowsBoxes['ip'], $rowsBoxes['sshport'])); ?></td>
							<td> RX:&nbsp;<?php echo bytesToSize($bwRxAvg); ?>/s </td>
							<td> TX:&nbsp;<?php echo bytesToSize($bwTxAvg); ?>/s </td>
							<td><span class="badge badge-<?php if ($cpu[2] < 65) { echo 'info'; } else if ($cpu[2] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $cpu[2].' %'; ?></span></td>
							<td><span class="badge badge-<?php if ($mem[3] < 65) { echo 'info'; } else if ($mem[3] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $mem[3].' %'; ?></span></td>
							<td><span class="badge badge-<?php if ($rowsBoxes['loadavg'] < $cpu[1]) { echo 'info'; } else if ($rowsBoxes['loadavg'] == $cpu[1]) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $rowsBoxes['loadavg']; ?></span></td>
							<td><span class="badge badge-<?php if ($hdd[3] < 65) { echo 'info'; } else if ($hdd[3] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $hdd[3].' %'; ?></span></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="boxprofile.php?id=<?php echo $rowsBoxes['boxid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="boxsummary.php?id=<?php echo $rowsBoxes['boxid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
						</tr>
<?php
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($boxes) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#boxes").tablesorter({
						headers: {
							5: {
								sorter: false
							},
							6: {
								sorter: false
							},
							7: {
								sorter: false
							},
							8: {
								sorter: false
							},
							9: {
								sorter: false
							},
							10: {
								sorter: false
							},
							11: {
								sorter: false
							}
						},
						sortList: [[1,0]]
					});
					$('#bw').tooltip();
					$('#cpu').tooltip();
					$('#ram').tooltip();
					$('#loadavg').tooltip();
					$('#hdd').tooltip();
				});
				</script>
<?php
}
unset($boxes);

?>
			</div>

			<div class="well">Last Update : <span class="label"><?php echo formatDate($cron['value']); ?></span><?php
if ($cron['value'] == 'Never')
{
	echo "\t\t\t<br />Setup the cron job to enable box monitoring!";
}
?></div>

<?php


include("./bootstrap/footer.php");
?>