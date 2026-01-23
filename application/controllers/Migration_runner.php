<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Ramom school management system
 * @version : 7.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @filename : Migration_runner.php
 */

class Migration_runner extends Dashboard_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('migration');

        if (!is_superadmin_loggedin()) {
            access_denied();
        }
    }

    public function index()
    {
        $this->data['title'] = translate('database_migrations');
        $this->data['sub_page'] = 'migration_runner/index';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }

    public function latest()
    {
        if ($this->migration->latest() === FALSE) {
            $this->data['status'] = 'error';
            $this->data['message'] = $this->migration->error_string();
        } else {
            $this->data['status'] = 'success';
            $this->data['message'] = translate('migration_successful');
        }

        $this->data['title'] = translate('migrate_to_latest');
        $this->data['sub_page'] = 'migration_runner/result';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }

    public function current()
    {
        if ($this->migration->current() === FALSE) {
            $this->data['status'] = 'error';
            $this->data['message'] = $this->migration->error_string();
        } else {
            $this->data['status'] = 'success';
            $this->data['message'] = translate('migration_successful');
        }

        $this->data['title'] = translate('migrate_to_current');
        $this->data['sub_page'] = 'migration_runner/result';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }

    public function status()
    {
        $current_version = $this->migration->get_version();
        $this->data['current_version'] = $current_version;

        $migrations_path = APPPATH . 'migrations/';
        $files = glob($migrations_path . '*.php');

        $migrations = [];
        if (!empty($files)) {
            $count = 1;
            foreach ($files as $file) {
                $basename = basename($file, '.php');

                if (preg_match('/^(\d+)_(.+)$/', $basename, $matches)) {
                    $version = $matches[1];
                    $name = ucwords(str_replace('_', ' ', $matches[2]));
                    $migrations[] = [
                        'number' => $count,
                        'version' => $version,
                        'name' => $name,
                        'status' => ($current_version && $version <= $current_version) ? 'applied' : 'pending'
                    ];

                    $count++;
                }
            }
        }

        $this->data['migrations'] = $migrations;
        $this->data['title'] = translate('migration_status');
        $this->data['sub_page'] = 'migration_runner/status';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }

    public function rollback()
    {
        $current_version = $this->migration->get_version();

        if (!$current_version) {
            $this->data['status'] = 'error';
            $this->data['message'] = translate('no_migrations_to_rollback');
            $this->data['title'] = translate('rollback_migration');
            $this->data['sub_page'] = 'migration_runner/result';
            $this->data['main_menu'] = 'settings';
            $this->load->view('layout/index', $this->data);
            return;
        }

        $migrations_path = APPPATH . 'migrations/';
        $files = glob($migrations_path . '*.php');

        $versions = [];
        foreach ($files as $file) {
            $basename = basename($file, '.php');
            if (preg_match('/^(\d+)_/', $basename, $matches)) {
                $versions[] = $matches[1];
            }
        }

        sort($versions);

        $current_index = array_search($current_version, $versions);
        if ($current_index === false || $current_index === 0) {
            $this->data['status'] = 'error';
            $this->data['message'] = translate('no_previous_migration_available');
            $this->data['title'] = translate('rollback_migration');
            $this->data['sub_page'] = 'migration_runner/result';
            $this->data['main_menu'] = 'settings';
            $this->load->view('layout/index', $this->data);
            return;
        }

        $previous_version = $versions[$current_index - 1];

        if ($this->migration->version($previous_version) === FALSE) {
            $this->data['status'] = 'error';
            $this->data['message'] = $this->migration->error_string();
        } else {
            $this->data['status'] = 'success';
            $this->data['message'] = translate('rollback_successful') . " (v$current_version → v$previous_version)";
        }

        $this->data['title'] = translate('rollback_migration');
        $this->data['sub_page'] = 'migration_runner/result';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }

    public function run_specific($version = null)
    {
        if (!$version) {
            set_alert('error', 'Migration version is required');
            redirect('migration_runner/status');
            return;
        }

        $current_version = $this->migration->get_version();

        $migrations_path = APPPATH . 'migrations/';
        $files = glob($migrations_path . $version . '_*.php');
        $migration_name = 'Unknown';
        $migration_file = null;

        if (!empty($files)) {
            $migration_file = $files[0];
            $basename = basename($migration_file, '.php');
            if (preg_match('/^\d+_(.+)$/', $basename, $matches)) {
                $migration_name = ucwords(str_replace('_', ' ', $matches[1]));
            }
        } else {
            $this->data['status'] = 'error';
            $this->data['message'] = "Migration file not found for version $version";
            $this->data['title'] = 'Run Specific Migration';
            $this->data['sub_page'] = 'migration_runner/result';
            $this->data['main_menu'] = 'settings';
            $this->load->view('layout/index', $this->data);
            return;
        }

        try {
            require_once($migration_file);

            $class_name = 'Migration_' . ucfirst(preg_replace('/^\d+_/', '', basename($migration_file, '.php')));
            if (!class_exists($class_name)) {
                throw new Exception("Migration class '$class_name' not found in file.");
            }

            $migration_instance = new $class_name();

            if ($current_version && $version <= $current_version) {
                $migration_instance->down();
            }
            $migration_instance->up();

            if (!($current_version && $version <= $current_version)) {
                $this->db->update('migrations', ['version' => $version]);
            }

            $action = ($current_version && $version <= $current_version) ? 'reapplied' : 'applied';
            $this->data['status'] = 'success';
            $this->data['message'] = "Migration '$migration_name' (v$version) has been $action successfully.";
        } catch (Exception $e) {
            $this->data['status'] = 'error';
            $this->data['message'] = "Error running migration: " . $e->getMessage();
        }

        $this->data['title'] = 'Run Specific Migration';
        $this->data['sub_page'] = 'migration_runner/result';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }
}
