<?php
/**
*
* phpBB File Check Hash Generator - Creates checksum packages for phpBB File Check
*
* PHP: >=8.0.0,<8.3.0
*
* @copyright (c) 2023 LukeWCS <phpBB.de>
* @license GNU General Public License, version 2 (GPL-2.0-only)
*
*/

# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames config

/*
 The static configuration can be defined here, which does not change with new phpBB versions.
 The dynamic configuration can then be passed via CLI. All config variables have the same name
 as their associated CLI parameters.

 Example:

 config: 'source-1' => ''
 CLI   : --source-1=""
*/

$config = [
/*-
 Primary source (ZIP or folder)
-*/
	'source-1'			=> '',

/*-
 Secondary source (ZIP or folder)
-*/
	'source-2'			=> '',

/*-
 The folder where the finished hash package ZIP will be created.
-*/
	'export-dir'		=> '',

/*-
 The folder within a ZIP, i.e. "phpBB3/". That could possibly change in 4.0.
-*/
	'zip-root'			=> 'phpBB3/',

/*-
 The label of the hash package. Required for various displays, including error messages.
 This is also used by “phpBB File Check”.
-*/
	'source-1-label'	=> 'phpBB.com',

/*-
 The label of the hash package. Required for various displays, including error messages.
 This is also used by “phpBB File Check”.
-*/
	'source-2-label'	=> 'phpBB-fr.com',

/*-
 Text file in which one RegEx expression can be entered per line.
 This file is only evaluated by "phpBB File Check" and is added to the hash package ZIP.
 The file name in the hash package ZIP is changed to "filecheck_ignore.txt".
-*/
	'ignore-file'		=> '',

/*-
 Text file in which one file/folder exception can be entered per line.
 This file is only evaluated by "phpBB File Check" and is added to the hash package ZIP.
 The file name in the hash package ZIP is changed to "filecheck_exceptions.txt".
-*/
	'exceptions-file'	=> 'filecheck_exceptions(example_fr).txt',

/*-
 The filename of the hash package ZIP to create, i.e. "phpBB_FileCheck_MD5_$PHPBB_VER$".
 $PHPBB_VER$ will be replaced with the phpBB version.
-*/
	'hash-zip-name'		=> 'phpBB_FileCheck_MD5_$PHPBB_VER$_fr',

/*-
 For the date display when checking the hash package ZIP.
-*/
	'timezone-id'		=> 'Europe/Paris',
];

# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames
