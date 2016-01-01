<?php
/**
 * DCRM System Updater
 * Copyright (c) 2015 Hintay <hintay@me.com>
 *
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Designed by Hintay in China
 */

if(!defined('IN_DCRM')) exit('Access Denied');
class Updater{
	public $version_file;
	public $update_silently = 0;
	public $update_times = 0;
	public $current_version = null;
	public $previous_version = null;

	public function init(){
		include($this->version_file);
		$this->current_version = $_version;
		if($this->current_version == VERSION) return;
		while($this->current_version){
			$this->include_updater($this->current_version);
			if($this->current_version == VERSION) {
				saveVersion($this->current_version);
				$this->show_message();
				return;
			}
		}
	}
	protected function include_updater($version){
		while($version) {
			$filepath = SYSTEM_ROOT."./function/updater/{$version}.php";
			if(file_exists($filepath)){
				include($filepath);
				return;
			} else {
				$version = substr($version, 0, strrpos($version, '.'));
			}
		}
		$this->fallback();
	}
	protected function update_version($version, $silently = false){
		//saveVersion($version);
		$this->previous_version = $this->current_version;
		$this->current_version = $version;
		$this->update_times++;
		if($silently) $this->update_silently++;
	}
	protected function show_message(){
		if($this->update_times == $this->update_silently) return;
		showmessage(sprintf(__( 'Successfully updated to %s' ), $this->current_version), $_SERVER['REQUEST_URI']);
		exit();
	}
	protected function fallback(){
		if($this->previous_version) {
			// For develop
			saveVersion($this->previous_version);
			throw new Exception(sprintf(__('Can not find the Updater!<br/>Error while upgrade from version %1$s to version %2$s.'), $this->previous_version, $this->current_version));
		} else {
			throw new Exception(sprintf(__('Can not find the Updater!<br/>Error while upgrade from version %1$s to version %2$s.'),$this->current_version, VERSION));
		}
	}
	protected function first_create(){
		if(!file_exists($this->version_file)){
			@touch($this->version_file);
			saveVersion('1.5');
		}
	}
	public function __construct(){
		$this->version_file = SYSTEM_ROOT.'version.inc.php';
		$this->first_create();
	}
}
?>