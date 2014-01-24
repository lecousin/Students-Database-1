<?php 
function getArrayStepsToDisplay ($steps_to_display){
	$json = "";
	if(count($steps_to_display) == 0)
		$json.= "[];";
	else {
		$json.=  "[";
		$first = true;
		foreach($steps_to_display as $s){
			if(!$first)
				$json.=  ", ";
			$first = false;
			$json.=  "{name:".json_encode($s["name"]).", id:".json_encode($s["id"])."}";
		}
		$json.=  "]";
	}
	return $json;
}
require_once("selection_page.inc");
class page_selection_main_page extends selection_page {
	public function get_required_rights() { return array(); }
	public function execute_selection_page(&$page){
		$calendar_id = PNApplication::$instance->selection->get_calendar_id();
		$calendar_name = SQLQuery::create()->bypass_security()->select("Calendar")->field("name")->where("id",$calendar_id)->execute_single_value();
		
		$page->add_javascript("/static/widgets/page_header.js");
		$page->add_javascript("/static/widgets/splitter_vertical/splitter_vertical.js");
		$page->onload("new splitter_vertical('selection_main_page_split',0.35);");
		$page->add_javascript("/static/widgets/vertical_layout.js");
		$this->onload("new vertical_layout('right');");
		$this->onload("new vertical_layout('left');");
		//TODO set rights to calendar table? bypass_security required above...
		
		$status_to_display = include("selection_main_page_status_screens.inc");
		$steps = PNApplication::$instance->selection->getSteps();
		$unvalid_steps_to_display = array();
		$valid_steps_to_display = array();
		
	?>
		<!--<div style = 'color:red'>
		TODO main page
		</div>
		<div id = 'selection_steps_dashboard'></div>-->
		<div id = "selection_main_page_split" style = 'height:100%; width:100%'>
				<div id = 'left'>
					<div id = 'status_header'></div>
					<div style = "overflow:auto" layout = "fill">
						<?php
						echo "<div style = 'width:100%'>";
						$js_to_run = array();
						foreach($status_to_display as $s){
							$id = $page->generateID();
							echo "<div id = '".$id."' style = 'width:100%'></div>";
							if(!$steps[$s[0]])
								array_push($unvalid_steps_to_display,array(
									"id" => $id,
									"name" => $s[2]
								));
							else {
								array_push($valid_steps_to_display,array(
									"id" => $id,
									"name" => $s[2]
								));
								$url = $s[3];
								$url .= "/";
								$url .= $s[1];
								$page->add_javascript("/static/selection/".$url);
								$js_name = str_replace(".js","",$s[1]);
								array_push($js_to_run,"new ".$js_name."('content_".$id."');");
							}
						}
						echo "</div>";
						?>
					</div>
				</div>
				<div id = 'right'>
					<div id = "header_calendar"></div>
					<div style = "overflow:auto" layout = "fill">
						<div id = 'selection_calendar' style='height:80%; width:97%; margin-left:10px; margin-right:10px; margin-top:10px; border:1px solid;'></div>
					</div>
				</div>
		</div>
		<script type = 'text/javascript'>
			var calendar_id = null;
			var calendar_name = null;
			var steps = null;
			<?php
			if(isset($calendar_id)) echo "calendar_id = ".json_encode($calendar_id).";";
			if(isset($calendar_name)) echo "calendar_name = ".json_encode($calendar_name).";";
			if(isset($steps)) echo "steps = ".$steps.";";
			
			echo "var unvalid_steps_to_display = ";
			echo getArrayStepsToDisplay($unvalid_steps_to_display);
			echo ";";
			echo "var valid_steps_to_display = ";
			echo getArrayStepsToDisplay($valid_steps_to_display);
			echo ";";
			
			?>
			require(["calendar.js","popup_window.js"],function(){
				if(calendar_id != null && calendar_name != null){
					var cal_manager = new CalendarManager();
					var PN_cal = new PNCalendar(calendar_id, calendar_name, "C0C0FF", true, true);
					cal_manager.addCalendar(PN_cal);
					require("calendar_view.js",function(){
						new CalendarView(cal_manager, "week", "selection_calendar", function(){});
					});
					var div = document.getElementById("selection_calendar");
					setBorderRadius(div, 5, 5, 5, 5, 5, 5, 5, 5);
					var header_calendar = new page_header("header_calendar",true);
					header_calendar.setTitle("<img src = '/static/calendar/event.png'/> Selection Calendar");
					var extend = document.createElement("div");
					extend.className = "button";
					extend.innerHTML = "<img src = '"+theme.icons_10.popup+"'style:'vertical-align:top'/> Extend Calendar";
					extend.onclick = function(){
						
						var content = document.createElement("div");
						content.id = 'content_calendar_extend';
						var width = parseFloat(getWindowWidth())-30;
						var height = parseFloat(getWindowHeight())-60;
						content.style.width = width.toString()+"px";
						content.style.height = height.toString()+"px";
						content_cal_manager = new CalendarManager();
						content_PN_cal = new PNCalendar(calendar_id, calendar_name, "C0C0FF", true, true);
						content_cal_manager.addCalendar(content_PN_cal);
						require("calendar_view.js",function(){
							new CalendarView(content_cal_manager, "week", content, function(){});
						});
						var pop = new popup_window("Selection Calendar","/static/calendar/event.png",content);
						pop.show();
					};
					header_calendar.addMenuItem(extend);
				}
			});
			
			function setStatusScreens (unvalid_steps, valid_steps){
				var header = new page_header("status_header",true);
				header.setTitle("<img src = '"+theme.icons_16.dashboard+"'/> Selection Dashboard");
				var t = this;
				
				t._init = function(){
					//set the unvalid steps
					for(var i = 0; i < unvalid_steps.length; i++){
						var content = document.createElement("div");
						var container = document.getElementById(unvalid_steps[i].id);
						t._setContainerStyle(container);
						t._setUnvalidContent(content);
						var sec = new section("",unvalid_steps[i].name,content,true);
						container.appendChild(sec.element);
					}
					//set the valid steps
					for(var i = 0; i < valid_steps.length; i++){
						var content = document.createElement("div");
						var container = document.getElementById(valid_steps[i].id);
						t._setContainerStyle(container);
						t._prepareContainerForValidContent(content, valid_steps[i].id);
						var sec = new section("",valid_steps[i].name,content,true);
						container.appendChild(sec.element);
					}
					//once everything is set, run the js
					t._run();
				}
				
				t._setContainerStyle = function(container){
					container.style.width = "95%";
					container.style.marginLeft = "10px";
					container.style.marginTop = "15px";
				}
				
				t._setUnvalidContent = function(content){
					var back = document.createElement("div");
					// back.style.backgroundColor = "rgba(128,128,128,0.5)";
					back.innerHTML = "<center><i>This step is not validated yet</i></center>";
					content.appendChild(back);
				}
				
				t._prepareContainerForValidContent = function(content, id){
					var div = document.createElement("div");
					div.id = "content_"+id;
					content.appendChild(div);
				}
				
				t._run = function(){
					<?php
					foreach($js_to_run as $js)
						echo "\n".$js;
					?>
				}
				
				require("section.js",function(){
					t._init();
				});
			}
			
			new setStatusScreens(unvalid_steps_to_display, valid_steps_to_display);
		</script>
	<?php
	}
	
}