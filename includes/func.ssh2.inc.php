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



if (!class_exists('Net_SSH2')) {
	if (file_exists('../libs/phpseclib/SSH2.php')) {
		// Admin Side
		require_once("../libs/phpseclib/SSH2.php");
	}
	else {
		// Client Side
		require_once("./libs/phpseclib/SSH2.php");
	}
}



/**
 * Establish a SSH2 connection using PHPSECLIB
 *
 * @return object (ssh obj) OR string (err)
 */
function newNetSSH2($ip, $sshport = 22, $login, $password)
{
	$ssh = new Net_SSH2($ip, $sshport);

	if (!$ssh->login($login, $password))
	{
		$socket = @fsockopen($ip, $sshport, $errno, $errstr, 5);

		if ($socket == FALSE) {
			$debug = "Unable to connect to $ip on port $sshport : $errstr ( Errno: $errno )";
			return $debug;
		}

		return 'Unable to connect to box with SSH';
	}

	return $ssh;
}

?>