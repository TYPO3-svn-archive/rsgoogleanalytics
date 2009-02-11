<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-6 	Steffen Ritter (info@rs-websystems.de)
*
*  All rights reserved
*
*  This script is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; version 2 of the License.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hooks for the 'rs_googleanalytics' extension.
 * Inspired by m1_google_analytics, using new ga.js
 *
 * @author	Steffen Ritter
 */


class tx_rsgoogleanalytics {


	/**
	 * Saves TypoScript config
	 */
	var $modConfig = array();

	
	/**
	 * adds the tracking code at the end of the body tag (pi Method called from TS USER_INT). further the method
	 * adds some js code for downloads and exteneral links if configured.
	 *
	 * @var		string	page content
	 * @return	string	page content with google tracking code.

	 */
	function processTrackingCode($content,$params){
		$this->getConfig();
		if ( $GLOBALS['TSFE']->type != 0 ) return;
		
		if ( $this->modConfig['registerTitle'] == 'title') {
			$pageName = '\''. $GLOBALS['TSFE']->page['title'] . '\'';
		}
		else if ($this->modConfig['registerTitle'] == 'rootline') {
			$rootline = $GLOBALS['TSFE']->sys_page->getRootLine($GLOBALS['TSFE']->page['uid']);
			$pageName = '\'';
			for ($i=0; $i < count($rootline) ; $i++ ) {
				if ( $rootline[$i]['is_siteroot']==0 )  {
					$pageName .= '/' . $rootline[$i]['title'];
				}
			}
			$pageName .= '\'';
		}


		// Add the tracking code to the end of <head> element
		return $content . $this->tracking_code( $pageName );
	}

	/**
	 * generates the google tracking code (js script at the end of the body tag).
	 *
	 * @return	string	js tracking code
	 */
	function tracking_code($pageName = '') {
		$protocolcode =
			'<script type="text/javascript">' . "\n" .
			'	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");'."\n" .
			"	document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));"."\n" .
			'</script>';"\n" .

		$specialOptions = $this->buildSpecials();

		$maincode =
			'<script type="text/javascript">'."\n" .
			'	var pageTracker = _gat._getTracker("' . $this->modConfig['account'] .'");' ."\n" .
			'	pageTracker._initData();' ."\n" .
				$specialOptions .
			'	pageTracker._trackPageview('.$pageName.');' ."\n" .
			'</script>'."\n" ;

		return $protocolcode . "\n". $maincode;
	}

	/**
	 * loads TypoScript configuration, fills probably needed fallback config
	 *
	 */
	private function getConfig() {
		$this->modConfig = $GLOBALS["TSFE"]->tmpl->setup['plugin.']['tx_rsgoogleanalytics.'];
		
		if(t3lib_extmgm::isLoaded('naw_securedl')) {
			$this->specialFiles = 'naw';
		}
		if(t3lib_extmgm::isLoaded('dam_frontend')) {
			$this->specialFiles = 'dam_frontend';
		}
	}

	/**
	 * buildSpecials()
	 * Function creates JS Commands for Special, non Standard Tracking behaviour
	 *
	 * @return string Returns Additional Lines JS Script
	 */
	 private function buildSpecials() {
	 	$addCommands = '';
		if ($this->modConfig['multipleDomains'] == 1) {
			$addCommands .= "	pageTracker._setDomainName(\"none\");\n";
		}
		if ( $this->modConfig['trackSubDomains'] == 1) {
			$addCommands .= "	pageTracker._setDomainName(\"".$_SERVER['HTTP_HOST']. "\");\n";
		}
		if ( $this->modConfig['multipleDomains'] && $this->modConfig['trackExternals'] ) {
			$addCommands .= "	pageTracker._setAllowLinker(true);\n";
		}
		if ($this->modConfig['changeCookiePath']) {
			$addCommands .= "	pageTracker._setCookiePath(\"".trim($this->modConfig['changeCookiePath'])."\");\n";
		}
		
		// Track less data
		if ($this->modConfig['disableData.']['browserInfo']) {
			$addCommands .= "	pageTracker._setClientInfo(false); // track browser info\n";
		}
		if ($this->modConfig['disableData.']['cookieTest']) {
			$addCommands .= "	pageTracker._setAllowHash(false); // cookie integrity checking\n";
		}
		if ($this->modConfig['disableData.']['flashTest']) {
			$addCommands .= "	pageTracker._setDetectFlash(false); // detect Flash version\n";
		}
		if ($this->modConfig['disableData.']['pageTitle']) {
			$addCommands .= "	pageTracker._setDetectTitle(false); // track title in reports\n";
		}
		
		// Modify tracking parameters
		if ($this->modConfig['sessionTimeOut']) {
			$addCommands .= "	pageTracker._setSessionTimeout(\"".trim($this->modConfig['sessionTimeOut'])."\");\n";
		}
		if ($this->modConfig['campaignTimeOut']) {
			$addCommands .= "	pageTracker._setCookieTimeout(\"".trim($this->modConfig['campaignTimeOut'])."\");\n";
		}
		
		// Set keywords which should marked as redirect
		if ($this->modConfig['redirectKeywords']) {
			$keywords = explode(',',$this->modConfig['redirectKeywords']);
			foreach($keywords as $key => $val) {
				$addCommands .= "	pageTracker._addIgnoredOrganic(\"".trim($val)."\");\n";
			}
		}
		
		// which redirects should be handled as "own domain"
		if ($this->modConfig['redirectReferer']) {
			$keywords = explode(',',$this->modConfig['redirectReferer']);
			foreach($keywords as $key => $val) {
				$addCommands .= "	pageTracker._addIgnoredRef(\"".trim($val)."\");\n";
			}
		}
		
		
		// Track user Information
		if(is_array($GLOBALS['TSFE']->fe_user->user)) {
			$cookieWert= explode('.',$_COOKIE['__utmv']);
			$cookieWert=$cookieWert[1];
			$user = $GLOBALS['TSFE']->fe_user->user;
			if( trim($cookieWert) != trim($user['name']) ) {
				$addCommands .= "	pageTracker._setVar(\"".trim($user['name'])."\");\n";	
			}
		}
		
		
	 	return $addCommands ;
	 }

	/**
	 * Checks wether URL is in list to track
	 *
	 * @param string $file filename (with directories from siteroot) which is linked
	 * @return boolean true if filename is in locations, false if not
	 *
	 */
	private function checkURL($url = string) {
		
		$locations = explode(',',$this->modConfig['trackExternals.']['domainList']);
		
		foreach($locations as $key => $location) {
			if ( false !== strpos($url,$location) ) return true;
		}
		return false;
	}



	/**
	 * Checks wether filePath is in paths to track
	 *
	 * @param string $file filename (with directories from siteroot) which is linked
	 * @return boolean true if filename is in locations, false if not
	 *
	 */
	private function checkFilePath($file = string) {
		$locations = explode(',',$this->modConfig['trackDownloads.']['folderList']);

		foreach($locations as $key => $location) {
			if ( false !== strpos($file,$location) ) return true;
		}
		return false;
	}
	/**
	 * Checks wether fileType is type to track
	 *
	 * @param string $file filename (with directories from siteroot) which is linked
	 * @return boolean true if filename is in list, false if not
	 *
	 */
	private function checkFileType($file = string) {
		$types = explode(',',$this->modConfig['trackDownloads.']['fileTypes']);

		foreach($types as $key => $type) {
			if ( $type == substr($file,-strlen($type)) ) return true;
		}
		return false;
	}
	/**
	 * Checks wether filePath and Type is in allowed range
	 * @param string $file filename (with directories from siteroot) which is linked
	 * @return boolean true if filename is in locations and filetype should be tracked, false if not
	 */
	private function checkFile(&$file = string) {
		if( $this->specialFiles ) {
				switch ($this->specialFiles ) {
					case 'naw':
						// Nothing special must be done, transformation is done in contentPostProc_all (typolinks are generated "normally")	
						break;
					case 'dam_frontend':
						if ( false !== strpos($file,'pushfile.php?docID') ) {// Get file UID and get Information from DAM
							$link = explode('?',$file);
							$new_link = $link[1];
							
							$parts = explode('&',$new_link);
							list($key,$param) = explode('=',$parts[0]);
							if($key == 'docID') {
								$file_id = $param;
								list($file_data) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('file_name','tx_dam','uid=' .$file_id);
								$file_name = 'DAM/'.$file_data['file_name'];
								if($this->modConfig['showDAMrealFile'] == 1) {
									$file = $file_name;
								}
							}
						}
						break;
					default:
						$file_name = $file;
				}	
		} 
		
		return $this->checkFilePath($file_name) && $this->checkFileType($file_name);
		
	}

	/**
	 * Hooks into TYPOLink Generation
	 * classic userFunc hook called in tslib/tslib_content.php
	 *
	 */
	function linkPostProcess(&$params, &$reference)  {
		
		$this->getConfig();

		// quit immediately if no Google Analytics Account defined, or not activated
		if (!($this->modConfig['account'] && $this->modConfig['active'])) return;

		switch($params['finalTagParts']['TYPE']) {
			case 'page':

				break;
			case 'url' :
				if( ($this->modConfig['trackExternals'] == 1 && $this->checkURL($params['finalTagParts']['url'])) || ($this->modConfig['trackExternals'] == '!ALL') ) {
					if ( ! stripos('onclick',$params['finalTagParts']['aTagParams'])) {
						$params['finalTagParts']['aTagParams'] .= " onclick=\"pageTracker._link(('". $params['finalTagParts']['url'] ."');\"";
						$params['finalTag'] = str_replace('>'," onclick=\"pageTracker._link('". $params['finalTagParts']['url'] ."');\">",$params['finalTag']);
					}
				}
				break;
			case 'file':
				if( ($this->modConfig['trackDownloads'] == 1 && $this->checkFile($params['finalTagParts']['url'])) || ($this->modConfig['trackDownloads'] == '!ALL') ) {
					if ( ! stripos('onclick',$params['finalTagParts']['aTagParams'])) {
						$params['finalTagParts']['aTagParams'] .= " onclick=\"pageTracker._trackPageview('". $params['finalTagParts']['url'] ."');\"";
						$params['finalTag'] = str_replace('>'," onclick=\"pageTracker._trackPageview('". $params['finalTagParts']['url'] ."');\">",$params['finalTag']);
					}
				}
				break;
		}

	}

}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rsgoogleanalytics/class.tx_rsgoogleanalytics.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rsgoogleanalytics/class.tx_rsgoogleanalytics.php"]);
}

?>