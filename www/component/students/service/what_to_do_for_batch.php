<?php 
class service_what_to_do_for_batch extends Service {
	
	public function getRequiredRights() { return array("manage_batches"); }
	
	public function documentation() {}
	public function inputDocumentation() {}
	public function outputDocumentation() {}
	
	public function getOutputFormat($input) { return "text/html"; }
	
	public function execute(&$component, $input) {
		$this->batch_id = $input["batch"];
		
		// no student in batch
		$student = SQLQuery::create()->select("Student")->whereValue("Student","batch",$this->batch_id)->limit(0, 1)->executeSingleRow();
		if ($student == null) {
			echo "<img src='".theme::$icons_16["warning"]."' style='vertical-align:bottom'/> No student in batch ".toHTML($this->getBatchName())."<br/>";
		}
		
		// no periods in batch
		$periods = PNApplication::$instance->curriculum->getBatchPeriods($this->batch_id);
		if (count($periods) == 0) {
			echo "<img src='".theme::$icons_16["warning"]."' style='vertical-align:bottom'/> No period defined in batch ".toHTML($this->getBatchName())."<br/>";
		}

		if (count($periods) > 0) {
			// no classes in period(s)/specialization(s)
			$problems = array();
			$periods_ids = array(); foreach ($periods as $p) array_push($periods_ids, $p["id"]);
			$periods_spes = PNApplication::$instance->curriculum->getBatchPeriodsSpecializations($periods_ids);
			$classes = PNApplication::$instance->curriculum->getAcademicClasses($this->batch_id);
			foreach ($periods as $period) {
				$spe_list = array();
				foreach ($periods_spes as $ps) if ($ps["period"] == $period["id"]) array_push($spe_list, $ps["specialization"]);
				if (count($spe_list) == 0) {
					// no specialization
					$list = array();
					foreach ($classes as $cl) if ($cl["period"] == $period["id"]) array_push($list, $cl);
					if (count($list) == 0) {
						$problems[$period["name"]] = array();
					}
				} else {
					foreach ($spe_list as $spe_id) {
						$list = array();
						foreach ($classes as $cl) if ($cl["period"] == $period["id"] && $cl["specialization"] == $spe_id) array_push($list, $cl);
						if (count($list) == 0) {
							if (!isset($problems[$period["name"]])) $problems[$period["name"]] = array();
							array_push($problems[$period["name"]],$this->getSpecializationName($spe_id));
						}
					}
				}
			}
			if (count($problems) > 0) {
				echo "<img src='".theme::$icons_16["warning"]."' style='vertical-align:bottom'/> Batch ".toHTML($this->getBatchName()).": No class in ";
				$first = true;
				foreach ($problems as $period_name=>$spe_list) {
					if ($first) $first = false; else echo ", ";
					echo toHTML($period_name);
					if (count($spe_list) > 0) {
						echo " (specialization";
						if (count($spe_list) > 1) echo "s";
						echo " ";
						$first_spe = true;
						foreach ($spe_list as $spe_name) {
							if ($first_spe) $first_spe = false; else echo ",";
							echo toHTML($spe_name);
						}
						echo ")";
					}
				}
			}
		}
		
		if ($student <> null && count($periods) > 0) {
			// there are students in the batch, and periods are defined
			$past_periods = array();
			$current_period = null;
			$next_period = null;
			$now = time();
			foreach ($periods as $period) {
				$ap = PNApplication::$instance->curriculum->getAcademicPeriodFromBatchPeriod($period["id"]);
				$start = datamodel\ColumnDate::toTimestamp($ap["start"]);
				$end = datamodel\ColumnDate::toTimestamp($ap["end"]);
				if ($end < $now)
					array_push($past_periods, array($period,$ap));
				else if ($start < $now && $end > $now)
					$current_period = array($period,$ap);
				else if ($next_period == null || $start < datamodel\ColumnDate::toTimestamp($next_period[1]["start"]))
					$next_period = array($period,$ap);
			}
			
			$relevant_periods = array_merge($past_periods);
			if ($current_period <> null) array_push($relevant_periods, $current_period);
			else if ($next_period <> null) array_push($relevant_periods, $next_period);

			$all_students = SQLQuery::create()->select("Student")->whereValue("Student","batch",$this->batch_id)->execute();
			$students_ids = array();
			foreach ($all_students as $s) array_push($students_ids, $s["people"]);
			$students_classes = SQLQuery::create()->select("StudentClass")->whereIn("StudentClass","people",$students_ids)->execute();
			$all_students_classes = array();
			$additional_classes = array();
			foreach ($students_classes as $sc) {
				if (isset($all_students_classes[$sc["class"]])) continue;
				$found = false;
				foreach ($classes as $c) if ($c["id"] == $sc["class"]) { $all_students_classes[$sc["class"]] = $c; $found = true; break; }
				if ($found) continue;
				// different class, probably a student who changed from one batch to another
				$all_students_classes[$sc["class"]] = $c = PNApplication::$instance->curriculum->getAcademicClass($sc["class"]);
				$additional_classes[$c["id"]] = PNApplication::$instance->curriculum->getAcademicPeriodAndBatchPeriod($c["period"]);
			}
				
			// check if students are not assigned to a class, and specialization
			$spe_checked = false;
			foreach ($relevant_periods as $p) {
				$spe_list = array();
				foreach ($periods_spes as $ps) if ($ps["period"] == $p[0]["id"]) array_push($spe_list, $ps["specialization"]);
				if (count($spe_list) == 0) {
					// no specialization
					$list = array();
					foreach ($classes as $cl) if ($cl["period"] == $p[0]["id"]) array_push($list, $cl);
					if (count($list) > 0) {
						$classes_ids = array();
						foreach ($list as $cl) array_push($classes_ids, $cl["id"]);
						foreach ($additional_classes as $cl=>$per) if ($per["academic_period_id"] == $p[1]["id"]) array_push($classes_ids, $cl);
						$students = array();
						foreach ($all_students as $s)
							if ($s["exclusion_date"] == null || datamodel\ColumnDate::toTimestamp($s["exclusion_date"]) > datamodel\ColumnDate::toTimestamp($p[1]["start"]))
								array_push($students, $s["people"]);
						foreach ($students_classes as $assign) {
							if (!in_array($assign["class"], $classes_ids)) continue;
							for ($i = 0; $i < count($students); $i++)
								if ($students[$i] == $assign["people"]) {
									array_splice($students, $i, 1);
									break;
								}
						}
						if (count($students) > 0) {
							echo "<img src='".theme::$icons_16["warning"]."' style='vertical-align:bottom'/> ".count($students)." student".(count($students)>1 ? "s are" : " is")." not assigned to a class for period ".toHTML($p[0]["name"])." of batch ".toHTML($this->getBatchName())."<br/>";
						}
					}
				} else {
					if (!$spe_checked) {
						$spe_checked = true;
						$students = array();
						foreach ($all_students as $s)
							if ($s["specialization"] == null && ($s["exclusion_date"] == null || datamodel\ColumnDate::toTimestamp($s["exclusion_date"]) > datamodel\ColumnDate::toTimestamp($p[1]["start"])))
								array_push($students, $s["people"]);
						if (count($students) > 0) {
							echo "<img src='".theme::$icons_16["warning"]."' style='vertical-align:bottom'/> ".count($students)." student".(count($students)>1 ? "s are" : " is")." not assigned to a specialization for batch ".toHTML($this->getBatchName())."<br/>";
						}
					}
					foreach ($spe_list as $spe_id) {
						$list = array();
						foreach ($classes as $cl) if ($cl["period"] == $p[0]["id"] && $cl["specialization"] == $spe_id) array_push($list, $cl);
						if (count($list) > 0) {
							$classes_ids = array();
							foreach ($list as $cl) array_push($classes_ids, $cl["id"]);
							foreach ($additional_classes as $cl=>$per) if ($per["academic_period_id"] == $p[1]["id"]) array_push($classes_ids, $cl);
							$students = array();
							foreach ($all_students as $s) 
								if ($s["specialization"] == $spe_id && ($s["exclusion_date"] == null || datamodel\ColumnDate::toTimestamp($s["exclusion_date"]) > datamodel\ColumnDate::toTimestamp($p[1]["start"])))
									array_push($students, $s["people"]);
							foreach ($students_classes as $assign) {
								if (!in_array($assign["class"], $classes_ids)) continue;
								for ($i = 0; $i < count($students); $i++)
									if ($students[$i] == $assign["people"]) {
										array_splice($students, $i, 1);
										break;
									}
							}
							if (count($students) > 0) {
								$peoples = PNApplication::$instance->people->getPeoples($students);
								$names = "";
								foreach ($peoples as $people)
									$names .= $people["last_name"]." ".$people["first_name"]."\r\n";
								echo "<span title=\"$names\"><img src='".theme::$icons_16["warning"]."' style='vertical-align:bottom'/> ".count($students)." student".(count($students)>1 ? "s are" : " is")." not assigned to a class in specialization ".toHTML($this->getSpecializationName($spe_id))." for period ".toHTML($p[0]["name"])." of batch ".toHTML($this->getBatchName())."</span><br/>";
							}
						}
					}
				}
				
			}
		}
	}
	
	private $batch_id;
	private $batch = null;
	private $specializations = null;
	private function getBatchName() {
		if ($this->batch == null)
			$this->batch = PNApplication::$instance->curriculum->getBatch($this->batch_id);
		return $this->batch["name"];
	}
	private function getSpecializationName($spe_id) {
		if ($this->specializations === null)
			$this->specializations = PNApplication::$instance->curriculum->getSpecializations();
		foreach ($this->specializations as $s)
			if ($s["id"] == $spe_id)
				return $s["name"];
	}
	
}
?>