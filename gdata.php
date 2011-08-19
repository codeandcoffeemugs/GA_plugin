<?php
if (!class_exists('clApi')) require('coreylib.php');

class GDataApi {
  
  private $email;
  private $password;
  private $token;
  
  function __construct($email, $password, $token = null) {
    $this->email = $email;
    $this->password = $password;
    $this->token = $token;
  }
  
  function login() {
    $ch = curl_init('https://www.google.com/accounts/ClientLogin');
    
    curl_setopt_array($ch, array(
      CURLOPT_POSTFIELDS => array(
        'Email' => $this->email,
        'Passwd' => $this->password,
        'accountType' => 'GOOGLE',
        'source' => 'wpgdata',
        'service' => 'analytics'
      ),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false
    ));
    
    $response = curl_exec($ch);
    
    if (!curl_error($ch)) {
      if (preg_match('/Auth=(.*)/', $response, $matches)) {
        $this->token = $matches[1];
      }
    }
    
    // TODO: add much better error processing here
    
    curl_close($ch);
    
    return $this->token;
  }
  
  function get_accounts($flush = false) {
    if (!$this->token) {
      throw new Exception("Must call login() first.");
    }

    $api = new clApi('https://www.google.com/analytics/feeds/accounts/default?prettyprint=true');
    $api->header(array(
      'Authorization' => "GoogleLogin Auth={$this->token}",
      'GData-Version' => '2'
    ));
    
    $accounts = array();
    
    if ($feed = $api->parse($flush ? null : '10 minutes')) {
      foreach($feed->get('entry') as $entry) {
        $accounts[] = (object) array(
          'title' => (string) $entry->get('title'), 
          'webPropertyId' => (string) $entry->get('property[name="ga:webPropertyId"]')->get('@value'),
          'tableId' => (string) $entry->get('tableId')
        );
      }
    }
    
    usort($accounts, array($this, '_sort_by_title'));
    
    return $accounts;
  }
  
function get_dates_for_intervals($begin = null, $end = null, $interval = '1 day') {
	// convert args to timestamps
	$start = strtotime($begin); // some date
	$end = strtotime($end); // some other date, or the same date
	$intervals = array();
	// build interval list
	$intervals[] = $next = $start;
	do {
	 	  $intervals[] = $next = strtotime($interval, $next); 
	 	} while ($next < $end);
	 	
	// function format_date($v) {
	// 		return date('Y-m-d', $v);
	// 	}
		$intervals = array_unique($intervals);
	 	return $intervals;
}

function get_data($tableId, $params = array(), $flush = false) {
    if (!$this->token) {
      throw new Exception("Must call login() first.");
    }
    
    $api = new clApi('https://www.google.com/analytics/feeds/data');
    $api->header(array(
      'Authorization' => "GoogleLogin Auth={$this->token}",
      'GData-Version' => '2'
    ));
    
    $api->param('ids', $tableId);
    $api->param($params);
    
    if ($feed = $api->parse()) {
      return $feed;
    } else {
      return false;
    }
  }
  
  function _sort_by_title($a1, $a2) {
    return strcasecmp($a1->title, $a2->title);
  }
  
}


