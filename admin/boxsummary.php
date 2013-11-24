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



$page = 'boxsummary';
$tab = 3;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: BoxID error.');
}

$boxid = $_GET['id'];
$return = 'boxsummary.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");


$title = T_('Box Summary');

$boxid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
$ips = mysql_query( "SELECT * FROM `".DBPREFIX."boxIp` WHERE `boxid` = '".$boxid."' ORDER BY `ipid`" );
$cache = unserialize(gzuncompress($rows['cache']));
$cron = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'lastcronrun' LIMIT 1" );
$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` WHERE `boxid` = '".$boxid."' ORDER BY `logid` DESC LIMIT 5" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li class="active"><a href="boxsummary.php?id=<?php echo $boxid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="boxip.php?id=<?php echo $boxid; ?>"><?php echo T_('IP Addresses'); ?></a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>"><?php echo T_('Charts'); ?></a></li>
				<li><a href="boxgamefile.php?id=<?php echo $boxid; ?>"><?php echo T_('Game File Repositories'); ?></a></li>
				<li><a href="#" onclick="ajxp()"><?php echo T_('WebFTP'); ?></a></li>
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
							<span class="label label-info"><?php echo T_('IP Addresses'); ?></span>
						</div>
						<table class="table table-bordered">
<?php

while ($rowsIps = mysql_fetch_assoc($ips))
{
?>
							<tr>
								<td>
									<div style="text-align: center;"><?php echo htmlspecialchars($rowsIps['ip'], ENT_QUOTES); ?></div>
								</td>
							</tr>
<?php
}
unset($ips);

?>
						</table>
						<div style="text-align: center;">
							<a href="boxip.php?id=<?php echo $boxid; ?>" class="btn btn-primary"><?php echo T_('Manage IPs'); ?></a>
						</div>
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
									<span class="badge badge-<?php

									if ($cache["{$rows['boxid']}"]['cpu']['usage'] < 65) {
										echo 'info';
									} else if ($cache["{$rows['boxid']}"]['cpu']['usage'] < 85) {
										echo 'warning';
									} else { echo 'important'; }

									?>"><?php echo $cache["{$rows['boxid']}"]['cpu']['usage']; ?>&nbsp;%</span>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('Bandwidth Usage'); ?> (<a href="#" id="bw" rel="tooltip" title="<?php echo T_('Shows Bandwidth Statistics. RX: receive, incoming data. TX: transmitting, outgoing data.'); ?>">?</a>)</td>
								<td>
									RX Total (<a href="#" id="bw2" rel="tooltip" title="Total incoming data since boot.">?</a>)&nbsp;:&nbsp;
									<span class="badge badge-info"><?php echo bytesToSize($cache["{$rows['boxid']}"]['bandwidth']['rx_total']); ?></span>&nbsp;
									RX:&nbsp;<span class="badge"><?php echo bytesToSize($cache["{$rows['boxid']}"]['bandwidth']['rx_usage']); ?>/s</span><br />
									TX Total (<a href="#" id="bw3" rel="tooltip" title="Total outgoing data since boot.">?</a>)&nbsp;:&nbsp;
									<span class="badge badge-info"><?php echo bytesToSize($cache["{$rows['boxid']}"]['bandwidth']['tx_total']); ?></span>&nbsp;
									TX:&nbsp;<span class="badge"><?php echo bytesToSize($cache["{$rows['boxid']}"]['bandwidth']['tx_usage']); ?>/s</span>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('Load Average'); ?> (<a href="#" id="loadavg" rel="tooltip" title="<?php echo T_('Represents the average system load during the last 15 minutes.'); ?>">?</a>) [<a href="http://en.wikipedia.org/wiki/Load_%28computing%29" target="_blank">Wiki</a>]</td>
								<td>
									<span class="badge badge-<?php

									if (substr($cache["{$rows['boxid']}"]['loadavg']['loadavg'], 0, -3) < $cache["{$rows['boxid']}"]['cpu']['cores']) {
										echo 'info';
									} else if (substr($cache["{$rows['boxid']}"]['loadavg']['loadavg'], 0, -3) == $cache["{$rows['boxid']}"]['cpu']['cores']) {
										echo 'warning';
									} else { echo 'important'; }

									?>"><?php echo $cache["{$rows['boxid']}"]['loadavg']['loadavg']; ?></span>
								</td>
							</tr>
							<tr>
								<td><?php echo T_('RAM Usage'); ?></td>
								<td>
									<div class="progress progress-<?php

									if ($cache["{$rows['boxid']}"]['ram']['usage'] < 65) {
										echo 'info';
									} else if ($cache["{$rows['boxid']}"]['ram']['usage'] < 85) {
										echo 'warning';
									} else { echo 'danger'; }

									?>">
										<div class="bar" style="width: <?php echo $cache["{$rows['boxid']}"]['ram']['usage']; ?>%;"></div>
									</div>
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['ram']['total']); ?> <?php echo T_('total'); ?>,&nbsp;
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['ram']['used']); ?> <?php echo T_('used'); ?>,&nbsp;
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['ram']['free']); ?> <?php echo T_('free'); ?>

								</td>
							</tr>
							<tr>
								<td><?php echo T_('Hostname'); ?></td>
								<td><?php echo $cache["{$rows['boxid']}"]['hostname']['hostname']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('OS'); ?></td>
								<td><?php echo $cache["{$rows['boxid']}"]['os']['os']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Date'); ?></td>
								<td><?php echo $cache["{$rows['boxid']}"]['date']['date']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Kernel Version - Machine Architecture'); ?></td>
								<td><?php echo $cache["{$rows['boxid']}"]['kernel']['kernel']; echo ' - '; echo $cache["{$rows['boxid']}"]['arch']['arch']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('CPU Info'); ?></td>
								<td><?php echo $cache["{$rows['boxid']}"]['cpu']['proc']; ?>, <?php echo $cache["{$rows['boxid']}"]['cpu']['cores']; ?> <?php echo T_('cores'); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Uptime'); ?></td>
								<td><?php echo $cache["{$rows['boxid']}"]['uptime']['uptime']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('SWAP Usage'); ?></td>
								<td>
									<div class="progress progress-<?php

									if ($cache["{$rows['boxid']}"]['swap']['usage'] < 65) {
										echo 'info';
									} else if ($cache["{$rows['boxid']}"]['swap']['usage'] < 85) {
										echo 'warning';
									} else { echo 'danger'; }

									?>">
										<div class="bar" style="width: <?php echo $cache["{$rows['boxid']}"]['swap']['usage']; ?>%;"></div>
									</div>
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['swap']['total']); ?> <?php echo T_('total'); ?>,&nbsp;
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['swap']['used']); ?> <?php echo T_('used'); ?>,&nbsp;
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['swap']['free']); ?> <?php echo T_('free'); ?>

								</td>
							</tr>
							<tr>
								<td><?php echo T_('HDD Usage'); ?></td>
								<td>
									<div class="progress progress-<?php

									if ($cache["{$rows['boxid']}"]['hdd']['usage'] < 65) {
										echo 'info';
									} else if ($cache["{$rows['boxid']}"]['hdd']['usage'] < 85) {
										echo 'warning';
									} else { echo 'danger'; }

									?>">
										<div class="bar" style="width: <?php echo $cache["{$rows['boxid']}"]['hdd']['usage']; ?>%;"></div>
									</div>
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['hdd']['total']); ?> <?php echo T_('total'); ?>,&nbsp;
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['hdd']['used']); ?> <?php echo T_('used'); ?>,&nbsp;
									<?php echo bytesToSize($cache["{$rows['boxid']}"]['hdd']['free']); ?> <?php echo T_('free'); ?>

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
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
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