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



$title = 'Script Settings';
$page = 'scriptprofile';
$tab = 5;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$scriptid = $_GET['id'];
}
else
{
	exit('Error:ScriptID error.');
}
###
$return = 'scriptprofile.php?id='.urlencode($scriptid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
{
	exit('Error: ScriptID is invalid.');
}

$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
$categories = mysql_query( "SELECT * FROM `".DBPREFIX."scriptCat` ORDER BY `id`" );
$boxes = mysql_query( "SELECT `boxid`, `name`, `ip` FROM `".DBPREFIX."box` ORDER BY `boxid`" );
$groups = mysql_query( "SELECT `groupid`, `name` FROM `".DBPREFIX."group` ORDER BY `groupid`" );


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
				<li><a href="scriptsummary.php?id=<?php echo $scriptid; ?>">Summary</a></li>
				<li class="active"><a href="scriptprofile.php?id=<?php echo $scriptid; ?>">Profile</a></li>
<?php

if ($rows['status'] == 'Active')
{
	echo "\t\t\t\t<li><a href=\"scriptconsole.php?id=".$scriptid."\">Console</a></li>";
}

?>
			</ul>
			<div class="well">
				<form method="post" action="scriptprocess.php">
					<input type="hidden" name="task" value="scriptprofile" />
					<input type="hidden" name="scriptid" value="<?php echo $scriptid; ?>" />
					<label>Script Name</label>
						<input type="text" name="name" class="span5" value="<?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?>">
<?php

//---------------------------------------------------------+

if ($rows['status'] != 'Pending')
{
?>
					<label>Status</label>
						<div class="btn-group" data-toggle="buttons-radio" style="margin-bottom: 5px;">
							<a class="btn btn-primary <?php
	if ($rows['status']	== 'Active')
	{
		echo 'active';
	}
?>" onclick="switchRadio();return false;">Active</a>
							<a class="btn btn-primary <?php
	if ($rows['status']	== 'Inactive')
	{
		echo 'active';
	}
?>" onclick="switchRadio();return false;">Inactive</a>
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
								<input id="status1" type="radio" value="Inactive" name="status" <?php
	if ($rows['status']	== 'Inactive')
	{
		echo "checked=\"\"";
	}
?>>
							</label>
						</div>
<?php
}
else
{
?>
					<input type="hidden" name="status" value="Pending" />
					<div class="alert alert-info">
						<h4 class="alert-heading">Script not validated !</h4>
						<p>
							You must validate the script before changing its status.
						</p>
						<p>
							<a class="btn btn-primary" href="scriptprocess.php?task=scriptvalidation&scriptid=<?php echo $scriptid; ?>">Validate</a>
						</p>
					</div>
<?php
}

//---------------------------------------------------------+

?>
					<label>Description</label>
						<textarea name="description" class="textarea span5"><?php echo htmlspecialchars($rows['description'], ENT_QUOTES); ?></textarea>
					<label>Owner Group</label>
						<select name="groupID">
							<option value="none">None</option>
<?php
//---------------------------------------------------------+

while ($rowsGroups = mysql_fetch_assoc($groups))
{
	if ($rowsGroups['groupid'] == $rows['groupid'])
	{
?>
							<option value="<?php echo $rowsGroups['groupid']; ?>" selected="selected">#<?php echo $rowsGroups['groupid'].' - '.htmlspecialchars($rowsGroups['name'], ENT_QUOTES); ?></option>
<?php
	}
	else
	{
?>
							<option value="<?php echo $rowsGroups['groupid']; ?>">#<?php echo $rowsGroups['groupid'].' - '.htmlspecialchars($rowsGroups['name'], ENT_QUOTES); ?></option>
<?php
	}
}

//---------------------------------------------------------+
?>
						</select>
					<label>Box</label>
<?php

if ($rows['status'] == 'Pending')
{
?>
						<select name="boxID">
<?php

	//---------------------------------------------------------+

	while ($rowsBoxes = mysql_fetch_assoc($boxes))
	{
		if ($rowsBoxes['boxid'] == $rows['boxid'])
		{
?>
							<option value="<?php echo $rowsBoxes['boxid']; ?>" selected="selected"><?php echo $rowsBoxes['ip'].' - '.htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></option>
<?php
		}
		else
		{
?>
							<option value="<?php echo $rowsBoxes['boxid']; ?>"><?php echo $rowsBoxes['ip'].' - '.htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></option>
<?php
		}
	}

	//---------------------------------------------------------+

?>
						</select>
<?php
}
else
{

	//---------------------------------------------------------+

	while ($rowsBoxes = mysql_fetch_assoc($boxes))
	{
		if ($rowsBoxes['boxid'] == $rows['boxid'])
		{
?>
						<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo $rowsBoxes['ip'].' - '.htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?>">
						<input type="hidden" name="boxID" value="<?php echo $rows['boxid']; ?>">
<?php
		}
	}

	//---------------------------------------------------------+

}

?>
					<label>Category</label>
						<select name="catID">
<?php
//---------------------------------------------------------+
while ($rowsCategories = mysql_fetch_assoc($categories))
{
	if ($rowsCategories['id'] == $rows['catid'])
	{
?>
							<option value="<?php echo $rowsCategories['id']; ?>" selected="selected"><?php echo htmlspecialchars($rowsCategories['name'], ENT_QUOTES); ?></option>
<?php
	}
	else
	{
?>
							<option value="<?php echo $rowsCategories['id']; ?>"><?php echo htmlspecialchars($rowsCategories['name'], ENT_QUOTES); ?></option>
<?php
	}
}
//---------------------------------------------------------+
?>
						</select>
					<label>File Name</label>
						<input type="text" name="file" class="span5" value="<?php echo htmlspecialchars($rows['filename'], ENT_QUOTES); ?>">
						<span class="help-inline">{script}</span>
					<label>Start Command</label>
						<textarea name="startLine" class="textarea span5"><?php echo htmlspecialchars($rows['startline'], ENT_QUOTES); ?></textarea>
					<label>Exec Mode</label>
<?php

if ($rows['status'] == 'Pending')
{
?>
						<select name="mode">
							<option value="0"<?php

	if ($rows['type'] == '0')
	{

	?> selected="selected" <?php

	}

?>>Non-Interactive</option>
							<option value="1"<?php

	if ($rows['type'] == '1')
	{

	?> selected="selected" <?php

	}

?>>Interactive</option>
<?php
}
else
{

	if ($rows['type'] == '0')
	{
?>
						<input class="input-xlarge disabled" type="text" disabled="" placeholder="Non-Interactive">
<?php
	}

	if ($rows['type'] == '1')
	{
?>
						<input class="input-xlarge disabled" type="text" disabled="" placeholder="Interactive">
<?php
	}

?>
						<input type="hidden" name="mode" value="<?php echo $rows['type']; ?>">
<?php
}

?>
						</select>
						<span class="help-inline"><a href="http://wiki.bgpanel.net/doku.php?id=wiki:scripts" target="_blank">About Scripts&nbsp;<i class="icon-share-alt <?php echo formatIcon(); ?>"></i></a></span>
					<label>Home Directory</label>
						<input type="text" name="homeDir" class="span6" value="<?php echo htmlspecialchars($rows['homedir'], ENT_QUOTES); ?>">
						<span class="help-inline">Script Directory</span>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Save Changes</button>
						<button type="reset" class="btn">Cancel Changes</button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="script.php">Back to Scripts</a>
							</li>
						</ul>
					</div>
				</form>
			</div>
			<script language="javascript" type="text/javascript">
			function switchRadio()
			{
				var statusActive = document.getElementById('status0');
				var statusInactive = document.getElementById('status1');
				<!-- -->
				var active = statusActive.getAttribute('checked');
				var inactive = statusInactive.getAttribute('checked');
				<!-- -->
				if (active == '') {
					statusActive.removeAttribute('checked');
					statusInactive.setAttribute('checked', '');
				} else if (inactive == '') {
					statusActive.setAttribute('checked', '');
					statusInactive.removeAttribute('checked');
				}
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>