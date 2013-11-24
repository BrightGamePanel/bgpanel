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



$page = 'utilitiesversion';
$tab = 4;
$return = 'utilitiesversion.php';


require("../configuration.php");
require("./include.php");


$title = T_('Version Check');


/**
 * REMOTE VERSION RETRIEVER
 * Retrieve the last version of the panel from www.bgpanel.net
 */
$request = "http://version.bgpanel.net/";

$data = json_decode(file_get_contents($request));


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


if ( (BRANCH != 'devel') && (version_compare(COREVERSION, $data->version) == -1) )
{
?>
			<div class="alert">
				<strong><?php echo T_('Software Update Available!'); ?></strong>
				<p><?php echo T_('It is strongly recommended that you apply this update to BrightGamePanel as soon as possible.'); ?></p>
			</div>
			<div class="container"><div style="text-align: center;"><a class="btn btn-large btn-large btn-primary" type="button" href="http://sourceforge.net/projects/brightgamepanel/files/latest/download" target="_blank"><i class="icon-download-alt icon-white"></i>&nbsp;<?php echo T_('Download From SourceForge.net'); ?></a></div></div>
<?php
}
else
{
?>
			<div class="alert alert-success">
				<strong><?php echo T_('Your system is up-to-date!'); ?></strong>
			</div>
<?php
}

?>
			<div class="pagination"></div>
			<div class="well">
				<div class="row-fluid">
					<div class="span6">
						<legend><?php echo T_('Current Install'); ?></legend>
						<form>
							<label><?php echo T_('Project'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo PROJECT; ?>">
							<label><?php echo T_('Package'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo PACKAGE; ?>">
							<label><?php echo T_('Branch'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo BRANCH; ?>">
							<label><?php echo T_('Version'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo COREVERSION; ?>">
							<label><?php echo T_('Release Date'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo RELEASEDATE; ?>">
						</form>
					</div>

					<div class="span6">
						<legend><?php echo T_('Remote Version (version.bgpanel.net)'); ?></legend>
						<form>
							<label><?php echo T_('Project'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo $data->project; ?>">
							<label><?php echo T_('Package'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo $data->package; ?>">
							<label><?php echo T_('Branch'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="master">
							<label><?php echo T_('Version'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo $data->version; ?>">
							<label><?php echo T_('Release Date'); ?></label>
								<input class="input-xlarge disabled" type="text" disabled="" placeholder="<?php echo $data->date; ?>">
						</form>
					</div>
				</div>
			</div>
<?php
unset($request, $data);


include("./bootstrap/footer.php");
?>