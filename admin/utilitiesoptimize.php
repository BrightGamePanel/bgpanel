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



$title = 'Optimize Database';
$page = 'utilitiesoptimize';
$tab = 4;
$return = 'utilitiesoptimize.php';


require("../configuration.php");
require("./include.php");


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
				<legend>
					This operation tells the MySQL server to clean up the database tables, optimizing them for better performance.<br />
					It is recommended that you run this at least once a month.
				</legend>
				<div style="text-align: center;">
					<a class="btn btn-large btn-large btn-primary" type="button" href="utilitiesoptimizeprocess.php?task=optimize"><i class="icon-wrench icon-white"></i>&nbsp;Optimize!</a>
				</div>
			</div>
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info">Analysis Result</span>
				</div>
				<table id="dbanalysis" class="zebra-striped">
					<thead>
						<tr>
							<th>Table</th>
							<th>Operation</th>
							<th>Msg_Type</th>
							<th>Message</th>
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
				<script type="text/javascript">
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