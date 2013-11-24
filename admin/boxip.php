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



$page = 'boxip';
$tab = 3;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: BoxID error.');
}

$boxid = $_GET['id'];
$return = 'boxip.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");


$title = T_('Box IP Adresses');

$boxid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `name`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
$ips = mysql_query( "SELECT * FROM `".DBPREFIX."boxIp` WHERE `boxid` = '".$boxid."' ORDER BY `ipid`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="boxsummary.php?id=<?php echo $boxid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li class="active"><a href="boxip.php?id=<?php echo $boxid; ?>"><?php echo T_('IP Addresses'); ?></a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>"><?php echo T_('Charts'); ?></a></li>
				<li><a href="boxgamefile.php?id=<?php echo $boxid; ?>"><?php echo T_('Game File Repositories'); ?></a></li>
				<li><a href="#" onclick="ajxp()"><?php echo T_('WebFTP'); ?></a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
			<div class="well">
				<form method="post" action="boxprocess.php">
					<input type="hidden" name="task" value="boxipedit" />
					<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />
					<div class="row">
						<div class="span8 offset2">
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label"><?php echo T_('IP Addresses'); ?></span>
							</div>
							<table class="table table-striped">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo T_('IP Address'); ?></th>
										<th><?php echo T_('Network Status'); ?></th>
									</tr>
								</thead>
								<tbody>
<?php

while ($rowsIps = mysql_fetch_assoc($ips))
{
?>
									<tr>
										<td><?php echo $rowsIps['ipid']; ?></td>
										<td><?php echo htmlspecialchars($rowsIps['ip'], ENT_QUOTES); ?></td>
										<td><?php echo formatStatus(getStatus($rowsIps['ip'], $rows['sshport'])); ?>&nbsp;(<?php echo T_('Port'); ?>: <?php echo $rows['sshport']; ?>)</td>
										<td>
											<label class="checkbox">
												<input type="checkbox" name="removeid<?php echo $rowsIps['ipid']; ?>"><i class="icon-remove-sign <?php echo formatIcon(); ?>"></i>
											</label>
										</td>
										<td></td>
									</tr>
<?php
}
unset($ips);

?>
									<tr>
										<td>#</td>
										<td>
											<input type="text" name="newip" class="span2" value="">
										</td>
										<td></td>
										<td>
											<label class="checkbox">
												<input type="checkbox" name="verify" checked="checked">&nbsp;<?php echo T_('Verify Login &amp; Password'); ?>
											</label>
										</td>
										<td><button type="submit" class="btn btn-primary btn-small" href=""><i class="icon-plus-sign icon-white"></i>&nbsp;<?php echo T_('Add'); ?></button></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary"><?php echo T_('Save Changes'); ?></button>
						<button type="reset" class="btn"><?php echo T_('Cancel Changes'); ?></button>
					</div>
				</form>
			</div>
			<script>
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>