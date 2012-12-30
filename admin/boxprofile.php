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



$title = 'Box Profile';
$page = 'boxprofile';
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
$return = 'boxprofile.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );


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
				<li class="active"><a href="boxprofile.php?id=<?php echo $boxid; ?>">Profile</a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>">Servers</a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>">Charts</a></li>
				<li><a href="boxgamefile.php?id=<?php echo $boxid; ?>">Game File Repositories</a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>">Activity Logs</a></li>
			</ul>
			<div class="well">
				<form method="post" action="boxprocess.php">
					<input type="hidden" name="task" value="boxprofile" />
					<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />
					<label>Server Name</label>
						<input type="text" name="name" class="span4" value="<?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?>">
					<label>IP Address</label>
						<input type="text" name="ip" class="span3" value="<?php echo htmlspecialchars($rows['ip'], ENT_QUOTES); ?>">
					<label>SSH Login</label>
						<input type="text" name="login" class="span3" value="<?php echo htmlspecialchars($rows['login'], ENT_QUOTES); ?>">
					<label>SSH Password</label>
						<input type="password" name="password" class="span3">
						<span class="help-inline">Leave blank for no change</span>
					<label>SSH Port</label>
						<input type="text" name="sshport" class="span1" value="<?php echo htmlspecialchars($rows['sshport'], ENT_QUOTES); ?>">
					<label>OS Type</label>
						<input type="text" class="input-xlarge disabled" disabled="" placeholder="Linux">
					<label>Admin Notes</label>
						<textarea name="notes" class="textarea span10"><?php echo htmlspecialchars($rows['notes'], ENT_QUOTES); ?></textarea>
					<label class="checkbox">
						<input type="checkbox" name="verify" checked="checked">&nbsp;Verify Login &amp; Password
					</label>
					<div style="text-align: center;">
						<ul class="pager">
							<li>
								<button type="submit" class="btn btn-primary">Save Changes</button>
							</li>
							<li>
								<button type="reset" class="btn">Cancel Changes</button>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php


include("./bootstrap/footer.php");
?>