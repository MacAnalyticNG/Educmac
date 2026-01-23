<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create Academic Terms Table
 *
 * This migration creates a centralized academic_terms table to support
 * the Nigerian academic system with 3 terms per session.
 * This replaces the limited exam_term table with a comprehensive term system.
 */
class Migration_create_academic_terms extends CI_Migration {

	public function up()
	{
		// Check if table already exists
		if (!$this->db->table_exists('academic_terms')) {
			// Define fields
			$this->dbforge->add_field([
				'id' => [
					'type' => 'INT',
					'constraint' => 11,
					'auto_increment' => TRUE,
					'null' => FALSE,
				],
				'session_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
				],
				'branch_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
				],
				'term_name' => [
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => FALSE,
				],
				'term_order' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
					'comment' => '1 = First Term, 2 = Second Term, 3 = Third Term',
				],
				'start_date' => [
					'type' => 'DATE',
					'null' => FALSE,
				],
				'end_date' => [
					'type' => 'DATE',
					'null' => FALSE,
				],
				'is_active' => [
					'type' => 'INT',
					'constraint' => 1,
					'null' => FALSE,
					'default' => 0,
					'comment' => '0 = Inactive, 1 = Active',
				],
				'total_weeks' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => TRUE,
				],
				'holidays' => [
					'type' => 'LONGTEXT',
					'null' => TRUE,
					'comment' => 'JSON array of holiday dates',
				],
				'created_at' => [
					'type' => 'TIMESTAMP',
					'null' => TRUE,
				],
				'updated_at' => [
					'type' => 'TIMESTAMP',
					'null' => TRUE,
				],
			]);

			// Add primary key
			$this->dbforge->add_key('id', TRUE);

			// Add indexes
			$this->dbforge->add_key('session_id');
			$this->dbforge->add_key('branch_id');
			$this->dbforge->add_key('is_active');

			// Create table
			$this->dbforge->create_table('academic_terms', TRUE);

			// Add foreign key constraints
			$this->db->query(
				'ALTER TABLE `academic_terms` ADD CONSTRAINT `academic_terms_session_fk` '
				. 'FOREIGN KEY (`session_id`) '
				. 'REFERENCES `schoolyear` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT'
			);

			$this->db->query(
				'ALTER TABLE `academic_terms` ADD CONSTRAINT `academic_terms_branch_fk` '
				. 'FOREIGN KEY (`branch_id`) '
				. 'REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT'
			);

			// Migrate data from exam_term if it exists
			if ($this->db->table_exists('exam_term')) {
				$this->migrate_exam_term_data();
			}
		}
	}

	public function down()
	{
		// Drop foreign key constraints first
		if ($this->db->table_exists('academic_terms')) {
			// Check and drop foreign keys safely
			$result = $this->db->query("
				SELECT CONSTRAINT_NAME
				FROM information_schema.KEY_COLUMN_USAGE
				WHERE TABLE_NAME = 'academic_terms'
				AND TABLE_SCHEMA = DATABASE()
				AND CONSTRAINT_NAME IN ('academic_terms_session_fk', 'academic_terms_branch_fk')
			");

			foreach ($result->result() as $row) {
				$this->db->query("ALTER TABLE `academic_terms` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
			}
		}

		// Drop table
		$this->dbforge->drop_table('academic_terms', TRUE);
	}

	/**
	 * Migrate existing exam_term data to academic_terms
	 */
	private function migrate_exam_term_data()
	{
		$exam_terms = $this->db->get('exam_term')->result();

		foreach ($exam_terms as $exam_term) {
			// Parse term name to determine term order
			$term_order = 1;
			$term_name = trim($exam_term->name);

			if (stripos($term_name, 'second') !== false || stripos($term_name, '2nd') !== false) {
				$term_order = 2;
			} elseif (stripos($term_name, 'third') !== false || stripos($term_name, '3rd') !== false) {
				$term_order = 3;
			}

			// Get session details for date calculation
			$session = $this->db->get_where('schoolyear', ['id' => $exam_term->session_id])->row();

			if ($session) {
				// Calculate term dates based on Nigerian academic calendar
				$session_years = explode('/', $session->school_year);
				if (count($session_years) == 2) {
					$start_year = $session_years[0];
					$end_year = $session_years[1];

					switch ($term_order) {
						case 1:
							$start_date = $start_year . '-09-01';
							$end_date = $start_year . '-12-15';
							$total_weeks = 15;
							break;
						case 2:
							$start_date = $end_year . '-01-15';
							$end_date = $end_year . '-04-15';
							$total_weeks = 13;
							break;
						case 3:
							$start_date = $end_year . '-05-01';
							$end_date = $end_year . '-08-15';
							$total_weeks = 15;
							break;
						default:
							$start_date = $start_year . '-09-01';
							$end_date = $start_year . '-12-15';
							$total_weeks = 15;
					}
				} else {
					// Fallback dates
					$start_date = date('Y-m-d');
					$end_date = date('Y-m-d', strtotime('+3 months'));
					$total_weeks = 13;
				}

				// Insert into academic_terms
				$data = [
					'session_id' => $exam_term->session_id,
					'branch_id' => $exam_term->branch_id,
					'term_name' => $term_name,
					'term_order' => $term_order,
					'start_date' => $start_date,
					'end_date' => $end_date,
					'is_active' => 0,
					'total_weeks' => $total_weeks,
					'created_at' => date('Y-m-d H:i:s'),
				];

				$this->db->insert('academic_terms', $data);
			}
		}
	}
}
