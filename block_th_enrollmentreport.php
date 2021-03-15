<?php
class block_th_enrollmentreport extends block_base {

	public function init() {
		$this->title = get_string('namereport', 'block_th_enrollmentreport');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}
		global $CFG;
		$this->content = new stdClass;
		$this->content->text = get_string("content", 'block_th_enrollmentreport');
		$this->content->footer = html_writer::link($CFG->wwwroot . '/blocks/th_enrollmentreport/view.php', get_string('reportlink', 'block_th_enrollmentreport'));
		return $this->content;
	}
}
