<?php 
/*
Copyright (C)2011

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('ABSPATH')) exit;

require('gdata.php');


/**
 * This PHP class is a namespace for the free version of your plugin. Bear in
 * mind that what you program here (and/or include here) is not only the 
 * the free plugin itself, but is also the basis for the pro version - the core
 * functionality, if you will.
 */
class GoogleAnalytics {
  
  const ACCOUNT_SETTING = '_google_analytics_account';
  const EMAIL_SETTING = '_google_analytics_email';
  const PASSWORD_SETTING = '_google_analytics_password';
  const TOKEN_SETTING = '_google_analytics_token';
  
  // holds the singleton instance of your plugin's core
  static $instance;
  // holds a reference to the pro version of the plugin
  static $pro;
  
  /**
   * Get the singleton instance of this plugin's core, creating it if it does
   * not already exist.
   */
  static function load() {
    return self::$instance ? self::$instance : ( self::$instance = new GoogleAnalytics() );
  }
  
  /**
   * Create a new instance of this plugin's core. There should only ever
   * be one instance of a plugin, so we make the constructor private, and
   * instead ask all other parts of WordPress to call PluginName::load().
   */
  private function __construct() {
    
    #
    # All plugins tend to need these basic actions.
    #
    add_action('init', array($this, 'init'), 11, 1);
    add_action('admin_init', array($this, 'admin_init'));
    add_action('admin_menu', array($this, 'admin_menu'));
    add_action('wp_head', array($this, 'wp_head'));
    add_action('wp_dashboard_setup', array($this, 'wp_dashboard_setup'));
    add_action('wp_ajax_ga_build_graph', array($this, 'ajax_build_graph'));
    
    #
    # Discover this file's path
    #
    $parts = explode(DIRECTORY_SEPARATOR, __FILE__);
    $fn = array_pop($parts);
    $fd = ($fd = array_pop($parts) != 'plugins' ? $fd : '');
    $file = $fd ? "{$fd}/{$fn}" : $fn;
    
    add_action("activate_{$fd}/lite.php", array($this, 'activate'));
    add_action("deactivate_{$fd}/lite.php", array($this, 'deactivate'));
    
    # 
    # Add actions and filters here that should be called before the "init" action
    # Note that self::$pro will be null until the "init" action is called
    #
    // add_action($action_name, array($this, $action_name), $priority = 10, $num_args_supported = 1);
    // add_filter($filter_name, array($this, $filter_name), $priority = 10, $num_args_supported = 1);
  }
  
  function wp_dashboard_setup() {
    wp_add_dashboard_widget('google-analytics-dashboard-widget', 'Google Analytics', array($this, 'dashboard_widget'));
  }
  
  function dashboard_widget() {
    ?>
      <p>Loading...</p>
      <script>
        (function($) {
          $('#google-analytics-dashboard-widget .inside').load(ajaxurl, { action: 'ga_build_graph' });
        })(jQuery);      
      </script>
    <?php
  }
  
  function ajax_build_graph() {
    if (($client = $this->get_client()) && ($tableId = $this->getAccountTableId())) {
		// get the dates for the past 30 days
		$dates = $client->get_dates_for_intervals('30 days ago', 'today');  // returns $intervals and $end
		//echo "<script type='text/javascript' src='http://manginojslib.s3.amazonaws.com/jquery.flot.js'></script>";
		echo '<h2>Past 30 Days Total visits</h2>';
		$graph_visitors = array();
		$graph_visits = array();
		foreach($dates as $start) {
			$params = array(
		        'start-date' => date('Y-m-d', $start),
		        'end-date' => date('Y-m-d', $start),
		        //'dimensions' => 'ga:source,ga:medium',
		        'metrics' => 'ga:visitors,ga:visits',
		        //'sort' => '-ga:visits',
		        //'filters' => 'ga:medium==referral',
		        'max-results' => 30
		      );

		      if ($data = $client->get_data($tableId, $params)) {
		        $data->inspect();

		        $total_visits = $data->get('entry metric')->get('@value') ? $data->get('entry metric')->get('@value') : '0' ;
				    $visits = $data->get('entry metric:eq(1)')->get('@value') ? $data->get('entry metric:eq(1)')->get('@value') : '0';
		        $title = $data->get('dataSource')->get('tableName');
						$date = date('Y-m-d', $start);
						$dateRestore = strtotime($date. " UTC");
						
						$graph_visitors[] = array($start * 1000, (int)$total_visits);
            $graph_visits[] = array($start * 1000, (int)$visits);
		      }
			}
			//print_r($graph_bounce);
			require_once('the_graph.php');
		  }
    
    // must exit at the end:
    exit;
  }

  function admin_menu() {
    // create a menu option for navigating to the options page
    add_options_page('Google Analytics', 'Google Analytics', 'administrator', 'google-analytics', array($this, 'settings'));
  }
  
  function admin_init() {
    // register settings for the options page here:
    register_setting('google-analytics-account', self::ACCOUNT_SETTING);

    register_setting('google-analytics-auth', self::TOKEN_SETTING);
    register_setting('google-analytics-auth', self::EMAIL_SETTING);
    register_setting('google-analytics-auth', self::PASSWORD_SETTING);

		if (is_admin()) {
			wp_enqueue_script('flot', 'http://manginojslib.s3.amazonaws.com/jquery.flot.js', array('jquery'));
		}
  }
  
  function get_auth() {
    if (($email = get_option(self::EMAIL_SETTING)) && ($password = get_option(self::PASSWORD_SETTING))) {
      return (object) array(
        'email' => $email,
        'password' => $password
      );
    }
  }
  
  function get_client() {
    $auth = $this->get_auth();
    
    if (!$auth) {
      return false;
    }
    
    if ($token = get_option(self::TOKEN_SETTING)) {
      return new GDataApi($auth->email, $auth->password, $token);
      
    } else {
      $client = new GDataApi($auth->email, $auth->password);
      if ($token = $client->login()) {
        update_option(self::TOKEN_SETTING, $token);
        return $client;
      
      } else {
        return false;
        
      }
    } 
  }
  
  function settings() {
    if (!($client = $this->get_client())) {
      require('auth.php');
      
    } else {
      $accounts = $client->get_accounts();
      require('account.php');
    }
  }
  
  function getWebPropertyId() {
    if ($account = get_option(self::ACCOUNT_SETTING)) {
      $account = json_decode($account);
      return $account->webPropertyId;
    }
  }
  
  function getAccountTitle() {
    if ($account = get_option(self::ACCOUNT_SETTING)) {
      $account = json_decode($account);
      return $account->title;
    }
  }
  
  function getAccountTableId() {
    if ($account = get_option(self::ACCOUNT_SETTING)) {
      $account = json_decode($account);
      return $account->tableId;
    }
  }
  
  
  // print the GA tracking code
  function wp_head() {
    if ($webPropertyId = $this->getWebPropertyId()) {
      ?>
        <script type="text/javascript">
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', '<?php echo $webPropertyId ?>']);
          _gaq.push(['_trackPageview']);
          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        </script>
      <?php
    }
  }
  
  function init() {
    # 
    # Add your own actions and filters here
    #
    // add_action($action_name, array($this, $action_name), $priority = 10, $num_args_supported = 1);
    // add_filter($filter_name, array($this, $filter_name), $priority = 10, $num_args_supported = 1);
    
    #
    # self::$pro will be defined here and will be a reference to your pro component
    # iff the pro plugin is installed and activated
    #
  }
  
  function activate() {
    global $wpdb;
    
    # 
    # Upgrade database tables here, and create any default data.
    #
    
  }
  
  function deactivate() {
    global $wpdb;
    
    # 
    # Cleanup stuff that shouldn't be left behind.
    #
    
  }
}

GoogleAnalytics::load();

/*
$client = new GDataApi('aaroncollegeman@gmail.com', '$yrah.*!');
if ($client->login()) {
  print_r($client->accounts());
}
*/