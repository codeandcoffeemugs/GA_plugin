<?php
/*
Plugin Name: Google Analytics Pro
Plugin URI: 
Description: 
Author: John Mangino, Aaron Collegeman
Author URI: 
Version: 1.0
License: GPL2
*/

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

// load the core dependency
require_once('core.php');

if (!defined('ABSPATH')) exit;

/**
 * This PHP class is a namespace for the pro version of your plugin. 
 */
class GoogleAnalyticsPro {
  
  // holds the singleton instance of your plugin's core
  static $instance;
  
  /**
   * Get the singleton instance of this plugin's core, creating it if it does
   * not already exist.
   */
  static function load() {
    return self::$instance ? self::$instance : ( self::$instance = new GoogleAnalyticsPro() );
  }
  
  private function __construct() {
    
    add_action('init', array($this, 'init'), 10, 1);
    
    #
    # Discover this file's path
    #
    $parts = explode(DIRECTORY_SEPARATOR, __FILE__);
    $fn = array_pop($parts);
    $fd = ($fd = array_pop($parts) != 'plugins' ? $fd : '');
    $file = $fd ? "{$fd}/{$fn}" : $fn;
  }
  
  function init() {
    // attach a reference to the pro version onto the lite version
    PluginName::$pro = $this;
    
    #
    # Use your own custom actions and filters to override and expand the
    # functionality in the lite version of your plugin.
    #
  }
  
}

GoogleAnalyticsPro::load();