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



$title = 'Cron Job';
$page = 'cron';

set_time_limit(60);

chdir(realpath(dirname(__FILE__)));
set_include_path(realpath(dirname(__FILE__)));

require('../configuration.php');

/**
 * CRYPT_KEY is the Passphrase Used to Cipher/Decipher SSH Passwords
 * The key is stored into the file: ".ssh/passphrase"
 */
define('CRYPT_KEY', file_get_contents("../.ssh/passphrase"));


require('../includes/functions.php');
require('../includes/mysql.php');
require_once('../libs/lgsl/lgsl_class.php');
require_once('../libs/phpseclib/SSH2.php');
require_once("../libs/phpseclib/Crypt/AES.php");


//Checks if the install directory has been removed
if (is_dir("../install"))
{
	die();
}

//Check PASSPHRASE file CHMOD
$perms = substr(sprintf('%o', fileperms('../.ssh/passphrase')), -4);
###
if ($perms != '0644')
{
	die();
}
unset($perms);


/**
 * GET BrightGamePanel Database INFORMATION
 * Load 'values' from `config` Table
 */
$panelVersion = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" );
$maintenance = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'maintenance' LIMIT 1" );


/**
 * GET BGP CORE FILES INFORMATION
 * Load version.xml (ROOT/.version/version.xml)
 */
$bgpCoreInfo = simplexml_load_file('../.version/version.xml');


/**
 * VERSION CONTROL
 * Check that core files are compatible with the current BrightGamePanel Database
 */
if ($panelVersion['value'] != $bgpCoreInfo->{'version'})
{
	die();
}

/**
 * MAINTENANCE CHECKER
 */
if ($maintenance['value']  == '1')
{
	die();
}


unset($panelVersion, $maintenance);


//------------------------------------------------------------------------------------------------------------+

query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '".date('Y-m-d H:i:s')."' WHERE `setting` = 'lastcronrun'" );

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

  lgsl_database();

//------------------------------------------------------------------------------------------------------------+
// CRON SETTINGS:

  $lgsl_config['cache_time'] = 60; // HOW OLD CACHE MUST BE BEFORE IT NEEDS REFRESHING
  $request = "sep";                // WHAT TO PRE-CACHE: [s] = BASIC INFO [e] = SETTINGS [p] = PLAYERS

//------------------------------------------------------------------------------------------------------------+

  $mysql_query  = "SELECT `type`,`ip`,`c_port`,`q_port`,`s_port` FROM `{$lgsl_config['db']['prefix']}{$lgsl_config['db']['table']}` WHERE `disabled`=0 ORDER BY `cache_time` ASC";
  $mysql_result = mysql_query($mysql_query) or die(mysql_error());

  while($mysql_row = mysql_fetch_array($mysql_result, MYSQL_ASSOC))
  {
    echo str_pad(lgsl_timer("taken"),  8,  " ").":".
         str_pad($mysql_row['type'],   15, " ").":".
         str_pad($mysql_row['ip'],     30, " ").":".
         str_pad($mysql_row['c_port'], 6,  " ").":".
         str_pad($mysql_row['q_port'], 6,  " ").":".
         str_pad($mysql_row['s_port'], 12, " ")."\r\n";

    lgsl_query_cached($mysql_row['type'], $mysql_row['ip'], $mysql_row['c_port'], $mysql_row['q_port'], $mysql_row['s_port'], $request);

  }

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

$data['boxids'] = NULL;
$data['boxnetstat'] = NULL;
$data['players'] = NULL;
$data['bw_rx'] = NULL;
$data['bw_tx'] = NULL;
$data['cpu'] = NULL;
$data['ram'] = NULL;
$data['loadavg'] = NULL;
$data['hdd'] = NULL;


if (query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box` ORDER BY `boxid`" ) != 0)
{
	$boxes = mysql_query( "SELECT `boxid`, `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box`" );

	while ($rowsBoxes = mysql_fetch_assoc($boxes))
	{
		$ssh = new Net_SSH2($rowsBoxes['ip'].':'.$rowsBoxes['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);

		if (!$ssh->login($rowsBoxes['login'], $aes->decrypt($rowsBoxes['password'])))
		{
			//Connection Error!

			$data['boxids'] .= $rowsBoxes['boxid'].';';
			$data['boxnetstat'] .= '0;';
			$data['players'] .= '0;';
			$data['bw_rx'] .= '0;';
			$data['bw_tx'] .= '0;';
			$data['cpu'] .= ';';
			$data['ram'] .= ';';
			$data['loadavg'] .= ';';
			$data['hdd'] .= ';';

			query_basic( "UPDATE `".DBPREFIX."box` SET
				`bw_rx` = '0',
				`bw_tx` = '0',
				`cpu` = 'NULL;0;0',
				`ram` = '0;0;0;0',
				`loadavg` = '0',
				`hostname` = 'NULL',
				`os` = 'NULL',
				`date` = 'NULL',
				`kernel` = 'NULL',
				`arch` = 'NULL',
				`uptime` = 'NULL',
				`swap` = '0;0;0;0',
				`hdd` = '0;0;0;0' WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

		}
		else
		{

			//------------------------------------------------------------------------------------------------------------+
			//We have to clean screenlog.0 files

			$servers = mysql_query( "SELECT `homedir` FROM `".DBPREFIX."server` WHERE `boxid` = '".$rowsBoxes['boxid']."' " );
			###
			while ($rowsServers = mysql_fetch_assoc($servers))
			{
				$ssh->exec('cd '.$rowsServers['homedir'].'; tail -n500 screenlog.0 > screenlog.1; cat screenlog.1 > screenlog.0; rm screenlog.1'."\n");
			}
			unset($servers);

			//------------------------------------------------------------------------------------------------------------+
			//Retrieves information from box

			$ifaceOutput = $ssh->exec('netstat -r | grep default'."\n");
			###
			//Preprocessing
				$netstatTable = explode(' ', $ifaceOutput);
				foreach ($netstatTable as $key => $value)
				{
					if ( preg_match("#^eth[0-9]#", $value) )
					{
						$iface = trim($value); //Correct interface
					}
				}
				if (!isset($iface))
				{
					$iface = 'eth0'; //Default value if unable to retrieve it from netstat
				}
			###
			$bw_rx = $ssh->exec('cat /sys/class/net/'.$iface.'/statistics/rx_bytes'."\n");
			$bw_tx = $ssh->exec('cat /sys/class/net/'.$iface.'/statistics/tx_bytes'."\n");
			unset($ifaceOutput, $netstatTable, $iface);

			$cpuOutput = $ssh->exec('top -b -n 2 | grep Cpu | tail -n+2'."\n");
			$procModelOutput = $ssh->exec('cat /proc/cpuinfo | grep \'model name\''."\n");
			$procCoresOutput = $ssh->exec('cat /proc/cpuinfo | grep \'cpu cores\''."\n");

			$memOutput = $ssh->exec('free -m | grep \'buffers/cache\''."\n");

			$loadavgOutput = $ssh->exec('top -b -n 1 | grep \'load average\''."\n");

			$hostname = trim($ssh->exec('hostname'."\n"));
			$os = trim($ssh->exec('uname -o'."\n"));
			$date = trim($ssh->exec('date'."\n"));
			$kernel = trim($ssh->exec('uname -r'."\n"));
			$arch = trim($ssh->exec('uname -m'."\n"));

			$uptimeOutput = $ssh->exec('cat /proc/uptime'."\n");

			$swapOutput = $ssh->exec('free -m | grep Swap'."\n");

			$hddOutput = $ssh->exec('df -h /'."\n");

			//Processing
				$cpuTable = explode(',', $cpuOutput);
				$cpuTable[0] = str_replace('Cpu(s):', '', $cpuTable[0]); //Remove useless chars
				$cpuTable[0] = str_replace('%us', '', $cpuTable[0]);
				###
				$procModelTable = explode("\n", $procModelOutput);
				$procModelTable[0] = str_replace("model name\t: ", '', $procModelTable[0]);
				###
				$procCoresTable = explode("\n", $procCoresOutput);
				$procCoresTable[0] = str_replace("cpu cores\t: ", '', $procCoresTable[0]);
				###
				$cpu = trim($procModelTable[0]).';'.trim($procCoresTable[0]).';'.trim($cpuTable[0]); // Proc Model ; Proc Num Cores ; CPU Usage in percentage
				unset($cpuOutput, $procModelOutput, $procModelTable, $procCoresOutput, $procCoresTable);
			###
				$memTable = explode(' ', $memOutput);
				foreach ($memTable as $key => $value)
				{
					if (is_numeric(trim($value)))
					{
						$memTable2[] = trim($value); //Correct Arr
					}
				}
				$mem = ( $memTable2[0] + $memTable2[1] ).';'.$memTable2[0].';'.$memTable2[1].';'.round( (($memTable2[0] * 100) / ($memTable2[0] + $memTable2[1])), 2); // Total Mem Mo ; used Mo ; free Mo ; percentage of used ram
				unset($memOutput, $memTable);
			###
				$loadavgTable = explode(',', $loadavgOutput);
				$loadavgTable[4] = trim($loadavgTable[4]);
				$loadavg = $loadavgTable[4];
				unset($loadavgOutput, $loadavgTable);
			###
				$uptimeTable = explode(' ', $uptimeOutput);
				$uptimeMin = $uptimeTable[0] / 60;
				if ($uptimeMin > 59)
				{
					$uptimeH = $uptimeMin / 60;
					if ($uptimeH > 23)
					{
						$uptimeD = $uptimeH / 24;
					}
					else
					{
						$uptimeD = 0;
					}
				}
				else
				{
					$uptimeH = 0;
					$uptimeD = 0;
				}
				$uptime = floor($uptimeD).' days '.($uptimeH % 24).' hours '.($uptimeMin % 60).' minutes ';
				unset($uptimeOutput, $uptimeTable, $uptimeMin, $uptimeH, $uptimeD);
			###
				$swapTable = explode(' ', $swapOutput);
				foreach ($swapTable as $key => $value)
				{
					if (is_numeric(trim($value)))
					{
						$swapTable2[] = trim($value); //Correct Arr
					}
				}
				$swap = $swapTable2[0].';'.$swapTable2[1].';'.$swapTable2[2].';'.round(( $swapTable2[1] * 100) / ( $swapTable2[0] ), 2); // Total Swap Mo ; used Mo ; free Mo ; percentage of used Swap
				unset($swapOutput, $swapTable, $swapTable2);
			###
				$hddTable = explode(' ', $hddOutput);
				foreach ($hddTable as $key => $value)
				{
					if ( (preg_match("#^[0-9]#", $value) && (preg_match("#M$#", $value) || preg_match("#G$#", $value) || preg_match("#T$#", $value) || preg_match("#%$#", $value))) )
					{
						$hddTable2[] = trim($value); //Correct Arr
					}
				}
				$hdd = $hddTable2[0].';'.$hddTable2[1].';'.$hddTable2[2].';'.substr($hddTable2[3], 0, (strlen($hddTable2[3]) - 1)); // Total HDD Mem ; used ; free ; percentage of used HDD
				unset($hddOutput, $hddTable);

			//------------------------------------------------------------------------------------------------------------+
			//Retrieves num players of the box

			$p = 0;

			$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

			while ($rowsServers = mysql_fetch_assoc($servers))
			{
				$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1");

				if ($rowsServers['status'] == 'Active' && $rowsServers['panelstatus'] == 'Started')
				{
					//---------------------------------------------------------+
					//Querying the server
					include_once("../libs/lgsl/lgsl_class.php");

					$cache = lgsl_query_cached($type['querytype'], $rowsBoxes['ip'], $rowsServers['port'], $rowsServers['queryport'], 0, 'sp');

					$p = $p + $cache['s']['players'];

					unset($cache);
					//---------------------------------------------------------+
				}

				unset($type);
			}
			unset($servers);

			//------------------------------------------------------------------------------------------------------------+
			//Data

			$data['boxids'] .= $rowsBoxes['boxid'].';';
			$data['boxnetstat'] .= '1;';
			$data['players'] .= $p.';';
			$data['bw_rx'] .= $bw_rx.';';
			$data['bw_tx'] .= $bw_tx.';';
			$data['cpu'] .= trim($cpuTable[0]).';';
			$data['ram'] .= round( (($memTable2[0] * 100) / ($memTable2[0] + $memTable2[1])), 2).';';
			$data['loadavg'] .= $loadavg.';';
			$data['hdd'] .= substr($hddTable2[3], 0, (strlen($hddTable2[3]) - 1)).';';

			unset($p, $cpuTable, $memTable2, $hddTable2);

			//------------------------------------------------------------------------------------------------------------+
			//Update DB for the current box

			query_basic( "UPDATE `".DBPREFIX."box` SET
				`bw_rx` = '".$bw_rx."',
				`bw_tx` = '".$bw_tx."',
				`cpu` = '".$cpu."',
				`ram` = '".$mem."',
				`loadavg` = '".$loadavg."',
				`hostname` = '".$hostname."',
				`os` = '".$os."',
				`date` = '".$date."',
				`kernel` = '".$kernel."',
				`arch` = '".$arch."',
				`uptime` = '".$uptime."',
				`swap` = '".$swap."',
				`hdd` = '".$hdd."' WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

			unset($bw_rx, $bw_tx, $cpu, $mem, $loadavg, $hostname, $os, $date, $kernel, $arch, $uptime, $swap, $hdd);
		}

		sleep(1);
	}
	unset($boxes);

	//------------------------------------------------------------------------------------------------------------+
	//Update dataBox table

	$data['timestamp'] = time();

	query_basic( "INSERT INTO `".DBPREFIX."boxData` SET
		`timestamp` = '".$data['timestamp']."',
		`boxids` = '".$data['boxids']."',
		`boxnetstat` = '".$data['boxnetstat']."',
		`players` = '".$data['players']."',
		`bw_rx` = '".$data['bw_rx']."',
		`bw_tx` = '".$data['bw_tx']."',
		`cpu` = '".$data['cpu']."',
		`ram` = '".$data['ram']."',
		`loadavg` = '".$data['loadavg']."',
		`hdd` = '".$data['hdd']."'" );

}

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/**
 * 'pChart' table operations
 */

	//---------------------------------------------------------+
	// Remove old data

	$time = time() - (60 * 60 * 24 * 7 + 3600);
	$numOldData = mysql_num_rows(mysql_query( "SELECT `id` FROM `".DBPREFIX."boxData` WHERE `timestamp` < '".$time."'" ));

	if ($numOldData > 0)
	{
		$oldData = mysql_query( "SELECT `id` FROM `".DBPREFIX."boxData` WHERE `timestamp` < '".$time."'" );
		while ($rowsData = mysql_fetch_assoc($oldData))
		{
			query_basic( "DELETE FROM `".DBPREFIX."boxData` WHERE `id` = '".$rowsData['id']."'" );
		}
		unset($oldData);
	}

	//---------------------------------------------------------+
	// Optimize table

	$sql = "OPTIMIZE TABLE `".DBPREFIX."boxData`";

	query_basic( $sql );

	unset($sql);

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/**
 * 'log' table operations
 */

	//---------------------------------------------------------+
	// Optimize table

	$sql = "OPTIMIZE TABLE `".DBPREFIX."log`";

	query_basic( $sql );

	unset($sql);

//------------------------------------------------------------------------------------------------------------+
?>