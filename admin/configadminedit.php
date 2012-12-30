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



$title = 'Edit Administrator';
$page = 'configadminedit';
$tab = 5;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$adminid = $_GET['id'];
}
else
{
	exit('Error: AdminID error.');
}
###
$return = 'configadminedit.php?id='.urlencode($adminid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `username` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."'" ) == 0)
{
	exit('Error: AdminID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."' LIMIT 1" );


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
					<input type="hidden" name="task" value="configadminedit" />
					<input type="hidden" name="adminid" value="<?php echo $adminid; ?>" />
					<label>Username</label>
						<input type="text" name="username" class="span4" value="<?php echo htmlspecialchars($rows['username'], ENT_QUOTES); ?>">
					<label>Password</label>
						<input type="password" name="password" class="span3">
						<span class="help-inline">Leave blank for no change</span>
					<label>Confirm Password</label>
						<input type="password" name="password2" class="span3">
					<label>Status</label>
						<div class="btn-group" data-toggle="buttons-radio" style="margin-bottom: 5px;">
							<a class="btn btn-primary <?php
if ($rows['status']	== 'Active')
{
	echo 'active';
}
?>" onclick="switchRadio();return false;">Active</a>
							<a class="btn btn-primary <?php
if ($rows['status']	== 'Suspended')
{
	echo 'active';
}
?>" onclick="switchRadio();return false;">Suspended</a>
						</div>
						<div class="collapse">
							<label class="radio">
								<input id="status0" type="radio" value="Active" name="status" <?php
if ($rows['status']	== 'Active')
{
	echo "checked=\"\"";
}
?>>
							</label>
							<label class="radio">
								<input id="status1" type="radio" value="Suspended" name="status" <?php
if ($rows['status']	== 'Suspended')
{
	echo "checked=\"\"";
}
?>>
							</label>
						</div>
					<label>First Name</label>
						<input type="text" name="firstname" class="span4" value="<?php echo htmlspecialchars($rows['firstname'], ENT_QUOTES); ?>">
					<label>Last Name</label>
						<input type="text" name="lastname" class="span4" value="<?php echo htmlspecialchars($rows['lastname'], ENT_QUOTES); ?>">
					<label>Email</label>
						<input type="text" name="email" class="span3" value="<?php echo htmlspecialchars($rows['email'], ENT_QUOTES); ?>">
					<label>Access Level</label>
						<select name="access">
							<option value="Super" <?php
if ($rows['access'] == 'Super')
{
	echo "selected=\"selected\"";
}
?>>Super Administrator</option>
							<option value="Full" <?php
if ($rows['access'] == 'Full')
{
	echo "selected=\"selected\"";
}
?>>Full Administrator</option>
							<option value="Limited" <?php
if ($rows['access'] == 'Limited')
{
	echo "selected=\"selected\"";
}
?>>Limited Administrator</option>
						</select>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Save Changes</button>
						<button type="reset" class="btn">Cancel Changes</button>
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
			<script language="javascript" type="text/javascript">
			function switchRadio()
			{
				var statusActive = document.getElementById('status0');
				var statusSuspended = document.getElementById('status1');
				<!-- -->
				var active = statusActive.getAttribute('checked');
				var suspended = statusSuspended.getAttribute('checked');
				<!-- -->
				if (active == '') {
					statusActive.removeAttribute('checked');
					statusSuspended.setAttribute('checked', '');
				} else if (suspended == '') {
					statusActive.setAttribute('checked', '');
					statusSuspended.removeAttribute('checked');
				}
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>