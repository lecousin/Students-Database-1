<?php 
class Database_Operations extends TestFunctionalitiesScenario {
	
	public function getName() { return "Database Operations"; }
	
	public function getCoveredFunctions() {
		return array();
	}
	
	public function getSteps() {
		return array(
			new Database_Operations_Test_Transaction_Rollback(),
			new Database_Operations_Test_Transaction_Commit(),
		);
	}
	
}

class Database_Operations_Test_Transaction_Rollback extends TestFunctionalitiesStep {
	public function getName() { return "Transaction Rollback"; }
	public function run(&$scenario_data) {
		SQLQuery::startTransaction();
		SQLQuery::create()->bypassSecurity()->insert("DataLocks", array("timestamp"=>time(), "locker_domain"=>"Test", "locker_username"=>"test_transaction", "table"=>"does_not_exist"));
		SQLQuery::rollbackTransaction();
		$res = SQLQuery::create()->bypassSecurity()->select("DataLocks")->whereValue("DataLocks", "table", "does_not_exist")->execute();
		if (count($res) > 0) return "Insert visible in database after transaction rollback (".count($res).")";
		return null;
	}
}
class Database_Operations_Test_Transaction_Commit extends TestFunctionalitiesStep {
	public function getName() { return "Transaction Commit"; }
	public function run(&$scenario_data) {
		SQLQuery::startTransaction();
		SQLQuery::create()->bypassSecurity()->insert("DataLocks", array("timestamp"=>time(), "locker_domain"=>"Test", "locker_username"=>"test_transaction", "table"=>"does_not_exist"));
		SQLQuery::commitTransaction();
		$res = SQLQuery::create()->bypassSecurity()->select("DataLocks")->whereValue("DataLocks", "table", "does_not_exist")->execute();
		if (count($res) == 0) return "Insert not visible in database after transaction commit";
		return null;
	}
}

?>