<?php 
class service_extend_temp extends Service {
	
	public function getRequiredRights() { return array(); }
	
	public function documentation() {}
	public function inputDocumentation() {}
	public function outputDocumentation() {}
	
	public function execute(&$component, $input) {
		$id = $input["id"];
		$file = SQLQuery::create()->bypassSecurity()->select("Storage")->whereValue("Storage", "id", $id)->executeSingleRow();
		if ($file == null) {
			PNApplication::error("Invalid storage id");
			return;
		}
		if ($file["expire"] == null) {
			return;
		}
		$component->set_expire($id, time()+5*60);
		echo "true";
	}
	
}
?>