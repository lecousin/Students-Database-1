<?php 
$sm->addTable("ExamSubject")
	->addPrimaryKey("id")
	->addString("name",255,1,false,false) //cannot have two exam with the same name on the same campaign
	->addDecimal("max_score",10,2,0,null,false,false) // must be calculated
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);

$sm->addTable("ExamSubjectPart")
	->addPrimaryKey("id")
	->addForeignKey("exam_subject","ExamSubject",true,false,true,false)
	->addInteger("index",32,null,null,false,false)
	->addDecimal("max_score",10,2,0,null,false,false) // must be calculated
	->addString("name",255,0,true)
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);

$sm->addTable("ExamSubjectQuestion")
	->addPrimaryKey("id")
	->addForeignKey("exam_subject_part","ExamSubjectPart",true,false,true)
	->addInteger("index",32,null,null,false,false) // index of the question in this part(in case the parts index is updated)
	->addDecimal("max_score",10,2,0,null,false,false) // must be editable
	->addString("correct_answer") //optional
	->addString("choices") //optional
	->addReadAccess("see_exam_subject",true)
	;
	//no insert / write / remove right: must pass by a functionality that checks the steps, the exam topic linked...
	
$sm->addTable("ExamTopicForEligibilityRule")
	/**
	 * As exam topics and exam subject are synchronized by some functionalities,
	* the rights must be exactly the same!
	*/
	->addPrimaryKey("id")
	->addString("name",255,0,false)
	->addDecimal("max_score",10,2,0,null,false,false) //calculated
	->addInteger("number_questions",32,null,null,false,false)
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);

$sm->addTable("ExamTopicFullSubject")
	/** Table to match an exam topic with exam(s) subject(s)
	 * if a topic is set in this table, it will be considered as
	* "full subject" for all the matching exams subjects
	*/
	->addForeignKey("exam_subject","ExamSubject",true,false,false,false,true)//An exam subject can be set as full subject for only one topic, because otherwize it means the subject parts would appear in several topics
	->addForeignKey("exam_topic_for_eligibility_rule","ExamTopicForEligibilityRule",true,false,true,false)
	->setKey(array("exam_subject","exam_topic_for_eligibility_rule"))
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);

$sm->addTable("ExamPartTopic")
	->addForeignKey("exam_topic_for_eligibility_rule","ExamTopicForEligibilityRule",true,false,true,false)
	->addForeignKey("exam_subject_part","ExamSubjectPart",true,false,false,false,true)
	/** cannot be multiple because cannot appear in several topics (otherwize the rules are biased...)
	 */
	->setKey(array("exam_topic_for_eligibility_rule","exam_subject_part"))
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);

// 	$sm->addTable("ExamQuestionResult")
// 		->addForeignKey("applicant","Applicant",true,false,true,false)
// 		->addForeignKey("exam_subject_question","ExamSubjectQuestion",true,false,true,false)
// 		->addString("answer")//optional
// 		->addDecimal("score",10,2,0,null,false,false)
// 		->setKey(array("applicant","exam_subject_question"));
// 		//TODO finish + update steps.inc

//TODO Exam_part_result, exam_subject_result (reduce the calculation time for statistics...)

$sm->addTable("EligibilityRule")
	->addPrimaryKey("id")
	->addForeignKey("parent", "EligibilityRule", true, false, false, true)//Can only have one parent. If null, root level
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);
	//optional: addString name?

$sm->addTable("EligibilityRuleExamTopic")
	->addForeignKey("eligibility_rule", "EligibilityRule", true, false, true)
	->addForeignKey("exam_topic_for_eligibility_rule", "ExamTopicForEligibilityRule", true, false, true)
	->addDecimal("expected",10,2,null,null,false,false) //the expected grade for this topic
	->addDecimal("coefficient") //optional. If empty, considered as 1
	->setKey(array("eligibility_rule","exam_topic_for_eligibility_rule"))
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);


$sm->addTable("ExamCenter")
	->addPrimaryKey("id")
	->addString("name",255,0,false)//optional
	->addForeignKey("geographic_area", "GeographicArea", true, false, true, false, true)//An exam center must be linked to a geographic area, and this area is unique in this table
	->addReadAccess("see_exam_center_detail", true)
	->addWriteAccess("manage_exam_center", true)
	->addRemoveAccess("manage_exam_center", true)
	->addInsertAccess("manage_exam_center", true)
	->onInsert(function($fields,$id,$sub_model_instance) {
		try {
			PNApplication::$instance->news->post("selection", null, array("campaign".$sub_model_instance), "New Exam Center: <i>".$fields["name"]."</i>");
		} catch (Exception $e) { PNApplication::error($e); }
	});

$sm->addTable("ExamCenterPartner")
	->addForeignKey("exam_center","ExamCenter",true,false,true,false)
	->addForeignKey("organization","Organization",true,false,true,false)
	->addBoolean("host")
	->addForeignKey("host_address","PostalAddress",false,false,true,true) //this attributes is set only when host attribute == true: can select a praticular address in case the organization has several ones
	->setKey(array("exam_center","organization"))
	->addWriteAccess("manage_exam_center",true)
	->addReadAccess("see_exam_center_detail",true)
	->addInsertAccess("manage_exam_center",true)
	->addRemoveAccess("manage_exam_center",true);
		
$sm->addTable("ExamCenterContactPoint")
	->addForeignKey("exam_center","ExamCenter",true,false,true,false)
	->addForeignKey("organization","Organization",true,false,true,false)
	//This two attributes are a foreignKey for ExamCenter partner table
	->addForeignKey("people","People",true,false,true,false)
	//People + orga = foreignKey for ContactPoint table
	->setKey(array("exam_center","organization","people"))
	->addWriteAccess("manage_exam_center",true)
	->addReadAccess("see_exam_center_detail",true)
	->addInsertAccess("manage_exam_center",true)
	->addRemoveAccess("manage_exam_center",true)
	;

$sm->addTable("ExamCenterInformationSession")//An exam center can created from one or several information sessions
	->addForeignKey("exam_center", "ExamCenter", true, false, true)
	->addForeignKey("information_session", "InformationSession", true, false, false,false,true)//An information session can only be linked to one exam center
	->setKey(array("exam_center","information_session"))
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true)
	;
		
$sm->addTable("ExamSession")
	->addForeignKey("event", "CalendarEvent", true, true, false, false, true)
	->addForeignKey("exam_center", "ExamCenter", true, false, true)//An exam center can have several session
	->setPrimary("event")//The event is the primary key of a session
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);

$sm->addTable("ExamCenterRoom")
	->addPrimaryKey("id")
	->addForeignKey("exam_center", "ExamCenter", true, false, true)
	->addInteger("capacity",32,0,null,false,false)//The capacity of the room cannot be null
	->addString("name",255,0,false,false)//Can be custom or not, but must be set
	->addWriteAccess("manage_exam_subject",true)
	->addReadAccess("see_exam_subject",true)
	->addInsertAccess("manage_exam_subject",true)
	->addRemoveAccess("manage_exam_subject",true);

$sm->addTable("ExamSessionSupervisor")
	->addForeignKey("session", "ExamSession", true, false, true, false)
	->addForeignKey("people", "People", true, false, true, false)
	->setKey(array("session","people"))
	->addWriteAccess("manage_exam_center",true)
	->addReadAccess("see_exam_center_detail",true)
	->addInsertAccess("manage_exam_center",true)
	->addRemoveAccess("manage_exam_center",true);
?>