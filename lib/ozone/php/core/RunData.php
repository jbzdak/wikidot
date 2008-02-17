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
 * Class containing most important properties of the request/response.
 */
class RunData {

	private $parameterList;
	private $screenTemplate;
	private $screenClassName;
	private $screenClassPath;
	
	// used only when processing a module
	private $moduleTemplate;
	private $moduleClassName;
	private $moduleClassPath;
	
	private $context;
	private $action;
	private $actionEvent;
	private $nextAction;
	private $nextActionEvent;
	private $errorMessages = array ();
	private $messages = array ();
	private $page;
	private $cookies;
	private $language;

	private $session = null;

	private $ajaxMode = false;
	private $ajaxResponse;

	private $requestUri;
	private $requestMethod;
	
	private $extra = array();

	private $formToolHttpProcessed = false;
	
	private $temp; // temporary variables

	/**
	 * Default constructor.
	 */
	public function __construct() {
		$this->page = new PageProperties();
	}

	/**
		* Initializes a RunData object.
		*/
	public function init() {

		$parameterList = new ParameterList();
		$parameterList->initParameterList($this);
		$this->parameterList = $parameterList;

		$this->setTemplateFromParameterList();
		//set action
		$action =  $this->parameterList->getParameterValue('action');

		$parameterArray = $this->parameterList->asArray();
		// now parse some importand parameters: language, skin

		if ($parameterArray["lang"] != null) {
			$this->language = $parameterArray["lang"];
		} else {
			$this->language = GlobalProperties :: $DEFAULT_LANGUAGE;
		}

		if ($parameterArray["skin"] != null) {
			$this->page->setSkin($parameterArray["skin"]);
		}

		if ($action !== null  && preg_match('/^[a-z0-9_\/]+$/i', $action) == 1) {
			$this->parameterList->delParameter['action'];
			$this->action = str_replace("__", "/",$action);

			// set action event
			// this on is more complicated - extract event from a key in the parameter list
			// of the form event_someevent

			//first check if event=foobar is present
			foreach ($parameterArray as $key => $value) {
				if ($key == 'event') {
					$this->actionEvent = $value.'Event';
				}
			}

			foreach ($parameterArray as $key => $value) {
				if (ereg('event_', $key)) {
					$this->actionEvent = str_replace('event_', '', $key).'Event';
					break;
				}
			}
		}

		// initialize cookies... 
		$this->cookies = $_COOKIE;

		// store original request uri and request method:
		$this->requestUri = $_SERVER['REQUEST_URI'];
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];

	}

	public function getRequestUri() {
		return $this->requestUri;
	}

	public function getRequestMethod() {
		return $this->requestMethod;

	}

	public function getScreenClassName() {
		return $this->screenClassName;
	}

	public function getScreenClassPath() {
		return $this->screenClassPath;
	}

	public function getParameterList() {
		return $this->parameterList;
	}

	public function setParameterList($parameterList) {
		$this->parameterList = $parameterList;
	}

	public function getPage() {
		return $this->page;
	}

	public function setPage($page) {
		$this->page = $page;
	}

	public function getSession() {
		return $this->session;
	}

	public function setSession($var) {
		$this->session = $var;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getAction() {
		return $this->action;
	}

	public function getActionEvent() {
		return $this->actionEvent;
	}

	public function setActionEvent($actionEvent) {
		$this->actionEvent = $actionEvent;
	}

	public function setNextAction($action) {
		$this->nextAction = $action;
	}

	public function getNextAction() {
		return $this->nextAction;
	}

	public function getNextActionEvent() {
		return $this->nextActionEvent;
	}

	public function setNextActionEvent($actionEvent) {
		$this->nextActionEvent = $actionEvent;
	}

	public function setScreenTemplate2($screenTemplate) {
		$this->screenTemplate = str_replace(',', '/', $screenTemplate);
	}

	public function setTemplateScreenFromGetPost($getArray, $postArray) {

		if (array_key_exists('template', $postArray)) {
			$this->screenTemplate = str_replace(',', '/', $postArray['template']);
		} else
			if (array_key_exists('template', $getArray)) {
				$this->screenTemplate = str_replace(',', '/', $getArray['template']);
			} else {
				$this->screenTemplate = "Index";

			}
		$this->findClass();
	}

	private function setTemplateFromParameterList() {
		if(!$this->ajaxMode){
			// normal mode
			$template = $this->parameterList->getParameterValue("template");
			if ($template == null || preg_match('/^[a-z0-9_\/]+$/i',$template) != 1) {
				$template = "Index";
			}
			$template = str_replace("__", "/", $template);
			$this->screenTemplate = $template;
			$this->findClass();
		} else {
			// ajax call mode
			$template = $this->parameterList->getParameterValue("moduleName");
			if ($template == null || preg_match('/^[a-z0-9_\/]+$/i',$template) != 1) {
				$template = "Empty";
			}	
			$this->moduleTemplate = $template;
			$this->findClass();
		}
	}

	public function getScreenTemplateRaw() {
		return $this->screenTemplate;
	}

	public function getScreenTemplate() {
		return str_replace(',', '/', $this->screenTemplate);
		
	}
	
	public function getModuleTemplate() {
		return  $this->moduleTemplate;
	}
	
	public function setModuleTemplate($template) {
		$this->moduleTemplate = $template;
		$this->findClass();
	}
	
	public function getModuleClassPath() {
		return  $this->moduleClassPath;
	}
	
	public function getModuleClassName() {
		return  $this->moduleClassName;
	}

	public function setScreenTemplate($template) {
		$this->screenTemplate = $template;
		$this->findClass();
	}

	public function addErrorMessage($message) {
		$this->errorMessages[] = $message;
	}

	public function addMessage($message) {
		$this->messages[] = $message;
	}

	public function getErrorMessages() {
		return $this->errorMessages;
	}

	public function getMessages() {
		return $this->messages;
	}

	public function contextDel($key = null) {
		if ($key != null) {
			unset ($this->context["$key"]);
		} else {
			$this->context = array ();
		}
	}

	public function contextAdd($key, $value) {
		$this->context["$key"] = $value;
	}

	public function contextGet($key) {
		return $this->context["$key"];
	}

	public function getContext() {
		return $this->context;
	}
	
	public function setContext($context){
		$this->context = $context;	
	}

	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage($lang){
		$this->language = $lang;	
	}

	public function setAjaxMode($val){
		$this->ajaxMode = $val;
	}	
	
	public function getAjaxMode(){
		return $this->ajaxMode;	
	}
	public function isAjaxMode(){
		return $this->ajaxMode;	
	}

	/**
	 * Finds class given the template name.
	 */
	private function findClass() {
		if(!$this->ajaxMode){
		$classFilename = PathManager :: screenClass($this->screenTemplate);
		
		if (file_exists($classFilename)) {
			$this->screenClassPath = $classFilename;
			$tmp1 = explode('/', $this->screenTemplate);
			$size = sizeof($tmp1);
			$this->screenClassName = $tmp1[$size -1];

		} else {
			$tmppath = PathManager :: screenClassDir();
			## generate list of possible classes:
			$template = $this->screenTemplate;
			$path44 = explode('/', $template);

			for ($i = sizeof($path44) - 1; $i >= 0; $i --) {

				$tmppath2 = "";
				for ($k = 0; $k < $i; $k ++) {
					$tmppath2 .= $path44[$k]."/";
				}
				$tmppath2 .= "DefaultScreen.php";
				$classFiles[] = $tmppath2;
			}

			foreach ($classFiles as $classFile) {
				if (file_exists($tmppath.$classFile)) {
					$this->screenClassPath = $tmppath.$classFile;
					$this->screenClassName = "DefaultScreen";
					break;
				}
			}

		}
		} else {
			$ttt = ModuleHelpers::findModuleClass($this->moduleTemplate);
			$this->moduleClassName = $ttt[0];
			$this->moduleClassPath = $ttt[1];
		}

	}

	/**
	 * Start handling session. If session does not exist - start one. If exists - do nothing.
	 */
	public function sessionStart(){
		if($this->session == null){
			// create a new session
			$sessionId = UniqueStrings :: timeBased();
			$cookieKey = GlobalProperties::$SESSION_COOKIE_NAME;
			$sessionSecure = GlobalProperties::$SESSION_COOKIE_SECURE;
			$cookieResult = setcookie($cookieKey, $sessionId, time() + 10000000, "/", GlobalProperties::$SESSION_COOKIE_DOMAIN, $sessionSecure);
			$session = new DB_OzoneSession();
	
			// set IP
			$session->setIpAddress($this->createIpString());
			// set unique SESSION_ID
			$session->setSessionId($sessionId);
	
			$date = new ODate();
			$session->setStarted($date);
			$session->setLastAccessed($date);
			
			$session->setNewSession(true);
			$session->setUserId(null); // will this work?
			$this->session = $session;
		}
	}
	
	/** 
	 * Stops handling session - removing the cookie etc.
	 * 
	 */
	public function sessionStop(){
		$s = $this->getSession();
		if ($s) {
			$memcache = Ozone::$memcache;
			$mkey = 'session..'.$s->getSessionId();
			$memcache->delete($mkey);
			
			DB_OzoneSessionPeer :: instance()->deleteByPrimaryKey($s->getSessionId());
			$this->session = null;
			
		}
		$cookieKey = GlobalProperties::$SESSION_COOKIE_NAME;
		setcookie($cookieKey, 'dummy', time() - 10000000, "/", GlobalProperties::$SESSION_COOKIE_DOMAIN);
	}

	/**
	 * Handle session at the beginning of the request procession.
	 */
	public function handleSessionStart() {
		// check if session cookie exists
		$cookieKey = GlobalProperties::$SESSION_COOKIE_NAME;
		$cookieSessionId = $this->cookies[$cookieKey];
		if ($cookieSessionId == false || $cookieSessionId == '' || !$cookieSessionId) {
			// no session cookie, we do not force one (new cool policy).
			return ;
		}
		//ok, cookie is here. check if corresponds to a valid session
		// try memcached first
		$memcache = Ozone::$memcache;
		$mkey = 'session..'.$cookieSessionId;
		
		$session = $memcache->get($mkey);
		if(!$session){
			$session = DB_OzoneSessionPeer :: instance()->selectByPrimaryKey($cookieSessionId);
		}
		if(!$session){
			// no session object, delete the cookie!
			setcookie($cookieKey, $cookieSessionId, time() - 10000000, "/", GlobalProperties::$SESSION_COOKIE_DOMAIN);
			return;
		}
		
		// if we are here it means that the session object EXISTS in the database. now see if it is 
		// valid. if ok - leave it. if not - clean up.
		$sessionValid = true;
		
		if ($session->getInfinite() == false) {

			$minTimestamp = new ODate();
			$minTimestamp->subtractSeconds(GlobalProperties :: $SESSION_TIMEOUT);

			if ($session->getLastAccessed()->before($minTimestamp)) {
				$sessionValid = false;
			}

		}

		if ($session->getCheckIp() == true) {
			$currentIpString = $this->createIpString();
			if ($currentIpString != $session->getIpAddress()) {
				$sessionValid = false;
				$this->session = null;
				return; // nasty, we should not remove this session.
			}
		}
		
		if($sessionValid == false){
			// cleanup again
			$c = new Criteria();
			$c->add("session_id", $session->getSessionId());
			DB_OzoneSessionPeer :: instance()->delete($c);
			$memcache->delete($mkey);
		}else {
		
			// 	all is right, set the session now.
			$this->session = $session;
		}
		return;
	
	}

	/**
	 * Handle session at the end of the request procession.
	 */
	public function handleSessionEnd() {
		if($this->session){
			// if session storage is empty and userId = null - clear stop the session!
			$session = $this->session;
			$serializedData = $session->getSerializedData();
			if(!$this->getUser() && count($serializedData) == 0){
				$this->sessionStop();
			} else{
				$date = new ODate();
				$session->setLastAccessed($date);
				
				// save it to the database too?
				$lastSavedDate = $session->getTemp("lastSaved");
				if($session->getSessionChanged() 
						|| !$lastSavedDate 
						|| $date->getTimestamp() - $lastSavedDate->getTimestamp() > 300
						|| $session->isNew()){
					$session->save();
					$session->setTemp("lastSaved", $date);
					$session->setSessionChanged(false);		
				}
					
				$mc = OZONE::$memcache;
				$key = 'session..'.$session->getSessionId();
				$mc->set($key, $session, 0, 600);
			}
		}
	}

	/**
	 * Resets all the session data - i.e. stops a session and starts a new one.
	 */
	public function resetSession() {
		$this->sessionStop();
		$this->sessionStart();

	}

	public function sessionAdd($key, $value) {
		
		if ($this->session == null) {
			$this->sessionStart();
		}
		$this->session->setSerialized($key, $value);
	}

	public function sessionGet($key) {
		if ($this->session !== null) {
			return $this->session->getSerialized($key);
		} else{
			return null;	
		}
	}

	public function sessionDel($key = null) {
		if ($this->session !== null) {
			$this->session->clearSerialized($key);
		}
	}

	public function clearSessionStorage($key = null) {
		if ($this->session !== null) {
			$this->session->clearSerialized($key);
		}
	}

	/**
	 * Returns an instance of the FormTool. FormTool requires usage of sessions!
	 */
	public function formTool() {
		$formTool = $this->sessionGet('form_tool');
		if ($formTool == null) {
			$formTool = new FormTool();
			$this->sessionAdd('form_tool', $formTool);
			OzoneLogger :: instance()->debug("obtaining new FormTool");
		}
		// 
		if ($this->formToolHttpProcessed == false) {
			// extract form data form the http request
			$formTool->processHttpRequest($this);
			$this->formToolHttpProcessed = true;
		}

		return $formTool;

	}

	// SECURITY-RELATED METHODS FOLLOW:

	/**
	 * Checks if the current user is authenticated (registered + logged in) or
	 * anonymous. Returns true if authenticated, false otherwise.
	 */
	public function isUserAuthenticated() {
		$session = $this->session;
		if (!$session) {
			return false;
		}
		if(!$this->getUser()){
			return false;
		} else {
			return true;
		}
	}

	public function getUserId() {
		if($this->session == null){
			return null;
		}
		$userId = $this->session->getUserId();
		return $userId;
	}

	public function getOzoneUser(){
		return $this->getUser();
	}

	public function getUser() {
		if($this->session == null){
			return null;
		}
		return $this->session->getOzoneUser();
	}
	
	public function setExtra($key, $value){
		$this->extra[$key] = $value;	
	}
	
	public function getExtra($key){
		return $this->extra[$key];	
	}
	
	public function extraAsArray(){
		return $this->extra;	
	}

	public function createIpString() {
		if ($_SERVER["HTTP_X_FORWARDED_FOR"] && preg_match('/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/', $_SERVER["HTTP_X_FORWARDED_FOR"]) === 1) {
			if ($_SERVER["HTTP_CLIENT_IP"]) {
				$proxy = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$proxy = $_SERVER["REMOTE_ADDR"];
			}
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			$out = $ip."|".$proxy;
		} else {
			if ($_SERVER["HTTP_CLIENT_IP"]) {
				$ip = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$ip = $_SERVER["REMOTE_ADDR"];
			}
			$out = $ip;
		}

		return $out;

	}
	
	public function setTemp($key, $value){
		$this->temp[$key] = $value;	
	}
	
	public function getTemp($key){
		return $this->temp[$key];	
	}
	
	public function ajaxResponseAdd($key, $value){
		$this->ajaxResponse[$key] = $value;	
	}
	
	public function ajaxResponseGet($key){
		return $this->ajaxResponse[$key];	
	}
	
	public function getAjaxResponse(){
		return $this->ajaxResponse;	
	}
	
	public function getSessionId(){
		if($this->session != null){
			return $this->session->getSessionId();
		} else {
			return null;	
		}	
	}

}