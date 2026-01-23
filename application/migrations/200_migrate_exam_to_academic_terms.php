<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Migrate Exam Module to Use Academic Terms
 *
 * This migration:
 * 1. Ensures all exam records reference academic_terms instead of exam_term
 * 2. Migrates any remaining exam_term data to academic_terms
 * 3. Updates exam.term_id to reference academic_terms.id
 * 4. Drops the obsolete exam_term table
 */
class Migration_migrate_exam_to_academic_terms extends CI_Migration {

	public function up()
	{
		// Step 1: Ensure academic_terms table exists
		if (!$this->db->table_exists('academic_terms')) {
			echo "ERROR: academic_terms table does not exist. Please run migration 694 first.\n";
			return false;
		}

		// Step 2: Migrate any remaining exam_term data that wasn't migrated
		if ($this->db->table_exists('exam_term')) {
			$this->migrate_remaining_exam_terms();
		}

		// Step 3: Update exam records to reference academic_terms
		if ($this->db->table_exists('exam')) {
			$this->migrate_exam_term_references();
		}

		// Step 4: Drop exam_term table
		if ($this->db->table_exists('exam_term')) {
			$this->dbforge->drop_table('exam_term', TRUE);
			echo "Dropped exam_term table successfully.\n";
		}
	}

	public function down()
	{
		// Recreate exam_term table
		$this->dbforge->add_field([
			'id' => [
				'type' => 'INT',
				'constraint' => '11',
				'auto_increment' => TRUE,
				'null' => FALSE,
			],
			'name' => [
				'type' => 'LONGTEXT',
				'null' => FALSE,
			],
			'branch_id' => [
				'type' => 'INT',
				'constraint' => '11',
			],
			'session_id' => [
				'type' => 'INT',
				'constraint' => '11',
				'null' => FALSE,
			],
		]);

		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('exam_term', TRUE);

		echo "Recreated exam_term table. Data migration reversal not supported.\n";
	}

	/**
	 * Migrate any remaining exam_term records to academic_terms
	 */
	private function migrate_remaining_exam_terms()
	{
		echo "Checking for unmigrated exam_term records...\n";

		$exam_terms = $this->db->get('exam_term')->result();
		$migrated_count = 0;

		foreach ($exam_terms as $exam_term) {
			// Check if this term already exists in academic_terms
			$exists = $this->db
				->where('session_id', $exam_term->session_id)
				->where('branch_id', $exam_term->branch_id)
				->where('term_name', trim($exam_term->name))
				->count_all_results('academic_terms');

			if ($exists > 0) {
				continue; // Already migrated
			}

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
				$years = [];
				if (strpos($session->school_year, '/') !== false) {
					$years = explode('/', $session->school_year);
				} elseif (strpos($session->school_year, '-') !== false) {
					$years = explode('-', $session->school_year);
				}

				if (count($years) == 2) {
					$start_year = trim($years[0]);
					$end_year = trim($years[1]);

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
				$migrated_count++;
			}
		}

		echo "Migrated {$migrated_count} exam_term records to academic_terms.\n";
	}

	/**
	 * Update exam records to reference academic_terms instead of exam_term
	 */
	private function migrate_exam_term_references()
	{
		echo "Updating exam records to reference academic_terms...\n";

		// Get all exams with term_id
		$exams = $this->db
			->select('exam.id, exam.term_id, exam.session_id, exam.branch_id, exam_term.name as term_name')
			->from('exam')
			->join('exam_term', 'exam_term.id = exam.term_id', 'left')
			->where('exam.term_id !=', 0)
			->get()
			->result();

		$updated_count = 0;
		$not_found_count = 0;

		foreach ($exams as $exam) {
			if (empty($exam->term_name)) {
				// term_id references a non-existent exam_term, set to 0
				$this->db->where('id', $exam->id);
				$this->db->update('exam', ['term_id' => 0]);
				$not_found_count++;
				continue;
			}

			// Find matching academic_term
			$academic_term = $this->db
				->where('session_id', $exam->session_id)
				->where('branch_id', $exam->branch_id)
				->where('term_name', trim($exam->term_name))
				->get('academic_terms')
				->row();

			if ($academic_term) {
				// Update exam to reference academic_terms.id
				$this->db->where('id', $exam->id);
				$this->db->update('exam', ['term_id' => $academic_term->id]);
				$updated_count++;
			} else {
				// Try to find by term order
				$term_order = 1;
				if (stripos($exam->term_name, 'second') !== false || stripos($exam->term_name, '2nd') !== false) {
					$term_order = 2;
				} elseif (stripos($exam->term_name, 'third') !== false || stripos($exam->term_name, '3rd') !== false) {
					$term_order = 3;
				}

				$academic_term_by_order = $this->db
					->where('session_id', $exam->session_id)
					->where('branch_id', $exam->branch_id)
					->where('term_order', $term_order)
					->get('academic_terms')
					->row();

				if ($academic_term_by_order) {
					$this->db->where('id', $exam->id);
					$this->db->update('exam', ['term_id' => $academic_term_by_order->id]);
					$updated_count++;
				} else {
					// No matching term found, set to 0
					$this->db->where('id', $exam->id);
					$this->db->update('exam', ['term_id' => 0]);
					$not_found_count++;
				}
			}
		}

		echo "Updated {$updated_count} exam records successfully.\n";
		echo "Set term_id to 0 for {$not_found_count} exams (no matching academic term found).\n";
	}
}
