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



$title = 'Box Game File Repositories';
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


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );


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
				<li><a href="boxsummary.php?id=<?php echo $boxid; ?>">Summary</a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>">Profile</a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>">Servers</a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>">Charts</a></li>
				<li class="active"><a href="boxgamefile.php?id=<?php echo $boxid; ?>">Game File Repositories</a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>">Activity Logs</a></li>
			</ul>
			<div class="alert alert-info">
			Work in Progress...
			</div>
			<div class="well">
			</div>
<?php


include("./bootstrap/footer.php");
?>