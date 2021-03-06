<?php

if (!file_exists('config.php')) {
  echo "<p>Please configure your dashboard in the config.php file</p>";
  echo "<p>Copy 'config.php.example' to 'config.php'</p>";
  die;
} else {
  require 'config.php';
  require_once './naglite.inc';
}

/* Disable caching and set refresh interval */
header("Pragma: no-cache");
if (!empty($_GET["refresh"]) && is_numeric($_GET["refresh"])) {
  $refresh = $_GET["refresh"];
}
header("Refresh: " .$refresh);

/* Get Nagios data */
$nagios_status = read_status_file();

/* Display all hostgroups available */
if($_GET["hostgroups"] == "display") {
  echo '<div class="display_hostgroups">';
  echo "Hostgroups: ";
  foreach($nagios_status['host_info']['all_hostgroups'] as $key => $value) {
    echo "<a href='?filter=$key'>[ $key ]</a>";
  }
  echo '</div>';
}

/* Get Nagios states mapping */
$state_values = get_nagios_states();

$in = $nagios_status['service_info']['in'];
$type =$nagios_status['service_info']['type'];
$status = $nagios_status['service_info']['status'];

/* host info */
$hostInfo = $nagios_status['host_info'];

/* Counts - OK - DOWN - ETC */
$counter = $nagios_status['service_info']['variables']['counter'];

/* all host in category ok down etc */
$states = $nagios_status['service_info']['variables']['states'];
$hosts = $nagios_status['service_info']['variables']['hosts'];

/**
 *
 * Status output
 *
 **/

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
echo "       \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
echo "<head>\n";
echo "	<title>$nagios_title</title>\n";
echo "	<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\" />\n";
echo "	<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"$theme\" />\n";
echo "  <script src=\"static/js/javascript.js\"></script>\n";
echo "</head>\n";
echo "<body>\n";

if($display_dashboard_message){
  echo '<div id="custom_message">';
  print("<h3>Important! </h3><br>" . $dashboard_message);
  echo '</div>';
}
print("</div>\n");
if (!empty($_GET["filter"])) {
  $nagios_title = $nagios_title . " - filter [" .$_GET["filter"] . "]";
}
echo '<div class="top">';
echo '<div class=\'top-left-header\'>';
print("<h1 >$nagios_title</h1>");

$filestatus = get_last_updated_status();

print(sprintf('<div class="statusFileState %s">', $filestatus['state']));
print(sprintf('Status file last updated at %s', date("Y-m-d H:i:s", $filestatus['time'])));

print("</div>\n");

print("</div>\n");
if($display_time_and_day){
  print('<div class="top-right-header">');
  print(sprintf("<p id='timer'>Monday 00:00:00 %s %s </p>", $day[$curDay-1], $timestamp));
  print("<br><a href='?hostgroups=display'>[Hostgroups]</a>");
  print("<a href='index.php'>[Default view]</a>");
  print("</div>\n");
}
echo '</div>';
echo '<div id="content">';
if(is_callable($nagliteHeading)) {
  $nagliteHeading();
} elseif ($nagliteHeading) {
  echo '<h1>'.$nagliteHeading.'</h1>';
}

if($display_host_table_output){
  displayHostTable($states['hosts']['down'],$state_values,$hostInfo,$counter);
}

foreach(array('unreachable', 'acknowledged', 'pending', 'notification') as $type) {
  if ($counter['hosts'][$type]) {
    print(sprintf('<div class="subhosts %s"><b>%s:</b> %s</div>', $type, ucfirst($type), implode(', ', $states['hosts'][$type])));
  }
}

if ($counter['services']['warning'] || $counter['services']['critical'] || $counter['services']['unknown']) {
  sectionHeader('services', $counter);
  displayServiceTable($state_values, $states['services'], $hostInfo, array('critical', 'warning', 'unknown'));
} else {
  require 'config.php';
  if($display_all_services_ok){
    print("<div class='state up'>ALL MONITORED SERVICES OK</div>\n");
  }

}
foreach(array('acknowledged', 'notification', 'pending') as $type) {
  if ($counter['services'][$type]) {

    if($type == "notification" && !$display_notifications){
      continue;
    }
    if($type == "acknowledged" && !$display_acknowledged){
      continue;
    }
    if($type == "pending" && !$display_pending){
      continue;
    }

    print(sprintf('<h3 class="title">%s</h3>', ucfirst($type)));
    print('<div class="subsection">');
    displayServiceTable($state_values, $states['services'], $hostInfo, array($type), $type);
    print('</div>');
  }
}

print("</body>\n");
print("</html>\n");

/**
 *
 * Functions
 *
 **/

function displayServiceTable($nagios, $services, $hostInfo, $select = false, $type = false) {
  if (!$type) {
    print("<table><tr>\n");
  } else {
    print(sprintf("<table><tr class='%s'>\n", $type));
  }

  require 'config.php';
  $header = (TRUE ? '<th>Host</th>' : '');
  $header = $header . ($display_host_ip ? '<th>Address</th>' : '');
  $header = $header . (TRUE ? '<th>Service</th>' : '');
  $header = $header . (TRUE ? '<th>Status</th>' : '');
  $header = $header . ($display_service_failure_duration ? '<th>Duration</th>' : '');
  $header = $header . ($display_service_attempts ? '<th>Attempts</th>' : '');
  $header = $header . (TRUE ? '<th>Plugin Output</th>' : '');

  foreach ($select as $selectedType) {
    if ($services[$selectedType]) {
      foreach ($services[$selectedType] as $service) {

        $display_service = filter_hostgroups($service,$hostInfo);
        if(!$display_service){continue;}

        $state = $nagios["service"][$service["current_state"]];

        if($state == 'warning' && !$display_service_warning){
          continue;
        }
        if($state == 'critical' && !$display_service_critical){
          continue;
        }
        if($state == 'unknown' && !$display_service_unknown){
          continue;
        }

        if(!$service_header_displayed){
          print($header . "</tr>");
          $service_header_displayed=true;
        }

        if (!$type) {
          $rowType = $state;
        } else {
          $rowType = $type;
          if ("acknowledged" !== $type) {
            $state = $type;
          }
        }
        print(sprintf("<tr class='%s'>\n", $rowType));
        print(sprintf("<td class='hostname'>%s</td>\n", $service["host_name"]));

        if($display_host_ip){
          $hostName = $service["host_name"];
          print("<td class='address'>{$hostInfo[$hostName]["address"]}</td>\n");
        }

        print(sprintf("<td class='service'>%s</td>\n", $service['service_description']));
        print(sprintf("<td class='state'>%s", $state));
        if ($service["current_attempt"] < $service["max_attempts"]) {
          print(" (Soft)");
        }

        print("</td>\n");

        if ($display_service_failure_duration){
          print(sprintf("<td class='duration'>%s</td>\n", duration($service['last_state_change'])));
        }
        if ($display_service_attempts){
          print(sprintf("<td class='attempts'>%s/%s</td>\n", $service['current_attempt'], $service['max_attempts']));
        }
        print(sprintf("<td class='output'>%s</td>\n", strip_tags($service['plugin_output'], '<a>')));

        print("</tr>\n");
      }
    }
  }
  if(!$service_header_displayed){
    print("<div class='state up'>ALL MONITORED SERVICES OK</div>\n");
  }
  print("</table>\n");
}

function sectionHeader($type, $counter) {
  require 'config.php';
  print(sprintf('<div id="%s" class="section">', $type));
  print(sprintf('<h2 class="title">%s Status</h2>', ucfirst($type)));
  print('<div class="stats">');
  foreach($counter[$type] as $type => $value) {

    if (!empty($_GET["filter"])) {
      array_pop($counter[$type]);
      continue;
    }

    if($type == 'warning' && !$display_service_warning){
      array_pop($counter[$type]);
      continue;
    }
    if($type == 'critical' && !$display_service_critical){
      array_pop($counter[$type]);
      continue;
    }
    if($type == 'unknown' && !$display_service_unknown){
      array_pop($counter[$type]);
      continue;
    }
    if($type == 'pending' && !$display_pending){
      array_pop($counter[$type]);
      continue;
    }
    if($type == 'notification' && !$display_notifications){
      array_pop($counter[$type]);
      continue;
    }
    if($type == 'acknowledged' && !$display_acknowledged){
      array_pop($counter[$type]);
      continue;
    }

    print(sprintf('<div class="stat %s">%s %s</div>', $type, $value, ucfirst($type)));
  }

  print('</div></div>');
}

function displayHostTable($hosts,$nagios,$hostInfo,$counter){

  sectionHeader('hosts', $counter);

  if ($counter['hosts']['down']) {
    print('<table>');
    print('<tr>');
    require 'config.php';
    $header = (TRUE ? '<th>Host</th>' : '');
    $header = $header . ($display_host_ip ? '<th>Address</th>' : '');
    $header = $header . (TRUE ? '<th>Status</th>' : '');
    $header = $header . ($display_host_failure_duration ? '<th>Duration</th>' : '');
    $header = $header . (TRUE ? '<th>Plugin Output</th>' : '');
    print('</tr>');

    foreach($hosts as $host) {

      $display_service = filter_hostgroups($host,$hostInfo);
      if(!$display_service){continue;}

      if(!$host_header_displayed){
        print($header);
        $host_header_displayed=true;
      }

      $state = $nagios["host"][$host["current_state"]];

      echo "<tr class='".$state."'>\n";
      echo "<td class='hostname'>{$host["host_name"]}</td>\n";

      if($display_host_ip){
        $hostName = $host["host_name"];
        echo "<td class='address'>{$hostInfo[$hostName]["address"]}</td>\n";
      }

      echo "<td class='state'>{$state}</td>\n";

      if ($display_host_failure_duration){
        echo "<td class='duration'>".duration($host["last_state_change"])."</td>\n";
      }

      print(sprintf("<td class='output'>%s</td>\n", htmlspecialchars($host['plugin_output'])));
      echo "</tr>\n";
    }
    echo "</table>";
    if(!$host_header_displayed){
      echo "<div class='state up'>ALL MONITORED HOSTS UP</div>\n";
    }
  } else {
    require 'config.php';
    if($display_all_hosts_up){
      echo "<div class='state up'>ALL MONITORED HOSTS UP</div>\n";
    }

  }
}

/* mapping for nagios states */
function get_nagios_states(){
  $states["host"]["ok"] = 0;
  $states["host"]["down"] = 1;
  $states["host"]["unreachable"] = 2;
  $states["host"] += array_keys($states["host"]);
  $states["service"]["ok"] = 0;
  $states["service"]["warning"] = 1;
  $states["service"]["critical"] = 2;
  $states["service"]["unknown"] = 3;
  $states["service"] += array_keys($states["service"]);

  return $states;
}

/* date converter */
function duration($end) {
  $DAY = 86400;
  $HOUR = 3600;
  $now = time();
  $diff = $now - $end;
  $days = floor($diff / $DAY);
  $hours = floor(($diff % $DAY) / $HOUR);
  $minutes = floor((($diff % $DAY) % $HOUR) / 60);
  $secs = $diff % 60;
  return sprintf("%dd, %02dh:%02dm:%02ds", $days, $hours, $minutes, $secs);
}

function filter_hostgroups($host,$hostInfo){
  if (!empty($_GET["filter"])) {
    $filter = $_GET["filter"];
    $hostName = $host["host_name"];
    $hostgroups = $hostInfo[$hostName]['hostgroup'];
    if(!in_array($filter,$hostgroups)){
      return false;
    }
  }
  return true;
}


