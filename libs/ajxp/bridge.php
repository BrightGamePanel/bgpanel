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
 * @version		(Release 0) DEVELOPER BETA 8
 * @link		http://www.bgpanel.net/
 */



/**
 *	@Class:		AjaXplorer Bridge Class For BrightGamePanel
 *	@Version:	1.0
 *	@Date:		25/08/2013
 */
class AJXP_Bridge {

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// VARIABLES

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	/**
	 * BGPanel Workspaces
	 *
	 * @var Array
	 * @access private
	 */
	private $bgpWorkspaces	= array();

	/**
	 * BGPanel User Vars
	 *
	 * @var String
	 * @access private
	 */
	private $bgpUser		= '';
	private $bgpUserRight	= '';

	/**
	 * AJXP Data Directories
	 *
	 * @var String
	 * @access private
	 */
	private $AJXP_DATA_PATH					=	'';
	private $AJXP_DATA_CONFSERIAL_REPOFILE	=	'';
	private $AJXP_DATA_AUTHSERIAL_DIR		=	'';
	// private $AJXP_DATA_BOOTCONF_FILE		=	'';


	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// FUNCTIONS

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// Default Constructor
	function __construct( $boxes, $servers, $user, $right = 'admin' )
	{
		// Var INIT
		$this->AJXP_DATA_PATH 					=	substr( realpath(dirname(__FILE__)), 0, -10 ).'/ajxp/data';
		$this->AJXP_DATA_CONFSERIAL_REPOFILE	=	$this->AJXP_DATA_PATH.'/plugins/conf.serial/repo.ser';
		$this->AJXP_DATA_AUTHSERIAL_DIR			=	$this->AJXP_DATA_PATH.'/plugins/auth.serial';
		// $this->AJXP_DATA_BOOTCONF_FILE			=	$this->AJXP_DATA_PATH.'/plugins/boot.conf/bootstrap.json';


		// Test write perms
		if (
				(!is_writable($this->AJXP_DATA_PATH)) ||
				(!is_writable($this->AJXP_DATA_CONFSERIAL_REPOFILE)) ||
				(!is_writable($this->AJXP_DATA_AUTHSERIAL_DIR))
			)
		{
			trigger_error("AJXP DATA DIRECTORY IS NOT WRITABLE!", E_USER_ERROR);
			exit( 'AJXP DATA DIRECTORY IS NOT WRITABLE!' );
		}


		// Test params
		if ( empty($user) )
		{
			trigger_error("NO BGP USER GIVEN FOR AjaXplorer Bridge Class!", E_USER_ERROR);
			exit( 'NO BGP USER GIVEN FOR AjaXplorer Bridge Class!' );
		}


		$this->bgpWorkspaces	= array_merge($this->bgpWorkspaces, $boxes);
		$this->bgpWorkspaces	= array_merge($this->bgpWorkspaces, $servers);
		$this->bgpUser			= $user;
		$this->bgpUserRight		= $right;
	}

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Update AJXP repo serialized file
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function updateAJXPWorspaces( ) {

		return FALSE;
	}

	/**
	 * Update AJXP user
	 * Add correct repositories to the user with rw perms
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function updateAJXPUser( ) {

		return FALSE;
	}
}



?>