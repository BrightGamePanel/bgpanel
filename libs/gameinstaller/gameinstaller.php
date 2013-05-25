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
 * @version		(Release 0) DEVELOPER BETA 7
 * @link		http://www.bgpanel.net/
 */


/**
 *	@Class:		Game Installer Main Class
 *	@Version:	1.0
 *	@Date:		22/05/2013
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
	 * Get Repository Information
	 *
	 * @param void
	 * @return mixed // False on Failure, Array() on Success
	 * @access public
	 */
	public function getRepoCacheInfo( )
	{
		if (!empty( $this->repoPath )) {
			// Test .cacheinfo exists
			if (trim( $this->sshConnection->exec('test -f '.$this->repoPath.".cacheinfo && echo 'true' || echo 'false'") ) == 'false') {
				return FALSE;
			}

			$info = array( 'size' => '', 'status' => '', 'mtime' => '');

			// Get last message
			$cache = trim( $this->sshConnection->exec('tail -n 1 '.$this->repoPath.'.cacheinfo') );

			$info['size'] = trim( $this->sshConnection->exec('du -sh '.$this->repoPath." | awk '{print $1}'") );

			if (strstr($cache, 'Status:') != FALSE) {
				$info['status'] = substr($cache, 8); // Remove "Status: " from string
				$info['mtime'] = time();
			}
			else {
				$info['status'] = 'Cache Ready';
				$info['mtime'] = substr($cache, 7); // Remove "mtime: " from string
			}

			return $info;
		}

		return FALSE;
	}

	/**
	 * Makes Game Cache Repository
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
					foreach ($this->actions['makeRepo'] as $action => $values) {
						$queryParts = $this->buildQuery( $action, $values );

						if ($queryParts == FALSE) {
							// Error while building query
							return FALSE;
						}

						$query .= $queryParts;
					}

					// Log Once Finished...
					$query .= "echo \"Status: GameInstaller::makeRepo( ) Completed\" >> ".$this->repoPath.'.cacheinfo ; ';
					$query .= "echo \"mtime: $(date +%s)\" >> ".$this->repoPath.'.cacheinfo ; '; // "Repository is Ready" marker

					$query .= "rm ".$this->repoPath.'.cachescript ; '; // Delete install script at the end
					$query .= "rm ".$this->repoPath.'.cacheuid ; '; // Delete screen uid

					$this->executeQuery( $query );

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Removes Game Cache Repository
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

			$this->executeQuery( $query );

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
	 *
	 * @param String $action
	 * @param String $values
	 * @return mixed // False on Failure, String on Success
	 * @access private
	 */
	private function buildQuery( $action, $values )
	{
		if (!empty( $this->repoPath )) {
			switch ( $action )
			{
				case 'comment':
				break;

				case 'get':
					$queryParts = "echo \"Status: Downloading Files...\" >> ".$this->repoPath.'.cacheinfo ; '; // Verbose
					foreach ($values as $value) {
						$url = parse_url($value['value']);

						if ( ($url['scheme'] == 'http') || ($url['scheme'] == 'https') ) {
							$queryParts .= 'wget -nv -q -o /dev/null -N -P '.$this->repoPath.' '.$value['value'].' ; '; // Get File
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

				case 'delete':
					$queryParts = "echo \"Status: Deleting Files...\" >> ".$this->repoPath.'.cacheinfo ; ';
					foreach ($values as $value) {
						$queryParts .= 'rm -rf '.$this->repoPath.$value['value'].' ; '; // Delete file or folder
					}
					$queryParts .= "echo \"Status: Delete Done\" >> ".$this->repoPath.'.cacheinfo ; ';
					return $queryParts;
				break;

/*
				case self::ac_mkdir:
					$dir = $this->formatPath($data[1]);
					if (!$dir){
						$this->logError('Error formating path: '.$data[1]);
						return false;
					}
					$ssh->exec('mkdir '.$dir);
					return true;
				break;

				case self::ac_rmdir:
					$dir = $this->formatPath($data[1]);
					if (!$dir){
						$this->logError('Error formating path: '.$data[1]);
						return false;
					}
					$ssh->exec('rm -rf '.$dir);
					return true;
				break;

				case self::ac_rmfile:
					$file = $this->formatPath($data[1]);
					if (!$file){
						$this->logError('Error formating path: '.$data[1]);
						return false;
					}
					$ssh->exec('rm -f '.$file);
					return true;
				break;

				case self::ac_rsync:
					$from = $this->formatPath($data[1]);
					$to = $this->formatPath($data[2]);
					if (!$from){
						$this->logError('Error formating path: '.$data[1]);
						return false;
					}
					if (!$to){
						$this->logError('Error formating path: '.$data[2]);
						return false;
					}
					$ssh->exec('rsync -a '.$from.' '.$to);
					return true;
				break;
*/

				default:
					return FALSE;
				break;
			}
		}
	}

	/**
	 * Execute Query On Remote Host
	 * The actions are saved as a script, then executed. Actions are wrapped into a Screen.
	 *
	 * @see: GameInstaller::makeRepo( )
	 * @see: GameInstaller::deleteRepo( )
	 * @see: GameInstaller::buildQuery( $action, $values )
	 *
	 * @param String $query
	 * @return void
	 * @access private
	 */
	private function executeQuery( $query )
	{
		if (!empty( $this->repoPath ) && !empty( $query )) {
			$this->sshConnection->exec( "echo \"".addslashes($query)."\" > ".$this->repoPath.'.cachescript ; chmod +x '.$this->repoPath.'.cachescript' ); // Create install script

			$uid = substr(uniqid(), 6, 8);
			$this->sshConnection->exec( 'screen -AdmS GameInstaller.Operation.'.$uid.' sh '.$this->repoPath.'.cachescript' ); // Start cooking...

			$this->sshConnection->exec( "echo \"".$uid."\" > ".$this->repoPath.'.cacheuid' ); // Store screen uid
			// Done
		}
	}

	/**
	 * Abort Current Actions For Current Repository
	 * Omg ! The screen is getting killed !!!
	 *
	 * @param void
	 * @return void
	 * @access public
	 */
	public function abortOperation( )
	{
		if (!empty( $this->repoPath )) {
			if (trim( $this->sshConnection->exec('test -f '.$this->repoPath.".cacheuid && echo 'true' || echo 'false'") ) == 'true') {
				// The screen is alive...
				$uid = trim( $this->sshConnection->exec( 'tail -n 1 '.$this->repoPath.'.cacheuid' ) ); // Get Screen ID

				$this->sshConnection->exec( 'screen -S GameInstaller.Operation.'.$uid." -p 0 -X stuff \"\"`echo -ne '\003'`" ); // After Kill Bill, now Kill Screen

				$this->sshConnection->exec( "echo \"Status: Aborted\" >> ".$this->repoPath.'.cacheinfo ; ' ); // Log
				// Done
			}
		}
	}











	public $insertmode		= 'preload';
	public $gamedir			= FALSE;

	function validateGPath($path)
	{
		$len = strlen ($path);
		if($len < 4){
			return false;
		}
		if(substr($path,$len-1,$len) != '/'){
			$path = $path.'/';
		}
		if(substr($path,0,1) != '/'){//we really need absolute paths!
			$path = '/'.$path;
		}
		return $path;
	}

	function formatPath($path)
	{
		$pref = substr($path,0,6);
		$rpath = substr($path,6);
		if(strcasecmp($pref,'repo:/') == 0){
			if($this->repodir == false) return false;
			return $this->repodir.$rpath;
		}
		if(strcasecmp($pref,'game:/') == 0){
			if($this->gamedir == false) return false;
			return $this->gamedir.$rpath;
		}
		return false;
	}

	//gamecmds


	public function setMode($mode){
		if(strcasecmp($mode,'preload') == 0){
			$this->insertmode = 'preload';
			return true;
		}
		if(strcasecmp($mode,'install') == 0){
			$this->insertmode = 'install';
			return true;
		}
		if(strcasecmp($mode,'update') == 0){
			$this->insertmode = 'update';
			return true;
		}
		return false;
	}

	public function download($fileurl,$path){
		$this->actions[$this->insertmode][] = [self::ac_download,$fileurl,$path];
	}
	public function makeDir($dir){
		$this->actions[$this->insertmode][] = [self::ac_mkdir,$dir];
	}
	public function destroyDir($dir){
		$this->actions[$this->insertmode][] = [self::ac_rmdir,$dir];
	}
	public function unTar($file,$dir){
		$this->actions[$this->insertmode][] = [self::ac_untar,$file,$dir];
	}
	public function unZip($file,$dir){
		$this->actions[$this->insertmode][] = [self::ac_unzip,$file,$dir];
	}
	public function move($from,$to){
		$this->actions[$this->insertmode][] = [self::ac_move,$from,$to];
	}
	public function rsync($from,$to){
		$this->actions[$this->insertmode][] = [self::ac_rsync,$from,$to];
	}
	public function delete($file){
		$this->actions[$this->insertmode][] = [self::ac_rmfile,$file];
	}







/*
	public function setGame($gamename){
		//just in case someone just want to check if there is a game install script...
		$this->gameset = false;
		$this->actions = array();
		$this->actions['preload'] = array();
		$this->actions['install'] = array();
		$this->actions['update'] = array();
		if(!file_exists (realpath(dirname(__FILE__)).'/games/'.strtolower($gamename).'.php')) return false;
		include('games/'.strtolower($gamename).'.php');
		return $this->gameset;
	}
*/


/*
	USELESS



	function isPreloaded($ssh){
		if($this->repodir!= false){
			$result = $ssh->exec('cat '.$this->repodir.'.cacheinfo');
			if(strstr($result,'No such file or directory') != false){
				return false;
			}
			return true;
			}else{
			return false;
		}
	}
*/


	public function unload($ssh){
		set_time_limit(0);
		if (is_object($ssh))
		{
			if($this->repodir != false){
				$ssh->exec('rm -rf '.$this->repodir);
				return true;
			}else{
				$this->logError('Invalid Game Cache Dir');
				return false;
			}
		}else{
			$this->logError('Invalid SSH Object');
			return false;
		}
	}

	public function install($ssh,$path,$clean,$update){
		set_time_limit(0);
		$path = $this->validateGPath($path);
		if(!$path){ //invalid game path? sorry no install..
			$this->logError('Invalid Game Path');
			return false;
		}
		$this->gamedir = $path;
		if (is_object($ssh))
		{
			if($this->repodir != false){
				if(!$this->gameset){
					$this->logError('Game not Set');
					return false;
				}
				if(!$this->isPreloaded($ssh)){
					$this->logError('Game not Preloaded');
					return false;
				}
				if($clean){
					$ssh->exec('rm -rf '.$path);
					$ssh->exec('mkdir '.$path);
				}
				$imode = 'install';
				if($update == true) $imode = 'update';
				foreach ($this->actions[$imode] as $data){
					if($this->executeAction($ssh,$data) == false){ // Some action did not executed properly STOP!
						return false;
					}
				}
				return true;
			}else{
				$this->logError('Invalid Game Cache Dir');
				return false;
			}
		}else{
			$this->logError('Invalid SSH Object');
			return false;
		}
	}
}
?>