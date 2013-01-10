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

include("./bootstrap/header.php");


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
<?php

/**
 * REMOTE VERSION RETRIEVER
 * Retrieve the last version of the panel from www.bgpanel.net
 */
$request = "http://version.bgpanel.net/";

$data = json_decode(file_get_contents($request));

?>
		<div class="span6">
			<legend><?php echo T_('Remote Version (version.bgpanel.net)'); ?></legend>
			<table class="table table-bordered">
				<thead>
					<th><?php echo T_('Project'); ?></th>
					<th><?php echo T_('Package'); ?></th>
					<th><?php echo T_('Version'); ?></th>
					<th><?php echo T_('Release Date'); ?></th>
				</thead>
				<tbody>
					<tr>
						<td><span class="badge"><?php echo $data->project; ?></span></td>
						<td><span class="badge badge-info"><?php echo $data->package; ?></span></td>
						<td><span class="badge badge-info"><?php echo $data->version; ?></span></td>
						<td><span class="badge badge-info"><?php echo $data->date; ?></td>
					</tr>
				</tbody>
			</table>
<?php

if (COREVERSION != $data->version)
{
?>
			<hr>
			<div class="alert">
				<strong><?php echo T_('Software Update Available!'); ?></strong>
				<p><?php echo T_('It is strongly recommended that you apply this update to BrightGamePanel as soon as possible.'); ?></p>
			</div>
			<a href="http://sourceforge.net/projects/brightgamepanel/files/latest/download" target="_blank" class="btn btn-block btn-primary" type="button"><?php echo T_('Download From SourceForge.net'); ?></a>
<?php
}

?>
		</div>
	</div>
</div>
<?php

unset($request, $data);


include("./bootstrap/footer.php");
?>