<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is a mock format for CleanURLs test.
 *
 * @package     local_cleanurls
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We are enforcing a namespace outside cleanurls scope to emulate a format plugin.
namespace format_cleanurls;

use cm_info;
use local_cleanurls\cleanurls_support_interface;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * This is a mock format for CleanURLs test.
 *
 * @package     local_cleanurls
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanurls_support implements cleanurls_support_interface {
    /**
     * @inheritdoc
     */
    public static function get_clean_subpath(stdClass $course, cm_info $cm) {
        return "customurlforforums/My{$cm->id}";
    }

    /**
     * @inheritdoc
     */
    public static function get_cmid_for_path(stdClass $course, array $path) {
        if (count($path) != 2) {
            return null;
        }
        return (int)substr($path[1], 2);
    }
}
