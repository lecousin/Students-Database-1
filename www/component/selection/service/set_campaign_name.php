<?php
class service_set_campaign_name extends Service{
	public function getRequiredRights(){return array("manage_selection_campaign");}
	public function inputDocumentation(){
		echo "<ul>";
		echo "<li>id = the campaign id</li>";
		echo "<li>name = the new campaign name</li>";
		echo "</ul>";
	}
	public function outputDocumentation(){
		echo "<ul>";
		echo "<li>{boolean} true if done</li>";
		echo "<li>{boolean} else false</li>";
		echo "</ul>";
	}
	public function documentation(){echo "Update a selection campaign name";}
	public function execute(&$component,$input){
		if(isset($input['name']) && isset($input["id"])){
			try{
				$component->renameCampaign($input["id"],$input["name"]);
			} catch(Exception $e) {
				PNApplication::error($e->getMessage());
			}
			$to_echo = PNApplication::hasErrors() ? "false" : "true";
			echo $to_echo;
		}
		else echo "false";
	}
}	
?>