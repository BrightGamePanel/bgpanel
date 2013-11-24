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




$page = 'utilitiesoptimize';
$tab = 4;
$return = 'utilitiesoptimize.php';


require("../configuration.php");
require("./include.php");


$title = T_('Optimize Database');


//---------------------------------------------------------+

/* ANALYZE BGP TABLES */
function analyze_database()
{
	$result = mysql_query('SHOW TABLES');
	$i = 0;

	while($table = mysql_fetch_row($result))
	{
		if (preg_match("#^".DBPREFIX."#", $table[0]))
		{
			$analysis[$i] = query_fetch_assoc('ANALYZE TABLE '.$table[0]);
			$i++;
		}
	}

	unset($result);

	if (isset($analysis))
	{
		return $analysis;
	}
}

//---------------------------------------------------------+

$dbanalysis = analyze_database();

//---------------------------------------------------------+


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="alert alert-info">
				<h4 class="alert-heading"><?php echo T_('Tip'); ?></h4>
				<?php echo T_('This operation tells the MySQL server to clean up the database tables, optimizing them for better performance.'); ?><br />
				<?php echo T_('It is recommended that you run this at least once a month.'); ?>
			</div>
			<div class="container">
				<div style="text-align: center;">
					<a class="btn btn-large btn-large btn-primary" type="button" href="utilitiesoptimizeprocess.php?task=optimize"><i class="icon-wrench icon-white"></i>&nbsp;<?php echo T_('Optimize!'); ?></a>
				</div>
			</div> <!-- End Container -->
			<div class="pagination"></div>
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info"><?php echo T_('Analysis Result'); ?></span>
				</div>
				<table id="dbanalysis" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('Table'); ?></th>
							<th><?php echo T_('Operation'); ?></th>
							<th><?php echo T_('Msg_Type'); ?></th>
							<th><?php echo T_('Message'); ?></th>
						</tr>
					</thead>
					<tbody>
<?php

foreach($dbanalysis as $key => $value)
{
?>
						<tr>
							<td><?php echo $value['Table']; ?></td>
							<td><?php echo $value['Op']; ?></td>
							<td><?php echo $value['Msg_type']; ?></td>
							<td><?php echo $value['Msg_text']; ?></td>
						</tr>
<?php
}
unset($dbanalysis);

?>
					</tbody>
				</table>
				<script>
				$(document).ready(function() {
					$("#dbanalysis").tablesorter({
						sortList: [[0,0]]
					});
				});
				</script>
			</div>
<?php


include("./bootstrap/footer.php");
?>