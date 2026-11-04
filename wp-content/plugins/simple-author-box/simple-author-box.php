<?php
/**
* Plugin Name: Simple Author Box
* Plugin URI: https://wpauthorbox.com/
* Description: Adds a responsive author box with social icons on any post.
* Version: 2.61
* Author: WebFactory Ltd
* Author URI: https://www.webfactoryltd.com/
* Requires: 4.6
* License: GPLv3 or later
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
* Requires PHP: 5.6
* Tested up to: 7.0

*
* Copyright 2014-2017 Tiguan				office@tiguandesign.com
* Copyright 2017-2019 MachoThemes 		office@machothemes.com
* Copyright 2019-2019 GreenTreeLabs		diego@greentreelabs.net
* Copyright 2019-2026 WebFactory Ltd		support@webfactoryltd.com
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 3, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if (!function_exists('is_plugin_active')) {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

define('SIMPLE_AUTHOR_BOX_PATH', plugin_dir_path(__FILE__));
define('SIMPLE_AUTHOR_BOX_ASSETS', plugins_url('/assets/', __FILE__));
define('SIMPLE_AUTHOR_BOX_SLUG', plugin_basename(__FILE__));
define('SIMPLE_AUTHOR_BOX_FILE', __FILE__);
define('SIMPLE_AUTHOR_SCRIPT_DEBUG', false);
define('SIMPLE_AUTHOR_POINTERS', 'sab_pointers');


$plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
define('SIMPLE_AUTHOR_BOX_VERSION', $plugin_data['version']);
require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box.php';

require_once dirname(__FILE__) . '/wf-flyout/wf-flyout.php';
new wf_flyout(__FILE__);

global $simple_author_box;
$simple_author_box = Simple_Author_Box::get_instance();
