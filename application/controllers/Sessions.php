<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Ramom school management system
 * @version : 7.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Sessions.php
 * @copyright : Reserved RamomCoder Team
 *
 * Enhanced with Nigerian Academic Term System (3 Terms per Session)
 */

class Sessions extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('sessions_model');
        $this->load->model('application_model');

        try {
            $this->auto_manage_sessions();
        } catch (Exception $e) {
            log_message('error', 'Session automation error: ' . $e->getMessage());
        }
    }

    /**
     * Automatically manage sessions and terms
     * Creates current session and terms if they don't exist
     */
    private function auto_manage_sessions()
    {
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            return;
        }

        // Throttle auto-run to once per hour
        $last_auto_run = $this->session->userdata('last_session_auto_run');
        if ($last_auto_run && (time() - $last_auto_run) < 3600) {
            return;
        }

        $this->session->set_userdata('last_session_auto_run', time());

        if (!$this->db->table_exists('schoolyear')) {
            log_message('error', 'Schoolyear table does not exist');
            return;
        }

        // Determine current academic year (September to August)
        $current_year = date('Y');
        $current_month = date('n');

        if ($current_month >= 9) {
            $academic_year_start = $current_year;
            $academic_year_end = $current_year + 1;
        } else {
            $academic_year_start = $current_year - 1;
            $academic_year_end = $current_year;
        }

        $session_name = $academic_year_start . '/' . $academic_year_end;

        // Check if current session exists
        $existing_session_query = $this->db->where('school_year', $session_name)->get('schoolyear');

        if (!$existing_session_query || $existing_session_query->num_rows() == 0) {
            // Try with hyphen format
            $session_name_hyphen = $academic_year_start . '-' . $academic_year_end;
            $existing_session_query = $this->db->where('school_year', $session_name_hyphen)->get('schoolyear');

            if ($existing_session_query && $existing_session_query->num_rows() > 0) {
                // Update format to slash
                $existing_session = $existing_session_query->row();
                $this->db->where('id', $existing_session->id);
                $this->db->update('schoolyear', array('school_year' => $session_name));
            } else {
                // Create new session
                $this->create_nigerian_session($academic_year_start, $academic_year_end);
            }
        } else {
            $existing_session = $existing_session_query->row();
            $this->create_terms_for_all_branches($existing_session->id, $academic_year_start, $academic_year_end);
        }

        // Pre-create next session between May and August
        if ($current_month >= 5 && $current_month <= 8) {
            $next_session_name = ($academic_year_start + 1) . '/' . ($academic_year_end + 1);
            $next_session_query = $this->db->where('school_year', $next_session_name)->get('schoolyear');

            if (!$next_session_query || $next_session_query->num_rows() == 0) {
                $this->create_nigerian_session($academic_year_start + 1, $academic_year_end + 1);
            }
        }

        $this->auto_set_current_session();
        $this->auto_set_current_term();
    }

    /**
     * Create a Nigerian academic session with 3 terms
     */
    private function create_nigerian_session($start_year, $end_year)
    {
        $session_name = $start_year . '/' . $end_year;

        // Check if session already exists
        $existing_session_query = $this->db->where('school_year', $session_name)->get('schoolyear');

        if ($existing_session_query && $existing_session_query->num_rows() > 0) {
            $existing_session = $existing_session_query->row();

            // Update session dates if missing
            if (empty($existing_session->start_date) || empty($existing_session->end_date)) {
                $update_data = array(
                    'start_date' => $start_year . '-09-01',
                    'end_date' => $end_year . '-08-31',
                    'status' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $this->db->where('id', $existing_session->id);
                $this->db->update('schoolyear', $update_data);
            }

            $this->create_terms_for_all_branches($existing_session->id, $start_year, $end_year);
            return $existing_session->id;
        }

        // Create new session
        $session_data = array(
            'school_year' => $session_name,
            'start_date' => $start_year . '-09-01',
            'end_date' => $end_year . '-08-31',
            'created_by' => get_loggedin_user_id(),
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('schoolyear', $session_data);
        $session_id = $this->db->insert_id();

        $this->create_terms_for_all_branches($session_id, $start_year, $end_year);

        return $session_id;
    }

    /**
     * Create terms for all active branches
     */
    private function create_terms_for_all_branches($session_id, $start_year, $end_year)
    {
        if (!$this->db->table_exists('academic_terms')) {
            return;
        }

        $branches = array();

        if ($this->db->table_exists('branch')) {
            $branch_query = $this->db
                ->select('id')
                ->where('status', 1)
                ->get('branch');

            if ($branch_query && $branch_query->num_rows() > 0) {
                $branches = $branch_query->result_array();
            }
        }

        if (empty($branches)) {
            $branches = array(array('id' => 1));
        }

        foreach ($branches as $branch) {
            $this->create_nigerian_terms($session_id, $branch['id'], $start_year, $end_year);
        }
    }

    /**
     * Create 3 terms for Nigerian academic calendar
     */
    private function create_nigerian_terms($session_id, $branch_id, $start_year, $end_year)
    {
        if (!$this->db->table_exists('academic_terms')) {
            return;
        }

        $terms = array(
            array(
                'term_name' => 'First Term',
                'term_order' => 1,
                'start_date' => $start_year . '-09-01',
                'end_date' => $start_year . '-12-15',
                'total_weeks' => 15
            ),
            array(
                'term_name' => 'Second Term',
                'term_order' => 2,
                'start_date' => $end_year . '-01-15',
                'end_date' => $end_year . '-04-15',
                'total_weeks' => 13
            ),
            array(
                'term_name' => 'Third Term',
                'term_order' => 3,
                'start_date' => $end_year . '-05-01',
                'end_date' => $end_year . '-08-15',
                'total_weeks' => 15
            )
        );

        foreach ($terms as $term) {
            // Check if term already exists
            $existing_term_query = $this->db
                ->where('session_id', $session_id)
                ->where('branch_id', $branch_id)
                ->where('term_order', $term['term_order'])
                ->get('academic_terms');

            if (!$existing_term_query || $existing_term_query->num_rows() == 0) {
                $term_data = array(
                    'session_id' => $session_id,
                    'branch_id' => $branch_id,
                    'term_name' => $term['term_name'],
                    'term_order' => $term['term_order'],
                    'start_date' => $term['start_date'],
                    'end_date' => $term['end_date'],
                    'is_active' => 0,
                    'total_weeks' => $term['total_weeks'],
                    'holidays' => NULL,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => NULL
                );

                $this->db->insert('academic_terms', $term_data);
            }
        }
    }

    /**
     * Automatically set the current session based on date
     */
    private function auto_set_current_session()
    {
        $current_month = date('n');
        $current_year = date('Y');

        if ($current_month >= 9) {
            $session_name = $current_year . '/' . ($current_year + 1);
        } else {
            $session_name = ($current_year - 1) . '/' . $current_year;
        }

        $active_session_query = $this->db->where('school_year', $session_name)->get('schoolyear');

        if ($active_session_query && $active_session_query->num_rows() > 0) {
            $active_session = $active_session_query->row();

            // Update global settings
            if ($this->db->table_exists('global_settings')) {
                $global_settings_query = $this->db->get('global_settings');
                if ($global_settings_query && $global_settings_query->num_rows() > 0) {
                    $global_settings = $global_settings_query->row();
                    if (property_exists($global_settings, 'session_id') && $global_settings->session_id != $active_session->id) {
                        $this->db->update('global_settings', array('session_id' => $active_session->id));
                    }
                }
            }

            $this->session->set_userdata('set_session_id', $active_session->id);
        }
    }

    /**
     * Automatically set the current term based on date
     */
    private function auto_set_current_term()
    {
        if (!$this->db->table_exists('academic_terms')) {
            return;
        }

        // Don't override manually set term
        $manually_set_term_id = $this->session->userdata('manually_set_term_id');
        if (!empty($manually_set_term_id)) {
            return;
        }

        $session_id = get_session_id();
        if (empty($session_id)) {
            return;
        }

        $branch_id = $this->application_model->get_branch_id();
        if (empty($branch_id)) {
            return;
        }

        $current_date = date('Y-m-d');

        // Find term by date range
        $this->db->where('session_id', $session_id);
        $this->db->where('branch_id', $branch_id);
        $this->db->where('start_date <=', $current_date);
        $this->db->where('end_date >=', $current_date);
        $term_by_date = $this->db->get('academic_terms')->row();

        if ($term_by_date) {
            if ($term_by_date->is_active != 1) {
                // Deactivate all other terms
                $this->db->where('session_id', $session_id);
                $this->db->where('branch_id', $branch_id);
                $this->db->update('academic_terms', ['is_active' => 0]);

                // Activate current term
                $this->db->where('id', $term_by_date->id);
                $this->db->update('academic_terms', [
                    'is_active' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            // No term matches current date, check for active term
            $active_term = $this->db
                ->where('session_id', $session_id)
                ->where('branch_id', $branch_id)
                ->where('is_active', 1)
                ->get('academic_terms')
                ->row();

            if (!$active_term) {
                // No active term, activate first term
                $first_term = $this->db
                    ->where('session_id', $session_id)
                    ->where('branch_id', $branch_id)
                    ->order_by('term_order', 'ASC')
                    ->limit(1)
                    ->get('academic_terms')
                    ->row();

                if ($first_term) {
                    $this->db->where('id', $first_term->id);
                    $this->db->update('academic_terms', [
                        'is_active' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    /**
     * Get current active term
     */
    public function get_current_term($session_id = null, $branch_id = null)
    {
        if (!$session_id) {
            $session_id = get_session_id();
        }
        if (!$branch_id) {
            $branch_id = $this->application_model->get_branch_id();
        }

        $current_date = date('Y-m-d');

        if ($this->db->table_exists('academic_terms') && $session_id) {
            // Try to find term by date
            $this->db->where('session_id', $session_id);
            $this->db->where('branch_id', $branch_id);
            $this->db->where('start_date <=', $current_date);
            $this->db->where('end_date >=', $current_date);
            $term = $this->db->get('academic_terms')->row();

            if (!$term) {
                // Fallback to active term
                $this->db->where('session_id', $session_id);
                $this->db->where('branch_id', $branch_id);
                $this->db->where('is_active', 1);
                $term = $this->db->get('academic_terms')->row();
            }

            if (!$term) {
                // Fallback to first term
                $this->db->where('session_id', $session_id);
                $this->db->where('branch_id', $branch_id);
                $this->db->order_by('term_order', 'ASC');
                $term = $this->db->get('academic_terms')->row();
            }

            return $term;
        }

        return null;
    }

    /**
     * Get all terms for a session
     */
    public function get_session_terms($session_id = null, $branch_id = null)
    {
        if (!$session_id) {
            $session_id = get_session_id();
        }
        if (!$branch_id) {
            $branch_id = $this->application_model->get_branch_id();
        }

        if ($this->db->table_exists('academic_terms') && $session_id) {
            $this->db->select('*');
            $this->db->from('academic_terms');
            $this->db->where('session_id', $session_id);
            $this->db->where('branch_id', $branch_id);
            $this->db->order_by('term_order', 'ASC');
            $query = $this->db->get();

            if ($query && $query->num_rows() > 0) {
                return $query->result();
            }
        }

        return array();
    }

    /* form validation rules */
    protected function rules()
    {
        $rules = array(
            array(
                'field' => 'session',
                'label' => 'Session',
                'rules' => 'trim|required|callback_unique_name',
            ),
        );
        return $rules;
    }

    public function index()
    {
        if (is_superadmin_loggedin()) {
            if (isset($_POST['save'])) {
                $this->form_validation->set_rules($this->rules());
                if ($this->form_validation->run() == true) {
                    $this->save($this->input->post());
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    redirect(base_url('sessions'));
                }
            }

            $branchID = $this->application_model->get_branch_id();
            if (empty($branchID) && is_superadmin_loggedin()) {
                $first_branch = $this->db->select('id')
                    ->where('status', 1)
                    ->order_by('id', 'ASC')
                    ->limit(1)
                    ->get('branch')
                    ->row();

                if ($first_branch) {
                    $branchID = $first_branch->id;
                    $this->session->set_userdata('loggedin_branch', $branchID);
                } else {
                    $branchID = 1;
                }
            }

            $this->data['current_term'] = $this->get_current_term(get_session_id(), $branchID);
            $this->data['all_terms'] = $this->get_session_terms(get_session_id(), $branchID);
            $this->data['branch_id'] = $branchID;

            $this->data['title'] = translate('session_settings');
            $this->data['sub_page'] = 'sessions/index';
            $this->data['main_menu'] = 'settings';
            $this->load->view('layout/index', $this->data);
        } else {
            $this->session->set_userdata('last_page', current_url());
            redirect(base_url(), 'refresh');
        }
    }

    public function set_academic($action = '')
    {
        if (is_loggedin()) {
            $id = urldecode($this->security->xss_clean($action));
            $row = $this->app_lib->get_table('schoolyear', $id, true);
            if (!empty($row)) {
                $this->session->set_userdata('set_session_id', $row['id']);
                $this->session->unset_userdata('manually_set_term_id');

                if (is_student_loggedin() || is_parent_loggedin()) {
                    $student_id = $this->session->userdata('student_id');
                    $enroll = $this->sessions_model->searchStudentActiveSession($student_id, $row['id']);
                    $this->session->set_userdata('enrollID', $enroll->id);
                }
                if (!empty($_SERVER['HTTP_REFERER'])) {
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    redirect(base_url('dashboard'), 'refresh');
                }
            } else {
                redirect(base_url(), 'refresh');
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    /**
     * Set active branch for super admin
     */
    public function set_branch($branch_id = '')
    {
        if (!is_superadmin_loggedin()) {
            redirect(base_url(), 'refresh');
            return;
        }

        $branch_id = urldecode($this->security->xss_clean($branch_id));

        if (!empty($branch_id)) {
            // Verify branch exists and is active
            $branch = $this->db->get_where('branch', [
                'id' => $branch_id,
                'status' => 1
            ])->row();

            if (!empty($branch)) {
                // Set the branch in session
                $this->session->set_userdata('loggedin_branch', $branch_id);

                // Clear manually set term so it auto-switches to active term of new branch
                $this->session->unset_userdata('manually_set_term_id');

                set_alert('success', 'Branch switched to ' . $branch->name);
            } else {
                set_alert('error', 'Invalid branch selected');
            }
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(base_url('dashboard'), 'refresh');
        }
    }

    /**
     * Set active term manually
     */
    public function set_term($term_id = '')
    {
        if (!is_loggedin()) {
            redirect(base_url(), 'refresh');
            return;
        }

        $term_id = urldecode($this->security->xss_clean($term_id));

        if (empty($term_id)) {
            $this->session->unset_userdata('manually_set_term_id');
            set_alert('success', 'Automatic term switching enabled');
        } else {
            $session_id = get_session_id();
            $branch_id = get_loggedin_branch_id();

            $term = $this->db->get_where('academic_terms', [
                'id' => $term_id,
                'session_id' => $session_id,
                'branch_id' => $branch_id
            ])->row();

            if (!empty($term)) {
                // For super admin and admin, permanently activate the term in database
                if (is_superadmin_loggedin() || is_admin_loggedin()) {
                    $this->set_active_term($term_id);
                }

                $this->session->set_userdata('manually_set_term_id', $term_id);
                set_alert('success', 'Term switched to ' . $term->term_name);
            } else {
                set_alert('error', 'Invalid term selected (Session: ' . $session_id . ', Branch: ' . $branch_id . ', Term: ' . $term_id . ')');
            }
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(base_url('dashboard'), 'refresh');
        }
    }

    /**
     * Activate a specific term
     */
    public function activate_term()
    {
        if (!$_POST) {
            exit('Invalid request method');
        }

        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('access_denied')
            ]);
            exit;
        }

        $term_id = $this->input->post('term_id');
        $branch_id = $this->input->post('branch_id');

        if (empty($term_id) || !is_numeric($term_id)) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('invalid_term_id')
            ]);
            exit;
        }

        if (!empty($branch_id)) {
            $term = $this->db->get_where('academic_terms', [
                'id' => $term_id,
                'branch_id' => $branch_id
            ])->row();

            if (!$term) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Term does not belong to selected branch'
                ]);
                exit;
            }
        }

        if ($this->set_active_term($term_id)) {
            echo json_encode([
                'status' => 'success',
                'message' => translate('term_activated_successfully')
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => translate('failed_to_activate_term')
            ]);
        }
        exit;
    }

    /**
     * Set a term as active
     */
    public function set_active_term($term_id)
    {
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            return false;
        }

        if (!$this->db->table_exists('academic_terms')) {
            log_message('error', 'Academic terms table does not exist');
            return false;
        }

        $term = $this->db->where('id', $term_id)->get('academic_terms')->row();

        if (!$term) {
            log_message('error', 'Term not found with ID: ' . $term_id);
            return false;
        }

        $this->db->trans_start();

        // Deactivate all terms for this session/branch
        $this->db->where('session_id', $term->session_id);
        $this->db->where('branch_id', $term->branch_id);
        $this->db->update('academic_terms', ['is_active' => 0]);

        // Activate selected term
        $this->db->where('id', $term_id);
        $this->db->update('academic_terms', [
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Failed to activate term ID: ' . $term_id);
            return false;
        }

        return true;
    }

    /**
     * Edit term details (AJAX)
     */
    public function edit_term()
    {
        if (!$_POST) {
            redirect(base_url('sessions'));
            return;
        }

        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            ajax_access_denied();
            return;
        }

        $this->form_validation->set_rules('term_id', translate('term_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('start_date', translate('start_date'), 'trim|required');
        $this->form_validation->set_rules('end_date', translate('end_date'), 'trim|required');
        $this->form_validation->set_rules('total_weeks', translate('total_weeks'), 'trim|numeric');

        if ($this->form_validation->run() == false) {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $error);
            echo json_encode($array);
            return;
        }

        $term_id = $this->input->post('term_id');
        $branch_id = $this->input->post('branch_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $total_weeks = $this->input->post('total_weeks');

        $term = $this->db->get_where('academic_terms', [
            'id' => $term_id,
            'branch_id' => $branch_id
        ])->row();

        if (!$term) {
            $array = array(
                'status' => 'fail',
                'error' => array('term' => translate('term_not_found'))
            );
            echo json_encode($array);
            return;
        }

        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);

        if ($end_timestamp <= $start_timestamp) {
            $array = array(
                'status' => 'fail',
                'error' => array('end_date' => translate('end_date_must_be_after_start_date'))
            );
            echo json_encode($array);
            return;
        }

        if (empty($total_weeks)) {
            $days_diff = ($end_timestamp - $start_timestamp) / (60 * 60 * 24);
            $total_weeks = ceil($days_diff / 7);
        }

        $update_data = array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_weeks' => $total_weeks,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->db->where('id', $term_id);
        $update_result = $this->db->update('academic_terms', $update_data);

        if ($update_result) {
            set_alert('success', translate('term_updated_successfully'));
            $array = array('status' => 'success');
        } else {
            $array = array(
                'status' => 'fail',
                'error' => array('database' => translate('failed_to_update_term'))
            );
        }

        echo json_encode($array);
    }

    /* academic sessions information are prepared and stored in the database here */
    public function edit()
    {
        if ($_POST) {
            if (!is_superadmin_loggedin()) {
               ajax_access_denied();
            }
            $this->form_validation->set_rules($this->rules());
            if ($this->form_validation->run() == true) {
                $this->save($this->input->post());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    /**
     * Quick adjust term dates (AJAX)
     * Allows adjusting start/end dates for a single term with validation
     */
    public function quick_adjust_term()
    {
        if (!$_POST) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('invalid_request')
            ]);
            exit;
        }

        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('access_denied')
            ]);
            exit;
        }

        $term_id = $this->input->post('term_id');
        $field = $this->input->post('field'); // 'start_date' or 'end_date'
        $value = $this->input->post('value');

        if (empty($term_id) || empty($field) || empty($value)) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('missing_required_fields')
            ]);
            exit;
        }

        // Validate field
        if (!in_array($field, ['start_date', 'end_date'])) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('invalid_field')
            ]);
            exit;
        }

        // Get current term
        $term = $this->db->get_where('academic_terms', ['id' => $term_id])->row();

        if (!$term) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('term_not_found')
            ]);
            exit;
        }

        // Validate dates
        $start_date = $field == 'start_date' ? $value : $term->start_date;
        $end_date = $field == 'end_date' ? $value : $term->end_date;

        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);

        if ($end_timestamp <= $start_timestamp) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('end_date_must_be_after_start_date')
            ]);
            exit;
        }

        // Calculate total weeks
        $days_diff = ($end_timestamp - $start_timestamp) / (60 * 60 * 24);
        $total_weeks = ceil($days_diff / 7);

        // Update term
        $update_data = [
            $field => $value,
            'total_weeks' => $total_weeks,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $term_id);
        $this->db->update('academic_terms', $update_data);

        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => translate('term_updated_successfully'),
                'total_weeks' => $total_weeks
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => translate('no_changes_made')
            ]);
        }
        exit;
    }

    /**
     * Bulk adjust all terms for a session
     * Proportionally adjusts all term dates when session dates change
     */
    public function bulk_adjust_terms()
    {
        if (!$_POST) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('invalid_request')
            ]);
            exit;
        }

        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('access_denied')
            ]);
            exit;
        }

        $session_id = $this->input->post('session_id');
        $branch_id = $this->input->post('branch_id');
        $new_start_year = $this->input->post('start_year');
        $new_end_year = $this->input->post('end_year');

        if (empty($session_id) || empty($branch_id) || empty($new_start_year) || empty($new_end_year)) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('missing_required_fields')
            ]);
            exit;
        }

        // Recalculate term dates
        $terms_data = [
            [
                'term_order' => 1,
                'start_date' => $new_start_year . '-09-01',
                'end_date' => $new_start_year . '-12-15',
                'total_weeks' => 15
            ],
            [
                'term_order' => 2,
                'start_date' => $new_end_year . '-01-15',
                'end_date' => $new_end_year . '-04-15',
                'total_weeks' => 13
            ],
            [
                'term_order' => 3,
                'start_date' => $new_end_year . '-05-01',
                'end_date' => $new_end_year . '-08-15',
                'total_weeks' => 15
            ]
        ];

        $updated_count = 0;
        foreach ($terms_data as $term_data) {
            $this->db->where([
                'session_id' => $session_id,
                'branch_id' => $branch_id,
                'term_order' => $term_data['term_order']
            ]);
            $this->db->update('academic_terms', [
                'start_date' => $term_data['start_date'],
                'end_date' => $term_data['end_date'],
                'total_weeks' => $term_data['total_weeks'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($this->db->affected_rows() > 0) {
                $updated_count++;
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => translate('terms_adjusted_successfully'),
            'updated_count' => $updated_count
        ]);
        exit;
    }

    /**
     * Get session terms via AJAX
     */
    public function get_session_terms_ajax()
    {
        if (!$_POST) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('invalid_request'),
                'terms' => []
            ]);
            exit;
        }

        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('access_denied'),
                'terms' => []
            ]);
            exit;
        }

        $session_id = $this->input->post('session_id');
        $branch_id = $this->input->post('branch_id');

        if (empty($session_id)) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('session_id_required'),
                'terms' => []
            ]);
            exit;
        }

        if (empty($branch_id)) {
            echo json_encode([
                'status' => 'error',
                'message' => translate('branch_id_required'),
                'terms' => []
            ]);
            exit;
        }

        $terms = $this->get_session_terms($session_id, $branch_id);

        if (!empty($terms)) {
            $formatted_terms = [];
            foreach ($terms as $term) {
                $formatted_terms[] = [
                    'id' => $term->id,
                    'term_name' => $term->term_name,
                    'term_order' => $term->term_order,
                    'start_date' => date('M d, Y', strtotime($term->start_date)),
                    'end_date' => date('M d, Y', strtotime($term->end_date)),
                    'total_weeks' => $term->total_weeks,
                    'is_active' => $term->is_active
                ];
            }

            echo json_encode([
                'status' => 'success',
                'message' => translate('terms_loaded_successfully'),
                'terms' => $formatted_terms
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => translate('no_terms_found'),
                'terms' => []
            ]);
        }
        exit;
    }

    public function delete($id = '')
    {
        if (is_superadmin_loggedin())
        {
            $this->db->where('id', $id);
            $this->db->delete('schoolyear');

            // Also delete terms for this session
            $this->db->where('session_id', $id);
            $this->db->delete('academic_terms');
        }
    }

    /* unique academic sessions name verification is done here */
    public function unique_name($year)
    {
        $schoolyearID = $this->input->post('schoolyear_id');
        if (!empty($schoolyearID)) {
            $this->db->where_not_in('id', $schoolyearID);
        }
        $this->db->where(array('school_year' => $year));
        $uniform_row = $this->db->get('schoolyear')->num_rows();
        if ($uniform_row == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_name", translate('already_taken'));
            return false;
        }
    }

    protected function save($data)
    {
        $arrayYear = array(
            'school_year' => $data['session'],
            'created_by' => get_loggedin_user_id(),
            'updated_at' => date('Y-m-d H:i:s')
        );

        if (!isset($data['schoolyear_id'])) {
            $arrayYear['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('schoolyear', $arrayYear);
            $session_id = $this->db->insert_id();

            if ($session_id) {
                $years = explode('/', $data['session']);
                if (count($years) == 2) {
                    $this->create_terms_for_all_branches($session_id, $years[0], $years[1]);
                }
            }
        } else {
            $this->db->where('id', $data['schoolyear_id']);
            $this->db->update('schoolyear', $arrayYear);
        }
    }
}
