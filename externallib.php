<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/report/log/locallib.php');

// Include the Log Manager to access the SQL Log Reader
use core\log\manager;

class local_wstemplate_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function hello_world_parameters() {
        return new external_function_parameters(
                array('welcomemessage' => new external_value(PARAM_TEXT, 'The welcome message. By default it is "Hello world,"', VALUE_DEFAULT, 'Hello world, '))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function hello_world($welcomemessage = 'Hello world, ') {
        global $USER;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::hello_world_parameters(),
                array('welcomemessage' => $welcomemessage));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }

        return $params['welcomemessage'] . $USER->firstname ;;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }

    /* ----------------------------------------------------------------------- */

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_available_courses_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_available_courses($welcomemessage = 'Hello world, ') {
        // Make the User object available in this function
        global $USER;
        // Make the DB object available in this function
        global $DB;

        //Parameter validation if any

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        /*
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
        */

        // Check courses that the user is enrolled in
        // $sql = 'SELECT * FROM {user};';
        $sql = "SELECT c.id AS 'courseid', c.shortname, u.id AS 'userid', u.username, ra.roleid FROM {course} c 
                LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid 
                LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid
                LEFT OUTER JOIN {user} u ON ra.userid = u.id 
                WHERE u.id = :u_id AND cx.contextlevel = :cx_level AND ra.roleid = :ra_roleid;";
        $queryparams = ['u_id' => $USER->id, 'cx_level' => '50', 'ra_roleid' => '3'];
        $result = $DB->get_records_sql($sql, $queryparams);

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_available_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'courseid' => new external_value(PARAM_INT, 'the course id'),
                    'shortname' => new external_value(PARAM_TEXT, 'the course shortname'),
                    'userid' => new external_value(PARAM_INT, 'the users id'),
                    'username' => new external_value(PARAM_TEXT, 'the username'),
                    'roleid' => new external_value(PARAM_INT, 'the role id in this course'),
                )
            )
        );
    }

    /* ----------------------------------------------------------------------- */
 
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_course_data_parameters() {
        return new external_function_parameters(
                array(
                    'courseid' => new external_value(PARAM_INT, 'the id of the course'),
                    'userid' => new external_value(PARAM_INT, 'filter results for this user (deactivated if "-1")', VALUE_DEFAULT, -1),
                    )
        );
    }


    /**
     * Query the reader. Store results in the object for use by build_table.
     * 
     * Taken from log/classes/event/table_log.php => method: setup_table(...).
     */
    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_course_data($courseid, $userid = -1) {
        global $USER;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::get_course_data_parameters(),
                array(
                    'courseid' => $courseid,
                    'userid' => $userid,
                ));

        $joins = array();
        $params = array();

        // Add course id as conditionto query
        $joins[] = "courseid = :courseid";
        $params['courseid'] = $courseid;


        // Add filter for specified user if any given
        if ($userid != -1) {
            $joins[] = "userid = :userid";
            $params['userid'] = $userid;
        }
/*
        // Add filter for specified time interval
        if (!empty($this->filterparams->date)) {
            $joins[] = "timecreated > :date AND timecreated < :enddate";
            $params['date'] = $this->filterparams->date;
            $params['enddate'] = $this->filterparams->date + DAYSECS; // Show logs only for the selected date.
        }
*/

        
        $selector = implode(' AND ', $joins);



        $logmanager = get_log_manager();
        $readers = $logmanager->get_readers('core\log\sql_reader');
        // TODO: only temporary select default reader. later specify exactly which one (should be the Standard Log reader...)
        $logreader = "";
        if (!empty($readers)) {
            reset($readers);
            $logreader = $readers[key($readers)];
        }
        else {
            throw new moodle_exception('nologreaderavailable');
        }
        $orderby = "timecreated ASC";
        $maxrecord = $logreader->get_events_select_count($selector, $params);
        $output = $logreader->get_events_select($selector, $params, $orderby, 0, $maxrecord);
        $result = array();
        foreach($output as $item){
            $result[] = $item->get_data();
        }

        // return var_dump($result);
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_data_returns() {
        // return new external_value(PARAM_TEXT, 'The welcome message + user first name');
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    // 'id' => new external_value(PARAM_TEXT, 'the action id'),
                    'action' => new external_value(PARAM_TEXT, 'the action type'),
                    'target' => new external_value(PARAM_TEXT, 'the target on which the action aims'),
                    'crud' => new external_value(PARAM_TEXT, 'the type of action (Create/Read/Update/Delete)'),
                    'eventname' => new external_value(PARAM_TEXT, 'the full moodle event name'),
                    'userid' => new external_value(PARAM_INT, 'the users id'),
                    'courseid' => new external_value(PARAM_TEXT, 'the course id'),
                    'timecreated' => new external_value(PARAM_INT, 'the creation time of the action'),
                )
            )
        );
    }
}
