<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Add Term ID to Fees Tables
 *
 * This migration adds term_id field to fees-related tables to support
 * term-based fee management following the Nigerian 3-term system.
 * This enables allocating and tracking fees per academic term.
 */
class Migration_add_term_id_to_fees_tables extends CI_Migration {

	public function up()
	{
		// Add term_id to fee_allocation table
		if ($this->db->table_exists('fee_allocation')) {
			if (!$this->db->field_exists('term_id', 'fee_allocation')) {
				$fields = [
					'term_id' => [
						'type' => 'INT',
						'constraint' => 11,
						'null' => TRUE,
						'after' => 'session_id'
					]
				];
				$this->dbforge->add_column('fee_allocation', $fields);
				echo "Added term_id to fee_allocation table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `fee_allocation` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on fee_allocation.term_id.\n";

				// Populate term_id for existing records
				$this->populate_fee_allocation_term_id();
			}
		}

		// Add term_id to fee_groups table
		if ($this->db->table_exists('fee_groups')) {
			if (!$this->db->field_exists('term_id', 'fee_groups')) {
				$fields = [
					'term_id' => [
						'type' => 'INT',
						'constraint' => 11,
						'null' => TRUE,
						'after' => 'session_id'
					]
				];
				$this->dbforge->add_column('fee_groups', $fields);
				echo "Added term_id to fee_groups table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `fee_groups` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on fee_groups.term_id.\n";
			}
		}

		// Add term_id to fee_fine table
		if ($this->db->table_exists('fee_fine')) {
			if (!$this->db->field_exists('term_id', 'fee_fine')) {
				$fields = [
					'term_id' => [
						'type' => 'INT',
						'constraint' => 11,
						'null' => TRUE,
						'after' => 'session_id'
					]
				];
				$this->dbforge->add_column('fee_fine', $fields);
				echo "Added term_id to fee_fine table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `fee_fine` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on fee_fine.term_id.\n";
			}
		}

		// Add term_id to transport_fee_details table if exists
		if ($this->db->table_exists('transport_fee_details')) {
			if (!$this->db->field_exists('term_id', 'transport_fee_details')) {
				// Check if session_id column exists before using it as position
				if ($this->db->field_exists('session_id', 'transport_fee_details')) {
					$fields = [
						'term_id' => [
							'type' => 'INT',
							'constraint' => 11,
							'null' => TRUE,
							'after' => 'session_id'
						]
					];
				} else {
					// Add term_id without position constraint if session_id doesn't exist
					$fields = [
						'term_id' => [
							'type' => 'INT',
							'constraint' => 11,
							'null' => TRUE
						]
					];
				}
				$this->dbforge->add_column('transport_fee_details', $fields);
				echo "Added term_id to transport_fee_details table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `transport_fee_details` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on transport_fee_details.term_id.\n";
			}
		}

		// Add term_id to fees_reminder table if exists
		if ($this->db->table_exists('fees_reminder')) {
			if (!$this->db->field_exists('term_id', 'fees_reminder')) {
				// Check if session_id column exists before using it as position
				if ($this->db->field_exists('session_id', 'fees_reminder')) {
					$fields = [
						'term_id' => [
							'type' => 'INT',
							'constraint' => 11,
							'null' => TRUE,
							'after' => 'session_id'
						]
					];
				} else {
					// Add term_id without position constraint if session_id doesn't exist
					$fields = [
						'term_id' => [
							'type' => 'INT',
							'constraint' => 11,
							'null' => TRUE
						]
					];
				}
				$this->dbforge->add_column('fees_reminder', $fields);
				echo "Added term_id to fees_reminder table.\n";

				// Add index for better query performance
				$this->db->query('ALTER TABLE `fees_reminder` ADD INDEX `idx_term_id` (`term_id`)');
				echo "Added index on fees_reminder.term_id.\n";
			}
		}
	}

	public function down()
	{
		// Remove term_id from fee_allocation
		if ($this->db->table_exists('fee_allocation')) {
			if ($this->db->field_exists('term_id', 'fee_allocation')) {
				$this->db->query('ALTER TABLE `fee_allocation` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('fee_allocation', 'term_id');
			}
		}

		// Remove term_id from fee_groups
		if ($this->db->table_exists('fee_groups')) {
			if ($this->db->field_exists('term_id', 'fee_groups')) {
				$this->db->query('ALTER TABLE `fee_groups` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('fee_groups', 'term_id');
			}
		}

		// Remove term_id from fee_fine
		if ($this->db->table_exists('fee_fine')) {
			if ($this->db->field_exists('term_id', 'fee_fine')) {
				$this->db->query('ALTER TABLE `fee_fine` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('fee_fine', 'term_id');
			}
		}

		// Remove term_id from transport_fee_details
		if ($this->db->table_exists('transport_fee_details')) {
			if ($this->db->field_exists('term_id', 'transport_fee_details')) {
				$this->db->query('ALTER TABLE `transport_fee_details` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('transport_fee_details', 'term_id');
			}
		}

		// Remove term_id from fees_reminder
		if ($this->db->table_exists('fees_reminder')) {
			if ($this->db->field_exists('term_id', 'fees_reminder')) {
				$this->db->query('ALTER TABLE `fees_reminder` DROP INDEX `idx_term_id`');
				$this->dbforge->drop_column('fees_reminder', 'term_id');
			}
		}

		echo "Removed term_id from all fees tables.\n";
	}

	/**
	 * Populate term_id for existing fee_allocation records
	 * Links allocations to first term of session by default
	 */
	private function populate_fee_allocation_term_id()
	{
		if (!$this->db->table_exists('academic_terms')) {
			echo "Skipping fee_allocation term population: academic_terms table doesn't exist.\n";
			return;
		}

		echo "Populating term_id for existing fee_allocation records...\n";

		// Get all fee allocations without term_id
		$allocations = $this->db
			->select('id, session_id, branch_id')
			->from('fee_allocation')
			->where('term_id IS NULL')
			->get()
			->result();

		$updated_count = 0;
		$not_found_count = 0;

		foreach ($allocations as $allocation) {
			if (empty($allocation->session_id) || empty($allocation->branch_id)) {
				$not_found_count++;
				continue;
			}

			// Get first term of session (term_order = 1)
			// This is typically when fees are allocated at start of year
			$term = $this->db
				->where('session_id', $allocation->session_id)
				->where('branch_id', $allocation->branch_id)
				->where('term_order', 1)
				->get('academic_terms')
				->row();

			// Fallback: Get any active term for this session/branch
			if (!$term) {
				$term = $this->db
					->where('session_id', $allocation->session_id)
					->where('branch_id', $allocation->branch_id)
					->where('is_active', 1)
					->get('academic_terms')
					->row();
			}

			// Fallback: Get any term for this session/branch
			if (!$term) {
				$term = $this->db
					->where('session_id', $allocation->session_id)
					->where('branch_id', $allocation->branch_id)
					->order_by('term_order', 'ASC')
					->limit(1)
					->get('academic_terms')
					->row();
			}

			if ($term) {
				$this->db->where('id', $allocation->id);
				$this->db->update('fee_allocation', ['term_id' => $term->id]);
				$updated_count++;
			} else {
				$not_found_count++;
			}
		}

		echo "Updated {$updated_count} fee_allocation records with term_id.\n";
		echo "Unable to match {$not_found_count} records (no matching term found).\n";
	}
}
