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


