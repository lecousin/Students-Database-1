<?php 
function getDataDisplayFromInput($category, $name, $input) {
	foreach ($input as $data)
		if ($data["data"]["category"] == $category && $data["data"]["name"] == $name)
			return $data["value"];
	return null;
}

function import_people($input) {
	require_once("component/data_model/Model.inc");
	try {
		$table = DataModel::get()->getTable("People");
		$display = $table->getDisplayHandler(null);
		$values = array();
		foreach ($display->getDisplayableData() as $data) {
			$value = getDataDisplayFromInput($display->category, $data->getDisplayName(), $input);
			$cols = $data->getHandledColumns();
			$values[$cols[0]] = $value;
		}
		$people_id = SQLQuery::create()->bypass_security()->insert("People", $values);
	} catch (Exception $e) {
		PNApplication::error($e);
		return null;
	}
	foreach (PNApplication::$instance->components as $c) {
		if (!($c instanceof PeoplePlugin)) continue;
		if (!$c->importPeopleData($input, $people_id)) {
			// error: rollback
			SQLQuery::create()->bypass_security()->remove_key("People", $people_id);
			return null;
		}
	}
	return $people_id;
}
?>