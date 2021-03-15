<?php
class th_enrollmentreport_form extends moodleform {
	//Add elements to form
	public function definition() {
		$mform = $this->_form; // Don't forget the underscore!

		global $CFG, $DB;
		$arraycourses = array();
		$courses = $DB->get_records_sql('SELECT id,fullname FROM {course} WHERE summaryformat=?', ['1']);
		foreach ($courses as $course) {
			$arraycourses[$course->id] = $course->fullname;
		}
		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('allareas', 'block_th_enrollmentreport'),
		);
		$mform->addElement('autocomplete', 'areaids', get_string('searcharea', 'block_th_enrollmentreport'), $arraycourses, $options);

		$mform->addElement('date_selector', 'startdate', get_string('from'));
		$date = (new DateTime())->setTimestamp(usergetmidnight(time()));
		$date->modify('-6 month');
		$mform->setDefault('startdate', $date->getTimestamp());

		$mform->addElement('date_selector', 'enddate', get_string('to'));

		$radioarray = array();
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('day', 'block_th_enrollmentreport'), 'day');
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('week'), 'week');
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('month'), 'month');
		$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
		$mform->setDefault('filter', 'day');

		$mform->addElement('submit', 'send', get_string('find', 'block_th_enrollmentreport'));
	}
}