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



$return = TRUE;


require("../configuration.php");
require("./include.php");


if (isset($_POST['task']))
{
	$task = mysql_real_escape_string($_POST['task']);
}
else if (isset($_GET['task']))
{
	$task = mysql_real_escape_string($_GET['task']);
}


switch (@$task)
{
	case 'deletelog':
		query_basic( "TRUNCATE `".DBPREFIX."log`" );
		$_SESSION['msg1'] = 'Activity Logs Deleted Successfully!';
		$_SESSION['msg2'] = 'All activity logs have been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: utilitieslog.php" );
		die();
		break;

	case 'dumplogtxt':
		$output = '';
		$date = formatDate(date('Y-m-d H:i:s'));
		$numLogs = query_numrows( "SELECT * FROM `".DBPREFIX."log` ORDER BY `logid`" );

//---------------------------------------------------------+
$output .= "
//==================================================================================
//
//	BRIGHT GAME PANEL ACTIVITY LOGS DUMP
//
//==================================================================================
//
//	FILENAME: bgp-activity-logs-".date('Y-m-d')."
//	DATE: {$date}
//	ADMIN USERNAME: {$_SESSION['adminusername']}
//	ADMIN FIRSTNAME: {$_SESSION['adminfirstname']}
//	ADMIN LASTNAME: {$_SESSION['adminlastname']}
//
//	NUMBER OF LOGS: {$numLogs}
//	ORDERED BY: LOGID
//
//==================================================================================
//
//	FORMAT:
//
//		ID:
//		Message:
//		Name:
//		IP:
//		Timestamp: date(Y-m-d H:i:s)
//
//==================================================================================



";
//---------------------------------------------------------+

		$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` ORDER BY `logid` DESC" );

		$i = 0;
		while ($rowsLogs = mysql_fetch_assoc($logs))
		{
//---------------------------------------------------------+
$output .= "
//---------------------------------------------------------+
	ID:	{$rowsLogs['logid']}
	Message: {$rowsLogs['message']}
	Name: {$rowsLogs['name']}
	IP: {$rowsLogs['ip']}
	Timestamp: {$rowsLogs['timestamp']}";
//---------------------------------------------------------+
			$i++;
		}
		unset($i);

//---------------------------------------------------------+
$output .= "



//==================================================================================
//	END
//==================================================================================
";
//---------------------------------------------------------+

		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="bgp-activity-logs-'.date('Y-m-d').'.txt"');

		echo $output;

		die();
		break;

	case 'dumplogcsv':

		/**
		 * CSV Export
		 * @link: http://www.comscripts.com/sources/php.export-csv.102.html
		 */

		$resQuery = mysql_query( "SELECT * FROM `".DBPREFIX."log` ORDER BY `logid` DESC" );

		header("Content-Type: application/csv-tab-delimited-table");
		header('Content-Disposition: attachment; filename="bgp-activity-logs-'.date('Y-m-d').'.csv"');

		if (mysql_num_rows($resQuery) != 0)
		{
			// Columns
			$fields = mysql_num_fields($resQuery);
			$i = 0;
			while ($i < $fields) {
				echo mysql_field_name($resQuery, $i).";";
				$i++;
			}
			echo "\n";

			// Table data
			while ($arrSelect = mysql_fetch_array($resQuery, MYSQL_ASSOC)) {
				foreach($arrSelect as $elem) {
					echo "$elem;";
				}
				echo "\n";
			}
		}

		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>