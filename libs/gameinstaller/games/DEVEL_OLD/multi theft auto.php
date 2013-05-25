<?php
	/*	Game Install Script
	Every directory is gameserver path relative,
	ExternalFiles are cached if they aren't temporal and saved in home of SSH User,
	use repo:/
	or  game:/
	functions:
		download(url,destination);
		makeDir(directory);
		unTar(filename,extractpath); 
		unZip(filename,extractpath); 
		rsync(frompath,topath);
		destroyDir(path); 
		execute(file);
		delete(file);
	------
	*/
	
	/****** PRELOAD ******/
	$this->setMode('preload');
	$this->download('http://linux.mtasa.com/dl/131/multitheftauto_linux-1.3.1.tar.gz','repo:/');
	$this->download('http://linux.mtasa.com/dl/131/baseconfig.tar.gz','repo:/');
	$this->download('https://mtasa-resources.googlecode.com/files/mtasa-resources-r924.zip','repo:/');
	
	$this->unZip('repo:/mtasa-resources-r924.zip','repo:/resources/');
	$this->unTar('repo:/multitheftauto_linux-1.3.1.tar.gz','repo:/');
	$this->unTar('repo:/baseconfig.tar.gz','repo:/');
	
	$this->delete('repo:/mtasa-resources-r924.zip');
	$this->delete('repo:/multitheftauto_linux-1.3.1.tar.gz');
	$this->delete('repo:/baseconfig.tar.gz');
	/****** INSTALL ******/
	$this->setMode('install');
	$this->makeDir('game:/mods/');
	$this->makeDir('game:/mods/deathmatch/');
	$this->makeDir('game:/mods/deathmatch/resources/');
	$this->rsync('repo:/multitheftauto_linux-1.3.1/*','game:/');
	$this->rsync('repo:/baseconfig/*','game:/mods/deathmatch/');
	$this->rsync('repo:/resources/*','game:/mods/deathmatch/resources/');
	/****** UPDATE ******/ 
	$this->setMode('update');
	$this->rsync('repo:/multitheftauto_linux-1.3.1/*','game:/');
	/* Just tell our gameinstaller we have a gaminstall script loaded */
	$this->gameset = true;
?>