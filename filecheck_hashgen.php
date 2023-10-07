<?php
/**
*
* phpBB File Check Hash Generator - Creates checksum packages for phpBB File Check
*
* @copyright (c) 2023 LukeWCS <phpBB.de>
* @license GNU General Public License, version 2 (GPL-2.0-only)
*
*/

/*
* Help: filecheck_hashgen.php -h
*/

# phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames config
# phpcs:disable PSR1.Files.SideEffects

/*
* Check requirements
*/
if (!(version_compare(PHP_VERSION, '8.0.0', '>=') && version_compare(PHP_VERSION, '8.3.0-dev', '<')))
{
	echo 'phpBB File Check Hash Generator: Invalid PHP Version ' . PHP_VERSION;
	exit;
}

/*
* Initialization
*/
define('EOL'			, "\n");
define('VALID_CHARS'	, 'a-zA-Z0-9\/\-_.');
define('CONFIG_FILE'	, __DIR__ . '/' . basename(__FILE__, '.php') . '_config.php');

$ver				= '1.0.0';
$title				= "phpBB File Check Hash Generator v{$ver}";
$ignore_file		= 'filecheck_ignore.txt';
$exceptions_file	= 'filecheck_exceptions.txt';
$start_time			= microtime(true);

/*
* Display: show title
*/
echo $title . EOL ;
echo str_repeat('=', strlen($title)) . EOL . EOL;

/*
* Include config
*/
if (file_exists(CONFIG_FILE))
{
	include CONFIG_FILE;
}
else
{
	terminate('Config file [' . basename(CONFIG_FILE) . '] not found');
}

/*
* CLI interface
*/
cli_init();

if (cli_is_option('h'))
{
	echo cli_help();
	exit;
}
if (cli_is_option('source-1'))
{
	$config['source-1'] = cli_get_option('source-1');
}
if (cli_is_option('source-2'))
{
	$config['source-2'] = cli_get_option('source-2');
}
if (cli_is_option('export-dir'))
{
	$config['export-dir'] = cli_get_option('export-dir');
}
if (cli_is_option('zip-root'))
{
	$config['zip-root'] = cli_get_option('zip-root');
}
if (cli_is_option('source-1-label'))
{
	$config['source-1-label'] = cli_get_option('source-1-label');
}
if (cli_is_option('source-2-label'))
{
	$config['source-2-label'] = cli_get_option('source-2-label');
}
if (cli_is_option('ignore-file'))
{
	$config['ignore-file'] = cli_get_option('ignore-file');
}
if (cli_is_option('exceptions-file'))
{
	$config['exceptions-file'] = cli_get_option('exceptions-file');
}
if (cli_is_option('hash-zip-name'))
{
	$config['hash-zip-name'] = cli_get_option('hash-zip-name');
}
if (cli_is_option('timezone-id'))
{
	$config['timezone-id'] = cli_get_option('timezone-id');
}

/*
* Check config
*/
if (empty($config['source-1']))
{
	terminate('Primary source is not set');
}
if (empty($config['source-2']) && !empty($config['source-2-label']))
{
	terminate('Secondary source is not set');
}
if (empty($config['export-dir']))
{
	$config['export-dir'] = __DIR__;
}
if (empty($config['zip-root']))
{
	terminate('phpBB ZIP root is not set');
}
if (empty($config['source-1-label']))
{
	terminate('Primary package name is not set');
}
if (empty($config['source-2-label']) && !empty($config['source-2']))
{
	terminate('Secondary package name is not set');
}
if (empty($config['hash-zip-name']))
{
	terminate('Hash package ZIP name is not set');
}
if (empty($config['timezone-id']))
{
	$config['timezone-id'] = 'UTC';
}
if (!@date_default_timezone_set($config['timezone-id']))
{
	terminate('Invalid timezone ID');
}

/*
* Add final path separator
*/
if (is_dir($config['source-1']))
{
	add_dir_separator($config['source-1']);
}
if (!empty($config['source-2']) && is_dir($config['source-2']))
{
	add_dir_separator($config['source-2']);
}
add_dir_separator($config['export-dir']);
add_dir_separator($config['zip-root'], '/');

/*
* Check the phpBB packages and determine their versions (ZIP or folder)
*/
if (file_exists($config['source-1']))
{
	$phpbb_version_primary = get_package_version($config['source-1'], $config['source-1-label']);
}
else
{
	terminate("Primary source [{$config['source-1']}] not found");
}

if (!empty($config['source-2']))
{
	if (file_exists($config['source-2']))
	{
		$phpbb_version_secondary = get_package_version($config['source-2'], $config['source-2-label']);
	}
	else
	{
		terminate("Secondary source [{$config['source-2']}] not found");
	}
}

$checksum_file_primary = 'filecheck_' . $phpbb_version_primary . '.md5';
if (!empty($config['source-2']))
{
	$checksum_file_diff = 'filecheck_' . $phpbb_version_secondary . '_diff' . '.md5';
}

/*
* Display: show phpBB package versions
*/
echo sprintf('Version primary    : %1$s (%2$s) %3$s',
	/* 1 */ $phpbb_version_primary,
	/* 2 */ $config['source-1-label'],
	/* 3 */ is_zip($config['source-1']) ? 'ZIP' : 'FOLDER'
) . EOL;
if (!empty($config['source-2']))
{
	echo sprintf('Version secondary  : %1$s (%2$s) %3$s',
		/* 1 */ $phpbb_version_secondary,
		/* 2 */ $config['source-2-label'],
		/* 3 */ is_zip($config['source-2']) ? 'ZIP' : 'FOLDER'
	) . EOL;
}

/*
* Check if both packages have the same version
*/
if (!empty($config['source-2']) && $phpbb_version_secondary != $phpbb_version_primary)
{
	terminate("{$config['source-1-label']} package and {$config['source-2-label']} package has different versions");
}

/*
* Check for ignore and exception files if defined.
*/
if (!empty($config['ignore-file']) && !file_exists($config['ignore-file']))
{
	terminate("Ignore list [{$config['ignore-file']}] not found");
}

if (!empty($config['exceptions-file']) && !file_exists($config['exceptions-file']))
{
	terminate("Exception list [{$config['exceptions-file']}] not found");
}

/*
* Generate internal checksum lists of phpBB packages (ZIP or folder)
*/
$checksums_primary = get_package_checksums($config['source-1']);
$count_primary = count($checksums_primary);

if (!empty($config['source-2']))
{
	$checksums_secondary = get_package_checksums($config['source-2']);
	$count_secondary = count($checksums_secondary);
}

/*
* Create an internal checksum list of the secondary package that only contains the differences to the primary package
*/
if (!empty($config['source-2']))
{
	$checksums_diff = array_diff_assoc($checksums_secondary, $checksums_primary);
	$count_diff = count($checksums_diff);
}

/*
* Generate the content of the checksum file for the primary package
*/
$checksum_file_content_primary = create_checksum_file($checksums_primary, $config['source-1-label'], $phpbb_version_primary);

/*
* Generate the content of the checksum file for the differences
*/
if (!empty($config['source-2']))
{
	$checksum_file_content_diff = create_checksum_file($checksums_diff, $config['source-2-label'], $phpbb_version_secondary);
}

/*
* Create the hash package ZIP
*/
$hash_zip_filename = str_replace('$PHPBB_VER$', $phpbb_version_primary, $config['export-dir'] . $config['hash-zip-name']) . '.zip';
$zip = new ZipArchive;
if ($zip->open($hash_zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true)
{
	$zip->addFromString(basename($checksum_file_primary), $checksum_file_content_primary);
	if (!empty($config['source-2']))
	{
		$zip->addFromString(basename($checksum_file_diff), $checksum_file_content_diff);
	}
	if (!empty($config['ignore-file']))
	{
		$zip->addFile($config['ignore-file'], $ignore_file);
		$zip->setMtimeIndex($zip->numFiles - 1, time());
	}
	if (!empty($config['exceptions-file']))
	{
		$zip->addFile($config['exceptions-file'], $exceptions_file);
		$zip->setMtimeIndex($zip->numFiles - 1, time());
	}
	$zip->close();
}
else
{
	terminate("[{$hash_zip_filename}] could not be created");
}

/*
* Display: show summary
*/
echo EOL;
echo sprintf('Checksums primary  : %1$ 4u (%2$s) -> %3$s',
	/* 1 */ $count_primary,
	/* 2 */ $config['source-1-label'],
	/* 3 */ $checksum_file_primary
) . EOL;
if (!empty($config['source-2']))
{
	echo sprintf('Checksums secondary: %1$ 4u (%2$s)',
		/* 1 */ $count_secondary,
		/* 2 */ $config['source-2-label']
	) . EOL;
	echo sprintf('Checksums diff     : %1$ 4u (%2$s) -> %3$s',
		/* 1 */ $count_diff,
		/* 2 */ $config['source-2-label'],
		/* 3 */ $checksum_file_diff
	) . EOL;
}

/*
* Display: show ZIP content
*/
echo EOL;
echo sprintf('Hash package ZIP   : %1$s (%2$u bytes)',
	/* 1 */ basename($hash_zip_filename),
	/* 2 */ filesize($hash_zip_filename)
) . EOL;

if ($zip->open($hash_zip_filename) === true)
{
	$zip_list = '';
	for ($i = 0; $i < $zip->numFiles; $i++)
	{
		$info = $zip->statIndex($i);
		$zip_list .= sprintf('%1$ 9u %2$ 9u  %3$s  %4$s',
			/* 1 */ $info['size'],
			/* 2 */ $info['comp_size'],
			/* 3 */ date('Y-m-d H:i:s', $info['mtime']),
			/* 4 */ $info['name']
		) . EOL;
	}
	add_list_lines($zip_list);
	$zip_list = EOL . 'Size      Comp.Size  Date       Time      File' . $zip_list;
	echo $zip_list;
	$zip->close();
}
else
{
	terminate("[{$hash_zip_filename}] could not be opened");
}

/*
* Display: show runtime
*/
echo EOL;
echo sprintf('Finished! Run time: %.3f seconds', microtime(true) - $start_time) . EOL;

/*
* Script end
*/

function terminate(string $message): void
{
	echo EOL . 'ERROR: ' . $message . EOL;
	exit;
}

function get_files_recursive(string $folder): array
{
	$files = [];

	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS));
	foreach ($iterator as $file)
	{
		$files[] = [
			'path' => str_replace('\\', '/', $file->getPath()),
			'file' => str_replace('\\', '/', $file->getPathname()),
		];
	}

	return $files;
}

function get_package_version(string $source, string $label): string
{
	global $config;

	$get_version = function ($content) use ($label)
	{
		preg_match('/\'PHPBB_VERSION\'\s*,\s*\'([0-9]+\.[0-9]+\.[0-9]+)\'/', $content, $matches);
		$version = $matches[1] ?? null;
		if ($version)
		{
			return $version;
		}
		else
		{
			terminate("{$label} package has an invalid version");
		}
	};

	if (is_zip($source))
	{
		$constants_file = $config['zip-root'] . 'includes/constants.php';
		$zip = new ZipArchive();
		if ($zip->open($source) === true)
		{
			$constants_content = $zip->getFromName($constants_file);
			$zip->close();
		}
		else
		{
			terminate("[{$source}] could not be opened");
		}
		if ($constants_content !== false)
		{
			return $get_version($constants_content);
		}
	}
	else if (is_dir($source))
	{
		$constants_file = $source . 'includes/constants.php';
		if (file_exists($constants_file))
		{
			$constants_content = @file_get_contents($constants_file);
			if ($constants_content !== false)
			{
				return $get_version($constants_content);
			}
			else
			{
				terminate("[{$constants_file}] could not be opened");
			}
		}
	}
	terminate("constants.php not found in [{$source}]");
}

function get_package_checksums(string $source): array
{
	global $config;

	$checksums		= [];
	$error_messages	= '';

	if (is_zip($source))
	{
		$checksums_zip = [];
		$zip = new ZipArchive();

		if ($zip->open($source) === true)
		{
			for ($i = 0; $i < $zip->numFiles; $i++)
			{
				$info = $zip->statIndex($i);
				if (substr($info['name'], -1, 1) == '/')
				{
					continue;
				}
				$file = str_replace($config['zip-root'], '', $info['name']);
				$checksums_zip[] = [
					'path'	=> dirname($file),
					'file'	=> $file,
					'hash'	=> md5($zip->getFromIndex($i)),
				];
			}
			$zip->close();
		}
		else
		{
			terminate("[{$source}] could not be opened");
		}

		usort($checksums_zip, function($a, $b) {
			return [$a['path'], $a['file']] <=> [$b['path'], $b['file']];
		});

		foreach ($checksums_zip as $row)
		{
			$checksums[$row['file']] = $row['hash'];
		}
	}
	else if (is_dir($source))
	{
		$preg_folder = preg_quote(str_replace('\\', '/', $source), '/');
		$files = get_files_recursive($source);

		usort($files, function($a, $b) {
			return [$a['path'], $a['file']] <=> [$b['path'], $b['file']];
		});

		foreach ($files as $file)
		{
			$hash = @md5_file($file['file']);
			$file = preg_replace(['/\\\\/', '/^' . $preg_folder . '/'], ['/', ''], $file['file']);
			if ($hash === false)
			{
				$error_messages .= "[{$file}] hash could not be calculated" . EOL;
			}
			$checksums[$file] = $hash;
		}
	}

	foreach ($checksums as $file => $hash)
	{
		if (preg_match_all('/[^' . VALID_CHARS . ']/', $file, $matches))
		{
			$error_messages .= "[{$file}] file name contains invalid characters: [" . implode('', $matches[0]) . "]" . EOL;
		}
	}
	if ($error_messages != '')
	{
		add_list_lines($error_messages);
		terminate("Source [{$source}] has the following issues:" . EOL . $error_messages);
	}

	return $checksums;
}

function create_checksum_file(&$list, $label, $phpbb_version)
{
	$content = '';
	foreach ($list as $file => $hash)
	{
		$content .= $hash . ' *' . $file . EOL;
	}
	$content .= $label . ':' . $phpbb_version . EOL;

	return $content;
}

function add_dir_separator(string &$path, string $separator = '', bool $before = false, bool $not_empty = true): void
{
	$path_tmp = $path;
	$separator = $separator != '' ? $separator : DIRECTORY_SEPARATOR;
	if ($path_tmp == '' && $not_empty)
	{
		return;
	}
	$path_tmp = preg_replace('/[\/\\\\]/', $separator, $path_tmp);
	if ($before)
	{
		$path = preg_replace('/^([^\/\\\\])/', '\\' . $separator . '$1', $path_tmp);
	}
	else
	{
		$path = preg_replace('/([^\/\\\\])$/', '$1' . $separator, $path_tmp);
	}
}

function add_list_lines(string &$text): void
{
	$output_rows = array_map('strlen', explode(EOL, $text));
	$list_separator = str_repeat('-', max($output_rows));
	$text = EOL . $list_separator . EOL . $text . $list_separator . EOL ;
}

function is_zip(string $file): bool
{
	if (file_exists($file))
	{
		return mime_content_type($file) == 'application/zip';
	}

	return false;
}

function cli_init(): void
{
	global $cli_options;

	$options_short	= 'h';
	$options_long	= [
		'source-1:',
		'source-2:',
		'export-dir:',
		'zip-root:',
		'source-1-label:',
		'source-2-label:',
		'ignore-file:',
		'exceptions-file:',
		'hash-zip-name:',
		'timezone-id:',
	];

	$cli_options = getopt($options_short, $options_long);
}

function cli_help(): string
{
	$missing = '{help missing}';
	$help_text = '';

	$help_text .= 'Syntax: ' . basename(__FILE__) . ' {parameters}' . EOL;
	$help_text .= EOL;
	$help_text .= 'Parameters:' . EOL;

	$config_content = @file_get_contents(CONFIG_FILE);
	preg_match_all('/\/\*-\s+(.*?)\s+-\*\/.*?\'(.*?)\'\s+=>/s', $config_content, $matches);
	if (is_array($matches) && count($matches) == 3)
	{
		$config_help = [];
		for ($i = 0; $i < count($matches[1]); $i++)
		{
			$config_help += [
				$matches[2][$i] => str_replace(EOL, '', $matches[1][$i])
			];
		}
	}
	$helpline = [
		'--source-1="{ZIP/folder}"'			=> $config_help['source-1'] ?? $missing,
		'--source-2="{ZIP/folder}"'			=> $config_help['source-2'] ?? $missing,
		'--export-dir="{folder}"'			=> $config_help['export-dir'] ?? $missing,
		'--zip-root="{folder}"'				=> $config_help['zip-root'] ?? $missing,
		'--source-1-label="{label}"'		=> $config_help['source-1-label'] ?? $missing,
		'--source-2-label="{label}"'		=> $config_help['source-2-label'] ?? $missing,
		'--ignore-file="{file}"'			=> $config_help['ignore-file'] ?? $missing,
		'--exceptions-file="{file}"'		=> $config_help['exceptions-file'] ?? $missing,
		'--hash-zip-name="{filename}"'		=> $config_help['hash-zip-name'] ?? $missing,
		'--timezone-id="{timezone ID}"'		=> $config_help['timezone-id'] ?? $missing,
	];

	$max_width		= 80;
	$para_indent	= 2;
	$para_len		= max(array_map('strlen', array_keys($helpline)));
	$separator		= ' -> ';
	$help_len		= $max_width - $para_indent - $para_len - strlen($separator);
	foreach ($helpline as $param => $help)
	{
		$help_text .= str_repeat(' ', $para_indent) . sprintf('%1$ -' . ($para_len) . 's%2$s', $param, $separator) .
			wordwrap($help, $help_len, EOL . str_repeat(' ', $para_indent + $para_len + strlen($separator))) . EOL;
	}

	return $help_text;
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
