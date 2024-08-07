## phpBB File Check Hash Generator
Tool for phpBB - Creates checksum packages for phpBB File Check

### Requirements
* PHP 8.0.0 - 8.3.x

### Introduction

FCHG automatically creates a checksum package (ZIP) that contains all the files required by phpBB File Check. For this, FCHG needs the original phpBB.com package as the primary source. Optionally, a secondary source can also be used that contains a national phpBB complete package. The third component required is an exception list, the content of which depends directly on the respective national language package. This file is then automatically added to the ZIP by FCHG.

The concept of FCHG is that all permanent settings are entered in the configuration file `config/filecheck_hashgen_config.php` and all dynamic settings are passed via CLI. All values ​​in the configuration file can also always be passed via CLI. CLI parameters always take precedence over the values ​​in the configuration file.

Both ZIPs and folders can be used as sources. If a ZIP is used, the root folder name in the ZIP must have a fixed name, i.e. it must not contain a version or other variable text. Otherwise, a folder must be used as the source. ZIP as a source is preferable because it can be processed much faster than folders.

In the folder `docs/examples` you will find examples for the configuration and for the CLI call.
