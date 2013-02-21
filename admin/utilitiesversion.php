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

 
if (COREVERSION != $data->version)
{
?>
			<div class="alert">
				<strong><?php echo T_('Software Update Available!'); ?></strong>
				<p><?php echo T_('It is strongly recommended that you apply this update to BrightGamePanel as soon as possible.'); ?></p>
			</div>
			<a href="http://sourceforge.net/projects/brightgamepanel/files/latest/download" target="_blank" class="btn btn-block btn-primary" type="button"><?php echo T_('Download From SourceForge.net'); ?></a>
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