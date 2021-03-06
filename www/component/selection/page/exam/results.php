<?php 
require_once("component/selection/page/SelectionPage.inc");
class page_exam_results extends SelectionPage {
	public function getRequiredRights() { return array(); }
	public function executeSelectionPage(){
			
		theme::css($this, "section.css");

		$this->requireJavascript("jquery.min.js");
		$this->requireJavascript("address_text.js");
		$this->addJavascript("/static/widgets/section/section.js");
		$this->onload("sectionFromHTML('sessions_list');");
		$this->onload("sectionFromHTML('session_info');");
		$this->onload("sectionFromHTML('session_applicants');"); 
		$this->addJavascript("/static/selection/exam/results.js");
		$this->requireJavascript("data_list.js");
		$this->onload("createDataList(".$this->component->getCampaignId().");");
		$this->onload("initResults()");
	?>
	

		<style>
			table>tbody>tr>td {
				text-align: center;
			}
			table>tbody>tr.clickable_row:hover{
			background-color: #FFF0D0;
			background: linear-gradient(to bottom, #FFF0D0 0%, #F0D080 100%);
			}
			
			table>tbody>tr.selectedRow{
			background-color: #FFF0D0;
			background: linear-gradient(to bottom, #FFF0D0 0%, orange 100%);
			}
		</style>
			
	<!-- main structure of the exam results page -->
	<div style="margin:10px;display:flex;flex-direction:row">
		<div id="sessions_with_button" style="display: inline-block;vertical-align: top;flex:1 1 auto;">
			<div style ="max-height: 300px;overflow: auto">
			      <div id = "sessions_list" title='Exam sessions list' icon="/static/calendar/calendar_16.png" collapsable='true' css="soft">
				<?php $this->createTableSessionsList();?>
			      </div>
			</div>
			<div id="sessions_buttons" style="position:relative;margin-right: 5px;height:28px;background-color:rgb(229,190,212);border-radius:3px;">
				<button id="edit_notes" class="action" style="position:absolute;top:0;bottom: 0;left:0;right:0;width:9%;height: 58%;margin: auto;" >EDIT NOTES</button>
			</div>
		</div>
		<div id = "session_info" title='Exam session informations' icon="/static/theme/default/icons_16/info.png" collapsable='true' style='display:inline-block;vertical-align: top;flex:none;height:100%;' css="soft">
			<div id="session_info_location" style='padding-left:5px;'></div>
		</div>
	</div>
		

	<!--List of applicants-->		
	<div id = "session_applicants" title='Applicants list' icon="/static/selection/applicant/applicants_16.png" collapsable='true' css="soft" style="width:500px;margin: 20px 0 0 10px;" >
	       <div id="session_applicants_list"></div>
	</div>

	<?php
	}
	/*
	 * Generate html Table element displaying Sessions List (grouped by Exam Center)
	 */
	private function createTableSessionsList()
		{
			$q = SQLQuery::create()->select("ExamCenter")
					->field("ExamCenter","name")
					->field("ExamCenter","id","center_id")
					->field("CalendarEvent","start")
					->field("CalendarEvent","end")
					->field("ExamCenterRoom","name","room_name")
					->field("ExamCenterRoom","id","room_id")
					->countOneField("Applicant","applicant_id","applicants")
					->join("ExamCenter","ExamSession",array("id"=>"exam_center"))
					->whereNotNull("ExamSession","event")
					->join("ExamSession","Applicant",array("event"=>"exam_session"))
					->field("ExamSession","event","session_id")
					->join("Applicant","ExamCenterRoom",array("exam_center_room"=>"id"));
			PNApplication::$instance->calendar->joinCalendarEvent($q, "ExamSession", "event");
			$exam_sessions=$q->groupBy("ExamSession","event")->groupBy("ExamCenterRoom","id")->execute();

		?>
			<table class="grid" id="table_exam_results" style="width: 100%">
				<thead>
					<tr>
					      <th>Exam Session</th>
					      <th>Room</th>
					      <th>Applicants</th>
					      <th>Status</th>					      
					</tr>
				</thead>
				<tbody>
			<?php
			$exam_center_id=null;
			foreach($exam_sessions as $exam_session){
				$session_name=date("Y.m.d",$exam_session['start'])." (".date("h:i:a",$exam_session['start'])." to ".date("h:i:a",$exam_session['end']).")";
				if ($exam_center_id<>$exam_session['center_id']){ // Group for a same exam center
				       $exam_center_id=$exam_session['center_id'] ?>
				       <tr class="exam_center_row" >
					       <th colspan="4" ><?php echo $exam_session['name'];?></th>
				       </tr><?php } //end of if statement ?> 
					<tr  class="clickable_row" style="cursor: pointer" session_id="<?php echo $exam_session['session_id'];?>" room_id="<?php echo $exam_session['room_id'];?>" exam_center_id="<?php echo $exam_center_id;?>" > 
						<td><?php echo $session_name ?></td>
						<td><?php echo $exam_session['room_name'] ?></td>
						<td><?php echo $exam_session['applicants'] ?></td>
						<td><?php echo 'TODO..' ?></td>
					</tr>
				<?php } // end of foreach statement ?>
				</tbody>
			</table>
		<?php
		}
}
?>