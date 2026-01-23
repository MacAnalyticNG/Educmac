<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Add Section Marksheet Template Mapping
 * 
 * This migration creates a table to map sections to marksheet templates,
 * allowing different sections (e.g., Nursery, Primary, Junior Secondary) 
 * to use different report card templates. Multiple sections can share the same template.
 * 
 * @package : Ramom school management system
 * @version : 7.0
 */
class Migration_Add_section_marksheet_template extends CI_Migration
{
    public function up()
    {
        // Create section_marksheet_template table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'branch_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'section_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'template_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
                'default' => 0
            )
        ));

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('branch_id');
        $this->dbforge->add_key('section_id');
        $this->dbforge->add_key('template_id');

        $this->dbforge->create_table('section_marksheet_template', TRUE);

        // Add timestamp columns with raw SQL (to avoid quoting issues)
        $this->db->query('ALTER TABLE `section_marksheet_template` 
            ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ADD COLUMN `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP');

        // Add foreign key constraints
        $this->db->query('ALTER TABLE `section_marksheet_template` 
            ADD CONSTRAINT `section_marksheet_template_branch_fk` 
            FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) 
            ON DELETE CASCADE ON UPDATE RESTRICT');

        $this->db->query('ALTER TABLE `section_marksheet_template` 
            ADD CONSTRAINT `section_marksheet_template_section_fk` 
            FOREIGN KEY (`section_id`) REFERENCES `section` (`id`) 
            ON DELETE CASCADE ON UPDATE RESTRICT');

        $this->db->query('ALTER TABLE `section_marksheet_template` 
            ADD CONSTRAINT `section_marksheet_template_template_fk` 
            FOREIGN KEY (`template_id`) REFERENCES `marksheet_template` (`id`) 
            ON DELETE CASCADE ON UPDATE RESTRICT');

        // Add unique constraint to prevent duplicate mappings
        $this->db->query('ALTER TABLE `section_marksheet_template` 
            ADD UNIQUE KEY `unique_branch_section` (`branch_id`, `section_id`)');
    }

    public function down()
    {
        // Drop foreign key constraints first
        if ($this->db->table_exists('section_marksheet_template')) {
            $this->db->query('ALTER TABLE `section_marksheet_template` DROP FOREIGN KEY `section_marksheet_template_branch_fk`');
            $this->db->query('ALTER TABLE `section_marksheet_template` DROP FOREIGN KEY `section_marksheet_template_section_fk`');
            $this->db->query('ALTER TABLE `section_marksheet_template` DROP FOREIGN KEY `section_marksheet_template_template_fk`');

            // Drop the table
            $this->dbforge->drop_table('section_marksheet_template', TRUE);
        }
    }
}
