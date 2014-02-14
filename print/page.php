<?php

require_once("../../../config.php");

global $CFG, $DB;
require_login();
$PAGE->set_context(get_system_context());
require_once('../genlist/rendersigninsheet.php');
$PAGE->set_pagelayout('base');
$PAGE->set_url('/blocks/signinsheet/print/page.php');
$logoEnabled = get_config('block_signinsheet', 'customlogoenabled');

echo $OUTPUT->header();

$studentsPerPage = $this->config->studentsPerPage;

if($logoEnabled){
	printHeaderLogo();
}


$renderType = optional_param('rendertype', '', PARAM_TEXT);
if(isset($renderType)){
	
	if($renderType == 'all' || $renderType == ''){

                echo renderGroup($studentsPerPage);
		
	}
	else if($renderType == 'group'){
	
		echo renderGroup($studentsPerPage);
	
	}
	
} else {

	renderGroup($studentsPerPage);
}


?>

<script>window.print();</script> 

