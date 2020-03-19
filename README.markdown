Naglite3.2
========
Clean, simple Nagios dashboard. Simple configuration. Filter dashboard on hostgroups.

![alt text](https://i.imgur.com/0E1kjpV.jpg "Nagios Dashboard")

Requirements
------------
- Web server of your choice with PHP support
- PHP 5.2 or newer
- git

Naglite3 must be installed on the same host where Nagios is running, as it
needs to read status.dat from Nagios.

Installation
------------

1. Switch to a directory accessible through your web server (e.g. /var/www/).
2. git clone this project
3. Copy config.php.example to config.php if you need to change a setting.
4. Open a browser and point it to your Naglite3 installation.

Customization
-------------

For all possible config options have a look at config.php.example

### Filter on hostgroups

If you want to filter the dashboard on spesific hosts. Create a hostgroup in Nagios.
use the ?filter=<hostgroup> parameter to filter. Example http://example.com/?filter=my_hostgroup

### CSS
To create a new css, copy lightmode.css from static/css to a new file. Change the colors and point the config.php to the new file
  
### Refresh interval
You can change the refresh interval (in seconds) through a GET parameter, too.

Example: http://your-host/Naglite3/?refresh=100

### Custom alert message
To display a custom alert message. Enable 'display dashboard message' and change 'dashboard_message' in the configuration file.

### Title
To change dashboard title. Change 'nagios_title' in the configuration file

Display
-------------
You can enable/disable information in the dashboard.

/* display host information */
$display_host_table_output = TRUE;
$display_host_failure_duration = TRUE;
$display_host_ip = FALSE;

/* display service information */
$display_service_attempts = TRUE;
$display_service_failure_duration = TRUE;
$display_service_unknown = TRUE;
$display_service_warning = TRUE;
$display_service_critical = TRUE;

/* display other information */
$display_time_and_day = TRUE;
$display_notifications = TRUE;
$display_acknowledged = TRUE;
$display_pending = TRUE;

/* if all host / service are ok - display message */
$display_all_hosts_up=TRUE;
$display_all_services_ok=TRUE;


