<?php

require_once("../../../config.php");

global $CFG, $DB;
require_login();
$PAGE->set_context(get_system_context());
require_once('../genlist/rendersigninsheet.php');
$PAGE->set_pagelayout('print');
$PAGE->set_url('/blocks/signinsheet/print/page.php');
$logoEnabled = get_config('block_signinsheet', 'customlogoenabled');

echo $OUTPUT->header();

$usersPerTable = get_config('block_signinsheet', 'studentsPerPage' );

if($logoEnabled){
	printHeaderLogo();
}


$renderType = optional_param('rendertype', '', PARAM_TEXT);
if(isset($renderType)){
	
	if($renderType == 'all' || $renderType == ''){

                echo renderGroup($usersPerTable);
		
	}
	else if($renderType == 'group'){
	
		echo renderGroup($usersPerTable);
	
	}
	
} else {

	renderGroup($usersPerTable);
}


echo $OUTPUT->footer();
?>
<script>window.print();</script> 

