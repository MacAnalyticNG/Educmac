<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Add Term ID to Attendance Tables
 *
 * This migration adds term_id field to attendance tables to support
 * term-based attendance tracking following the Nigerian 3-term system.
 * This enables filtering attendance by specific terms.
 */
class Migration_add_term_id_to_attendance_tables extends CI_Migration {

	public function up()
	{
		// Add term_id to student_attendance table
		if ($this->db->table_exists('student_attendance')) {
			if (!$this->db->field_exists('term_id', 'student_attendance')) {
				$fields = [
					'term_id' => [
						'type' => 'INT',
						'constraint' => 11,
						'null' => TRUE,
						'after' => 'branch_id'
					]
				];
				$this->dbforge->add_column('student_attendance', $fields);
				echo "Added term_id to student_attendance table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `student_attendance` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on student_attendance.term_id.\n";

				// Populate term_id for existing records
				$this->populate_student_attendance_term_id();
			}
		}

		// Add term_id to student_subject_attendance table
		if ($this->db->table_exists('student_subject_attendance')) {
			if (!$this->db->field_exists('term_id', 'student_subject_attendance')) {
				$fields = [
					'term_id' => [
						'type' => 'INT',
						'constraint' => 11,
						'null' => TRUE,
						'after' => 'branch_id'
					]
				];
				$this->dbforge->add_column('student_subject_attendance', $fields);
				echo "Added term_id to student_subject_attendance table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `student_subject_attendance` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on student_subject_attendance.term_id.\n";

				// Populate term_id for existing records
				$this->populate_subject_attendance_term_id();
			}
		}

		// Add term_id to staff_attendance table if it exists
		if ($this->db->table_exists('staff_attendance')) {
			if (!$this->db->field_exists('term_id', 'staff_attendance')) {
				$fields = [
					'term_id' => [
						'type' => 'INT',
						'constraint' => 11,
						'null' => TRUE,
						'after' => 'branch_id'
					]
				];
				$this->dbforge->add_column('staff_attendance', $fields);
				echo "Added term_id to staff_attendance table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `staff_attendance` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on staff_attendance.term_id.\n";

				// Populate term_id for existing records
				$this->populate_staff_attendance_term_id();
			}
		}
	}

	public function down()
	{
		// Remove term_id from student_attendance
		if ($this->db->table_exists('student_attendance')) {
			if ($this->db->field_exists('term_id', 'student_attendance')) {
				$this->db->query('ALTER TABLE `student_attendance` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('student_attendance', 'term_id');
			}
		}

		// Remove term_id from student_subject_attendance
		if ($this->db->table_exists('student_subject_attendance')) {
			if ($this->db->field_exists('term_id', 'student_subject_attendance')) {
				$this->db->query('ALTER TABLE `student_subject_attendance` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('student_subject_attendance', 'term_id');
			}
		}

		// Remove term_id from staff_attendance
		if ($this->db->table_exists('staff_attendance')) {
			if ($this->db->field_exists('term_id', 'staff_attendance')) {
				$this->db->query('ALTER TABLE `staff_attendance` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('staff_attendance', 'term_id');
			}
		}

		echo "Removed term_id from all attendance tables.\n";
	}

	/**
	 * Populate term_id for existing student_attendance records
	 * Matches attendance date with term date ranges
	 */
	private function populate_student_attendance_term_id()
	{
		if (!$this->db->table_exists('academic_terms')) {
			echo "Skipping student_attendance term population: academic_terms table doesn't exist.\n";
			return;
		}

		echo "Populating term_id for existing student_attendance records...\n";

		// Get all attendance records without term_id
		$attendance_records = $this->db
			->select('sa.id, sa.date, sa.branch_id, e.session_id')
			->from('student_attendance sa')
			->join('enroll e', 'e.id = sa.enroll_id', 'left')
			->where('sa.term_id IS NULL')
			->get()
			->result();

		$updated_count = 0;
		$not_found_count = 0;

		foreach ($attendance_records as $record) {
			if (empty($record->session_id) || empty($record->branch_id) || empty($record->date)) {
				$not_found_count++;
				continue;
			}

			// Find matching term based on date range
			$term = $this->db
				->where('session_id', $record->session_id)
				->where('branch_id', $record->branch_id)
				->where('start_date <=', $record->date)
				->where('end_date >=', $record->date)
				->get('academic_terms')
				->row();

			if ($term) {
				$this->db->where('id', $record->id);
				$this->db->update('student_attendance', ['term_id' => $term->id]);
				$updated_count++;
			} else {
				$not_found_count++;
			}
		}

		echo "Updated {$updated_count} student_attendance records with term_id.\n";
		echo "Unable to match {$not_found_count} records (no matching term or missing data).\n";
	}

	/**
	 * Populate term_id for existing student_subject_attendance records
	 */
	private function populate_subject_attendance_term_id()
	{
		if (!$this->db->table_exists('academic_terms')) {
			echo "Skipping subject_attendance term population: academic_terms table doesn't exist.\n";
			return;
		}

		echo "Populating term_id for existing student_subject_attendance records...\n";

		// Get all subject attendance records without term_id
		$attendance_records = $this->db
			->select('ssa.id, ssa.date, ssa.branch_id, e.session_id')
			->from('student_subject_attendance ssa')
			->join('enroll e', 'e.id = ssa.enroll_id', 'left')
			->where('ssa.term_id IS NULL')
			->get()
			->result();

		$updated_count = 0;
		$not_found_count = 0;

		foreach ($attendance_records as $record) {
			if (empty($record->session_id) || empty($record->branch_id) || empty($record->date)) {
				$not_found_count++;
				continue;
			}

			// Find matching term based on date range
			$term = $this->db
				->where('session_id', $record->session_id)
				->where('branch_id', $record->branch_id)
				->where('start_date <=', $record->date)
				->where('end_date >=', $record->date)
				->get('academic_terms')
				->row();

			if ($term) {
				$this->db->where('id', $record->id);
				$this->db->update('student_subject_attendance', ['term_id' => $term->id]);
				$updated_count++;
			} else {
				$not_found_count++;
			}
		}

		echo "Updated {$updated_count} student_subject_attendance records with term_id.\n";
		echo "Unable to match {$not_found_count} records (no matching term or missing data).\n";
	}

	/**
	 * Populate term_id for existing staff_attendance records
	 */
	private function populate_staff_attendance_term_id()
	{
		if (!$this->db->table_exists('academic_terms')) {
			echo "Skipping staff_attendance term population: academic_terms table doesn't exist.\n";
			return;
		}

		echo "Populating term_id for existing staff_attendance records...\n";

		// Get all staff attendance records without term_id
		$attendance_records = $this->db
			->select('id, date, branch_id')
			->from('staff_attendance')
			->where('term_id IS NULL')
			->get()
			->result();

		$updated_count = 0;
		$not_found_count = 0;

		foreach ($attendance_records as $record) {
			if (empty($record->branch_id) || empty($record->date)) {
				$not_found_count++;
				continue;
			}

			// Find the session based on the date
			// Assuming the date falls within a session year
			$year = date('Y', strtotime($record->date));
			$month = date('m', strtotime($record->date));

			// Determine session based on Nigerian academic calendar
			// Sep-Aug academic year
			if ($month >= 9) {
				$start_year = $year;
				$end_year = $year + 1;
			} else {
				$start_year = $year - 1;
				$end_year = $year;
			}

			$session_name = $start_year . '/' . $end_year;

			// Find session
			$session = $this->db
				->like('school_year', $session_name)
				->get('schoolyear')
				->row();

			if ($session) {
				// Find matching term based on date range
				$term = $this->db
					->where('session_id', $session->id)
					->where('branch_id', $record->branch_id)
					->where('start_date <=', $record->date)
					->where('end_date >=', $record->date)
					->get('academic_terms')
					->row();

				if ($term) {
					$this->db->where('id', $record->id);
					$this->db->update('staff_attendance', ['term_id' => $term->id]);
					$updated_count++;
				} else {
					$not_found_count++;
				}
			} else {
				$not_found_count++;
			}
		}

		echo "Updated {$updated_count} staff_attendance records with term_id.\n";
		echo "Unable to match {$not_found_count} records (no matching term or missing data).\n";
	}
}
