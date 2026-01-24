<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create Fee Student Exclusions Table
 *
 * This migration creates a table to track fee types excluded from individual
 * student invoices, allowing administrators to remove specific fees from
 * student bills when needed.
 */
class Migration_create_fee_student_exclusions extends CI_Migration {

	public function up()
	{
		// Check if table already exists
		if (!$this->db->table_exists('fee_student_exclusions')) {
			// Define fields
			$this->dbforge->add_field([
				'id' => [
					'type' => 'INT',
					'constraint' => 11,
					'auto_increment' => TRUE,
					'null' => FALSE,
				],
				'student_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
				],
				'enroll_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
				],
				'fee_type_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
				],
				'fee_group_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
				],
				'allocation_id' => [
					'type' => 'INT',
					'constraint' => 11,
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
				'excluded_by' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE,
					'comment' => 'User ID who performed the exclusion',
				],
				'excluded_date' => [
					'type' => 'TIMESTAMP',
					'null' => FALSE,
				],
				'term_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'null' => TRUE,
				],
			]);

			// Add primary key
			$this->dbforge->add_key('id', TRUE);

			// Add indexes
			$this->dbforge->add_key('student_id');
			$this->dbforge->add_key('enroll_id');
			$this->dbforge->add_key('allocation_id');
			$this->dbforge->add_key('fee_type_id');
			$this->dbforge->add_key('session_id');
			$this->dbforge->add_key('branch_id');
			$this->dbforge->add_key('term_id');

			// Create table
			$this->dbforge->create_table('fee_student_exclusions', TRUE);

			// Add CURRENT_TIMESTAMP default using raw SQL (DBForge doesn't support it)
			$this->db->query("ALTER TABLE `fee_student_exclusions` MODIFY `excluded_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
		}
	}

	public function down()
	{
		// Drop table
		$this->dbforge->drop_table('fee_student_exclusions', TRUE);
	}
}
