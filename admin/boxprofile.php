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
 * @version		(Release 0) DEVELOPER BETA 9
 * @link		http://www.bgpanel.net/
 */



$page = 'boxprofile';
$tab = 3;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: BoxID error.');
}

$boxid = $_GET['id'];
$return = 'boxprofile.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");


$title = T_('Box Profile');

$boxid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="boxsummary.php?id=<?php echo $boxid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li class="active"><a href="boxprofile.php?id=<?php echo $boxid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="boxip.php?id=<?php echo $boxid; ?>"><?php echo T_('IP Addresses'); ?></a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>"><?php echo T_('Charts'); ?></a></li>
				<li><a href="boxgamefile.php?id=<?php echo $boxid; ?>"><?php echo T_('Game File Repositories'); ?></a></li>
				<li><a href="#" onclick="ajxp()"><?php echo T_('WebFTP'); ?></a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
			<div class="well">
				<form method="post" action="boxprocess.php">
					<input type="hidden" name="task" value="boxprofile" />
					<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />
					<label><?php echo T_('Server Name'); ?></label>
						<input type="text" name="name" class="span4" value="<?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?>">
					<label><?php echo T_('IP Address'); ?></label>
						<input type="text" name="ip" class="span3" value="<?php echo htmlspecialchars($rows['ip'], ENT_QUOTES); ?>">
					<label><?php echo T_('SSH Login'); ?></label>
						<input type="text" name="login" class="span3" value="<?php echo htmlspecialchars($rows['login'], ENT_QUOTES); ?>">
					<label><?php echo T_('SSH Password'); ?></label>
						<input type="password" name="password" class="span3">
						<span class="help-inline"><?php echo T_('Leave blank for no change'); ?></span>
					<label><?php echo T_('SSH Port'); ?></label>
						<input type="text" name="sshport" class="span1" value="<?php echo htmlspecialchars($rows['sshport'], ENT_QUOTES); ?>">
					<label><?php echo T_('OS Type'); ?></label>
						<input type="text" class="input-xlarge disabled" disabled="" placeholder="Linux">
					<label><?php echo T_('Admin Notes'); ?></label>
						<textarea name="notes" class="textarea span10"><?php echo htmlspecialchars($rows['notes'], ENT_QUOTES); ?></textarea>
					<label class="checkbox">
						<input type="checkbox" name="verify" checked="checked">&nbsp;<?php echo T_('Verify Login &amp; Password'); ?>
					</label>
					<div style="text-align: center;">
						<ul class="pager">
							<li>
								<button type="submit" class="btn btn-primary"><?php echo T_('Save Changes'); ?></button>
							</li>
							<li>
								<button type="reset" class="btn"><?php echo T_('Cancel Changes'); ?></button>
							</li>
						</ul>
					</div>
				</form>
			</div>
			<script>
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>