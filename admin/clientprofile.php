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



$title = 'Client Profile';
$page = 'clientprofile';
$tab = 1;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$clientid = $_GET['id'];
}
else
{
	exit('Error: ClientID error.');
}
###
$return = 'clientprofile.php?id='.urlencode($clientid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) == 0)
{
	exit('Error: ClientID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."' LIMIT 1" );


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
				<li><a href="clientsummary.php?id=<?php echo $clientid; ?>">Summary</a></li>
				<li class="active"><a href="clientprofile.php?id=<?php echo $clientid; ?>">Profile</a></li>
				<li><a href="clientserver.php?id=<?php echo $clientid; ?>">Servers</a></li>
				<li><a href="clientlog.php?id=<?php echo $clientid; ?>">Activity Logs</a></li>
			</ul>
			<div class="well">
				<form method="post" action="clientprocess.php">
					<input type="hidden" name="task" value="clientprofile" />
					<input type="hidden" name="clientid" value="<?php echo $clientid; ?>" />
					<label>Username</label>
						<input type="text" name="username" class="span4" value="<?php echo htmlspecialchars($rows['username'], ENT_QUOTES); ?>">
					<label>Password</label>
						<input type="password" name="password" class="span3">
						<span class="help-inline">Leave blank for no change</span>
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
					<label class="checkbox">
						<input type="checkbox" name="sendemail" checked="checked">&nbsp;Resend New Client Account Email
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