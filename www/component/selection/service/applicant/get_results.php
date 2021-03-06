<?php 
class service_applicant_get_results extends Service {
	
	private $subject; // the full exam subject (parts and questions included) 
	private $correct_answers; // list of correct answers for this subject
	private $current_applicant=array(); // datas related to the applicant being processed 
	
	public function getRequiredRights() { return array("see_applicant_info"); }
	
	public function documentation() { return "Compute results of applicants answers"; }
	public function inputDocumentation() { echo "Object containing subject and applicants answers "; }
	public function outputDocumentation() { echo "JSON object containing applicants computed scores"; }
	
	public function execute(&$component, $input) {
		
		extract($input,EXTR_REFS); // $subject and $applicants_exam
		$this->subject=$subject;
		
		/* getting correct answers list */
		$this->getCorrectAnswers();
					
		extract($applicants_exam,EXTR_REFS); // $exam_subject and $applicants_answers
		
		/* Computing exam score */
		array_walk($applicants_answers,array($this,'computeApplicantExamScore'));
		
		//Storing datas into database
		
		$this->storeApplicantsExam($applicants_answers);
		
		/* return datas to client side */ 
		echo json_encode($applicants_answers);
		
	}
	
	/* 
	* compute exam score for a given applicant
	* @param array $value an associative array representing on applicant exam ( keys 'applicant', 'subject_version', 'score', 'parts'(array))
	* @param integer $index The index of the applicant into array applicants_exam
	*/
	
	private function computeApplicantExamScore(&$value,$index)
	{
		
		extract($value,EXTR_REFS); //$applicant, $subject_version, $score, $parts(array)
		$this->current_applicant["id"]=$applicant;
		$this->current_applicant["subject_version"]=$subject_version;
		array_walk($parts,array($this,'computeApplicantExamPartScore'));
		
		/* computing total score of the exam */
		foreach($parts as $part)
			$score+=$part['score'];
			
		
	}
	
	/*
	 * compute part score
	 * @param array $value an associative array representing one part ( keys 'exam_subject_part','score', 'answers' (array))
	 * @param integer $index the index of the part into array parts
	 */
	private function computeApplicantExamPartScore(&$value,$index)
	{
		
		extract($value,EXTR_REFS); // $exam_subject_part,$score,$answers(array)
		/* computing score of each answer */
		array_walk($answers,array($this,'computeApplicantExamAnswerScore'));

		/* computing part score */
		foreach($answers as $answer)
			$score+=$answer['score'];
		
	}
	
	/*
	 * compute answer score
	 * @param array $value an associative array representing one answer (keys 'exam_subject_question','answer','score')
	 * @param integer $index the index of the answer into array answers
	 */
	
	private function computeApplicantExamAnswerScore(&$value,$index)
	{
		extract($value,EXTR_REFS); //$exam_subject_question,$answer,$score
		
		/* Looking for the question matching applicant answer */
		
		$question_found=false;
		
		foreach($this->subject["parts"] as $part){
			if ($question_found) break;
			foreach ($part["questions"] as $question)
			{
				if($question["id"]==$exam_subject_question){
					$question_found=true;
					/* computing applicant score */
					$score=$this->computeAnswerScore($question,$answer);
					break;
				}
			}
		}
		
	}
	/*
	 * computing the score of an applicant answer
	 *@param {array} question : associative array representing a question (keys "id","index","max_score","type","type_config")
	 *@param {string} answer : the answer of an applicant
	 *@return {integer} the score of the applicant
	 */
	private function computeAnswerScore($question,$applicant_answer)
	{
		
		foreach($this->correct_answers as $correct_answer)
		{
			/* finding the correct answer for this question and this version  */
			if (($correct_answer["question_id"]==$question["id"]) && ($correct_answer["version_id"]==$this->current_applicant["subject_version"]))
			{
				if ($applicant_answer==null || $applicant_answer=="")
					return 0; // no answer => 0 points
			
				if($applicant_answer==$correct_answer["answer"])
					return $question["max_score"]; // correct answer => max_score
				
				/* wrong answer */
				 
				switch($question["type"])
				{
					case 'mcq_single':
						return (-$question["max_score"]/($question["type_config"]-1)); // wrong answer
					break;
					case 'number':
						return -1; //TODO : configuration parameter ? 
					/* TODO : other cases to implement (not used at the time) */
				}
			}		
		}	
		
		return null; /* no answer found */
	}
	
	/* Getting Exam subject correct answers */
	private function getCorrectAnswers()
	{
		
		$q = SQLQuery::create()->select("ExamSubject")
			->join("ExamSubject","ExamSubjectPart",array("id" => "exam_subject"),null,array("exam_subject"=>$this->subject["id"]))
			->join("ExamSubjectPart","ExamSubjectQuestion",array("id" => "exam_subject_part"))
			->join("ExamSubjectQuestion","ExamSubjectAnswer",array("id" => "exam_subject_question"));
		
		$alias = $q->getTableAlias("ExamSubjectAnswer");
		if(!$alias)
			$alias = "ExamSubjectAnswer";	
		$q	
			->field($alias,"exam_subject_question","question_id")
			->field($alias,"exam_subject_version","version_id")
			->field($alias,"answer");
		
		$this->correct_answers = $q->execute();
		
	}
	
	
	/* storing datas into tables */
	private function storeApplicantsExam(&$applicants_answers)
	{
	
	   /* Preparing datas into arrays for SQL insertions */
	   
		$rows_exam=array();
		$rows_parts=array();
		$rows_answers=array();
		
		foreach($applicants_answers as $applicant_answers)
		{
			array_push($rows_exam,array('applicant'=>$applicant_answers['applicant'],'exam_subject'=>$this->subject['id'],'exam_subject_version'=>$applicant_answers['subject_version'],'score'=>$applicant_answers['score']));
			foreach($applicant_answers['parts'] as $part)
			{
				/* pushing part records for ApplicantExamSubjectPart table */
				array_push($rows_parts,array('applicant'=>$applicant_answers['applicant'],'exam_subject_part'=>$part['exam_subject_part'],'score'=>$part['score']));
				
				/* pushing answers records for ApplicantExamAnswer table */
				foreach($part['answers'] as $answer)
					array_push($rows_answers,array('applicant'=>$applicant_answers['applicant'],'exam_subject_question'=>$answer['exam_subject_question'],'answer'=>$answer['answer'],'score'=>$answer['score']));
			}
		}
		
	   /* Inserting into SQL tables */
	
	
	SQLQuery::create()->bypassSecurity()->removeRows("ApplicantExamSubject", $rows_exam);
	$ids = SQLQuery::create()->bypassSecurity()->insertMultiple("ApplicantExamSubject", $rows_exam);
	
	SQLQuery::create()->bypassSecurity()->removeRows("ApplicantExamSubjectPart", $rows_parts);
	$ids = SQLQuery::create()->bypassSecurity()->insertMultiple("ApplicantExamSubjectPart", $rows_parts);
	
	SQLQuery::create()->bypassSecurity()->removeRows("ApplicantExamAnswer", $rows_answers);
	$ids = SQLQuery::create()->bypassSecurity()->insertMultiple("ApplicantExamAnswer", $rows_answers);	
	}
	
	
}
?>