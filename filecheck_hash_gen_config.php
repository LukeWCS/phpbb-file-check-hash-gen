<?php
/**
*
* phpBB File Check Hash Generator - Generates checksum files for the phpBB packages
*
* PHP: >=7.1.0,<8.3.0
*
* @copyright (c) 2023 LukeWCS <phpBB.de>
* @license GNU General Public License, version 2 (GPL-2.0-only)
*
*/

# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames config

$config = [
	'source_primary'		=> '',		// CLI parameter: --source-1=""
	'source_secondary'		=> '',		// CLI parameter: --source-2=""
	'folder_export'			=> '',		// CLI parameter: --export-to=""

	'zip_phpbb_root'		=> 'phpBB3/',

	'name_primary'			=> 'phpBB.com',
	'name_secondary'		=> 'phpBB.de',

	'file_ignore'			=> '',
	'file_exceptions'		=> 'filecheck_exceptions.txt',

	'hash_package_zip_file'	=> 'phpBB_FileCheck_MD5_$phpbb_ver$',
];

# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames
