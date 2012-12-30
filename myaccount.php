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



$title = 'My Account';
$page = 'myaccount';
$tab = 9;
$isSummary = TRUE;
$return = 'myaccount.php';


require("configuration.php");
include("include.php");


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."client` WHERE `clientid` = '".$_SESSION['clientid']."' LIMIT 1" );


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
				<li class="active"><a href="#">Profile</a></li>
			</ul>
			<div class="well">
				<form method="post" action="process.php">
					<input type="hidden" name="task" value="myaccount" />
					<input type="hidden" name="clientid" value="<?php echo $_SESSION['clientid']; ?>" />
					<label>First Name</label>
						<input type="text" name="firstname" class="span4" value="<?php echo htmlspecialchars($rows['firstname'], ENT_QUOTES); ?>">
					<label>Last Name</label>
						<input type="text" name="lastname" class="span4" value="<?php echo htmlspecialchars($rows['lastname'], ENT_QUOTES); ?>">
					<label>Email</label>
						<input type="text" name="email" class="span3" value="<?php echo htmlspecialchars($rows['email'], ENT_QUOTES); ?>">
					<label>Username</label>
						<input type="text" name="username" class="span4" value="<?php echo htmlspecialchars($rows['username'], ENT_QUOTES); ?>">
					<label>Password</label>
						<input type="password" name="password" class="span3" placeholder="">
						<span class="help-inline">Leave blank for no change</span>
					<label>Password</label>
						<input type="password" name="password2" class="span3" placeholder="">
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