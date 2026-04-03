<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_custom_field extends CI_Migration {

	public function up() {
		// Define fields
		$this->dbforge->add_field([
			'id' => [
				'type' => 'INT',
				'constraint' => '11',
				'auto_increment' => TRUE,
				'null' => FALSE,
			],
			'form_to' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
			'field_label' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => FALSE,
			],
			'default_value' => [
				'type' => 'TEXT',
			],
			'field_type' => [
				'type' => 'ENUM',
				'constraint' => "'text','textarea','dropdown','date','checkbox','number','url','email'",
				'null' => FALSE,
			],
			'required' => [
				'type' => 'VARCHAR',
				'constraint' => '5',
				'null' => FALSE,
				'default' => 'false',
			],
			'status' => [
				'type' => 'TINYINT',
				'constraint' => '4',
				'null' => FALSE,
				'default' => 1,
			],
			'show_on_table' => [
				'type' => 'VARCHAR',
				'constraint' => '5',
			],
			'field_order' => [
				'type' => 'INT',
				'constraint' => '11',
				'null' => FALSE,
			],
			'bs_column' => [
				'type' => 'INT',
				'constraint' => '11',
				'null' => FALSE,
			],
			'branch_id' => [
				'type' => 'INT',
				'constraint' => '11',
				'null' => FALSE,
			],
			'created_at' => [
				'type' => 'TIMESTAMP',
				'null' => FALSE,
			],
		]);

		// Add primary key
		$this->dbforge->add_key('id', TRUE);

		// Create table
		$this->dbforge->create_table('custom_field', TRUE);
	}

	public function down() {
		$this->dbforge->drop_table('custom_field', TRUE);
	}
}
