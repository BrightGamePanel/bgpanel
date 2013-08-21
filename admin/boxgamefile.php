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
 * @version		(Release 0) DEVELOPER BETA 8
 * @link		http://www.bgpanel.net/
 */



$page = 'boxgamefile';
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
$return = 'boxgamefile.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");
require_once("../includes/func.ssh2.inc.php");
require_once("../libs/phpseclib/Crypt/AES.php");
require_once("../libs/gameinstaller/gameinstaller.php");


$title = T_('Box Game File Repositories');


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
$games = mysql_query( "SELECT * FROM `".DBPREFIX."game` ORDER BY `game`" );

$aes = new Crypt_AES();
$aes->setKeyLength(256);
$aes->setKey(CRYPT_KEY);

// Get SSH2 Object OR ERROR String
$ssh = newNetSSH2($rows['ip'], $rows['sshport'], $rows['login'], $aes->decrypt($rows['password']));
if (!is_object($ssh))
{
	$_SESSION['msg1'] = T_('Connection Error!');
	$_SESSION['msg2'] = $ssh;
	$_SESSION['msg-type'] = 'error';
}

$gameInstaller = new GameInstaller( $ssh );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="boxsummary.php?id=<?php echo $boxid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="boxip.php?id=<?php echo $boxid; ?>"><?php echo T_('IP Addresses'); ?></a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>"><?php echo T_('Charts'); ?></a></li>
				<li class="active"><a href="boxgamefile.php?id=<?php echo $boxid; ?>"><?php echo T_('Game File Repositories'); ?></a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
			<div class="well">
				<table id="gamefiles" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('Game'); ?></th>
							<th><?php echo T_('Cache Directory'); ?></th>
							<th><?php echo T_('Disk Usage'); ?></th>
							<th><?php echo T_('Last Modification'); ?></th>
							<th><?php echo T_('Status'); ?></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

while ($rowsGames = mysql_fetch_assoc($games))
{
	$repoCacheInfo =	$gameInstaller->getCacheInfo( $rowsGames['cachedir'] );
	$gameExists =		$gameInstaller->gameExists( $rowsGames['game'] );

?>
						<tr>
							<td><?php echo htmlspecialchars($rowsGames['game'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsGames['cachedir'], ENT_QUOTES); ?></td>
							<td><?php if ($repoCacheInfo != FALSE) { echo htmlspecialchars($repoCacheInfo['size'], ENT_QUOTES); } else { echo T_('None'); } ?></td>
							<td><?php if ($repoCacheInfo != FALSE) { echo @date('l | F j, Y | H:i', $repoCacheInfo['mtime']); } else { echo T_('Never'); } ?></td>
							<td><?php

	if ($gameExists == FALSE) {
		echo "<span class=\"label\">".T_('Game Not Supported')."</span>";
	}
	else if ($repoCacheInfo == FALSE) {
		echo "<span class=\"label label-warning\">".T_('No Cache')."</span>";
	}
	else if ($repoCacheInfo['status'] == 'Ready') {
		echo "<span class=\"label label-success\">Ready</span>";
	}
	else if ($repoCacheInfo['status'] == 'Aborted') {
		echo "<span class=\"label label-important\">Aborted</span>";
	}
	else {
		echo "<span class=\"label label-info\">".htmlspecialchars($repoCacheInfo['status'], ENT_QUOTES)."</span>";
	}

?></td>
							<td>
								<!-- Actions -->
								<div style="text-align: center;">
<?php

	if ($gameExists)
	{
		if ( ($repoCacheInfo == FALSE) || ($repoCacheInfo['status'] == 'Aborted') ) {
		// No repo OR repo not ready
?>
									<a class="btn btn-small" href="#" onclick="doRepoAction('<?php echo $boxid; ?>', '<?php echo $rowsGames['gameid']; ?>', 'makeRepo', '<?php echo T_('create a new cache repository for'); ?>', '<?php echo htmlspecialchars($rowsGames['game'], ENT_QUOTES); ?>')">
										<i class="icon-download-alt <?php echo formatIcon(); ?>"></i>
									</a>
<?php
		}

		if ( ($repoCacheInfo != FALSE) && ($repoCacheInfo['status'] != 'Aborted') && ($repoCacheInfo['status'] != 'Ready') ) {
		// Operation in progress
?>
									<a class="btn btn-small" href="#" onclick="doRepoAction('<?php echo $boxid; ?>', '<?php echo $rowsGames['gameid']; ?>', 'abortOperation', '<?php echo T_('abort current operation for repository'); ?>', '<?php echo htmlspecialchars($rowsGames['game'], ENT_QUOTES); ?>')">
										<i class="icon-stop <?php echo formatIcon(); ?>"></i>
									</a>
<?php
		}

		if ( $repoCacheInfo['status'] == 'Ready') {
		// Cache Ready
?>
									<a class="btn btn-small" href="#" onclick="doRepoAction('<?php echo $boxid; ?>', '<?php echo $rowsGames['gameid']; ?>', 'makeRepo', '<?php echo T_('refresh repository contents for'); ?>', '<?php echo htmlspecialchars($rowsGames['game'], ENT_QUOTES); ?>')">
										<i class="icon-repeat <?php echo formatIcon(); ?>"></i>
									</a>
<?php
		}

	}

?>
								</div>
							</td>
							<td>
								<!-- Drop Action -->
								<div style="text-align: center;">
<?php

	if ($gameExists)
	{
		if ( ($repoCacheInfo != FALSE) && ( ($repoCacheInfo['status'] == 'Aborted') || ($repoCacheInfo['status'] == 'Ready') ) ) {
		// Repo exists AND no operation in progress
?>
									<a class="btn btn-small" href="#" onclick="doRepoAction('<?php echo $boxid; ?>', '<?php echo $rowsGames['gameid']; ?>', 'deleteRepo', '<?php echo T_('remove cache repository for'); ?>', '<?php echo htmlspecialchars($rowsGames['game'], ENT_QUOTES); ?>')">
										<i class="icon-trash <?php echo formatIcon(); ?>"></i>
									</a>
<?php
		}

	}

?>
								</div>
							</td>
						</tr>
<?php
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($games) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#gamefiles").tablesorter({
						headers: {
							5: {
								sorter: false
							},
							6: {
								sorter: false
							}
						},
						sortList: [[0,0]]
					});
				});
				<!-- -->
				function doRepoAction(boxid, gameid, task, action, game)
				{
					if (confirm('<?php echo T_('Are you sure you want to'); ?> '+action+' '+game+' ?'))
					{
						window.location='boxprocess.php?boxid='+boxid+'&gameid='+gameid+'&task='+task;
					}
				}
				</script>
<?php
}
unset($games);

?>
			</div>
<?php


include("./bootstrap/footer.php");
?>