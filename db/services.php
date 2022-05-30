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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    local_moodle_ws_la_trace_exporter
 * @copyright  2020 HEIA-FR (heia-fr.ch/)
 * @author     Uchendu Nwachukwu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
  'local_moodle_ws_la_trace_exporter_get_available_courses' => array(
    'classname'   => 'local_moodle_ws_la_trace_exporter_external',
    'methodname'  => 'get_available_courses',
    'classpath'   => 'local/moodle_ws_la_trace_exporter/externallib.php',
    'description' => 'Return the list of courses where the user is enrolled as Teacher (editingteacher)',
    'type'        => 'read',
  ),

  'local_moodle_ws_la_trace_exporter_get_course_data' => array(
    'classname'   => 'local_moodle_ws_la_trace_exporter_external',
    'methodname'  => 'get_course_data',
    'classpath'   => 'local/moodle_ws_la_trace_exporter/externallib.php',
    'description' => 'Return the log for the given course',
    'type'        => 'read',
  ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
  'TB WAFED Web Services' => array(
    'functions' => array('local_moodle_ws_la_trace_exporter_get_available_courses', 'local_moodle_ws_la_trace_exporter_get_course_data'),
    'restrictedusers' => 0,
    'enabled' => 1,
    'shortname' => 'wafed_webservices'
  )
);
