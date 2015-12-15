<?php
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
	protected function update_version($version,
	 $silently = false){
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