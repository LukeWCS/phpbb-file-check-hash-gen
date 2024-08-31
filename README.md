## phpBB File Check Hash Generator
Tool for phpBB - Creates checksum packages for phpBB File Check

### Requirements
* PHP 8.0.0 - 8.3.x

### Information

FCHG automatically creates a checksum package (ZIP) that contains all the files required by [phpBB File Check](https://github.com/LukeWCS/phpbb-file-check). For this, FCHG needs the original phpBB.com package as the primary source. Optionally, a secondary source can also be used that contains a national phpBB complete package. The third component required is an exception list, the content of which depends directly on the respective national language package. This file is then automatically added to the ZIP by FCHG.

The concept of FCHG is that all permanent settings are entered in the configuration file `config/filecheck_hashgen_config.php` and all dynamic settings are passed via CLI. All values ​​in the configuration file can also always be passed via CLI. CLI parameters always take precedence over the values ​​in the configuration file.

With `filecheck_hashgen.php -h` you can display an overview of the parameters.

Both ZIPs and folders can be used as sources. If a ZIP is used, the root folder name in the ZIP must have a fixed name, i.e. it must not contain a version or other variable text. Otherwise, a folder must be used as the source. ZIP as a source is preferable because it can be processed much faster than folders.

In the folder `docs/examples` you will find examples for the configuration and for the CLI call.

Example display of a package generation. In this example, the checksum package was created for the German phpBB 3.3.12 complete package. After the ZIP has been created, FCHG opens it and displays the contents.
```
phpBB File Check Hash Generator v1.0.4
======================================

Version primary    : 3.3.12 (phpBB.com) ZIP
Version secondary  : 3.3.12 (phpBB.de) ZIP

Checksums primary  : 3861 (phpBB.com) -> filecheck_3.3.12.md5
Checksums secondary: 4069 (phpBB.de)
Checksums diff     :  210 (phpBB.de) -> filecheck_3.3.12_diff.md5

Hash package ZIP   : phpBB-3.3.12-deutsch-FileCheck-MD5.zip (114031 bytes)

Size      Comp.Size  Date/Time            File
-------------------------------------------------------------------
   333776    108439  2024-08-31 22:44:30  filecheck_3.3.12.md5
    14810      5077  2024-08-31 22:44:30  filecheck_3.3.12_diff.md5
      160        74  2024-08-31 22:44:30  filecheck_exceptions.txt
-------------------------------------------------------------------

Finished!

Script/PHP information
-----------------------------------
Run time          : 0.777 seconds
Max execution time: 0 seconds
Memory peak usage : 5,162,096 bytes
Memory limit      : 128M
```
