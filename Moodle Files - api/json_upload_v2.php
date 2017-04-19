<?php

require_once('../../config.php');
require_once('lib.php');

class FormResponse{
	public $moodleExamID =0;
	public $tabletExamID =0;
	
	public function __construct($mfID, $fID)
	{
		$this->moodleExamID = $mfID;
		$this->tabletExamID = $fID;
	}
}

class Response{
	public $response = "";
	public $formList = array();
}

class Field{
	public $name = "";
	public $value = "";
}

$res = new Response();
//get the exam id
if(!isset($_GET["examId"]))
{
	$res->response = "upload";
	header('Content-Type: application/json');
	echo json_encode($res);
	return;
}



$json = file_get_contents('php://input');
$json_output = json_decode($json);

$ignorenames = array('MAX_FILE_SIZE','sesskey','d','rid','saveandview','cancel');  // strings to be ignored in input data

if ($json_output == null || count($json_output) == 0){
	$res->response = "List is empty";
	header('Content-Type: application/json');
	echo json_encode($res);
	return;
}


$res->response = "success";
foreach($json_output as $exam){
	if ($exam==null && $exam->fields==null && count($exam->fields) == 0)
		continue;
	
	$exam_id = $exam->moodleExamID;
	
	if (! $data = $DB->get_record('data', array('id'=>$exam_id))) {
		print_error('invalidid', 'data');
	}
	
	if (! $course = $DB->get_record('course', array('id'=>$data->course))) {
		print_error('coursemisconf');
	}
	
	if (! $cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
		print_error('invalidcoursemodule');
	}
	
	
	$currentgroup = groups_get_activity_group($cm);
	$emptyform = true;      // assume the worst
	
	
	// foreach here if there are a lot of fields
	$recordid = $exam->moodleRecordID;
	
	
	if ($recordid > 0){
		// Update fields/records
		$record = $DB->get_record('data_records', array(
			'id' => $recordid,
			'dataid' => $exam_id,
		), '*', MUST_EXIST);
		
		if ($record){
			$record->timemodified = time();
			$DB->update_record('data_records', $record);
			
			// for all fields inside the request update them
			// edit rid, 'content', field_136
			foreach($exam->fields as $eachField){
				$namearr = explode('_', $eachField->name);
				$field = data_get_field_from_id($namearr[1], $data);
				if ($field) {
					$field->update_content($recordid, $eachField->value, $eachField->name);
					$tabletForm = new FormResponse($recordid, $exam->tabletExamID);
					$res->formList[] = $tabletForm;
				}
				else{
					//error
				}
			}
		}
		else{
			//Add
			addRecord($data, $currentgroup, $res, $DB, $exam, $ignorenames);
		}
	}
	else{
		// Add new fields/records
		addRecord($data, $currentgroup, $res, $DB, $exam, $ignorenames);
	}
}


header('Content-type: application/json');
echo json_encode($res);


	
function addRecord($data, $currentgroup, &$res, $DB, $exam, $ignorenames) {
	// Add new fields/records
	$recordid = data_add_record($data, $currentgroup);
		
	if ($recordid>0) {  
		$tabletForm = new FormResponse($recordid, $exam->tabletExamID);
		$res->formList[] = $tabletForm;
		
		
		$fields = $DB->get_records('data_fields', array('dataid'=>$data->id));
		
		$records = array();
		foreach ($fields as $fieldd) {
			$content = new stdClass();
			$content->recordid = $recordid;
			$content->fieldid = $fieldd->id;
			$records[] = $content;
		}
		
		// Bulk insert the records now. Some records may have no data but all must exist.
		$DB->insert_records('data_content', $records);
		
		
		foreach($exam->fields as $eachField){
			if (!in_array($eachField->name, $ignorenames)) {
				$namearr = explode('_', $eachField->name);  // Second one is the field id

				$field = data_get_field_from_id($namearr[1], $data);
				if ($field) {
					$field->update_content($recordid, $eachField->value, $eachField->name);
				}
			}
		}
		
	}
	else{
		// could not create record
	}
	
}

?>