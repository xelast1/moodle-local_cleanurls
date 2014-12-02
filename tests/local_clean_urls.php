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
 * @package    local
 * @subpackage clean_urls
 * @author     Brendan Heywood <brendan@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden');
class local_clean_urls_test extends advanced_testcase {

    private $category;
    private $course;

    protected function setUp() {
        global $DB;
        global $CFG;
        parent::setup();
        $this->resetAfterTest(true);

        $this->category = $this->getDataGenerator()->create_category(array('idnumber' => 'cat1'));

        $this->course   = $this->getDataGenerator()->create_course(array('fullname'=> 'Some course',
                                                                 'shortname'=> 'shortcode',
                                                                 'visible'=> 1, 'category'=> $this->category->id));

        $this->mancourse   = $this->getDataGenerator()->create_course(array('fullname'=> 'Some course',
                                                                 'shortname'=> 'management',
                                                                 'visible'=> 1, 'category'=> $this->category->id));


        // Create dummy student.
        $this->staff = $this->getDataGenerator()->create_user(array('email'=>'head1@example.com', 'username'=>'head1'));
        // The user logs in.
        $this->setUser($staff);

    }

    public function test_local_clean_urls_simple() {
        global $DB, $CFG;
        $this->resetAfterTest(true);
        require_once("$CFG->dirroot/local/clean_urls/lib.php");

        set_config('cleaningon', 0, 'local_clean_urls');

        $url = 'http://www.example.com/moodle/course/view.php?id=' . $this->course->id;
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals($url, $clean, "Urls shouldn't be touched if cleaning setting is off");

        set_config('cleaningon', 1, 'local_clean_urls');


        $url = 'http://www.example.com/moodle/theme/whatever.php';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals($url, $clean, "Nothing: Theme files should not be touched");

        $url = 'http://www.example.com/moodle/lib/whatever.php';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals($url, $clean, "Nothing: Lib files should not be touched");


        // $url = 'http://www.example.com/moodle/course/view.php?edit=1&id=' . $this->course->id;
        // $murl = new moodle_url($url);
        // $clean = $murl->out();
        // $this->assertEquals('http://www.example.com/moodle/course/shortcode?edit=1', $clean, "Clean: course with param");
        //
        // $unclean = local_clean_urls_unclean($clean);
        // $this->assertEquals('http://www.example.com/moodle/course/view.php?name=shortcode&edit=1', $unclean, "Unclean: course with param");


        $url = 'http://www.example.com/moodle/foo/bar.php';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals('http://www.example.com/moodle/foo/bar', $clean, "Clean: Remove php extension");

        $unclean = clean_moodle_url::unclean($clean)->orig_out();
        $this->assertEquals($url, $unclean, "Unclean: Put php extension back");


        $url = 'http://www.example.com/moodle/foo/bar.php?ding=pop';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals('http://www.example.com/moodle/foo/bar?ding=pop', $clean, "Clean: Remove php extension with params");

        $unclean = clean_moodle_url::unclean($clean)->orig_out();
        $this->assertEquals($url, $unclean, "Unclean: Put php extension back with params");


        $url = 'http://www.example.com/moodle/foo/bar.php#hash';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals('http://www.example.com/moodle/foo/bar#hash', $clean, "Clean: Remove php extension with hash");

        $unclean = clean_moodle_url::unclean($clean)->orig_out();
        $this->assertEquals($url, $unclean, "Unclean: Put php extension back with hash");


        $url = 'http://www.example.com/moodle/one/two/three/index.php?foo=bar#hash';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals('http://www.example.com/moodle/one/two/three/?foo=bar#hash', $clean, "Clean: Remove index");

        $url = 'http://www.example.com/moodle/admin/settings.php?section=local_clean_urls';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals($url, $clean, "Clean: Don't clean when clash with directory");



        $url = 'http://www.example.com/moodle/course/view.php?id=' . $this->course->id;
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals('http://www.example.com/moodle/course/shortcode', $clean, "Clean: course");

        $unclean = clean_moodle_url::unclean($clean)->orig_out();
        $this->assertEquals('http://www.example.com/moodle/course/view.php?name=shortcode', $unclean, "Unclean: course");

        $url = 'http://www.example.com/moodle/course/view.php?id=' . $this->mancourse->id;
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals('http://www.example.com/moodle/course/view?id=' . $this->mancourse->id, $clean, "Clean: course is ignored because it's shortname clashes with dir or file");

        $unclean = clean_moodle_url::unclean($clean)->orig_out();
        $this->assertEquals($url, $unclean, "Unclean: course is ignored as clashed with php file");

        $url = 'http://www.example.com/moodle/course/index.php';
        $murl = new moodle_url($url);
        $clean = $murl->out();
        $this->assertEquals('http://www.example.com/moodle/course/', $clean, "Clean: index.php off url");

        // Nothing to unclean because these urls will get routed directly by apache not router.php


        # id mapping

        # module index mapping

        # if slash arguments are used then just skip it

#        set_config('categoryon', 1, 'local_clean_urls');
#        $this->assertEquals('http://www.example.com/cat1/course/shortcode', $murl->out(), " );
    }

}
