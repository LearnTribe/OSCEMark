# OSCEMark
App for use on Windows 8.1+ tablets to link to a Moodle site database activity to record student practical examinations offline and submit electronically
The app is installed using Windows Powershell, and must be configured to interact with a named Moodle site.
Two files must be installed on the Moodle site to allow download and upload of the results and exam form to the tablet:
- json_upload.php at /mod/data
- json_produce.php at /course

To Do
The default admin password is provided in documentation - it is recommended that a separate 'staff' account should be created within OSCEMark for use with the live exam environment on Moodle - the username and password for this user(s) should match an existing user on the Moodle site with editing rights on the course the database activity (OSCE exam) sits within
A sample database activity is provided that can be restored into an existing Moodle site course as a template - copies of this can then be modified to create the criteria and marking scheme (on a per exam basis) for your institutional requirements.
