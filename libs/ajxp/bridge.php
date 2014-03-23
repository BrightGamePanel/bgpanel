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



/**
 *	@Class:		AjaXplorer Bridge Class For BrightGamePanel
 *	@Version:	2.0
 *	@Date:		22/03/2014
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
	 * BGPanel User
	 *
	 * @var String
	 * @access private
	 */
	private $bgpUser		= '';



	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// FUNCTIONS

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// Default Constructor
	function __construct( $boxes, $servers, $user )
	{
		// Test params
		if ( empty($user) )
		{
			trigger_error("NO BGP USER GIVEN FOR AjaXplorer Bridge Class!", E_USER_ERROR);
			exit( 'NO BGP USER GIVEN FOR AjaXplorer Bridge Class!' );
		}


		$this->bgpWorkspaces	= array_merge($this->bgpWorkspaces, $boxes);
		$this->bgpWorkspaces	= array_merge($this->bgpWorkspaces, $servers);
		$this->bgpUser			= $user;
	}

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Generate dynamically AJXP workspaces
	 *
	 *
	 * See:
	 *
	 *	STRING:
	 *	"BGPANEL HOOK"
	 *
	 *	FILES:
	 *	/ajxp/data/
	 *	/ajxp/conf/bootstrap_repositories.php
	 *	/ajxp/plugins/auth.remote/glueCode.php
	 *
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function updateAJXPWorspaces( )
	{
		$i = 0;

		foreach ( $this->bgpWorkspaces as $key => $item )
		{

			if ( array_key_exists( 'boxid', $item ) ) {
				$description = 'Box';
			}
			else if ( array_key_exists( 'serverid', $item ) ) {
				$description = 'Game Server';
			}
			else {
				$description = '';
			}

			$ajxp_workspaces[$i] = array(
				"DISPLAY"		=> $item['name'],
				"AJXP_SLUG"		=> strtolower(str_replace(' ', '-', $item['name'])),

				"DRIVER"		=> "sftp_psl",

				"DRIVER_OPTIONS"=> array(
					"CREATION_TIME" => time(),
					"CREATION_USER" => $this->bgpUser,

					"SFTP_HOST" => $item['ip'],
					"SFTP_PORT" => $item['sshport'],
					"PATH" => $item['path'],
					"FIX_PERMISSIONS" => 'detect_remote_user_id',
					"CREATE" => FALSE,

					"USER" => $item['login'],
					"PASS" => $item['password'],
					"USE_SESSION_CREDENTIALS" => FALSE,

					"RECYCLE_BIN" => '',
					"CHARSET" => '',
					"PAGINATION_THRESHOLD" => 500,
					"PAGINATION_NUMBER" => 200,
					"USER_DESCRIPTION" => $description,

					"DEFAULT_RIGHTS" =>  "",
					"AJXP_WEBDAV_DISABLED" => TRUE,

					"META_SOURCES"		=> array(
						"metastore.serial"=> array(
							"METADATA_FILE" => ".ajxp_meta",
							"METADATA_FILE_LOCATION" => "infolders"
						),
						"meta.filehasher"   => array(),
						"meta.watch"        => array(),
						"index.lucene" => array(
							"index_content" => 'false',
							"index_meta_fields" => '',
							"repository_specific_keywords" => ''
						)
					)
				),
			);

			$i++;
		}

		unset($i);

		// Export vars
		$GLOBALS['AJXP_WORKSPACES'] = $ajxp_workspaces;

		return TRUE;
	}

}
