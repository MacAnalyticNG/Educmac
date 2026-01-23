<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Add Show Skills Column to Marksheet Template
 * 
 * This migration adds a show_skills column to the marksheet_template table
 * to control whether skills assessment data should be displayed on report cards.
 * This allows templates to flexibly show academic results, skills assessment, or both.
 * 
 * @package : Ramom school management system
 * @version : 7.0
 */
class Migration_Add_show_skills_to_marksheet_template extends CI_Migration
{
    public function up()
    {
        // Add show_skills column to marksheet_template table
        $fields = array(
            'show_skills' => array(
                'type' => 'TINYINT',
                'constraint' => '4',
                'default' => 0,
                'null' => FALSE,
                'after' => 'subjects_table'
            ),
        );

        $this->dbforge->add_column('marksheet_template', $fields);

        // Add comment to column (raw query for MySQL compatibility)
        $this->db->query("ALTER TABLE `marksheet_template` MODIFY COLUMN `show_skills` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '0 = Hide Skills Section, 1 = Show Skills Section'");
    }

    public function down()
    {
        // Check if column exists before dropping
        if ($this->db->field_exists('show_skills', 'marksheet_template')) {
            $this->dbforge->drop_column('marksheet_template', 'show_skills');
        }
    }
}
