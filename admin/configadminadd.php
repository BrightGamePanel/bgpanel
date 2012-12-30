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



$title = 'Add New Administrator';
$page = 'configadminadd';
$tab = 5;
$return = 'configadminadd.php';


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
				<form method="post" action="configadminprocess.php">
					<input type="hidden" name="task" value="configadminadd" />
					<label>Username</label>
						<input type="text" name="username" class="span4" value="<?php
if (isset($_SESSION['username']))
{
	echo htmlspecialchars($_SESSION['username'], ENT_QUOTES);
	unset($_SESSION['username']);
}
?>">
					<label>Password</label>
						<input type="password" name="password" class="span3" placeholder="">
					<label>Confirm Password</label>
						<input type="password" name="password2" class="span3" placeholder="">
					<label>First Name</label>
						<input type="text" name="firstname" class="span4" value="<?php
if (isset($_SESSION['firstname']))
{
	echo htmlspecialchars($_SESSION['firstname'], ENT_QUOTES);
	unset($_SESSION['firstname']);
}
?>">
					<label>Last Name</label>
						<input type="text" name="lastname" class="span4" value="<?php
if (isset($_SESSION['lastname']))
{
	echo htmlspecialchars($_SESSION['lastname'], ENT_QUOTES);
	unset($_SESSION['lastname']);
}
?>">
						<span class="help-inline">Optional</span>
					<label>Email</label>
						<input type="text" name="email" class="span3" value="<?php
if (isset($_SESSION['email']))
{
	echo htmlspecialchars($_SESSION['email'], ENT_QUOTES);
	unset($_SESSION['email']);
}
?>">
					<label>Access Level</label>
						<select name="access">
							<option value="Super" <?php
if (!empty($_SESSION['access']) && $_SESSION['access'] == 'Super')
{
	echo " selected=\"selected\"";
	unset($_SESSION['access']);
}
?>>Super Administrator</option>
							<option value="Full" <?php
if (!empty($_SESSION['access']) && $_SESSION['access'] == 'Full')
{
	echo " selected=\"selected\"";
	unset($_SESSION['access']);
}
?>>Full Administrator</option>
							<option value="Limited" <?php
if (!empty($_SESSION['access']) && $_SESSION['access'] == 'Limited')
{
	echo " selected=\"selected\"";
	unset($_SESSION['access']);
}
?>>Limited Administrator</option>
						</select>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Add New Administrator</button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="configadmin.php">Back to Administrators</a>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php


include("./bootstrap/footer.php");
?>