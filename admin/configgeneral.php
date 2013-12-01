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



$page = 'configgeneral';
$tab = 5;
$return = 'configgeneral.php';


require("../configuration.php");
require("./include.php");
require("../includes/templates.php");

$title = T_('General Settings');

$systemUrl = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'systemurl' LIMIT 1" );
$adminTemplate = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'admintemplate' LIMIT 1" );
$clientTemplate = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'clienttemplate' LIMIT 1" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#"><?php echo T_('General'); ?></a></li>
			</ul>
			<div class="well">
				<form method="post" action="configgeneralprocess.php">
					<input type="hidden" name="task" value="generaledit" />
					<label><?php echo T_('Version'); ?></label>
						<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo COREVERSION; ?>">
					<label><?php echo T_('Panel Name'); ?></label>
						<input type="text" name="panelName" class="span4" value="<?php echo htmlspecialchars(SITENAME, ENT_QUOTES); ?>">
						<span class="help-inline"><?php echo T_('The name of the panel for the header in the client panel'); ?></span>
					<label><?php echo T_('Panel URL'); ?></label>
						<input type="text" name="systemUrl" class="span6" value="<?php echo htmlspecialchars($systemUrl['value'], ENT_QUOTES); ?>">
						<span class="help-inline"><?php echo T_('Client side URL, http://www.yourdomain.com/panel/'); ?></span>
					<label><?php echo T_('Maintenance Mode'); ?></label>
						<div class="btn-group" data-toggle="buttons-radio" style="margin-bottom: 5px;">
							<a class="btn btn-primary <?php
//---------------------------------------------------------+
if (MAINTENANCE	== '1') // On
{
	echo 'active';
}
//---------------------------------------------------------+
?>" onclick="switchRadio();return false;"><?php echo T_('On'); ?></a>
							<a class="btn btn-primary <?php
//---------------------------------------------------------+
if (MAINTENANCE	== '0') // Off
{
	echo 'active';
}
//---------------------------------------------------------+
?>" onclick="switchRadio();return false;"><?php echo T_('Off'); ?></a>
						</div>
						<div class="collapse">
							<label class="radio">
								<input id="status0" type="radio" value="1" name="status" <?php
//---------------------------------------------------------+
if (MAINTENANCE	== '1') // On
{
	echo "checked=\"\"";
}
//---------------------------------------------------------+
?>>
							</label>
							<label class="radio">
								<input id="status1" type="radio" value="0" name="status" <?php
//---------------------------------------------------------+
if (MAINTENANCE	== '0') // Off
{
	echo "checked=\"\"";
}
//---------------------------------------------------------+
?>>
							</label>
						</div>
						<div>
							<span class="help-block">
								<?php echo T_('Switch the panel in maintenance mode.'); ?>
								<?php echo T_('Only'); ?> <b><?php echo T_('Super Administrators'); ?></b> <?php echo T_('will be able to log into the panel,'); ?>
								<i><?php echo T_('Limited / Full Administrators'); ?></i> <?php echo T_('and'); ?> <i><?php echo T_('Clients'); ?></i> <?php echo T_('will be redirected to a page showing that your panel is down for maintenance.'); ?>
								<b><?php echo T_('NOTE: CRON JOB IS DISABLED IN THIS MODE!'); ?></b>
							</span>
						</div>
					<label>Admin Template</label>
						<select class="span2" name="adminTemplate">
<?php
//---------------------------------------------------------+
foreach ($global_templates as $key => $value)
{
	if ($value == htmlspecialchars($adminTemplate['value'], ENT_QUOTES))
	{
		$output = "\t\t\t\t\t\t\t<option value=\"".$value."\" selected=\"selected\">".$key."</option>\r\n";
		echo $output;
	}
	else
	{
		$output = "\t\t\t\t\t\t\t<option value=\"".$value."\">".$key."</option>\r\n";
		echo $output;
	}
}
//---------------------------------------------------------+
?>
						</select>
					<label>Client Template</label>
						<select class="span2" name="clientTemplate">
<?php
//---------------------------------------------------------+
foreach ($global_templates as $key => $value)
{
	if ($value == htmlspecialchars($clientTemplate['value'], ENT_QUOTES))
	{
		$output = "\t\t\t\t\t\t\t<option value=\"".$value."\" selected=\"selected\">".$key."</option>\r\n";
		echo $output;
	}
	else
	{
		$output = "\t\t\t\t\t\t\t<option value=\"".$value."\">".$key."</option>\r\n";
		echo $output;
	}
}
//---------------------------------------------------------+
?>
						</select>
						<span class="help-inline"><a href="http://getbootstrap.com/" target="_blank">Learn more about Bootstrap&nbsp;<i class="icon-share-alt <?php echo formatIcon(); ?>"></i></a></span>
						<span class="help-inline"><a href="http://bootswatch.com/" target="_blank">Bootswatch - Free themes for Twitter Bootstrap&nbsp;<i class="icon-share-alt <?php echo formatIcon(); ?>"></i></a></span>
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