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
    public static function available_courses_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function available_courses($welcomemessage = 'Hello world, ') {
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
        $sql = "SELECT c.id AS 'course_id', c.shortname, u.id AS 'user_id', u.username, ra.roleid AS 'role_id' FROM {course} c 
                LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid 
                LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid
                LEFT OUTER JOIN {user} u ON ra.userid = u.id 
                WHERE u.id = '".$USER->id."' AND cx.contextlevel = '50' AND ra.roleid = '3';";
        $result = $DB->get_records_sql($sql);

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function available_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'course_id' => new external_value(PARAM_INT, 'the course id'),
                    'shortname' => new external_value(PARAM_TEXT, 'the course shortname'),
                    'user_id' => new external_value(PARAM_INT, 'the users id'),
                    'username' => new external_value(PARAM_TEXT, 'the username'),
                    'role_id' => new external_value(PARAM_INT, 'the role id in this course'),
                )
            )
        );
    }


}
