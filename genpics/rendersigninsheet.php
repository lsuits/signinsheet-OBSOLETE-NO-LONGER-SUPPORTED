<?php
global $CFG, $DB;
require_login();
/*
 * 
 * 
 *
 * 
 * */ 
function renderPicSheet(){
	global $DB, $cid, $CFG, $OUTPUT;
        $pageCounter = 0;
        $userPicture = '';
        $j = 0;
        $usersPerRow = get_config('block_signinsheet', 'columnsPerRow');
        $rowsPerPage = get_config('block_signinsheet', 'rowsPerPage');
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
	$courseName = $DB->get_record('course', array('id'=>$cid), 'fullname', $strictness=IGNORE_MISSING); 

        while(!empty($result)){
            $pageCounter++;

        $parentDivOpen = html_writer::start_tag('div', array('class' => 'placeholder'));
        $parentDivClose = html_writer::end_tag('div');
        $rowDivOpen = html_writer::start_tag('div', array('class' => 'ROWplaceholder'));
        $title = html_writer::div(html_writer::tag('p',$courseName->fullname . ' &mdash; ' . get_string('signaturesheet', 'block_signinsheet') . ': page ' . $pageCounter), NULL, array('class' => 'rolltitle center'));
        $disclaimer = html_writer::tag('p',get_string('disclaimer', 'block_signinsheet'), array('class' => 'center disclaimer'));

	    foreach($result as $face){
		$j++;
		$userPicture .= html_writer::div($OUTPUT->user_picture($face, array('size' => 100, 'class' => 'welcome_userpicture')) . html_writer::tag('p',$face->firstname . ' ' . $face->lastname, array('class' => 'center')), NULL, array('class' => 'floatleft'));
            }
            echo $title;
            echo $userPicture;
            echo $disclaimer;
}
}
