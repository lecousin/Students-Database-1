<?php 
class service_exam_remove_center extends Service {
	
	public function getRequiredRights() { return array("manage_exam_center"); }
	public function documentation() {
		echo "Remove an exam center from its ID";
	}
	public function inputDocumentation() {
		echo "<code>id</code> exam center ID";
	}
	public function outputDocumentation() {
		?>
		<ul>
			<li><code>true</code> if well performed</li>
			<li><code>false</code> if not</li>
		</ul>
		<?php
	}
	
	/**
	 * @param $component selection
	 * @see Service::execute()
	 */
	public function execute(&$component, $input) {
		if(isset($input["id"])){
			$r = PNApplication::$instance->selection->removeExamCenter($input["id"]);
			if($r)
				echo "true";
			else
				echo "false";
		} else
				echo "false";
	}
	
}
?>