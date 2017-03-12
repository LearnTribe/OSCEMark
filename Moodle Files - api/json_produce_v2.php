<?php
	header('Content-Type: application/json');
	require_once('../config.php');
	
	
	//$params = array();
	//$params = array('id' => 2);
	//$course = $DB->get_record('course', $params, '*', MUST_EXIST);
	
	
	//require_login($course);
	
	class Response{
		public $response = "";
	}
	
	class Course{
		public $id=0;
		public $name="";
		public $course=0;
		public $course_name="";
	}
	
	class Feedback{
		public $id=0;
		public $name="";
		public $content="";
	}
	
	class Folder {
		public $id=0;
		public $name="";
	}
	
	class Exam{
		public $id=0;
		public $name="";
		public $intro="";
		public $addtemplate="";
		public $course=0;
		public $course_name="";
	}
	
	class Field {
		public $id=0;
		public $dataid=0;
		public $type="";
		public $name="";
		public $description="";
		public $param1="";
		public $param2="";
		public $param3="";
	}
	
	$dataToSend = array();
	
    // require_once($CFG->libdir.'/conditionlib.php');
    // require_once($CFG->libdir.'/completionlib.php');
	
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
	
	//$headerMoodle = $OUTPUT->header();
	//$html = htmlspecialchars($headerMoodle);
	//echo $header;
	
	//$ans = strstr($html,"Log out");
	//echo '<p>strstr: '.$ans[0].'|</p>';
	//echo '<p>'.$html.'</p>';
	//if ( !$ans ) {
	//	redirect(new moodle_url('/login/index.php'));
	//}
	
	if(!isset($_GET["action"]))
	{
		$res = new Response();
		$res->response = "download";
		//$jsonText = '{"response": "download"}';
		header('Content-Type: application/json');
		echo json_encode($res);
		return;
	}
	$action = clean_param($_GET["action"], PARAM_RAW);
	$id = 0;
		
	
	$json_title_array = array(
	'course' , 					//0
	'course_sections' , 		//1
	'course_categories' , 		//2
	'data' , 					//3
	'data_fields' , 			//4
	'course_modules', 			//5
	'data_content');			//6

	
	
	if ($action==1){
		$alldata = $DB->get_records_sql("SELECT distinct c.id, c.fullname as name FROM {".$json_title_array[0]."} c left join {".$json_title_array[2]."} cc on cc.id=c.category where cc.name = ? and cc.visible = ? ",array('OSCE_Exams', 1));
		
		foreach($alldata as $data){
			$postObj = new Folder();
			$postObj->id = intval($data->id);
			$postObj->name = $data->name;
			
			$dataToSend[] = $postObj;
		}
	}
	else if($action==2){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$query = "SELECT d.id as id,d.name as name, cs.id as course, cs.name as course_name FROM {".$json_title_array[1]."} cs join {".$json_title_array[5]."} cm on cs.id = cm.section join {".$json_title_array[3]."} d on d.id = cm.instance where cs.visible = 1  and cm.visible =1 and cs.section<>0 and d.name not like '%feedback%'";
		if ($id > 0)
			$query .=" and cs.id = ?";
		$alldata = $DB->get_records_sql($query, array($id));
		
		foreach($alldata as $data){
			$course = new Course();
			$course->id = intval($data->id);
			$course->name = $data->name;
			$course->course = intval($data->course);
			$course->course_name = $data->course_name;
			
			$dataToSend[] = $course;
		}
	}
	else if($action == 3){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$alldata = $DB->get_records_sql("SELECT D.id , D.name , D.intro , D.addtemplate , cs.name as course_name , cs.id as course FROM {".$json_title_array[3]."} d inner join {".$json_title_array[5]."} cm on cm.instance = d.id inner join {".$json_title_array[1]."} cs on cs.id=cm.section where d.id = ?",array($id));
		

		foreach($alldata as $data){
			$exam = new Exam();
			$exam->id = intval($data->id);
			$exam->name = $data->name;
			$exam->intro = removeSpecialChars("intro",$data->intro);
			$exam->addtemplate = removeSpecialChars("addtemplate",$data->addtemplate);
			$exam->course = intval($data->course);
			$exam->course_name = $data->course_name;
			
			$dataToSend[] = $exam;
		}
	}
	else if($action == 4){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$alldata = $DB->get_records_sql("SELECT DF.id, DF.dataid, DF.type, DF.name, DF.description, DF.param1, DF.param2, DF.param3 FROM {".$json_title_array[4]."} DF WHERE DF.dataid=?", array($id));
		
		foreach($alldata as $data){
			$field = new Field();
			$field->id = intval($data->id);
			$field->dataid = intval($data->dataid);
			$field->type = $data->type;
			$field->name = $data->name;
			$field->description = $data->description;
			$field->param1 = removeSpecialChars("param1",$data->param1);
			$field->param2 = $data->param2;
			$field->param3 = $data->param3;
			
			$dataToSend[] = $field;
		}
	}
	else if($action==5){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$alldata = $DB->get_records_sql("select cs.id as id, cs.name as name from mdl_course_sections cs left join mdl_course c on c.id = cs.course where cs.section <>0 and cs.visible = 1 and cs.name<>'' and c.id = ?", array($id));
		
		foreach($alldata as $data){
			$folder = new Folder();
			$folder->id = intval($data->id);
			$folder->name = $data->name;
			
			$dataToSend[] = $folder;
		}
	}
	else if($action==6){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$alldata = $DB->get_records_sql("SELECT dc.id , d.name , dc.content FROM {".$json_title_array[6]."} dc left join {".$json_title_array[4]."} df on df.id = dc.fieldid left join {".$json_title_array[3]."} d on d.id = df.dataid WHERE d.name like (select concat('%',(select TRIM(REPLACE(REPLACE(d.name,'DOPS',''),'OSCE','')) AS name from {".$json_title_array[3]."} d where d.id = ?),'%')) and d.name like '%feedback%'", array($id, $id));
		
		foreach($alldata as $data){
			$feedback = new Feedback();
			$feedback->id = intval($data->id);
			$feedback->name = $data->name;
			$feedback->content = $data->content;
			
			$dataToSend[] = $feedback;
		}
	}
	
	header('Content-Type: application/json');
	echo json_encode($dataToSend);
	
	
/*
	The function that generates the json file.
*/
//$jsonText = make_json("OSCE", array('id','name','intro', 'addtemplate'), $alldata);	
function removeSpecialChars($field, $dataValue){
	$trimmedValue = "";
	if(strcmp($field,"param1")==0){
		$trimmedValue = str_replace("\n", "|", $dataValue);
		$trimmedValue = str_replace("\r", "", $trimmedValue);
	}
	else if(strcmp($field,"addtemplate")==0){
		$trimmedValue = str_replace("\"", "'", $dataValue);
		$trimmedValue = str_replace("\n", " ", $trimmedValue);
		$trimmedValue = str_replace("\r", " ", $trimmedValue);
		//$trimmedValue = str_replace("<br>", " ", $trimmedValue);
		//$trimmedValue = preg_replace("!\\r?\\n!", "", $trimmedValue);
	}
	else if(strcmp($field,"intro")==0){
		$trimmedValue = str_replace("\"", "'", $dataValue);
		$trimmedValue = str_replace("\n", " ", $trimmedValue);
		$trimmedValue = str_replace("\r", " ", $trimmedValue);
		$trimmedValue = str_replace("<br>", " ", $trimmedValue);
		$trimmedValue = str_replace("</ br>", " ", $trimmedValue);
	}
	
	return $trimmedValue;
}

	
	
	
	
	
	

?>