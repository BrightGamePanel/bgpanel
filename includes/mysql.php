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



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}

$connection = mysql_connect(DBHOST, DBUSER, DBPASSWORD);	// Connection to database
if (!$connection)	// Return error if connection is broken
{
	exit("<html><head></head><body><h1>Database maintenance</h1><p>Please check back later</p></body></html>");
}


$db_connection = mysql_select_db(DBNAME);	// Select our database
if (!$db_connection)	// Return error	if error happened with database
{
	exit("<html><head></head><body><h1>Database maintenance</h1><p>Please check back later</p></body></html>");
}



/**
 * query_basic -- mysql_query ALIAS
 *
 * Used for INSERT INTO - UPDATE - DELETE requests.
 *
 * Return true on success
 */
function query_basic($query) {
	$conn = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBNAME);
	$result = mysqli_query($conn, $query);
	if ($result == FALSE)
	{
		$msg = 'Invalid query : '.mysqli_error($conn)."\n";
		echo $msg;
		return FALSE;
	}
	else
		return TRUE;
}

/**
 * query_numrows -- mysql_query + mysql_num_rows
 *
 * Retrieves the number of rows from a result set and return it.
 */
function query_numrows($query) {
	$conn = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBNAME);
	$result = mysqli_query($conn, $query);
	if ($result == FALSE)
	{
		$msg = 'Invalid query : '.mysqli_error($conn)."\n";
		echo $msg;
	}
	return (mysqli_num_rows($result));
}

/**
 * query_fetch_assoc -- mysql_query + mysql_fetch_assoc
 *
 * Returns an associative array that corresponds to the fetched row.
 */
function query_fetch_assoc($query) {
	$conn = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBNAME);
	$result = mysqli_query($conn, $query);
	if ($result == FALSE)
	{
		$msg = 'Invalid query : '.mysqli_error($conn)."\n";
		echo $msg;
	}
	return mysqli_fetch_assoc($result);
}
?>