<?php
require_once "../../config.php";
require_once "$CFG->libdir/formslib.php";
require_once "lib.php";
global $DB, $CFG;

require_login();

$PAGE->set_url(new moodle_url('/blocks/reportenrollment/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_heading(get_string('namereport', 'block_reportenrollment'));
$PAGE->set_title(get_string('namereport', 'block_reportenrollment'));

echo $OUTPUT->header();

$mform = new reportenrollment_form();
$fromform = $mform->get_data();

$mform->display();
//lay ngay sau ngay ket thuc
function layngay($endday, $range) {
	$day = (new DateTime())->setTimestamp(usergetmidnight($endday));
	$day->modify($range);
	$start = $day->getTimestamp();
	return $start;
}
//lay du lieu
function laytk($endday, $range, $courseid) {
	global $DB;
	$day = (new DateTime())->setTimestamp(usergetmidnight($endday));
	$day->modify($range);
	$start = $day->getTimestamp();
	//echo date('d/m/Y H:i:s', $start) . ' - ' . date('d/m/Y H:i:s', $endday) . '</br>';
	return $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname,e.courseid
		FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
			WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5
			AND c.id=? AND ue.timecreated>=? AND ue.timecreated<?', [$courseid, $start, $endday]);
}
//chuyen ngay kieu int sang kieu date
function day($date) {
	return date('d/m/Y', $date);
}
if (!empty($fromform->areaids)) {
	//ngay bat dau
	$from = $fromform->startdate;
	//ngay ket thuc
	$to = $fromform->enddate;
	if ($fromform->filter == 'day') {
		foreach ($fromform->areaids as $key => $courseid) {
			$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
			$id = $course->id;

			if (layngay($to, '-1 day') >= $from) {
				$acc = laytk($to, '-1 day', $id);
				$sotk = count($acc);
			}
			//lay du lieu sau Ngay ket thuc 2 ngay
			if (layngay($to, '-2 day') >= $from) {
				$acc1 = laytk(layngay($to, '-1 day'), '-1 day', $id);
				$sotk1 = count($acc1);
			}
			//lay du lieu sau Ngay ket thuc 3 ngay
			if (layngay($to, '-3 day') >= $from) {
				$acc2 = laytk(layngay($to, '-2 day'), '-1 day', $id);
				$sotk2 = count($acc2);
			}
			//sau Ngay ket thuc 3 ngay
			$day = (new DateTime())->setTimestamp(usergetmidnight($to));
			$day->modify('-3 day');
			$last = $day->getTimestamp();

			//lay du lieu Con lai
			$accDayLeft = $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname,u.email,e.courseid FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
			 WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND c.id=? AND ue.timecreated>=? AND ue.timecreated<?', [$id, $from, $last]);
			//dem so ban ghi
			$countAccDayLeft = count($accDayLeft);

			// echo date('d/m/Y H:i:s', $from) . ' - ' . date('d/m/Y H:i:s', $last) . '</br>';
			// echo $sotk . '-' . $sotk1 . '-' . $sotk2 . '-' . $countAccDayLeft . '</br>';
			// Start of table
			$table = new html_table();
			echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $id, $course->fullname));
			$table->head = array(get_string('id', 'block_reportenrollment'), get_string('fullname'), get_string('time'));
			$stt = 1;
			//do du lieu ra bang
			foreach ($acc as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
				$row->cells[] = $cell;
				if ($stt == 1) {
					$cell = new html_table_cell(day(layngay($to, '-1 day')));
				} else {
					$cell = new html_table_cell('');
				}
				$row->cells[] = $cell;
				$table->data[] = $row;
				$stt++;
			}
			if ($acc1 != null) {
				foreach ($acc1 as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + 1) {
						$cell = new html_table_cell(day(layngay($to, '-2 day')));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			if ($acc2 != null) {
				foreach ($acc2 as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + $sotk1 + 1) {
						$cell = new html_table_cell(day(layngay($to, '-3 day')));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			if ($accDayLeft != null) {
				foreach ($accDayLeft as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + $sotk1 + $sotk2 + 1) {
						$cell = new html_table_cell(day($from) . ' - ' . day($last));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			echo html_writer::table($table);
		}
	} elseif ($fromform->filter == 'week') {
		foreach ($fromform->areaids as $key => $courseid) {
			$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
			$id = $course->id;

			//lay du lieu sau Ngay ket thuc 1 tuan
			if (layngay($to, '-1 week') >= $from) {
				$acc = laytk($to, '-1 week', $id);
				$sotk = count($acc);
			}

			//lay du lieu sau Ngay ket thuc 2 tuan
			if (layngay($to, '-2 week') >= $from) {
				$acc1 = laytk(layngay($to, '-1 week'), '-1 week', $id);
				$sotk1 = count($acc1);
			}
			//lay du lieu sau Ngay ket thuc 3 tuan
			if (layngay($to, '-3 week') >= $from) {
				$acc2 = laytk(layngay($to, '-2 week'), '-1 week', $id);
				$sotk2 = count($acc2);
			}
			//lay ngay con lai
			$day = (new DateTime())->setTimestamp(usergetmidnight($to));
			$day->modify('-3 week');
			$last = $day->getTimestamp();
			//Ngay ket thuc lon hon ngay bat dau nho hon 1 tuan
			if (layngay($to, '-1 week') < $from) {
				$last = $to;
			}

			//lay du lieu Con lai
			$accDayLeft = $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname,u.email,e.courseid FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
			 WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND c.id=? AND ue.timecreated>=? AND ue.timecreated<?', [$id, $from, $last]);
			//dem so ban ghi
			$countAccDayLeft = count($accDayLeft);

			// echo date('d/m/Y H:i:s', $from) . ' - ' . date('d/m/Y H:i:s', $last) . '</br>';
			// echo $sotk . '-' . $sotk1 . '-' . $sotk2 . '-' . $countAccDayLeft . '</br>';
			// Start of table
			$table = new html_table();
			echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $id, $course->fullname));
			$table->head = array(get_string('id', 'block_reportenrollment'), get_string('fullname'), get_string('time'));
			$stt = 1;
			//do du lieu ra bang
			foreach ($acc as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
				$row->cells[] = $cell;
				if ($stt == 1) {
					$cell = new html_table_cell(day(layngay($to, '-1 week')) . ' - ' . day($to));
				} else {
					$cell = new html_table_cell('');
				}
				$row->cells[] = $cell;
				$table->data[] = $row;
				$stt++;
			}
			if ($acc1 != null) {
				foreach ($acc1 as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + 1) {
						$cell = new html_table_cell(day(layngay($to, '-2 week')) . ' - ' . day(layngay($to, '-1 week')));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			if ($acc2 != null) {
				foreach ($acc2 as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + $sotk1 + 1) {
						$cell = new html_table_cell(day(layngay($to, '-3 week')) . ' - ' . day(layngay($to, '-2 week')));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			if ($accDayLeft != null) {
				foreach ($accDayLeft as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + $sotk1 + $sotk2 + 1) {
						$cell = new html_table_cell(day($from) . ' - ' . day($last));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			echo html_writer::table($table);
		}
	} elseif ($fromform->filter == 'month') {
		foreach ($fromform->areaids as $key => $courseid) {
			$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
			$id = $course->id;

			//sau ngay ket thuc 1 thang
			if (layngay($to, '-1 month') >= $from) {
				$acc = laytk($to, '-1 month', $id);
				$sotk = count($acc);
			}
			//sau ngay ket thuc 2 thang
			if (layngay($to, '-2 month') >= $from) {
				$acc1 = laytk(layngay($to, '-1 month'), '-1 month', $id);
				$sotk1 = count($acc1);
			}
			//sau ngay ket thuc 3 thang
			if (layngay($to, '-3 month') >= $from) {
				$acc2 = laytk(layngay($to, '-2 month'), '-1 month', $id);
				$sotk2 = count($acc2);
			}
			$day = (new DateTime())->setTimestamp(usergetmidnight($to));
			$day->modify('-3 month');
			$last = $day->getTimestamp();
			//Ngay ket thuc lon hon ngay bat dau nho hon 1 thang
			if (layngay($to, '-1 month') < $from) {
				$last = $to;
			}

			//lay du lieu Con lai
			$accDayLeft = $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname,u.email,e.courseid FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
			 WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND c.id=? AND ue.timecreated>=? AND ue.timecreated<?', [$id, $from, $last]);
			//dem so ban ghi
			$countAccDayLeft = count($accDayLeft);

			// echo date('d/m/Y H:i:s', $from) . ' - ' . date('d/m/Y H:i:s', $last) . '</br>';
			// echo $sotk . '-' . $sotk1 . '-' . $sotk2 . '-' . $countAccDayLeft . '</br>';
			// Start of table
			$table = new html_table();
			echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $id, $course->fullname));
			$table->head = array(get_string('id', 'block_reportenrollment'), get_string('fullname'), get_string('time'));
			$stt = 1;
			//do du lieu ra bang
			foreach ($acc as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
				$row->cells[] = $cell;
				if ($stt == 1) {
					$cell = new html_table_cell(day(layngay($to, '-1 month')) . ' - ' . day($to));
				} else {
					$cell = new html_table_cell('');
				}
				$row->cells[] = $cell;
				$table->data[] = $row;
				$stt++;
			}
			if ($acc1 != null) {
				foreach ($acc1 as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + 1) {
						$cell = new html_table_cell(day(layngay($to, '-2 month')) . ' - ' . day(layngay($to, '-1 month')));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			if ($acc2 != null) {
				foreach ($acc2 as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + $sotk1 + 1) {
						$cell = new html_table_cell(day(layngay($to, '-3 month')) . ' - ' . day(layngay($to, '-2 month')));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			if ($accDayLeft != null) {
				foreach ($accDayLeft as $key => $value) {
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $value->id, $value->lastname . ' ' . $value->firstname));
					$row->cells[] = $cell;
					if ($stt == $sotk + $sotk1 + $sotk2 + 1) {
						$cell = new html_table_cell(day($from) . ' - ' . day($last));
					} else {
						$cell = new html_table_cell('');
					}
					$row->cells[] = $cell;
					$table->data[] = $row;
					$stt++;
				}
			}
			echo html_writer::table($table);
		}
	}

}

// Finish the page.
echo $OUTPUT->footer();