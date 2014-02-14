

<?php
 
class block_signinsheet_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
       // echo " I AM IN EDITFORM FOR SIGNINSHEET BLOCK";
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
 
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_studentsPerPage', get_string('studentsPerPage', 'block_signinsheet'));
        $mform->setDefault('config_studentsPerPage', get_config('block_signinsheet', 'studentsPerPage'));
        $mform->setType('config_studentsPerPage', PARAM_TEXT);    
        //        $usersPerTable = get_config('block_signinsheet', 'studentsPerPage' );

 
    }
}