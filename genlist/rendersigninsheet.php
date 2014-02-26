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
function renderRollsheet(){
	global $DB, $cid, $CFG, $OUTPUT;
        $pageCounter = 0;
        $usersPerTable = get_config('block_signinsheet', 'studentsPerPage' );
	$cid = required_param('cid', PARAM_INT);
	$selectedGroupId = optional_param('selectgroupsec', '', PARAM_INT);
	$appendOrder = '';
	$orderBy = optional_param('orderby', '', PARAM_TEXT);		
		if($orderBy == 'byid'){
			$appendOrder = ' order by u.id';
		}
		else if($orderBy == 'firstname'){
			$appendOrder = ' order by u.firstname, u.lastname';
		}
		else if($orderBy == 'lastname'){
			$appendOrder = ' order by u.lastname, u.firstname';
		}
		 else {
			$appendOrder = ' order by u.lastname, u.firstname, u.idnumber';
		}

	// Check if we need to include a custom field
	$groupName = $DB->get_record('groups', array('id'=>$selectedGroupId), $fields='*', $strictness=IGNORE_MISSING); 
        if($groupName) {
                $query = 'SELECT u.id, gm.id, gm.groupid, gm.userid, r.shortname, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber
                                FROM {course} AS c
                                INNER JOIN {context} AS cx ON c.id = cx.instanceid AND cx.contextlevel = "50"
                                INNER JOIN {role_assignments} AS ra ON cx.id = ra.contextid
                                INNER JOIN {role} AS r ON ra.roleid = r.id
                                INNER JOIN {user} AS u ON ra.userid = u.id
                                INNER JOIN {groups_members} AS gm ON u.id = gm.userid
                                INNER JOIN {groups} AS g ON gm.groupid = g.id AND c.id = g.courseid
                                WHERE r.shortname = "student" AND gm.groupid = ?' . $appendOrder;
                $result = $DB->get_records_sql($query,array($selectedGroupId));
        } else {
                $query = 'SELECT u.id, u.id AS userid, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber
                                FROM {course} AS c
                                INNER JOIN {context} AS cx ON c.id = cx.instanceid AND cx.contextlevel = "50"
                                INNER JOIN {role_assignments} AS ra ON cx.id = ra.contextid
                                INNER JOIN {role} AS r ON ra.roleid = r.id
                                INNER JOIN {user} AS u ON ra.userid = u.id
                                WHERE r.shortname = "student" AND c.id = ?' . $appendOrder;
                $result = $DB->get_records_sql($query, array($cid));
	}

	$date = date('m-d-y');

	$courseName = $DB->get_record('course', array('id'=>$cid), 'fullname', $strictness=IGNORE_MISSING); 

        $totalUsers = count($result);

        while(!empty($result)){
            $pageCounter++;

	$title = html_writer::div(html_writer::tag('p',$courseName->fullname . ' &mdash; ' . get_string('signaturesheet', 'block_signinsheet') . ': page ' . $pageCounter), NULL, array('class' => 'rolltitle center'));

	$disclaimer = html_writer::tag('p',get_string('disclaimer', 'block_signinsheet'), array('class' => 'center disclaimer'));
	
	$colCounter = 0;
	$totalRows = 0;
            $k = 1;
	    $table = new html_table();
	    $table->attributes['class'] = 'roll';

            $addTextField = get_config('block_signinsheet', 'includecustomtextfield');
            $addIdField = get_config('block_signinsheet', 'includeidfield');
            $numExtraFields = get_config('block_signinsheet', 'numExtraFields');
            $emptyField = '';

            $userdata = array();
            
		$j = 0;

            $userdatas = array();

	    foreach($result as $face){
	        $j++;	
		$userdata = array($face->firstname . ' ' . $face->lastname);

                if($addIdField){
                    $userdata[1] = $face->idnumber;
                }

                if($addTextField){
                    $userdata[2] = ' ';
                }

		for ($i = 0; $i < $numExtraFields; $i++) {
                    $userdata[] = $emptyField;
		}

                array_shift($result);

	        $userdatas[$j] = $userdata;

                if ($k++ == $usersPerTable) { 
		    break;
		}


            }

	$table->head = array(get_string('fullName', 'block_signinsheet'));

        // Id number field
        if($addIdField){
                $table->head[1] = get_string('idnumber', 'block_signinsheet');
        }

        // Additional custom text field
        if($addTextField){
                $table->head[2] = get_config('block_signinsheet', 'customtext');
        }

        for ($i = 0; $i < $numExtraFields; $i++) {
           $table->head[] = get_string('date', 'block_signinsheet');
        }

	$table->data = $userdatas;

        echo $title;

	echo html_writer::table($table);

        echo $disclaimer;
    }
}
// FOR GRABBING PICS $OUTPUT->user_picture($face, array('size' => 75, 'class' => 'welcome_userpicture'))
