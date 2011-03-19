<?php

if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if(TYPO3_MODE=='FE') require_once(t3lib_extMgm::extPath('rsgoogleanalytics').'class.tx_rsgoogleanalytics.php');

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'][] = '&tx_rsgoogleanalytics->linkPostProcess';


?>