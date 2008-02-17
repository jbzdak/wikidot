<?php
/**
 * Wikidot - free wiki collaboration software
 * Copyright (c) 2008, Wikidot Inc.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * For more information about licensing visit:
 * http://www.wikidot.org/license
 * 
 * @category Ozone
 * @package Ozone_Web
 * @version $Id$
 * @copyright Copyright (c) 2008, Wikidot Inc.
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * Parameters for the web request.
 *
 */
class ParameterList {

	private $parameterArray = array ();
	private $parameterTypes = array ();
	private $parameterFrom = array();

	public function initParameterList($runData) {
		
		if($runData->isAjaxMode()){
			foreach ($_POST as $key => $value) {
			  // 	if magic_quotes_gpc are set, reove escaping
			 	if(get_magic_quotes_gpc()){
				    $value = stripslashes($value);
			 	}
	
				$value = $this->fixNewLines($value);
				
				$this->parameterArray[$key] = $value;
				$this->parameterTypes[$key] = "AMODULE";
				$this->parameterFrom[$key] = 0; // 0 means "direct", + values means 'inherited'
				
			}
		} else{
			//initialize GET parameters from the url... because of mod_rewrite	
			$qs =  $_SERVER['QUERY_STRING'];
			$splited = explode("/",$qs);
			if(count($splited)>= 1){
				$this->parameterArray['template'] = $splited[0];
				$this->parameterTypes['template'] = "GET";
			}
			
			// now populate other parameters...
			
			for($i=1; $i<count($splited); $i+=2){
				$key = $splited[$i];
				$value=$splited[$i+1];
				$this->parameterArray[$key] = urldecode($value);
				$this->parameterTypes[$key] = "GET";
				$this->parameterFrom[$key] = 0;
			}

			// POST parameters are not affected by mod_rewrite
			
			foreach ($_POST as $key => $value) {
			  // 	if magic_quotes_gpc are set, reove escaping
			 	if(get_magic_quotes_gpc()){
				    $value = stripslashes($value);
			 	}
	
				$value = $this->fixNewLines($value);
				
				$this->parameterArray[$key] = $value;
				$this->parameterTypes[$key] = "POST";
				$this->parameterFrom[$key] = 0;
			}
		
		}

	}

	public function containsParameter($name) {
		return !array_search($name, $this->parameterArray) === false;
	}

	public function getParameterValue($name, $type = null, $type2 = null) {
		if($type == null || $this->parameterTypes[$name] == $type || $this->parameterTypes[$name] == $type2){
			return $this->parameterArray[$name];
		}
		return null;
	}

	public function delParameter($key){
		unset($this->parameterArray[$key]);
		unset($this->parameterTypes[$key]);
	}

	/**
	 * Returns type of the passed parameter: POST or GET.
	 */
	public function getParameterType($name) {
		return $this->parameterTypes[$name];
	}

	public function asArray() {
		return $this->parameterArray;
	}
	
	public function addParameter($key, $value, $type=null){
			$this->parameterArray["$key"] = $value;
			$this->parameterTypes["$key"] = $type;
	}
	
	public function numberOfParameters(){
		return count($this->parameterArray);	
	}

	private function fixNewLines($text){
		$text = str_replace("\r\n", "\n", $text);
		$text = str_replace("\r", "\n", $text);
		return $text;	
	}
	
	public function getParametersByType($type){
		$out = array();
		foreach($this->parameterArray as $key => $value){
			if($this->parameterTypes[$key] === $type){
				$out[$key] = $value;	
			}	
		}	
		return $out;
	}

}