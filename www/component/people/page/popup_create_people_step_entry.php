<?php 
class page_popup_create_people_step_entry extends Page {
	
	public function getRequiredRights() { return array(); }
	
	public function execute() {
		$input = json_decode($_POST["input"], true);
		
		if (isset($input["multiple"]) && $input["multiple"] == "true") {
			require_once("component/data_model/page/create_multiple_data.inc");
			$fixed_columns = $input["fixed_columns"];
			$fixed_data = $input["fixed_data"];
			$prefilled_columns = $input["prefilled_columns"];
			$prefilled_data = $input["prefilled_data"];
			$precreated = $input["precreated"];
			
			$root_table = @$input["root_table"];
			if ($root_table == null) $root_table = "People";
			$sub_model = @$input["sub_model"];
				
			createMultipleDataPage($this, $root_table, $sub_model, @$input["sub_models"], $fixed_columns, $fixed_data, $prefilled_columns, $prefilled_data, $precreated);
			?>
			<script type='text/javascript'>
			var fixed_columns = [<?php
			$first = true;
			foreach ($fixed_columns as $fc) {
				if ($first) $first = false; else echo ",";
				echo "{table:".json_encode($fc["table"]).",column:".json_encode($fc["column"]).",value:".json_encode($fc["value"])."}";
			} 
			?>];
			var win = window;
			win.popup_there = function() {
				var popup = window.top.get_popup_window_from_frame(win);
				popup.addNextButton(function() {
					popup.freeze("We are checking if the new people are already in the database...");
					var peoples = [];
					for (var i = 0; i < grid.getNbRows(); ++i) {
						var row = grid.getRow(i);
						if (row._is_new) continue;
						var people = [];
						for (var j = 0; j < row.childNodes.length; ++j) {
							var cell = row.childNodes[j];
							var field = cell.field;
							var data = grid.getColumnById(cell.col_id).attached_data;
							if (field.error) {
								var name;
								if (typeof data.name != 'undefined')
									name = data.name;
								else
									name = data.datadisplay.name+" "+data.datadisplay.sub_data.names[data.sub_data];
								alert("Please correct the problems: "+name+": "+field.error);
								popup.unfreeze();
								return;
							}
							var path = cell.col_id;
							if (path == "remove") continue;
							var k = path.lastIndexOf('#');
							path = path.substring(0,k);
							if (typeof field.config.sub_data_index != 'undefined') {
								k = path.lastIndexOf('#');
								path = path.substring(0,k);
							}
							var ppath = null;
							for (var k = 0; k < people.length; ++k)
								if (people[k].path == path) { ppath = people[k]; break; }
							if (ppath == null) {
								ppath = {path:path,columns:{},value:[]};
								var dp = new DataPath(path);
								var table = dp.lastElement().table;
								for (var k = 0; k < fixed_columns.length; ++k)
									if (fixed_columns[k].table == table)
										ppath.columns[fixed_columns[k].column] = fixed_columns[k].value;
								people.push(ppath);
							}
							if (typeof field.config.sub_data_index != 'undefined') {
								// check we don't have yet this value
								var found = false;
								for (var k = 0; k < ppath.value.length; ++k)
									if (ppath.value[k].name == data.datadisplay.name && ppath.value[k].value == field.getCurrentData()) { found = true; break; }
								if (!found)
									ppath.value.push({name:data.datadisplay.name,value:field.getCurrentData()});
							} else
								ppath.value.push({name:data.name,value:field.getCurrentData()});
						}
						peoples.push(people);
					}
					if (peoples.length == 0) {
						alert("You didn't enter anybody to create");
						popup.unfreeze();
						return;
					}
					popup.removeButtons();
					var data = {peoples:peoples};
					data.sub_models = <?php echo json_encode(@$input["sub_models"]);?>;
					data.multiple = true;
					<?php 
					if (isset($input["ondone"])) echo "data.ondone = ".json_encode($input["ondone"]).";";
					else if (isset($input["donotcreate"])) echo "data.donotcreate = ".json_encode($input["donotcreate"]).";";
					?>
					postData("popup_create_people_step_check", data, window);
				});
				popup.addCancelButton(function () {
					confirm_dialog("Cancel creation ?",function(yes){
						if (yes) {
							<?php if (isset($input["oncancel"])) echo "window.frameElement.".$input["oncancel"]."();"; ?>
							popup.close();
						}
					});
					return false;
				});
			};
			window.top.require("popup_window.js", function() {
				win.popup_there();
			});
			</script>
			<?php
		} else {
			require_once("component/data_model/page/create_data.inc");
			$values = new DataValues();
			foreach ($input["fixed_columns"] as $v)
				$values->addTableColumnValue($v["table"], $v["column"], $v["value"]);
			foreach ($input["fixed_data"] as $v)
				$values->addTableDataValue($v["table"], $v["data"], $v["value"]);
			$prefilled_values = new DataValues();
			foreach ($input["prefilled_data"] as $v)
				$prefilled_values->addTableDataValue($v["table"], $v["data"], $v["value"]);
			foreach ($input["precreated"] as $pc) {
				$cat = DataModel::get()->getDataCategory($pc["category"]);
				if ($cat == null) continue;
				foreach ($cat->getTables() as $table_name) {
					$display = DataModel::get()->getTableDataDisplay($table_name);
					$data = $display->getDataDisplayByName($pc["data"], null, @$pc["sub_model"]);
					if ($data <> null) {
						// found it
						if (isset($pc["forced"]) && $pc["forced"])
							$values->addTableDataValue($table_name, $pc["data"], $pc["value"]);
						else
							$prefilled_values->addTableDataValue($table_name, $pc["data"], $pc["value"]);
						break;
					}
				}
			}
			
			$structure_name = createDataPage($this, "People", null, @$input["sub_models"], $values, $prefilled_values);
			?>
			<script type='text/javascript'>
			var structure = <?php echo $structure_name;?>;
			var popup = window.parent.get_popup_window_from_frame(window);
			popup.addNextButton(function() {
				popup.freeze("We are checking if the new people are already in the database...");
				var people = [];
				var error = null;
				for (var i = 0; i < structure.length; ++i) {
					var p = {path:structure[i].path};
					error = structure[i].validate();
					if (error != null) break;
					p.value = structure[i].getValue();
					p.columns = typeof structure[i].columns != 'undefined' ? structure[i].columns : [];
					people.push(p);
				}
				if (error != null) {
					alert("Please correct the data before to continue: "+error);
					popup.unfreeze();
					return;
				}
				popup.removeButtons();
				var data = {peoples:[people]};
				data.sub_models = <?php echo json_encode(@$input["sub_models"]);?>;
				<?php 
				if (isset($input["ondone"])) echo "data.ondone = ".json_encode($input["ondone"]).";";
				else if (isset($input["donotcreate"])) echo "data.donotcreate = ".json_encode($input["donotcreate"]).";";
				if (isset($input["oncancel"])) echo "data.oncancel = ".json_encode($input["oncancel"]).";";
				?>
				postData("popup_create_people_step_check", data, window);
			});
			popup.addCancelButton(function() {
				<?php if (isset($input["oncancel"])) echo "window.frameElement.".$input["oncancel"]."();"; ?>
				return true;
			});
			if (popup.isFrozen()) popup.unfreeze();
			</script>
			<?php
		} 
	}
	
}
?>