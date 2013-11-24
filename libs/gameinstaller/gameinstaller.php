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
 * @author		Simon Mora <samt2497@hotmail.com>
 * @author		warhawk3407 <warhawk3407@gmail.com> @NOSPAM
 * @copyleft	2013
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 9
 * @link		http://www.bgpanel.net/
 */


/**
 *	@Class:		Game Installer Main Class
 *	@Version:	1.0
 *	@Date:		20/08/2013
 */
class GameInstaller {

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// VARIABLES

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	/**
	 * SSH2 Object
	 * Connection To Remote Host
	 * Must Be Constructed With PHPSECLIB
	 *
	 * @see: http://phpseclib.sourceforge.net/
	 * @var object
	 * @access private
	 */
	private $sshConnection;

	/**
	 * Tells If A Game Has Been Selected For The Creation Of A Repository
	 *
	 * @var bool
	 * @access public
	 */
	public $gameSet = FALSE;

	/**
	 * Repository Directory On Remote Server
	 * Relative Path From User Home Directory
	 * Path Should Start with "~/"
	 *
	 * @var string
	 * @access public
	 */
	public $repoPath = '';

	/**
	 * Game Server Directory On Remote Server
	 * Absolute Path From Root
	 *
	 * @var string
	 * @access public
	 */
	public $gameServerPath = '';

	/**
	 * Supported Games
	 * Associative Array
	 * Full Names => Short Names
	 *
	 * @var array
	 * @access public
	 */
	public $games = array();

	/**
	 * Holds Operations To Do For:
	 * - Creating new repo
	 * - Installing game from repo
	 * - Updating game from repo
	 *
	 * @var array
	 * @access public
	 */
	public $actions = array();

	/**
	 * Error Messages
	 * Associative Array That Holds Errors If Any
	 *
	 * @var array
	 * @access public
	 */
	//public $errors;

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// FUNCTIONS

	//------------------------------------------------------------------------------------------------------------+
	//------------------------------------------------------------------------------------------------------------+

	// Default Constructor
	function __construct( $sshObject )
	{
		// VAR INIT
		if (is_object( $sshObject )) {
			$this->sshConnection = $sshObject;
		}
		else {
			trigger_error("Invalid SSH2 Object", E_USER_ERROR);
		}

		// Get Supported Games
		$ini_games = parse_ini_file('games.ini');
		$this->games = array_flip($ini_games);
	}

	//------------------------------------------------------------------------------------------------------------+

	// THIRD-PARTY LIBS

	/**
	 * XML To Associative Array
	 * by Sergey Aikinkulov
	 *
	 * @see: http://www.php.net/manual/en/class.xmlreader.php#83929
	 *
	 * $xml = new XMLReader();
	 * $xml->open([XML file]);
	 * $assoc = xml2assoc($xml);
	 * $xml->close();
	 *
	 * @param Object $xml
	 * @return array
	 * @access private
	 */
	private function xml2assoc( $xml ) {
		$assoc = NULL;
		while( $xml->read() ) {
			switch ( $xml->nodeType ) {
				case XMLReader::END_ELEMENT: return $assoc;
				case XMLReader::ELEMENT:
					$assoc[$xml->name][] = array( 'value' => $xml->isEmptyElement ? '' : $this->xml2assoc($xml) );
					break;
				case XMLReader::TEXT:
				case XMLReader::CDATA: $assoc .= $xml->value;
			}
		}
		return $assoc;
	}

	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Check If The Specified Game Is Supported
	 * Note: The game name must be full, something like "Counter-Strike: Source"
	 *
	 * @param String $game
	 * @return bool
	 * @access public
	 */
	public function gameExists( $game )
	{
		return array_key_exists($game, $this->games);
	}

	/**
	 * Load Game Actions And Specifications
	 * Note: The game name must be full, something like "Counter-Strike: Source"
	 *
	 * @param String $game
	 * @return bool
	 * @access public
	 */
	public function setGame( $game )
	{
		$this->actions = array();
		$this->gameSet = FALSE;

		if ($this->gameExists( $game )) {
			$game = $this->games[$game];
			$manifest = __DIR__.'/games/'.$game.'/manifest.xml';

			if (file_exists( $manifest )) {
				$xml = new XMLReader();

				$xml->open( $manifest );

				$arr = $this->xml2assoc( $xml );

				$this->actions['makeRepo'] = $arr['game'][0]['value']['actions'][0]['value']['repository'][0]['value'];
				$this->actions['installGame'] = $arr['game'][0]['value']['actions'][0]['value']['installgame'][0]['value'];
				$this->actions['updateGame'] = $arr['game'][0]['value']['actions'][0]['value']['updategame'][0]['value'];

				$xml->close( );

				$this->gameSet = TRUE;
			}
		}

		return $this->gameSet;
	}

	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Set Repository Directory Of Current Remote Server
	 *
	 * @param String $path
	 * @param bool $mkdir // Make Directory
	 * @return bool
	 * @access public
	 */
	public function setRepoPath( $path, $mkdir = false )
	{
		$this->repoPath = '';
		$len = strlen($path);

		if ($len < 3) {
			// Path is too short
			return FALSE;
		}

		if ($path[$len-1] != '/') {
			// Add ending slash
			$path = $path.'/';
		}

		// Test $path is home relative
		// Fix it otherwise
		if ($path[0] == '~' && $path[1] == '/') {
			// Nothing to do !
		}
		else if ($path[0] == '/') {
			$path = '~'.$path;
		}
		else {
			$path = '~/'.$path;
		}

		// Escape spaces
		$path = str_replace(' ', '\ ', $path);

		// Test $path exists
		if (trim( $this->sshConnection->exec('test -d '.$path." && echo 'true' || echo 'false'") ) == 'false') {
			if ($mkdir) {
				$this->sshConnection->exec('mkdir -p '.$path);
			}
			else {
				return FALSE;
			}
		}

		$this->repoPath = $path;

		return TRUE;
	}

	/**
	 * Set Game Server Directory Of Current Remote Server
	 * Should Be An Absolute Path From Root
	 *
	 * @param String $path
	 * @param bool $mkdir // Make Directory
	 * @return bool
	 * @access public
	 */
	public function setGameServerPath( $path, $mkdir = false )
	{
		$this->gameServerPath = '';
		$len = strlen($path);

		if ($len < 3) {
			// Path is too short
			return FALSE;
		}

		if ($path[$len-1] != '/') {
			// Add ending slash
			$path = $path.'/';
		}

		// Escape spaces
		$path = str_replace(' ', '\ ', $path);

		// Test $path exists
		if (trim( $this->sshConnection->exec('test -d '.$path." && echo 'true' || echo 'false'") ) == 'false') {
			if ($mkdir) {
				$this->sshConnection->exec('mkdir -p '.$path);
			}
			else {
				return FALSE;
			}
		}

		$this->gameServerPath = $path;

		return TRUE;
	}

	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Get Repository Information
	 * Works With Game Cache Repositories And Game Servers Created With This Class
	 *
	 * @param String $path
	 * @return mixed // False on Failure, Array() on Success
	 * @access public
	 */
	public function getCacheInfo( $path )
	{
		if (!empty( $path )) {
			$len = strlen($path);
			if ($path[$len-1] != '/') {
				// Add ending slash
				$path = $path.'/';
			}

			// Test .cacheinfo exists
			if (trim( $this->sshConnection->exec('test -f '.$path.".cacheinfo && echo 'true' || echo 'false'") ) == 'false') {
				return FALSE;
			}

			$info = array( 'size' => '', 'status' => '', 'mtime' => '');

			// Get last message
			$cache = trim( $this->sshConnection->exec('tail -n 1 '.$path.'.cacheinfo') );

			$info['size'] = trim( $this->sshConnection->exec('du -sh '.$path." | awk '{print $1}'") );

			if (strstr($cache, 'Status:') != FALSE) {
				$info['status'] = substr($cache, 8); // Remove "Status: " from string
				$info['mtime'] = time();
			}
			else {
				$info['status'] = 'Ready';
				$info['mtime'] = substr($cache, 7); // Remove "mtime: " from string
			}

			return $info;
		}

		return FALSE;
	}

	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Make Game Cache Repository
	 * Execute All Loaded Actions For The Selected Game
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function makeRepo( )
	{
		if ( $this->gameSet ) {
			if (!empty( $this->repoPath )) {
				if (!empty( $this->actions )) {

					$query = "echo \"Status: GameInstaller::makeRepo( ) Initialized ".date("Y-m-d H:i:s")."\" > ".$this->repoPath.'.cacheinfo ; '; // Verbose...
					$query = "echo \"Cache Repository Locked\" > ".$this->repoPath.'.cachelock ; '; // Lock Game Repo : Operation In Progress

					foreach ($this->actions['makeRepo'] as $action => $values) {
						$queryParts = $this->buildQuery( $action, $values, 'makeRepo' );

						if ($queryParts == FALSE) {
							// Error while building query
							return FALSE;
						}

						$query .= $queryParts;
					}

					// Log Once Finished...
					$query .= "echo \"Status: GameInstaller::makeRepo( ) Completed\" >> ".$this->repoPath.'.cacheinfo ; ';
					$query .= "echo \"mtime: $(date +%s)\" >> ".$this->repoPath.'.cacheinfo ; '; // "Repository is Ready" marker

					$query .= "rm ".$this->repoPath.'.cachelock ; '; // Delete lock file
					$query .= "rm ".$this->repoPath.'.cachescript ; '; // Delete install script at the end
					$query .= "rm ".$this->repoPath.'.cacheuid ; '; // Delete screen uid

					$this->executeQuery( $query, 'makeRepo' );

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Remove Game Cache Repository
	 * Flush repository contents
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function deleteRepo( )
	{
		if (!empty( $this->repoPath )) {
			$query = 'sleep 0.2 ; ';
			$query .= "rm -rf ".$this->repoPath.'* ; '; // Flush all contents
			$query .= "rm -rf ".$this->repoPath.'.* ; '; // Flush all cached contents

			$this->executeQuery( $query, 'makeRepo' );

			sleep(0.4);

			return TRUE;
		}

		return FALSE;
	}

	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Make Game Server
	 * Execute All Loaded Actions For The Selected Game
	 *
	 * @see: GameInstaller::makeRepo( )
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function makeGameServer( )
	{
		if ( $this->gameSet ) {
			if ( (!empty( $this->repoPath )) && (!empty($this->gameServerPath)) ) {
				if (!empty( $this->actions )) {

					$query = "echo \"Status: GameInstaller::makeGameServer( ) Initialized ".date("Y-m-d H:i:s")."\" > ".$this->gameServerPath.'.cacheinfo ; ';

					foreach ($this->actions['installGame'] as $action => $values) {
						$queryParts = $this->buildQuery( $action, $values, 'installGame' );

						if ($queryParts == FALSE) {
							return FALSE;
						}

						$query .= $queryParts;
					}

					// Log Once Finished...
					$query .= "echo \"Status: GameInstaller::makeGameServer( ) Completed\" >> ".$this->gameServerPath.'.cacheinfo ; ';
					$query .= "echo \"mtime: $(date +%s)\" >> ".$this->gameServerPath.'.cacheinfo ; ';

					$query .= "rm ".$this->gameServerPath.'.cachescript ; '; // Delete install script at the end
					$query .= "rm ".$this->gameServerPath.'.cacheuid ; '; // Delete screen uid

					$this->executeQuery( $query, 'installGame' );

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Update Game Server
	 * Execute All Loaded Actions For The Selected Game
	 *
	 * @see: GameInstaller::makeRepo( )
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function updateGameServer( )
	{
		if ( $this->gameSet ) {
			if ( (!empty( $this->repoPath )) && (!empty($this->gameServerPath)) ) {
				if (!empty( $this->actions )) {

					$query = "echo \"Status: GameInstaller::updateGameServer( ) Initialized ".date("Y-m-d H:i:s")."\" > ".$this->gameServerPath.'.cacheinfo ; ';

					foreach ($this->actions['updateGame'] as $action => $values) {
						$queryParts = $this->buildQuery( $action, $values, 'updateGame' );

						if ($queryParts == FALSE) {
							return FALSE;
						}

						$query .= $queryParts;
					}

					// Log Once Finished...
					$query .= "echo \"Status: GameInstaller::updateGameServer( ) Completed\" >> ".$this->gameServerPath.'.cacheinfo ; ';
					$query .= "echo \"mtime: $(date +%s)\" >> ".$this->gameServerPath.'.cacheinfo ; ';

					$query .= "rm ".$this->gameServerPath.'.cachescript ; '; // Delete install script at the end
					$query .= "rm ".$this->gameServerPath.'.cacheuid ; '; // Delete screen uid

					$this->executeQuery( $query, 'updateGame' );

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Remove Game Server Files
	 * Flush game server contents
	 *
	 * @param void
	 * @return bool
	 * @access public
	 */
	public function deleteGameServer( )
	{
		if (!empty( $this->gameServerPath )) {
			$query = 'sleep 0.2 ; ';
			$query .= "rm -rf ".$this->gameServerPath.' ; '; // Remove the game server folder

			$this->executeQuery( $query, 'installGame' );

			sleep(0.4);

			return TRUE;
		}

		return FALSE;
	}

	//------------------------------------------------------------------------------------------------------------+

	/**
	 * Build Action Query
	 * Instead of executing each action independently, we build a single query.
	 *
	 * @see: GameInstaller::makeRepo( )
	 * @see: GameInstaller::makeGameServer( )
	 *
	 * @param String $action
	 * @param String $values
	 * @param String $context
	 * @return mixed // False on Failure, String on Success
	 * @access private
	 */
	private function buildQuery( $action, $values, $context )
	{
		switch ( @$context )
		{

			//------------------------------------------------------+

			case 'makeRepo':
				if (!empty( $this->repoPath ))
				{
					switch ( $action )
					{
						case 'comment':
						break;

						case 'get':
							$queryParts = "echo \"Status: Downloading Files...\" >> ".$this->repoPath.'.cacheinfo ; '; // Verbose
							foreach ($values as $value) {
								$url = parse_url($value['value']);

								switch ( $url['scheme'] )
								{
									case 'http':
									case 'https':
									case 'ftp':
										$queryParts .= 'wget --content-disposition -nv -q -o /dev/null -N -P '.$this->repoPath.' '.$value['value'].' ; '; // Get File
									break;

									case 'local':
										$source = '/'; // Root (base path)
										$source .=	str_replace( "\\", "/",
														substr( $value['value'], 8 )
													);
										$queryParts .= 'cp -rf '.trim($source).' '.$this->repoPath.' ; '; // Force Copy from SOURCE to DEST
									break;

									default:
										return FALSE;
									break;
								}
							}
							$queryParts .= "echo \"Status: Download Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'untargz':
							$queryParts = "echo \"Status: Decompressing Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'tar -C '.$this->repoPath.' -xzf '.$this->repoPath.$value['value'].' ; '; // Decompress + extract (gzip)
							}
							$queryParts .= "echo \"Status: Decompress Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'move':
							$queryParts = "echo \"Status: Moving Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								list($source, $dest) = explode(',', $value['value']);

								$queryParts .= 'mv -f '.$this->repoPath.trim($source).' '.$this->repoPath.trim($dest).' ; '; // Force Move from SOURCE to DEST
							}
							$queryParts .= "echo \"Status: Move Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'rename':
							$queryParts = "echo \"Status: Renaming Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								list($source, $dest) = explode(',', $value['value']);

								$queryParts .= 'mv '.$this->repoPath.trim($source).' '.$this->repoPath.trim($dest).' ; '; // Rename
							}
							$queryParts .= "echo \"Status: Rename Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'copy':
							$queryParts = "echo \"Status: Copying Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								list($source, $dest) = explode(',', $value['value']);

								$queryParts .= 'cp -rf '.$this->repoPath.trim($source).' '.$this->repoPath.trim($dest).' ; '; // Force Copy from SOURCE to DEST
							}
							$queryParts .= "echo \"Status: Copy Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'chmodx':
							$queryParts = "echo \"Status: CHMODing+x Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'chmod +x '.$this->repoPath.$value['value'].' ; '; // Allow file or folder to be executed by the user
							}
							$queryParts .= "echo \"Status: CHMOD+x Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'mkfile':
							$queryParts = "echo \"Status: Making Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'touch '.$this->repoPath.$value['value'].' ; '; // Create new empty file (not recursive)
							}
							$queryParts .= "echo \"Status: Make Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'mkdir':
							$queryParts = "echo \"Status: Making Directories...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'mkdir -p '.$this->repoPath.$value['value'].' ; '; // Create new directory
							}
							$queryParts .= "echo \"Status: Make Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'delete':
							$queryParts = "echo \"Status: Deleting Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'rm -rf '.$this->repoPath.$value['value'].' ; '; // Delete file or folder
							}
							$queryParts .= "echo \"Status: Delete Done\" >> ".$this->repoPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						default:
							return FALSE;
						break;
					}
				}
			break;

			//------------------------------------------------------+

			case 'installGame':
			case 'updateGame':
				if (!empty( $this->gameServerPath ))
				{
					switch ( $action )
					{
						case 'comment':
						break;

						case 'get':
							$queryParts = "echo \"Status: Downloading Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$url = parse_url($value['value']);

								switch ( $url['scheme'] )
								{
									case 'http':
									case 'https':
									case 'ftp':
										$queryParts .= 'wget --content-disposition -nv -q -o /dev/null -N -P '.$this->gameServerPath.' '.$value['value'].' ; ';
									break;

									case 'local':
										$source = '/'; // Root (base path)
										$source .=	str_replace( "\\", "/",
														substr( $value['value'], 8 )
													);
										$queryParts .= 'cp -rf '.trim($source).' '.$this->gameServerPath.' ; '; // Force Copy from SOURCE to DEST
									break;

									default:
										return FALSE;
									break;
								}
							}
							$queryParts .= "echo \"Status: Download Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'untargz':
							$queryParts = "echo \"Status: Decompressing Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'tar -C '.$this->gameServerPath.' -xzf '.$this->gameServerPath.$value['value'].' ; '; // Decompress + extract (gzip)
							}
							$queryParts .= "echo \"Status: Decompress Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'move':
							$queryParts = "echo \"Status: Moving Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								list($source, $dest) = explode(',', $value['value']);

								$queryParts .= 'mv -f '.$this->gameServerPath.trim($source).' '.$this->gameServerPath.trim($dest).' ; '; // Force Move from SOURCE to DEST
							}
							$queryParts .= "echo \"Status: Move Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'rename':
							$queryParts = "echo \"Status: Renaming Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								list($source, $dest) = explode(',', $value['value']);

								$queryParts .= 'mv '.$this->gameServerPath.trim($source).' '.$this->gameServerPath.trim($dest).' ; '; // Rename
							}
							$queryParts .= "echo \"Status: Rename Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'copy':
							$queryParts = "echo \"Status: Copying Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								list($source, $dest) = explode(',', $value['value']);

								$queryParts .= 'cp -rf '.$this->gameServerPath.trim($source).' '.$this->gameServerPath.trim($dest).' ; '; // Force Copy from SOURCE to DEST
							}
							$queryParts .= "echo \"Status: Copy Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'chmodx':
							$queryParts = "echo \"Status: CHMODing+x Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'chmod +x '.$this->gameServerPath.$value['value'].' ; '; // Allow file or folder to be executed by the user
							}
							$queryParts .= "echo \"Status: CHMOD+x Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'mkfile':
							$queryParts = "echo \"Status: Making Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'touch '.$this->gameServerPath.$value['value'].' ; '; // Create new empty file (not recursive)
							}
							$queryParts .= "echo \"Status: Make Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'mkdir':
							$queryParts = "echo \"Status: Making Directories...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'mkdir -p '.$this->gameServerPath.$value['value'].' ; '; // Create new directory
							}
							$queryParts .= "echo \"Status: Make Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						case 'delete':
							$queryParts = "echo \"Status: Deleting Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							foreach ($values as $value) {
								$queryParts .= 'rm -rf '.$this->gameServerPath.$value['value'].' ; '; // Delete file or folder
							}
							$queryParts .= "echo \"Status: Delete Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
							return $queryParts;
						break;

						//------------------------------------------------------------------------------------------------------------+
						// GAME SERVERS CASES

						// [C]reate
						case 'rsync_c':
							if (!empty( $this->repoPath )) {
								$queryParts = "echo \"Status: Installing Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';

								$exclusion = '';
								foreach ($values as $value)
								{
									if ( !empty($value['value']) ) {

										$excludedParts = explode( ',', $value['value'] );

										foreach ($excludedParts as $excludedPart)
										{
											$exclusion .= ' --exclude '.trim($excludedPart);
										}
									}
								}

								$source = $this->repoPath;
								$dest = $this->gameServerPath;

								$queryParts .= 'rsync -arv --delete --exclude .cacheinfo --exclude .cachelock* '.trim($exclusion).' '.trim($source).' '.trim($dest).' ; '; // Install Game Server From Game Repository

								$queryParts .= "echo \"Status: Installation Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
								return $queryParts;
							}
							return FALSE;
						break;

						// [U]pdate
						case 'rsync_u':
							if (!empty( $this->repoPath )) {
								$queryParts = "echo \"Status: Updating Files...\" >> ".$this->gameServerPath.'.cacheinfo ; ';

								$exclusion = '';
								foreach ($values as $value)
								{
									if ( !empty($value['value']) ) {

										$excludedParts = explode( ',', $value['value'] );

										foreach ($excludedParts as $excludedPart)
										{
											$exclusion .= ' --exclude '.trim($excludedPart);
										}
									}
								}

								$source = $this->repoPath;
								$dest = $this->gameServerPath;

								$queryParts .= 'rsync -arv --update --exclude .cacheinfo --exclude .cachelock* '.trim($exclusion).' '.trim($source).' '.trim($dest).' ; '; // Update Game Server From Game Repository

								$queryParts .= "echo \"Status: Update Done\" >> ".$this->gameServerPath.'.cacheinfo ; ';
								return $queryParts;
							}
							return FALSE;
						break;

						//------------------------------------------------------------------------------------------------------------+

						default:
							return FALSE;
						break;
					}
				}
			break;

			//------------------------------------------------------+

		}
	}

	/**
	 * Execute Query On Remote Host
	 * The actions are saved as a script, then executed.
	 * Actions are wrapped into a Screen.
	 *
	 * @see: GameInstaller::makeRepo( )
	 * @see: GameInstaller::deleteRepo( )
	 * @see: GameInstaller::buildQuery( $action, $values )
	 *
	 * @param String $query
	 * @param String $context
	 * @return void
	 * @access private
	 */
	private function executeQuery( $query, $context )
	{
		if (!empty( $query ))
		{

			switch ( @$context )
			{
				case 'makeRepo':
					if (!empty( $this->repoPath ))
					{
						$uid = substr(uniqid(), 6, 8);

						$this->sshConnection->exec( "echo \"".addslashes($query)."\" > ".$this->repoPath.'.cachescript ; chmod +x '.$this->repoPath.'.cachescript' ); // Create install script

						$this->sshConnection->exec( 'screen -AdmS GameInstaller.Operation.'.$uid.' sh '.$this->repoPath.'.cachescript' ); // Start cooking...

						$this->sshConnection->exec( "echo \"".$uid."\" > ".$this->repoPath.'.cacheuid' ); // Store screen uid
						// Done
					}
				break;

				//------------------------------------------------------+

				case 'installGame':
				case 'updateGame':
					if ( !empty( $this->gameServerPath ) && (!empty( $this->repoPath )) )
					{
						$uid = substr(uniqid(), 6, 8);

						$query =
							"echo \"Cache Repository Locked\" > ".$this->repoPath.'.cachelock-'.$uid.' ; '.
							$query.
							"rm ".$this->repoPath.'.cachelock-'.$uid.' ; ';

						$this->sshConnection->exec( "echo \"".addslashes($query)."\" > ".$this->gameServerPath.'.cachescript ; chmod +x '.$this->gameServerPath.'.cachescript' ); // Create install script

						$this->sshConnection->exec( 'screen -AdmS GameInstaller.Operation.'.$uid.' sh '.$this->gameServerPath.'.cachescript' ); // Start cooking...

						$this->sshConnection->exec( "echo \"".$uid."\" > ".$this->gameServerPath.'.cacheuid' ); // Store screen uid
						// Done
					}
				break;
			}

		}
	}

	/**
	 * Abort Current Actions For The Selected Context
	 *
	 * @param String $context
	 * @return void
	 * @access public
	 */
	public function abortOperation( $context )
	{

		switch ( @$context )
		{
			case 'makeRepo':
				if (!empty( $this->repoPath ))
				{
					if (trim( $this->sshConnection->exec('test -f '.$this->repoPath.".cacheuid && echo 'true' || echo 'false'") ) == 'true')
					{
						// The screen is alive...
						$uid = trim( $this->sshConnection->exec( 'tail -n 1 '.$this->repoPath.'.cacheuid' ) ); // Get Screen ID

						$this->sshConnection->exec( 'screen -S GameInstaller.Operation.'.$uid." -p 0 -X stuff \"\"`echo -ne '\003'`" ); // Kill Screen

						$this->sshConnection->exec( "echo \"Status: Aborted\" >> ".$this->repoPath.'.cacheinfo ; ' ); // Log

						// Clean Up
						$this->sshConnection->exec( "rm ".$this->repoPath.'.cachelock ; ' ); // Delete lock file
						$this->sshConnection->exec( "rm ".$this->repoPath.'.cachescript ; ' ); // Delete install script
						$this->sshConnection->exec( "rm ".$this->repoPath.'.cacheuid ; ' ); // Delete screen uid

						// Done
					}
				}
			break;

			//------------------------------------------------------+

			case 'installGame':
			case 'updateGame':
				if ( !empty( $this->gameServerPath ) && (!empty( $this->repoPath )) )
				{
					if (trim( $this->sshConnection->exec('test -f '.$this->gameServerPath.".cacheuid && echo 'true' || echo 'false'") ) == 'true')
					{
						$uid = trim( $this->sshConnection->exec( 'tail -n 1 '.$this->gameServerPath.'.cacheuid' ) ); // Get Screen ID

						$this->sshConnection->exec( 'screen -S GameInstaller.Operation.'.$uid." -p 0 -X stuff \"\"`echo -ne '\003'`" ); // Kill Screen

						$this->sshConnection->exec( "echo \"Status: Aborted\" >> ".$this->gameServerPath.'.cacheinfo ; ' ); // Log

						// Clean Up
						$this->sshConnection->exec( "rm ".$this->repoPath.'.cachelock-'.$uid.' ; ' ); // Delete lock file
						$this->sshConnection->exec( "rm ".$this->gameServerPath.'.cachescript ; ' );
						$this->sshConnection->exec( "rm ".$this->gameServerPath.'.cacheuid ; ' );

						// Done
					}
				}
			break;
		}

	}

	/**
	 * Check If An action Is In Progress For The Selected Context
	 *
	 * @param String $context
	 * @return bool
	 * @access public
	 */
	public function checkOperation( $context )
	{

		switch ( @$context )
		{
			case 'makeRepo':
				if (!empty( $this->repoPath ))
				{
					if (intval(trim( $this->sshConnection->exec('cd '.$this->repoPath."; ls -a | grep -c '.cachelock*'") )) != 0) {
						// Operation In Progress : Game Repo Locked
						return TRUE;
					}
					return FALSE;
				}
			break;

			//------------------------------------------------------+

			case 'installGame':
			case 'updateGame':
				if (!empty( $this->gameServerPath ))
				{
					if (trim( $this->sshConnection->exec('test -f '.$this->gameServerPath.".cacheuid && echo 'true' || echo 'false'") ) == 'true') {
						return TRUE;
					}
					return FALSE;
				}
			break;
		}

	}


}
?>