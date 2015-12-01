<?php

	require_once('../../config.php');
	require_once('lib.php');
	// require_once("$CFG->libdir/rsslib.php");
	// require_once("$CFG->libdir/form/filemanager.php");
	
	//Check if user is loged in. If not redirect to home page.
	//echo $OUTPUT->header();
	//$ans = strpos($OUTPUT->header(),"Log out");
	
	
	// function curPageURL() {
	 // $pageURL = 'http';
	 // if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	 // $pageURL .= "://";
	 // if ($_SERVER["SERVER_PORT"] != "80") {
	  // $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	 // } else {
	  // $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	 // }
	 // return $pageURL;
	// }
		
	//$header = $OUTPUT->header();
	//$html = htmlspecialchars($header);
	
	//$ans = strstr($html,"Log out");
	//echo '<p>strstr: '.$ans[0].'|</p>';
	//echo '<p>'.$html.'</p>';
	//if ( !$ans ) {
	//	redirect(new moodle_url('/login/index.php'));
	//}
		
	//get the exam id
	if(!isset($_GET["examId"]) && !isset($_GET["id"]))
	{
		$jsonText = '{"response": "upload"}';
		header('Content-type: application/json');
		echo $jsonText;
		return;
	}
	
	$exam_id=clean_param($_GET["examId"], PARAM_RAW);
	
	//get the ids of the fields
	$idsnum = clean_param($_GET["id"], PARAM_RAW);
	
	
	$array = explode(",",$idsnum);
	$values = array();
	/*
	$i=0;
	foreach($array as $num){
		$values[$i++] = clean_param($_POST["field_".$num], PARAM_RAW);
	}
	$i=0;
	foreach($values as $value){
		if(($i<3) || ($i== count($values)-1)){
			echo $value."   |   ";
		}
		else{
			$mark = split("_",$value);
			$mark = $mark[count($mark)-1];
			echo $mark."   |   ";
		}
		$i++;
	}
	*/
	
	//echo '<p>1-------------1</p>';
	//$con=mysqli_connect("http://localhost/moodle","root","","moodle");
	//$alldatabycmc = mysqli_query($con,"SELECT * FROM mdl_course_modules");
	
	//echo '<p>-------2</p>';
	if (! $data = $DB->get_record('data', array('id'=>$exam_id))) {
        print_error('invalidid', 'data');
    }
	//echo '<p>-------3</p>';
    if (! $course = $DB->get_record('course', array('id'=>$data->course))) {
        print_error('coursemisconf');
    }
	//echo '<p>-------4</p>';
    if (! $cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
	//echo '<p>-------5</p>';
	$currentgroup = groups_get_activity_group($cm);
	
	
	//echo '<p>2-------------2</p>';
	//echo 'Exam id:'.$data->id.'|';
	
	if ($datarecord = data_submitted()){
	
		$ignorenames = array('MAX_FILE_SIZE','sesskey','d','rid','saveandview','cancel');  // strings to be ignored in input data
		//echo '<p>----------6</p>';
		$emptyform = true;      // assume the worst
		//echo '<p>----------7</p>';
		
		
        foreach ($datarecord as $name => $value) {
            if (!in_array($name, $ignorenames)) {
                $namearr = explode('_', $name);  // Second one is the field id

                if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
                    $field = data_get_field_from_id($namearr[1], $data);
                }
                if ($field->notemptyfield($value, $name)) {
                    $emptyform = false;
                    break;             // if anything has content, this form is not empty, so stop now!
                }
            }
        }
	
		if (!$emptyform && $recordid = data_add_record($data, $currentgroup)) {    //add instance to data_record
			/// Insert a whole lot of empty records to make sure we have them
			$fields = $DB->get_records('data_fields', array('dataid'=>$data->id));
			
			
			
			
			foreach ($fields as $field) {
				$content = new stdClass();
				$content->recordid = $recordid;
				$content->fieldid = $field->id;
				$DB->insert_record('data_content',$content);
			}
			
			
			/// For each field in the add form, add it to the data_content.
			foreach ($datarecord as $name => $value){
				if (!in_array($name, $ignorenames)) {
					$namearr = explode('_', $name);  // Second one is the field id
					if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
						$field = data_get_field_from_id($namearr[1], $data);
					}
					if ($field) {
						$field->update_content($recordid, $value, $name);
					}
				}
			}

			if (!empty($datarecord->saveandview)) {
				//redirect($CFG->wwwroot.'/mod/data/view.php?d='.$data->id.'&rid='.$recordid);
			}
			$jsonText = '{"upload": "success"}';
			
		}
	}
	else{
		$jsonText = '{"upload": "fail"}';
	}
	
	//$json_title_array = array('course' , 'course_sections' , 'course_categories' , 'data' , 'data_fields');

	
	header('Content-type: application/json');
	echo $jsonText;
	


?>
