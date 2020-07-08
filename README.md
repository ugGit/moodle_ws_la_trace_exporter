# WAFED Moodle Webs Service plugin for Moodle 3.X

This plugin is necessary to allow the export of course specific activity log data to the Graasp Learning Analytics App.
The plugin essentially exposes 2 web services:

- get_available_courses: returns a list of courses where the user is enrolled as Teacher (editingteacher)
- get_course_data: returns the log for specified courses

In order to function properly, follow carefully the instructions below, which will assure that:

- web services and required protocols are enabled
- users can create a web token 
- the plugin is properly installed

## Configure Your Moodle Instance
As the plugin works with web services, they have to be enabled in your Moodle instance:

1. Access Site administration > Advanced features
2. Check 'Enable web services' then click 'Save Changes'
3. Access Site administration > Plugins > Web services > Manage protocols
4. Enable the REST protocol

Additionally, you must equip the users with some extended capabilities:

1. Access Site administration > Users > Permissions > Define roles
2. Assign (to Authenticated Users) the capability _moodle/webservice:createtoken_ to allow the generation of a security key
3. Assign (to Authenticated Users) the capability _webservice/rest:use_ to allow the use of the communication protocol

Note that assignment to Authenticated Users is only a suggestion. You could create as well a new system role called "Web Service User" or something alike.

Further information concerning the configuration of Moodle to enable web services can be found here: https://docs.moodle.org/38/en/Using_web_services


## Install Plugin

Now, you're ready to install the plugin. Therefore, choose one of the below approaches. More information concerning the installation of Moodle plugins is available on https://docs.moodle.org/39/en/Installing_plugins#Installing_a_plugin

### Installing Manually at the Server (recommended)
1. Login to your webserver
2. Navigate to /path/to/moodle/local/
3. Clone this github project into the folder by executing: `git clone https://gitlab.forge.hefr.ch/uchendu.nwachukw/wafed_moodle_webservice_plugin.git`
4. In your browser, login to your Moodle as admin.
5. You should be notified, that additional plugins are ready to be installed. Confirm the database upgrade.

Alternatively to clone the github project you may also copy the plugin using a different approach. Just make sure that the location and folder name are important.

### Installing via Uploaded ZIP File
1. Download the zip from this github project.
2. Login to your Moodle site as an admin and go to Administration > Site administration > Plugins > Install plugins.
3. Upload the ZIP file. You should only be prompted to add extra details (in the Show more section) if your plugin is not automatically detected.
4. If your target directory is not writable, you will see a warning message.
5. Check the plugin validation report



## Test the Plugin with Postman (optional)
You can test that all web service calls work using Postman by sending the following specified requests to your Moodle instance.

### Request a Token
It's important to note that the authenticated user should be **enrolled as Teacher (editingteacher) in at least one course**. Furthermore, it's not possible to request a token for an admin user, so make sure you use a regular user.

`[POST] {{yourMoodleUrl}}/login/token.php?username={{yourUsername}}&password={{yourPassword}}&service=wafed_webservices`

You should receive a response containing a `token`, which will be used in the next requests identify the user securely. E.g.:
```
{
    "token": "a7eb3737b6b61e33991217305e8c5e59",
    "privatetoken": null
}
```

### Get a List of Available Courses
`[POST] {{yourMoodleUrl}/webservice/rest/server.php?wstoken={{token}}&wsfunction=local_wafed_moodle_webservice_plugin_get_available_courses&moodlewsrestformat=json`

You should receive a response which lists all courses where the user is enrolled as Teacher. E.g.:
```
[
    {
        "courseid": 2,
        "shortname": "ttc",
        "userid": 4,
        "username": "john.doe",
        "roleid": 3
    },
    {
        "courseid": 4,
        "shortname": "ac",
        "userid": 4,
        "username": "john.doe",
        "roleid": 3
    }
}
```

### Get the Activity Log for a specific Course
`[POST] {{yourMoodleUrl}/webservice/rest/server.php?wstoken={{token}}&wsfunction=local_wafed_moodle_webservice_plugin_get_course_data&moodlewsrestformat=json&courseids[0]={{courseId}}`

You should receive a response which contains details over actions related to that course. E.g.:

```
{
    {
        "action": "viewed",
        "target": "course_module",
        "crud": "r",
        "contextlevel": "70",
        "edulevel": "2",
        "eventname": "\\mod_assign\\event\\course_module_viewed",
        "userid": 3,
        "role": "teacher",
        "relateduserid": null,
        "courseid": "2",
        "timecreated": 1592560102
    },
    {
        "action": "viewed",
        "target": "submission_status",
        "crud": "r",
        "contextlevel": "70",
        "edulevel": "0",
        "eventname": "\\mod_assign\\event\\submission_status_viewed",
        "userid": 3,
        "role": "teacher",
        "relateduserid": null,
        "courseid": "2",
        "timecreated": 1592560103
    }
}
```

## You're Done! Use it with the Graasp Learning Analytics App
Just by exposing these web services you won't get any wiser. Follow the instructions on https://github.com/graasp/graasp-app-moodle to import the data into an external application. Later this application will allow you to perform learning analytics on your Moodle data. This will make you wiser!
