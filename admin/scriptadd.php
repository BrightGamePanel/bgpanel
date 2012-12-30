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



$title = 'Add New Script';
$page = 'scriptadd';
$tab = 5;
$return = 'scriptadd.php';


require("../configuration.php");
require("./include.php");


$numBoxes = query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box`" );
$numCats = query_numrows( "SELECT `id` FROM `".DBPREFIX."scriptCat`" );


//---------------------------------------------------------+

if ($numBoxes == 0)
{
	$step = 'noboxes';
}
else if ($numCats == 0)
{
	$step = 'nocats';
}
else
{
	$step = 'form';
}

//---------------------------------------------------------+


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


switch ($step)
{

//------------------------------------------------------------------------------------------------------------+



	case 'noboxes':
?>
			<div class="well">
				<div style="text-align: center;">
					<span class="label label-warning">No Boxes Found</span><br />
					No boxes found. <a href="boxadd.php">Click here</a> to add a new box.
				</div>
			</div>
			<div style="text-align: center;">
				<ul class="pager">
					<li>
						<a href="script.php">Back to Scripts</a>
					</li>
				</ul>
			</div>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+



	case 'nocats':
?>
			<div class="well">
				<div style="text-align: center;">
					<span class="label label-warning">No Categories Found</span><br />
					No categories found. <a href="scriptcatadd.php">Click here</a> to add a new category.
				</div>
			</div>
			<div style="text-align: center;">
				<ul class="pager">
					<li>
						<a href="script.php">Back to Scripts</a>
					</li>
				</ul>
			</div>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+



	case 'form':
		$boxes = mysql_query( "SELECT `boxid`, `name`, `ip` FROM `".DBPREFIX."box` ORDER BY `boxid`" );
		$categories = mysql_query( "SELECT `id`, `name` FROM `".DBPREFIX."scriptCat` ORDER BY `id`" );
		$groups = mysql_query( "SELECT `groupid`, `name` FROM `".DBPREFIX."group` ORDER BY `groupid`" );
		###
?>
			<div class="well">
				<form method="post" action="scriptprocess.php">
					<input type="hidden" name="task" value="scriptadd" />
					<label>Script Name</label>
						<input type="text" name="name" class="span5" value="<?php
if (isset($_SESSION['name']))
{
	echo htmlspecialchars($_SESSION['name'], ENT_QUOTES);
	unset($_SESSION['name']);
}
?>">
					<label>Description</label>
						<textarea name="description" class="textarea span5"><?php
if (isset($_SESSION['description']))
{
	echo htmlspecialchars($_SESSION['description'], ENT_QUOTES);
	unset($_SESSION['description']);
}
?></textarea>
					<label>Owner Group</label>
						<select name="groupID">
							<option value="none">None</option>
<?php
//---------------------------------------------------------+
while ($rowsGroups = mysql_fetch_assoc($groups))
{
	if (isset($_SESSION['groupid']) && $rowsGroups['groupid'] == $_SESSION['groupid'])
	{
?>
							<option value="<?php echo $rowsGroups['groupid']; ?>" selected="selected">#<?php echo $rowsGroups['groupid'].' - '.htmlspecialchars($rowsGroups['name'], ENT_QUOTES); ?></option>
<?php
		unset($_SESSION['groupid']);
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
						<select name="boxID">
<?php
//---------------------------------------------------------+
while ($rowsBoxes = mysql_fetch_assoc($boxes))
{
	if (isset($_SESSION['boxid']) && $rowsBoxes['boxid'] == $_SESSION['boxid'])
	{
?>
							<option value="<?php echo $rowsBoxes['boxid']; ?>" selected="selected"><?php echo $rowsBoxes['ip'].' - '.htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></option>
<?php
		unset($_SESSION['boxid']);
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
					<label>Category</label>
						<select name="catID">
<?php
//---------------------------------------------------------+
while ($rowsCategories = mysql_fetch_assoc($categories))
{
	if (isset($_SESSION['catid']) && $rowsCategories['id'] == $_SESSION['catid'])
	{
?>
							<option value="<?php echo $rowsCategories['id']; ?>" selected="selected"><?php echo htmlspecialchars($rowsCategories['name'], ENT_QUOTES); ?></option>
<?php
		unset($_SESSION['catid']);
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
						<input type="text" name="file" class="span5" value="<?php
if (isset($_SESSION['file']))
{
	echo htmlspecialchars($_SESSION['file'], ENT_QUOTES);
	unset($_SESSION['file']);
}
?>">
						<span class="help-inline">{script}</span>
					<label>Start Command</label>
						<textarea name="startLine" class="textarea span5"><?php
if (isset($_SESSION['startline']))
{
	echo htmlspecialchars($_SESSION['startline'], ENT_QUOTES);
	unset($_SESSION['startline']);
}
else
{
	echo "&#123;script&#125;";
}
?></textarea>
					<label>Exec Mode</label>
						<select name="mode">
							<option value="0"<?php

if (isset($_SESSION['mode']) && $_SESSION['mode'] == '0')
{

?> selected="selected" <?php

}

?>>Non-Interactive</option>
							<option value="1"<?php

if (isset($_SESSION['mode']) && $_SESSION['mode'] == '1')
{

?> selected="selected" <?php

}

?>>Interactive</option>
						</select>
						<span class="help-inline"><a href="http://wiki.bgpanel.net/doku.php?id=wiki:scripts" target="_blank">About Scripts&nbsp;<i class="icon-share-alt <?php echo formatIcon(); ?>"></i></a></span>
					<label>Home Directory</label>
						<input type="text" name="homeDir" class="span6" value="<?php
if (isset($_SESSION['homedir']))
{
	echo htmlspecialchars($_SESSION['homedir'], ENT_QUOTES);
	unset($_SESSION['homedir']);
}
?>">
						<span class="help-inline">Script Directory</span>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Add New Script</button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="script.php">Back</a>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+

}


include("./bootstrap/footer.php");
?>