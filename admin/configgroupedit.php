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



$title = 'Edit Group';
$page = 'configgroupedit';
$tab = 5;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$groupid = $_GET['id'];
}
else
{
	exit('Error: GroupID error.');
}
###
$return = 'configgroupedit.php?id='.urlencode($groupid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
{
	exit('Error: GroupID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `name`, `description` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."' LIMIT 1" );

$clients = getGroupClients($groupid);

if ($clients == FALSE)
{
	$error = 'This group doesn\'t have clients.';
}


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
				<form method="post" action="configgroupprocess.php">
					<input type="hidden" name="task" value="configgroupedit" />
					<input type="hidden" name="groupid" value="<?php echo $groupid; ?>" />
					<label>Group Name</label>
						<input type="text" name="name" class="span4" value="<?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?>">
					<label>Group Description</label>
						<textarea name="notes" class="textarea span10"><?php echo htmlspecialchars($rows['description'], ENT_QUOTES); ?></textarea>
					<div class="row">
						<div class="span5">
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label">Group Configuration</span>
							</div>
							<table class="table table-striped">
								<thead>
									<tr>
										<th>#</th>
										<th>First Name</th>
										<th>Last Name</th>
										<th>Username</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
<?php

if (!isset($error))
{
	foreach($clients as $key => $value)
	{
		$client = query_fetch_assoc( "SELECT `firstname`, `lastname`, `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$value."'" );
?>
									<tr>
										<td><?php echo ($key + 1); ?></td>
										<td><?php echo htmlspecialchars($client['firstname'], ENT_QUOTES); ?></td>
										<td><?php echo htmlspecialchars($client['lastname'], ENT_QUOTES); ?></td>
										<td><?php echo htmlspecialchars($client['username'], ENT_QUOTES); ?></td>
										<td>
											<label class="checkbox">
												<input type="checkbox" name="removeid<?php echo $key; ?>"><i class="icon-remove-sign <?php echo formatIcon(); ?>"></i>
											</label>
										</td>
									</tr>
<?php
	}
	unset($clients);
}
else
{
?>
									<tr>
										<td colspan="5"><div style="text-align: center;"><span class="label label-warning"><?php echo $error; ?></span></div></td>
									</tr>
<?php
}

?>
									<tr>
										<td>#</td>
										<td>~</td>
										<td>~</td>
										<td>
											<select class="span3" name="newClient">
												<option>-Select-</option>
<?php

$clients = mysql_query( "SELECT `clientid`, `username` FROM `".DBPREFIX."client` WHERE `status` = 'Active'" );

while ($rowsClients = mysql_fetch_assoc($clients))
{
	if (!checkClientGroup($groupid, $rowsClients['clientid']))
	{
?>
												<option value="<?php echo htmlspecialchars($rowsClients['username'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($rowsClients['username'], ENT_QUOTES); ?></option>
<?php
	}
}
unset($clients);

?>
											</select>
										</td>
										<td><button type="submit" class="btn btn-primary btn-small" href=""><i class="icon-plus-sign icon-white"></i>&nbsp;Add</button></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Save Changes</button>
						<button type="reset" class="btn">Cancel Changes</button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="configgroup.php">Back to Groups</a>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php


include("./bootstrap/footer.php");
?>