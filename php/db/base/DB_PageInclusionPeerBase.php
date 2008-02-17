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
 * @category Wikidot
 * @package Wikidot_Db_Base
 * @version $Id$
 * @copyright Copyright (c) 2008, Wikidot Inc.
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 */
 
/**
 * Base peer class mapped to the database table page_inclusion.
 */
class DB_PageInclusionPeerBase extends BaseDBPeer {
	public static $peerInstance;
	
	protected function internalInit(){
		$this->tableName='page_inclusion';
		$this->objectName='DB_PageInclusion';
		$this->primaryKeyName = 'inclusion_id';
		$this->fieldNames = array( 'inclusion_id' ,  'site_id' ,  'including_page_id' ,  'included_page_id' ,  'included_page_name' );
		$this->fieldTypes = array( 'inclusion_id' => 'serial',  'site_id' => 'int',  'including_page_id' => 'int',  'included_page_id' => 'int',  'included_page_name' => 'varchar(128)');
		$this->defaultValues = array();
	}
	
	public static function instance(){
		if(self::$peerInstance == null){
			$className = "DB_PageInclusionPeer";
			self::$peerInstance = new $className();
		}
		return self::$peerInstance;
	}

}