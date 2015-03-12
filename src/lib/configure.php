<?php
  include_once 'install-funcs.inc.php';

  // Default action is to configure
  $configure_action = '3';

  // Check if previous configuration file exists
  if (file_exists($CONFIG_FILE)) {
    $configure_action = '0';
  }

  while ($configure_action !== '1' && $configure_action !== '2' &&
      $configure_action !== '3') {

    // File exists. Does user want to just go with previous configuration?
    print "Previous configuration file found. What would you like to do?\n" .
      "   [1] Use previous configuration.\n" .
      "   [2] Re-configure using current configuration as defaults.\n" .
      "   [3] Re-configure using default configuration as defaults.\n" .
      'Enter the number corresponding to the action you would like to take: ';

    $configure_action = trim(fgets(STDIN));
    print "\n";
  }

  if ($configure_action === '1') {
    // Do not configure. File is in place and user wants to use it.
    print "Using previous configuration.\n";

    // pre-install depends on this variable
    $CONFIG = parse_ini_file($CONFIG_FILE);
  } else if ($configure_action === '2') {
    // Use current config as default and re-configure interactively.
    print "Using current config file as defaults, and re-configuring.\n";
    $CONFIG = parse_ini_file($CONFIG_FILE);

    // Make sure all default parameters are in the new configuration.
    $CONFIG = array_merge($DEFAULTS, $CONFIG);

    // TODO :: Should this check be done in a loop where we notify the user
    // about each missing configuration parameter ???

    // TODO :: Should we do a reverse-check to make sure everything in the
    // config.ini file is also one of our defaults? This might be useful
    // during development if we accidently directly modify the config.ini
    // file to add a new parameter and it gets lost when we move into
    // production.
  } else if ($configure_action === '3') {
    // Use defaults and re-configure interactively.
    print "Reverting to default configuration and re-configuring.\n";
    $CONFIG = $DEFAULTS;
  }

  if ($configure_action === '2' || $configure_action === '3') {
    // interactively configure
    foreach ($CONFIG as $key => $value) {
      $secure = (stripos($key, 'pass') !== false);
      $unknown = !isset($DEFAULTS[$key]);

      $CONFIG[$key] = configure(
        $key, // Name of option
        $value, // Default value
        (isset($HELP_TEXT[$key]))?$HELP_TEXT[$key]:null, // Help text
        $secure, // Should echo be turned off for inputs?
        $unknown // Is this a known/unkown option?
      );
    }

    // build config file content
    $ini = '; this file was autogenerated' . "\n" .
           '; at ' . date('r') . "\n";
    foreach ($CONFIG as $key => $value) {
      $help = 'no help available for this option';
      if (isset($HELP_TEXT[$key])) {
        $help = $HELP_TEXT[$key];
      }
      $ini .= sprintf("\n; %s\n%s = '%s'\n", $help, $key, $value);
    }

    // write config file
    file_put_contents($CONFIG_FILE, $ini);
  }
?>