<?php 
class service_get_available_fields extends Service {
	
	public function get_required_rights() {
		return array();
	}
	
	public function documentation() { echo "Start from the given table, and search for all reachable fields, and return the list of displayable fields"; }
	public function input_documentation() { echo "<code>table</code>: name of starting table"; }
	public function output_documentation() {
?>
TODO
<?php 
	}
	
	public function execute(&$component) {
		$table = $_POST["table"];

		require_once("component/data_model/DataPath.inc");
		$ctx = new DataPathBuilderContext();
		$paths = DataPathBuilder::search_from($ctx, $table);
		echo "[";
		for ($i = 0; $i < count($paths); $i++) {
			if ($i>0) echo ",";
			$p = $paths[$i];
			echo "{";
			echo "path:".json_encode($p->get_string());
			$disp = $p->table->getDisplayableDataCategoryAndName($p->field_name);
			echo ",cat:".json_encode($disp[0]);
			echo ",name:".json_encode($disp[1]);
			// TODO allow to edit multiple
			echo ",editable:".($p->table->canModifyField($p->field_name) && $paths[$i]->is_unique() ? "true" : "false");
			echo "}";
		}
		echo "]";
	}
	
}
?>