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



$title = 'Add New Box';
$page = 'boxadd';
$tab = 3;
$return = 'boxadd.php';


require("../configuration.php");
require("./include.php");


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
				<form method="post" action="boxprocess.php">
					<input type="hidden" name="task" value="boxadd" />
					<label>Server Name</label>
						<input type="text" name="name" class="span4" value="<?php
if (isset($_SESSION['name']))
{
	echo htmlspecialchars($_SESSION['name'], ENT_QUOTES);
	unset($_SESSION['name']);
}
?>">
					<label>IP Address</label>
						<input type="text" name="ip" class="span3" value="<?php
if (isset($_SESSION['ip']))
{
	echo htmlspecialchars($_SESSION['ip'], ENT_QUOTES);
	unset($_SESSION['ip']);
}
?>">
					<label>SSH Login</label>
						<input type="text" name="login" class="span3" value="<?php
if (isset($_SESSION['login']))
{
	echo htmlspecialchars($_SESSION['login'], ENT_QUOTES);
	unset($_SESSION['login']);
}
?>">
					<label>SSH Password</label>
						<input type="password" name="password" class="span3">
					<label>Confirm Password</label>
						<input type="password" name="password2" class="span3">
					<label>SSH Port</label>
						<input type="text" name="sshport" class="span1" placeholder="22" value="<?php
if (isset($_SESSION['sshport']))
{
	echo htmlspecialchars($_SESSION['sshport'], ENT_QUOTES);
	unset($_SESSION['sshport']);
}
?>">
					<label>OS Type</label>
						<input type="text" class="input-xlarge disabled" disabled="" placeholder="Linux">
					<label>Admin Notes</label>
						<textarea name="notes" class="textarea span10"><?php
if (isset($_SESSION['notes']))
{
	echo htmlspecialchars($_SESSION['notes'], ENT_QUOTES);
	unset($_SESSION['notes']);
}
?></textarea>
					<label class="checkbox">
						<input type="checkbox" name="verify" checked="checked">&nbsp;Verify Login &amp; Password
					</label>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Add New Box</button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="box.php">Back to Boxes</a>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php


include("./bootstrap/footer.php");
?>