<?php 
require_once("component/people/PeopleTypePlugin.inc");
class TeacherPeopleTypePlugin implements PeopleTypePlugin {
	
	public function getSecurityPriority() { return 20; }
	
	public function getId() { return "teacher"; }
	public function getName() { return "Teacher"; }
	
	public function canRead() {
		return PNApplication::$instance->user_management->has_right("consult_curriculum");
	}
	public function canWrite() {
		return PNApplication::$instance->user_management->has_right("edit_curriculum");
	}
	public function canRemove() {
		return PNApplication::$instance->user_management->has_right("edit_curriculum");
	}
	
	public function getPeopleRowDescription($people) {
		return "Teacher";
	}
	
}
?>