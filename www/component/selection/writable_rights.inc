<?php 
return array(
	new AccessRightCategory("Selection",array(
		// information session
		new BooleanRight("manage_information_session","Add/Remove/Edit Information Sessions",array(true=>array("see_information_session"=>true))),
		// exam
		new BooleanRight("manage_exam_subject","Edit Exam Subjects",array(true=>array("see_exam_subject"=>true))),
		new BooleanRight("manage_exam_center","Edit Exam centers",array(true=>array("see_exam_center"=>true))),
		new BooleanRight("manage_exam_rules","Edit Exam Eligibility Rules",array(true=>array("manage_exam_subject"=>true,"see_exam_rules"=>true))),
		// interview
		new BooleanRight("manage_interview_center","Manage interview centers",array(true=>array("see_interview_center"=>true))),
		new BooleanRight("manage_interview_criteria","Manage Interview Criteria and Rules",array(true=>array("see_interview_criteria"=>true))),
		// applicants
		new BooleanRight("edit_applicants","Create/Import/Remove/Edit applicants",array(true=>array("see_applicant_info"=>true))),
		new BooleanRight("edit_application_form_fields","Edit the list of information we can specify for each applicant",array(true=>array("edit_applicants"=>true))),
		// general
		new BooleanRight("manage_selection_campaign","Add/Remove/Configure a selection campaign",array(true=>array("can_access_selection_data"=>true))),
	)),
);


//TODO update the rights for the calendar in selection.inc#createCampaign



?>