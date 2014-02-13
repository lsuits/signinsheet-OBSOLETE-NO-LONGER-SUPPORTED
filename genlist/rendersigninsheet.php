<?php
global $CFG, $DB;
require_login();

/*
 * 
 * Retrieve and print the logo for the top of the
 * sign in sheet.
 * 
 * */
function printHeaderLogo(){
	global $DB;
 	$imageURL =  $DB->get_field('block_signinsheet', 'field_value', array('id'=>1), $strictness=IGNORE_MISSING);
	echo '<img src="'.$imageURL.'"/><br><div class="printHeaderLogo"></div>';
}

/*
 * 
 * 
 *
 * 
 * */ 
function renderGroup(){
	global $DB, $cid, $CFG;
	$outputHTML = '';
	$cid = required_param('cid', PARAM_INT);
	$selectedGroupId = optional_param('selectgroupsec', '', PARAM_INT);
	$appendOrder = '';
	$orderBy = optional_param('orderby', '', PARAM_TEXT);		
		if($orderBy == 'byid'){
			$appendOrder = ' order by userid';
		}
		else if($orderBy == 'firstname'){
			$appendOrder = ' order by  (select firstname from '.$CFG->prefix.'user usr where userid = usr.id)';
		}
		else if($orderBy == 'lastname'){
			$appendOrder = ' order by  (select lastname from '.$CFG->prefix.'user usr where userid = usr.id)';
		}
		 else {
			$appendOrder = ' order by userid';
		}

	// Check if we need to include a custom field
	$addFieldEnabled = get_config('block_signinsheet', 'includecustomfield');
	$groupName = $DB->get_record('groups', array('id'=>$selectedGroupId), $fields='*', $strictness=IGNORE_MISSING); 
        if($groupName) {
                $query = 'select * from '.$CFG->prefix.'groups_members where groupid = ?' . $appendOrder;
                $result = $DB->get_records_sql($query,array($selectedGroupId));
        } else {
                $query = "select userid from ".$CFG->prefix."user_enrolments en where en.enrolid IN (select e.id from ".$CFG->prefix."enrol e where courseid= ?)" . $appendOrder;
                $result = $DB->get_records_sql($query, array($cid));
	}

	$date = date('m-d-y');
	$courseName = $DB->get_record('course', array('id'=>$cid), 'fullname', $strictness=IGNORE_MISSING); 

	$outputHTML .= '<div class="titles">';
	$outputHTML .= '<p class="rolltitle center">'. get_string('signaturesheet', 'block_signinsheet') . '</p>';
	$rolltitle = '<table class="borderless center disclaimer"><tr><td class = "thirty">' . get_string('course', 'block_signinsheet') . ': ' . $courseName->fullname . '</td><td class = "thirty">' . get_string('teacher', 'block_signinsheet') . ': </td><td class = "thirty">' . get_string('room', 'block_signinsheet') . ': </td><br />';
	
	if($groupName){
        	$rolltitle = '<table class="borderless center disclaimer"><tr><td class = "thirty">' . get_string('course', 'block_signinsheet') . ': ' . $courseName->fullname . ' (' . $groupName->name . ')' . '</td><td class = "thirty">' . get_string('teacher', 'block_signinsheet') . ': </td><td class = "thirty">' . get_string('room', 'block_signinsheet') . ': </td><br />';
	}

        $outputHTML .= $rolltitle;

	$outputHTML .= '</div>';

	$outputHTML .= '
		<table class="roll">
			<tr>
				<td class="rolldata-rb">Name</td>';
	
	if($addFieldEnabled){
		$fieldId = get_config('block_signinsheet', 'customfieldselect');
		$fieldName = $DB->get_field('user_info_field', 'name', array('id'=>$fieldId), $strictness=IGNORE_MISSING);
		$outputHTML.='<td class="rolldata-rb">'.$fieldName.'</td>';
	}

	//Add custom field text if enabled
	$addTextField = get_config('block_signinsheet', 'includecustomtextfield');
	if($addTextField){
		$fieldData = get_config('block_signinsheet', 'customtext');
		$outputHTML.='<td class="rolldata-rb">'.$fieldData.'</td>';
	}	

	// Id number field enabled
	$addIdField = get_config('block_signinsheet', 'includeidfield');
	if($addIdField){	
		$outputHTML.='<td class="rolldata-rb">'. get_string('idnumber', 'block_signinsheet').'</td>';
	}

	$outputHTML .= '
	<td class="rolldata-rb">Absences</td>
	<td class="rolldata-rb">Date</td>
	<td class="rolldata-rb"></td>
	<td class="rolldata-rb"></td>
	<td class="rolldata-rb"></td>
	<td class="rolldata-rb"></td>
	<td class="rolldata-rb"></td>
	<td class="rolldata-rb"></td>
	</tr>';
	
	$colCounter = 0;
	$totalRows = 0;

	foreach($result as $face){
		$outputHTML .=  printSingleFace($face->userid, $cid);
	}

	$outputHTML .= '</tr></table>';
	$outputHTML .= '<p class="center disclaimer">'. get_string('disclaimer', 'block_signinsheet').'</p>';
	return $outputHTML;
}


/*
 *  Render a single profile face
 * 
 * 
 */
function printSingleFace($uid, $cid){
	global $DB, $OUTPUT;

	$singleRec = $DB->get_record('user', array('id'=> $uid), $fields='*', $strictness=IGNORE_MISSING); 
	
	$firstName = $singleRec->firstname;
	$lastname = $singleRec->lastname;

	$picOutput = '';
	
	global $PAGE; 
	
	$outputHTML =  '
	<tr>
		<td class="rolldata-rb">' . $firstName . ' ' . $lastname . '</td>';

	$addFieldEnabled = get_config('block_signinsheet', 'includecustomfield');
	
	// Include additional field data if enabled
	if($addFieldEnabled){
		$fieldId = get_config('block_signinsheet', 'customfieldselect');
		$fieldData = $DB->get_field('user_info_data', 'data', array('fieldid'=>$fieldId, 'userid'=>$uid), $strictness=IGNORE_MISSING);
		$outputHTML .=	'<td class="rolldata-rb">'.$fieldData.'  </td>';
	} else {
	}
	
	//Add custom field text if enabled
	$addTextField = get_config('block_signinsheet', 'includecustomtextfield');
	if($addTextField){
		$outputHTML .=	'<td class="rolldata-rb">  </td>';
	}

	// Id number field enabled
	$addIdField = get_config('block_signinsheet', 'includeidfield');
	if($addIdField){
		$outputHTML .=	'<td class="rolldata-rb"> '.$singleRec->idnumber.' </td>';
	}

	$outputHTML .='
	<td class="rolldata-rb ten-percent"></td>
	<td class="rolldata-rb ten-percent"></td>
	<td class="rolldata-rb ten-percent"></td>
	<td class="rolldata-rb ten-percent"></td>
	<td class="rolldata-rb ten-percent"></td>
	<td class="rolldata-rb ten-percent"></td>
	<td class="rolldata-rb ten-percent"></td>
	<td class="rolldata-rb ten-percent"></td>
	</tr>';

return $outputHTML;
}
