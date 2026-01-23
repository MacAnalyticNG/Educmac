<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Migration extends CI_Migration
{

    public function get_version()
    {
        $row = $this->db->select('version')
            ->get($this->_migration_table)
            ->row();

        return $row ? $row->version : 0;
    }
}
