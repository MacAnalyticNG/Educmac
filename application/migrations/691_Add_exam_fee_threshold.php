<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Add Fee Threshold to Exam
 * 
 * This migration adds a fee_threshold_percentage column to the exam table
 * to control student access to exam results based on their fee payment status.
 * 
 * @package : Ramom school management system
 * @version : 7.0
 */
class Migration_Add_exam_fee_threshold extends CI_Migration
{
    public function up()
    {
        // Add fee_threshold_percentage column to exam table
        $fields = array(
            'fee_threshold_percentage' => array(
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
                'null' => FALSE,
                'after' => 'publish_result'
            ),
        );

        $this->dbforge->add_column('exam', $fields);

        // Add comment to column (raw query for MySQL compatibility)
        $this->db->query("ALTER TABLE `exam` MODIFY COLUMN `fee_threshold_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Minimum fee payment percentage required to view/print exam results'");
    }

    public function down()
    {
        // Check if column exists before dropping
        if ($this->db->field_exists('fee_threshold_percentage', 'exam')) {
            $this->dbforge->drop_column('exam', 'fee_threshold_percentage');
        }
    }
}
