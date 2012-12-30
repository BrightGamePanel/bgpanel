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



$title = 'Add New Game';
$page = 'configgameadd';
$tab = 5;
$return = 'configgameadd.php';


require("../configuration.php");
require("./include.php");
include("../libs/lgsl/lgsl_protocol.php");


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
				<form method="post" action="configgameprocess.php">
					<input type="hidden" name="task" value="configgameadd" />
					<label>Game Name</label>
						<input type="text" name="gameName" class="span4" value="<?php
if (isset($_SESSION['gameName']))
{
	echo htmlspecialchars($_SESSION['gameName'], ENT_QUOTES);
	unset($_SESSION['gameName']);
}
?>">
					<label>Max Slots</label>
						<input type="text" name="maxSlots" class="span1" value="<?php
if (isset($_SESSION['maxSlots']))
{
	echo htmlspecialchars($_SESSION['maxSlots'], ENT_QUOTES);
	unset($_SESSION['maxSlots']);
}
?>">
						<span class="help-inline">{slots}</span>
					<label>Default Server Port</label>
						<input type="text" name="defaultPort" class="span1" value="<?php
if (isset($_SESSION['defaultPort']))
{
	echo htmlspecialchars($_SESSION['defaultPort'], ENT_QUOTES);
	unset($_SESSION['defaultPort']);
}
?>">
						<span class="help-inline">{port}</span>
					<label>Query Port</label>
						<input type="text" name="queryPort" class="span1" value="<?php
if (isset($_SESSION['queryPort']))
{
	echo htmlspecialchars($_SESSION['queryPort'], ENT_QUOTES);
	unset($_SESSION['queryPort']);
}
?>">
						<span class="help-inline">Leave blank to use server port</span>
					<div class="row">
						<div class="span6">
							<div style="text-align: center; margin-bottom: 5px;">
								<span class="label">Game Configuration</span>
							</div>
							<table class="table table-striped table-bordered">
								<tr>
									<td>Configuration Name</td>
									<td>Associated Option</td>
									<td>Alias</td>
								</tr>
<?php

$n = 1;
while ($n < 10)
{
?>
								<tr>
									<td>
										<input type="text" name="cfg<?php echo $n; ?>Name" class="span2" style="margin-bottom: 0px;" value="<?php
if (isset($_SESSION['cfg'.$n.'Name']))
{
	echo htmlspecialchars($_SESSION['cfg'.$n.'Name'], ENT_QUOTES);
	unset($_SESSION['cfg'.$n.'Name']);
}
?>">
									</td>
									<td>
										<input type="text" name="cfg<?php echo $n; ?>" class="span4" style="margin-bottom: 0px;" value="<?php
if (isset($_SESSION['cfg'.$n]))
{
	echo htmlspecialchars($_SESSION['cfg'.$n], ENT_QUOTES);
	unset($_SESSION['cfg'.$n]);
}
?>">
									</td>
									<td style="padding-left: 3px;">
										<div style="text-align: center; margin-bottom: 0px;">
											<span class="help-inline" style="padding-top: 5px;">{cfg<?php echo $n; ?>}</span>
										</div>
									</td>
								</tr>
<?php
	++$n;
}
unset ($n);

?>
							</table>
						</div>
					</div>
					<label>Start Command</label>
						<textarea name="startLine" class="textarea span5"><?php
if (isset($_SESSION['startLine']))
{
	echo htmlspecialchars($_SESSION['startLine'], ENT_QUOTES);
	unset($_SESSION['startLine']);
}
?></textarea>
					<label>Query Type</label>
						<select name="queryType">
<?php
//---------------------------------------------------------+

$gamequery = lgsl_type_list();

foreach ($gamequery as $key => $value)
{
	if (isset($_SESSION['queryType']) && $key == $_SESSION['queryType'])
	{
		$output = "\t\t\t\t\t\t<option value=\"".$key."\" selected=\"selected\">".$value." -- ".$key."</option>\r\n";
		unset($_SESSION['queryType']);
		echo $output;
	}
	else
	{
		$output = "\t\t\t\t\t\t<option value=\"".$key."\">".$value." -- ".$key."</option>\r\n";
		echo $output;
	}
}
//---------------------------------------------------------+
?>
						</select>
					<label>Cache Directory</label>
						<input type="text" name="cacheDir" class="span6" value="<?php
if (isset($_SESSION['cacheDir']))
{
	echo htmlspecialchars($_SESSION['cacheDir'], ENT_QUOTES);
	unset($_SESSION['cacheDir']);
}
?>">
						<span class="help-inline">Optional</span>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn btn-primary">Add New Game</button>
					</div>
					<div style="text-align: center; margin-top: 19px;">
						<ul class="pager">
							<li>
								<a href="configgame.php">Back to Games</a>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php


include("./bootstrap/footer.php");
?>