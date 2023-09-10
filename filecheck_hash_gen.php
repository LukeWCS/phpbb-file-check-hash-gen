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

# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames config
# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames cli_options

/*
* Initialization
*/
include __DIR__ . '/filecheck_hash_gen_config.php';

$ver				= '0.4.1';
$title				= "phpBB File Check Hash Generator v{$ver}";
$lf					= "\n";
$start_time			= microtime(true);

echo $title . $lf ;
echo str_repeat('=', strlen($title)) . $lf . $lf;

/*
* CLI interface
*/
$cli_options		= cli_init();

if (cli_is_option('h'))
{
	cli_help();
}

if (cli_is_option('source-1'))
{
	$config['source_primary'] = cli_get_option('source-1');
}
else if (empty($config['source_primary']))
{
	terminate('Parameter for the primary package is not set');
}

if (cli_is_option('source-2'))
{
	$config['source_secondary'] = cli_get_option('source-2');
}
else if (empty($config['source_secondary']))
{
	terminate('Parameter for the secondary package is not set');
}

if (cli_is_option('export-to'))
{
	$config['folder_export'] = cli_get_option('export-to');
}
else if (empty($config['folder_export']))
{
	$config['folder_export'] = __DIR__;
}

/*
* Add final path separator
*/
if (!is_zip($config['source_primary']))
{
	$config['source_primary'] = add_dir_separator($config['source_primary']);
}

if (!is_zip($config['source_secondary']))
{
	$config['source_secondary'] = add_dir_separator($config['source_secondary']);
}
$config['folder_export'] = add_dir_separator($config['folder_export']);

/*
* Check the phpBB packages and determine their versions (ZIP or folder)
*/
$phpbb_version_primary = get_package_version($config['source_primary'], $config['name_primary'], $config['zip_phpbb_root']);
$phpbb_version_secondary = get_package_version($config['source_secondary'], $config['name_secondary'], $config['zip_phpbb_root']);

$checksum_file_primary = 'filecheck_' . $phpbb_version_primary . '.md5';
$checksum_file_diff = 'filecheck_' . $phpbb_version_secondary . '_diff' . '.md5';

echo sprintf('Version primary    : %1$s (%2$s)', $phpbb_version_primary, $config['name_primary']) . $lf;
echo sprintf('Version secondary  : %1$s (%2$s)', $phpbb_version_secondary, $config['name_secondary']) . $lf;

/*
* Check if both packages have the same version
*/
if ($phpbb_version_secondary != $phpbb_version_primary)
{
	terminate("{$config['name_primary']} package and {$config['name_secondary']} package has different versions");
}

/*
* Check for ignore and exception files if defined.
*/
if ($config['file_ignore'] != '' && !file_exists($config['file_ignore']))
{
	terminate("{$config['file_ignore']} not found");
}

if ($config['file_exceptions'] != '' && !file_exists($config['file_exceptions']))
{
	terminate("{$config['file_exceptions']} not found");
}

/*
* Create an internal checksum list of the primary package (ZIP or folder)
*/
if (is_zip($config['source_primary']))
{
	$checksums_primary = get_checksums_from_zip($config['source_primary'], $config['zip_phpbb_root']);
}
else
{
	$checksums_primary = get_checksums_from_folder($config['source_primary']);
}
$count_primary = count($checksums_primary);

/*
* Create an internal checksum list of the secondary package (ZIP or folder)
*/
if (is_zip($config['source_secondary']))
{
	$checksums_secondary = get_checksums_from_zip($config['source_secondary'], $config['zip_phpbb_root']);
}
else
{
	$checksums_secondary = get_checksums_from_folder($config['source_secondary']);
}
$count_secondary = count($checksums_secondary);

/*
* Create an internal checksum list of the secondary package that only contains the differences to the primary package
*/
$checksums_diff = array_diff_assoc($checksums_secondary, $checksums_primary);
$count_diff = count($checksums_diff);

/*
* Generate the contents of the checksum file for the primary package
*/
$checksum_file_content_primary = '';
foreach ($checksums_primary as $file => $hash)
{
	$checksum_file_content_primary .= $hash . ' *' . $file . $lf;
}
$checksum_file_content_primary .= "{$config['name_primary']}:" . $phpbb_version_primary . $lf;

/*
* Generate the contents of the checksum file for the differences
*/
$checksum_file_content_diff = '';
foreach ($checksums_diff as $file => $hash)
{
	$checksum_file_content_diff .= $hash . ' *' . $file . $lf;
}
$checksum_file_content_diff .= "{$config['name_secondary']}:" . $phpbb_version_secondary . $lf;

/*
* Create the ZIP archive
*/
$hash_package_zip_file = str_replace('$phpbb_ver$', $phpbb_version_primary, $config['folder_export'] . $config['hash_package_zip_file']) . '.zip';
$zip = new ZipArchive;
if ($zip->open($hash_package_zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true)
{
	$zip->addFromString(basename($checksum_file_primary), $checksum_file_content_primary);
	$zip->addFromString(basename($checksum_file_diff), $checksum_file_content_diff);
	if ($config['file_ignore'])
	{
		$zip->addFile($config['file_ignore'], basename($config['file_ignore']));
	}
	if ($config['file_exceptions'])
	{
		$zip->addFile($config['file_exceptions'], basename($config['file_exceptions']));
	}
	$zip->close();
}
else
{
	terminate("{$hash_package_zip_file} could not be created");
}

/*
* Show summary and exit script
*/
echo $lf;
echo sprintf('Checksums primary  : %1$ 4u (%2$s) -> %3$s (%4$u bytes)',
	$count_primary,
	$config['name_primary'],
	$checksum_file_primary,
	strlen($checksum_file_content_primary)
) . $lf;
echo sprintf('Checksums secondary: %1$ 4u (%2$s)',
	$count_secondary,
	$config['name_secondary']
) . $lf;
echo sprintf('Checksums diff     : %1$ 4u (%2$s) -> %3$s (%4$u bytes)',
	$count_diff,
	$config['name_secondary'],
	$checksum_file_diff,
	strlen($checksum_file_content_diff)
) . $lf;
echo sprintf('ZIP archive        : %1$s (%2$u bytes)',
	basename($hash_package_zip_file),
	filesize($hash_package_zip_file)
) . $lf;
echo $lf;
echo sprintf('Finished! Run time: %.3f seconds', microtime(true) - $start_time) . $lf;

/*
* Script end
*/

function terminate(string $message): void
{
	global $lf;

	echo $lf . 'ERROR: ' . $message . $lf;
	exit;
}

function get_files_recursive(string $folder): array
{
	$files = [];
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));
	foreach ($iterator as $file)
	{
		if ($file->isDir())
		{
			continue;
		}
		$files[] = [
			'file' => str_replace('\\', '/', $file->getPathname()),
			'path' => str_replace('\\', '/', $file->getPath()),
		];
	}

	usort($files, function($a, $b) {
		return [$a['path'], $a['file']] <=> [$b['path'], $b['file']];
	});

	return $files;
}

function get_package_version(string $source, string $package_name, string $zip_root): string
{
	$get_version = function ($content) use ($package_name)
	{
		preg_match('/\'PHPBB_VERSION\'.*?\'([0-9]+?\.[0-9]+?\.[0-9]+?)\'/', $content, $matches);
		$version = $matches[1] ?? null;
		if ($version)
		{
			return $version;
		}
		else
		{
			terminate("{$package_name} package has an invalid version");
		}
	};

	if (is_zip($source) && file_exists($source))
	{
		$constants_file = $zip_root .'includes/constants.php';
		$zip = new ZipArchive();
		$zip->open($source);
		$constants_content = $zip->getFromName($constants_file);
		$zip->close();
		if ($constants_content !== false)
		{
			return $get_version($constants_content);
		}
	}
	else
	{
		$constants_file = $source . 'includes/constants.php';
		if (file_exists($constants_file))
		{
			return $get_version(file_get_contents($constants_file));
		}
	}
	terminate("constants.php not found in {$source}");
}

function get_checksums_from_folder(string $folder): array
{
	$checksums = [];
	$preg_folder = preg_quote(str_replace('\\', '/', $folder), '/');
	$files = get_files_recursive($folder);

	foreach ($files as $file)
	{
		$hash = md5_file($file['file']);
		$file = preg_replace(['/\\\\/', '/^' . $preg_folder . '/'], ['/', ''], $file['file']);
		$checksums[$file] = $hash;
	}

	return $checksums;
}

function get_checksums_from_zip(string $zip_file, string $zip_root): array
{
	$checksums = [];
	$checksums_zip = [];

	$zip = new ZipArchive();
	$zip->open($zip_file);
	for ($i = 0; $i < $zip->numFiles; $i++)
	{
		$info = $zip->statIndex($i);
		if (substr($info['name'], -1, 1) == '/')
		{
			continue;
		}
		$file = str_replace($zip_root, '', $info['name']);
		$checksums_zip[] = [
			'path'	=> dirname($file),
			'file'	=> $file,
			'hash'	=> md5($zip->getFromIndex($i)),
		];
	}
	$zip->close();
	usort($checksums_zip, function($a, $b) {
		return [$a['path'], $a['file']] <=> [$b['path'], $b['file']];
	});

	foreach ($checksums_zip as $row)
	{
		$checksums[$row['file']] = $row['hash'];
	}

	return $checksums;
}

function add_dir_separator(string $path, bool $before = false, bool $not_empty = true): string
{
	if ($path == '' && $not_empty)
	{
		return '';
	}
	if ($before)
	{
		$path = preg_replace('/^([^\\\\\/])/', '\\' . DIRECTORY_SEPARATOR . '$1', $path);
	}
	else
	{
		$path = preg_replace('/([^\\\\\/])$/', '$1' . DIRECTORY_SEPARATOR, $path);
	}

	return $path;
}

function is_zip(string $file): bool
{
	return substr($file, -4, 4) == '.zip';
}

function cli_init()
{
	$options_short	= 'h';
	$options_long	= [
		'source-1:',
		'source-2:',
		'export-to:',
	];

	return getopt($options_short, $options_long);
}

function cli_help()
{
	global $lf;

	echo 'Syntax: filecheck_hash_gen.php {parameter 1} {parameter 2} ...' . $lf;
	echo $lf;
	echo 'Parameters:' . $lf;
	echo '    --source-1="{ZIP or folder}"    [primary phpBB package]' . $lf;
	echo '    --source-2="{ZIP or folder}"    [secondary phpBB package]' . $lf;
	echo '    --export-to="{folder}"          [export folder for the hash package]' . $lf;
	exit;
}

function cli_get_option(string $opt, $default = null)
{
	global $cli_options;

	return !empty($cli_options[$opt]) ? $cli_options[$opt] : $default;
}

function cli_is_option(string $opt): bool
{
	global $cli_options;

	return isset($cli_options[$opt]);
}

# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames
