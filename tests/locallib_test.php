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

namespace mod_offlinequiz;

/**
 * Tests for functions in locallib.php.
 *
 * @package    mod_offlinequiz
 * @category   test
 * @copyright  2026 Université de Montréal
 * @author     Salem Saidi <salem.saidi@umontreal.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class locallib_test extends \advanced_testcase {
    /**
     * Returns a filter stub replacing every text by the given output.
     *
     * @param string $output
     * @return object
     */
    private function get_filter(string $output): object {
        return new class ($output) {
            /** @var string output returned by the filter */
            private $output;

            /**
             * Constructor.
             *
             * @param string $output
             */
            public function __construct(string $output) {
                $this->output = $output;
            }

            /**
             * Replaces the text.
             *
             * @param string $text
             * @return string
             */
            public function filter(string $text): string {
                return $this->output;
            }
        };
    }

    /**
     * The link wrapping a TeX image must be removed, as it misplaces the following text in TCPDF.
     *
     * @covers ::offlinequiz_apply_filters
     */
    public function test_apply_filters_removes_tex_link(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/offlinequiz/locallib.php');

        $img = '<img class="texrender" title="x" alt="x" src="http://localhost/filter/tex/pix.php/a.gif" />';
        foreach (['displaytex', 'texdebug'] as $page) {
            $tex = '<span class="MathJax_Preview"><a href="http://localhost/filter/tex/' . $page .
                '.php?texexp=x" title="TeX">' . $img . '</a></span><script type="math/tex">x</script>';
            $html = '<p>a ' . $tex . ' b ' . $tex . ' c</p>';

            $result = offlinequiz_apply_filters('ignored', [$this->get_filter($html)]);

            $this->assertStringNotContainsString('<a ', $result);
            $this->assertStringNotContainsString('MathJax_Preview', $result);
            $this->assertSame(2, substr_count($result, $img));
        }
    }

    /**
     * Text without TeX formulas, including ordinary links, must stay unchanged.
     *
     * @covers ::offlinequiz_apply_filters
     */
    public function test_apply_filters_keeps_other_content(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/offlinequiz/locallib.php');

        $html = '<p><b>Bold</b> <a href="http://example.com">link</a> <img src="http://example.com/a.png" /></p>';

        $this->assertSame($html, offlinequiz_apply_filters($html, [$this->get_filter($html)]));
    }
}
