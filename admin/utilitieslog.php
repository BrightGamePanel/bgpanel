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
 * @copyleft	2012
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 4
 * @link		http://sourceforge.net/projects/brightgamepanel/
 */



$title = 'Activity Logs';
$page = 'utilitieslog';
$tab = 4;
$return = 'utilitieslog.php';


require("../configuration.php");
require("./include.php");


//---------------------------------------------------------+
//Num Pages Process:

$numLogs = query_numrows( "SELECT * FROM `".DBPREFIX."log` ORDER BY `logid` LIMIT 750" );

$numPages = ceil($numLogs / 50);

//---------------------------------------------------------+
//Pages Process:

if (isset($_GET['page']))
{
	$page = mysql_real_escape_string($_GET['page']);
}
else
{
	$page = 1;
}

//Security
if ($page > 15 || !is_numeric($page))
{
	exit('Page error!');
}

//---------------------------------------------------------+
//Logs:

$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` ORDER BY `logid` DESC LIMIT ".($page * 50)."" );

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
				<div class="btn-group">
					<a class="btn btn-large dropdown-toggle" href="#" data-toggle="dropdown">
						<i class="icon-tasks <?php echo formatIcon('', TEMPLATE); ?>"></i>
						&nbsp;Actions&nbsp;
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
						<li>
							<a tabindex="-1" href="#" onclick="deleteLogs();return false;">
								<i class="icon-warning-sign <?php echo formatIcon('', TEMPLATE); ?>"></i>
								Purge All Logs
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a tabindex="-1" href="#" onclick="dlTxtLogs();return false;">
								<i class="icon-download-alt <?php echo formatIcon('', TEMPLATE); ?>"></i>
								Download TXT file
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a tabindex="-1" href="#" onclick="dlCsvLogs();return false;">
								<i class="icon-download-alt <?php echo formatIcon('', TEMPLATE); ?>"></i>
								Download CSV file
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="pagination" style="text-align: center;">
				<ul>
<?php

for ($i = 1; $i < $numPages + 1; $i++)
{
?>
					<li <?php
	if ($i == $page) {
		echo "class=\"active\"";
	} ?>>
						<a href="<?php
	if ($i == $page) {
		echo "#";
	} else {
		echo "utilitieslog.php?page=".$i;
	}?>"><?php echo $i; ?></a>
					</li>
<?php
}

?>
				</ul>
			</div>
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info">Activity Logs</span>
				</div>
				<table id="logs" class="tablesorter">
					<thead>
						<tr>
							<th>ID</th>
							<th>Message</th>
							<th>Name</th>
							<th>IP</th>
							<th>Timestamp</th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($logs) == 0)
{
?>
						<tr>
							<td colspan="5"><div style="text-align: center;"><span class="label label-warning">No Logs Found</span></div></td>
						</tr>
<?php
}

$n = 0;
while ($rowsLogs = mysql_fetch_assoc($logs))
{
	//We dump entries before our page
	if ($n >= ($page - 1) * 50)
	{
?>
						<tr>
							<td><?php echo $rowsLogs['logid']; ?></td>
							<td><?php echo $rowsLogs['message']; ?></td>
							<td><?php echo $rowsLogs['name']; ?></td>
							<td><?php echo $rowsLogs['ip']; ?></td>
							<td><?php echo formatDate($rowsLogs['timestamp']); ?></td>
						</tr>
<?php
	}
	$n++;
}
unset($n);

?>
					</tbody>
				</table>
<?php

if (mysql_num_rows($logs) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#logs").tablesorter({
						sortList: [[0,1]]
					});
				});
				<!-- -->
				function deleteLogs()
				{
					if (confirm("WARNING : All logs will be deleted!"))
					{
						window.location.href='utilitieslogprocess.php?task=deletelog';
					}
				}
				<!-- -->
				function dlTxtLogs()
				{
					if (confirm("Download all logs (TXT) ?"))
					{
						window.location.href='utilitieslogprocess.php?task=dumplogtxt';
					}
				}
				<!-- -->
				function dlCsvLogs()
				{
					if (confirm("Download all logs (CSV: Comma-Separated Values \";\") ?"))
					{
						window.location.href='utilitieslogprocess.php?task=dumplogcsv';
					}
				}
				</script>
<?php
}
unset($logs);

?>
			</div>
			<div class="pagination" style="text-align: center;">
				<ul>
<?php

for ($i = 1; $i < $numPages + 1; $i++)
{
?>
					<li <?php
	if ($i == $page) {
		echo "class=\"active\"";
	} ?>>
						<a href="<?php
	if ($i == $page) {
		echo "#";
	} else {
		echo "utilitieslog.php?page=".$i;
	}?>"><?php echo $i; ?></a>
					</li>
<?php
}

?>
				</ul>
			</div>
<?php


include("./bootstrap/footer.php");
?>