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



/**
 * Game Server Path Hotfix
 *
 * Add to the game server path its associated binary (depending the full game name)
 *
 * Only required by serveradd.php during form process
 */
function addBin2GameServerPath( $path, $game )
{
	// Known List
	$binaries = parse_ini_file( INCLUDES_INI_DIR . "/game-binaries.ini" );
	$binaries = array_flip($binaries);

	// Fix path
	$len = strlen($path);
	if ( $path[$len-1] != '/' ) {
			// Add ending slash
			$path = $path.'/';
	}

	// Process
	if (array_key_exists( $game, $binaries )) {
		return $path.$binaries[$game];
	}
	else {
		return $path.'bin.bin';
	}
}

?>