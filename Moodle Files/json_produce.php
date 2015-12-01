<?php

	require_once('../config.php');
    require_once('lib.php');
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
		$jsonText = '{"response": "download"}';
		header('Content-type: application/json');
		echo $jsonText;
		return;
	}
	$action = clean_param($_GET["action"], PARAM_RAW);
	$id = 0;
	//echo 'This is: |'.$action.'|</br>';

	
	//$con=mysqli_connect("http://localhost/moodle","root","","moodle");
	//$alldatabycmc = mysqli_query($con,"SELECT * FROM mdl_course_modules");


	
	
	$json_title_array = array(
	'course' , 					//0
	'course_sections' , 		//1
	'course_categories' , 		//2
	'data' , 					//3
	'data_fields' , 			//4
	'course_modules', 			//5
	'data_content');			//6

	//$jsonText = 'JSON_START';
	$jsonText = '';
	if($action==0){
	
	}
	else if ($action==1){
		$alldata = $DB->get_records_sql("SELECT distinct c.id, c.fullname as name FROM {".$json_title_array[0]."} c left join {".$json_title_array[2]."} cc on cc.id=c.category where cc.name = ? and cc.visible = ? ",array('OSCE_Exams', 1));
		
		$jsonText .= make_json("Courses", array('id' , 'name' ), $alldata);
		
	}
	else if($action==2){
		$id = clean_param($_GET["id"], PARAM_RAW);
		//echo "This is id: ".$id."<br>";

		$alldata = $DB->get_records_sql("SELECT d.id as id,d.name as name, cs.id as course FROM {".$json_title_array[1]."} cs join {".$json_title_array[5]."} cm on cs.id = cm.section join {".$json_title_array[3]."} d on d.id = cm.instance where cs.visible = 1  and cm.visible =1 and cs.section<>0 and d.name not like '%feedback%' and cs.id = ?", array($id));
		//$alldata = $DB->get_records_sql("SELECT distinct d.id, d.name, d.course FROM {".$json_title_array[3]."} d join mdl_course_modules cm on cm.instance = d.id where d.name not like '%feedback%' and cm.visible = 1 and d.course = ?",array($id));
		$jsonText = make_json("Exams", array('id','name','course'), $alldata);
	}
	else if($action == 3){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$alldata = $DB->get_records_sql("SELECT D.id, D.name, D.intro, D.addtemplate FROM {".$json_title_array[3]."} D WHERE D.id=?", array($id));
		
		$jsonText = make_json("OSCE", array('id','name','intro', 'addtemplate'), $alldata);		
	}
	else if($action == 4){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$alldata = $DB->get_records_sql("SELECT DF.id, DF.dataid, DF.type, DF.name, DF.description, DF.param1, DF.param2, DF.param3 FROM {".$json_title_array[4]."} DF WHERE DF.dataid=?", array($id));
		
		$jsonText = make_json("Fields", array('id','dataid','type','name','description','param1','param2','param3'), $alldata);
		
	}
	else if($action==5){
		$id = clean_param($_GET["id"], PARAM_RAW);
		$alldata = $DB->get_records_sql("select cs.id as id, cs.name as name from mdl_course_sections cs left join mdl_course c on c.id = cs.course where cs.section <>0 and cs.visible = 1 and cs.name<>'' and c.id = ?", array($id));
		
		$jsonText = make_json("Folders", array('id','name'), $alldata);
	}
	else if($action==6){
		$id = clean_param($_GET["id"], PARAM_RAW);
		//$alldata = $DB->get_records_sql("SELECT dc.id, d.name, dc.content FROM mdl_data_content dc left join mdl_data_fields df on df.id = dc.fieldid left join mdl_data d on d.id = df.dataid WHERE d.id in (SELECT d.id FROM mdl_data d where d.name like '%feedback%')  ");
		$alldata = $DB->get_records_sql("SELECT dc.id , d.name , dc.content FROM {".$json_title_array[6]."} dc left join {".$json_title_array[4]."} df on df.id = dc.fieldid left join {".$json_title_array[3]."} d on d.id = df.dataid WHERE d.name like (select concat('%',(select name from {".$json_title_array[3]."} d where d.id = ?),'%')) and d.name like '%feedback%'", array($id, $id));
				
				
		$jsonText = make_json("Feedback", array('id','name','content'), $alldata);
	}
	

	//$jsonText .= 'JSON_END';
	
	//echo '<br>Count rows: '.count($alldata).'<br><br>';
	
	
	
	header('Content-type: application/json');
	echo $jsonText;
	
	//echo $OUTPUT->footer();
	
	
/*
	The function that generates the json file.
*/
function make_json($title, $info, $alldata){
	
	
	
	//for the commas for the elements of the array
	$flag = 0;
	
	$jsonText = '{"'.$title.'": [';
	
	foreach ($alldata as $row){
		//for the commas in fields of each element of the json array
		$flag2=0;
		
		if($flag == 1)
			$jsonText .= ',';
			
		$jsonText .= '{';
		for($i=0; $i<count($info) ; $i++)
		{
		
			if($flag2==1)
				$jsonText .= ',';
			
			$details = $row->$info[$i];
			if(strcmp($info[$i],"param1")==0){
				$details = str_replace("\n", "|", $row->$info[$i]);
				$details = str_replace("\r", "", $details);
			}
			else if(strcmp($info[$i],"addtemplate")==0){
				$details = str_replace("\"", "'", $row->$info[$i]);
				$details = str_replace("\n", " ", $details);
				$details = str_replace("\r", " ", $details);
			}
			else if(strcmp($info[$i],"intro")==0){
				$details = str_replace("\"", "'", $row->$info[$i]);
				$details = str_replace("\n", " ", $details);
				$details = str_replace("\r", " ", $details);
			}
			$jsonText .= '"'.$info[$i].'": "'.$details.'"';
			$flag2=1;
		}
		$jsonText .=  '}';
		$flag = 1;
	}
	
	$jsonText .= ']}';
	
	return $jsonText;


}	
	
function make_data(){	

	/*
	{
	  "data": [
		{
		  "id": "",
		  "course": "",
		  "name": "",
		  "intro": "",
		  "singletemplate": ""
		},
		{
		  "id": "",
		  "course": "",
		  "name": "",
		  "intro": "",
		  "singletemplate": ""
		}
	  ]
	}
	*/

	
	
	//print the necessary data from the mdl_data table
	$info = array('id','course','name','intro','addtemplate');
	
	//for the commas for the elements of the array
	$flag = 0;
	
	$jsonText = '{<br>&#09;"data": [<br>';
	foreach ($alldatabycmc as $row){
		//for the commas in fields of each element of the json array
		$flag2=0;
		
		if($flag == 1)
			$jsonText .= ',<br>';
			
		$jsonText .= '&#09;{<br>';
		for($i=0; $i<count($info) ; $i++)
		{
			if($flag2==1)
				$jsonText .= ',<br>';
				
			$jsonText .= '&#09;&#09;"'.$info[$i].'": "'.$row->$info[$i].'"';
			//echo '<p>'.$info[$i].': '.$row->$info[$i].'|</p>';
			$flag2=1;
		}
		$jsonText .=  '<br>&#09;}';
		$flag = 1;
	}
	$jsonText .= '<br>&#09;]<br>
					}<br>';
	
	return $jsonText;
}
	
	
	
	

?>
