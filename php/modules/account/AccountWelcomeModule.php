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
 * @package Wikidot
 * @version $Id$
 * @copyright Copyright (c) 2008, Wikidot Inc.
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 */

class AccountWelcomeModule extends AccountBaseModule {
	
	public function build($runData){
		
		$user = $runData->getUser();
		$runData->contextAdd("user", $user);
		
		$userId = $user->getUserId();
		
		$tips = array();
		
		// check if has an avatar
		$avatarDir = WIKIDOT_ROOT.'/web/files--common/images/avatars/';
		$avatarDir .= '' . floor($userId/1000).'/'.$userId;
		$avatarPath = $avatarDir."/a48.png";
		if(file_exists($avatarPath)){
			$hasAvatar = true;
			$avatarUri = '/common--images/avatars/'.floor($userId/1000).'/'.$userId.'/a48.png';
			$avatarUri .= '?'.rand(1,10000);
			$runData->contextAdd("avatarUri", $avatarUri);
			
		}else{
			$hasAvatar = false;	
			$tips['avatar'] = true;
		}
		
		$runData->contextAdd("hasAvatar", $hasAvatar);	
		if(count($tips)>0){
			$runData->contextAdd("tips", $tips);	
		}

	}
	
}