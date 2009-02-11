<?php

########################################################################
# Extension Manager/Repository config file for ext: "rsgoogleanalytics"
#
# Auto generated 24-10-2008 11:18
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Google Analytics',
	'description' => 'Supports full options of Google Analytics with new ga.js (within Domains, FilesLinks, External URLs e.g.)',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.1.3',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Steffen Ritter',
	'author_email' => 'info@steffen-ritter.net',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.2.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:9:"ChangeLog";s:4:"c0b8";s:10:"README.txt";s:4:"9fa9";s:30:"class.tx_rsgoogleanalytics.php";s:4:"0529";s:12:"ext_icon.gif";s:4:"18fd";s:17:"ext_localconf.php";s:4:"dbfb";s:24:"ext_typoscript_setup.txt";s:4:"9613";s:32:"doc/InstallingGATrackingCode.pdf";s:4:"2d96";s:19:"doc/wizard_form.dat";s:4:"4c8d";s:20:"doc/wizard_form.html";s:4:"acc3";}',
	'suggests' => array(
	),
);

?>