<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Academium
 * @version : 10.6
 * @developed by : codeindevelopers
 * @support : info@codeindevelopers.com.ng
 * @author url : http://codeindevelopers.com.ng
 * @filename : Skills.php
 * @copyright : Reserved codeindevelopers Team
 */

class Skills extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('skills_model');
        $this->load->model('exam_model');
    }

    /**
     * Categories list and management
     */
    public function categories()
    {
        if (!get_permission('skills_categories', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('skills_categories', 'is_add')) {
                ajax_access_denied();
            }
            $this->category_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                // Super admin can specify branch_id, others use their own branch
                $branch_id = is_superadmin_loggedin() && !empty($post['branch_id'])
                    ? $post['branch_id']
                    : $this->application_model->get_branch_id();

                $data = [
                    'name' => $post['name'],
                    'type' => $post['type'],
                    'class_level' => $post['class_level'],
                    'description' => $post['description'],
                    'status' => $post['status'],
                    'branch_id' => $branch_id
                ];

                $this->skills_model->saveCategory($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('skills/categories');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        // Check if super admin is filtering by branch via GET parameter
        $filter_branch_id = $this->input->get('branch_id');
        if (is_superadmin_loggedin() && !empty($filter_branch_id)) {
            $this->data['categories'] = $this->skills_model->getCategories($filter_branch_id);
        } else {
            $this->data['categories'] = $this->skills_model->getCategories($this->data['branch_id']);
        }
        $this->data['title'] = translate('skills_categories');
        $this->data['sub_page'] = 'skills/categories';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/categories';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Edit category
     */
    public function edit_category($id = '')
    {
        if (!get_permission('skills_categories', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $this->category_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $data = [
                    'name' => $post['name'],
                    'type' => $post['type'],
                    'class_level' => $post['class_level'],
                    'description' => $post['description'],
                    'status' => $post['status']
                ];

                $this->skills_model->saveCategory($data, $id);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('skills/categories');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['category'] = $this->skills_model->getCategoryById($id);
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('edit_skills_category');
        $this->data['sub_page'] = 'skills/edit_category';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/categories';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Delete category
     */
    public function delete_category($id = '')
    {
        if (!get_permission('skills_categories', 'is_delete')) {
            ajax_access_denied();
        }

        $this->skills_model->deleteCategory($id);
        echo json_encode(array('status' => 'success'));
    }

    /**
     * Category validation rules
     */
    protected function category_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('name', translate('name'), 'trim|required');
        $this->form_validation->set_rules('type', translate('type'), 'trim|required');
        $this->form_validation->set_rules('class_level', translate('class_level'), 'trim|required');
        $this->form_validation->set_rules('status', translate('status'), 'trim|required');
    }

    // =====================================================
    // SKILLS ITEMS MANAGEMENT
    // =====================================================

    /**
     * Items list and management
     */
    public function items()
    {
        if (!get_permission('skills_items', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('skills_items', 'is_add')) {
                ajax_access_denied();
            }
            $this->item_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();

                $data = [
                    'category_id' => $post['category_id'],
                    'item_name' => $post['item_name'],
                    'description' => $post['description'],
                    'display_order' => $post['display_order'],
                    'status' => $post['status']
                ];

                $this->skills_model->saveItem($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('skills/items');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        // Check if super admin is filtering by branch via GET parameter
        $filter_branch_id = $this->input->get('branch_id');
        if (is_superadmin_loggedin() && !empty($filter_branch_id)) {
            $this->data['items'] = $this->skills_model->getItems($filter_branch_id);
            $this->data['categories'] = $this->skills_model->getCategories($filter_branch_id, 'active');
            $this->data['next_display_order'] = $this->skills_model->getMaxDisplayOrder($filter_branch_id) + 1;
        } else {
            $this->data['items'] = $this->skills_model->getItems($this->data['branch_id']);
            $this->data['categories'] = $this->skills_model->getCategories($this->data['branch_id'], 'active');
            $this->data['next_display_order'] = $this->skills_model->getMaxDisplayOrder($this->data['branch_id']) + 1;
        }
        $this->data['title'] = translate('skills_items');
        $this->data['sub_page'] = 'skills/items';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/items';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Edit item
     */
    public function edit_item($id = '')
    {
        if (!get_permission('skills_items', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $this->item_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $data = [
                    'category_id' => $post['category_id'],
                    'item_name' => $post['item_name'],
                    'description' => $post['description'],
                    'display_order' => $post['display_order'],
                    'status' => $post['status']
                ];

                $this->skills_model->saveItem($data, $id);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('skills/items');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['item'] = $this->skills_model->getItemById($id);
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['categories'] = $this->skills_model->getCategories($this->data['branch_id'], 'active');
        $this->data['title'] = translate('edit_skills_item');
        $this->data['sub_page'] = 'skills/edit_item';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/items';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Delete item
     */
    public function delete_item($id = '')
    {
        if (!get_permission('skills_items', 'is_delete')) {
            ajax_access_denied();
        }

        $this->skills_model->deleteItem($id);
        echo json_encode(array('status' => 'success'));
    }

    /**
     * Item validation rules
     */
    protected function item_validation()
    {
        $this->form_validation->set_rules('category_id', translate('category'), 'trim|required');
        $this->form_validation->set_rules('item_name', translate('item_name'), 'trim|required');
        $this->form_validation->set_rules('display_order', translate('display_order'), 'trim|numeric');
        $this->form_validation->set_rules('status', translate('status'), 'trim|required');
    }

    // =====================================================
    // SKILLS RATINGS MANAGEMENT
    // =====================================================

    /**
     * Ratings list and management
     */
    public function ratings()
    {
        if (!get_permission('skills_ratings', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('skills_ratings', 'is_add')) {
                ajax_access_denied();
            }
            $this->rating_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                // Super admin can specify branch_id, others use their own branch
                $branch_id = is_superadmin_loggedin() && !empty($post['branch_id'])
                    ? $post['branch_id']
                    : $this->application_model->get_branch_id();

                $data = [
                    'label' => $post['label'],
                    'numeric_value' => $post['numeric_value'],
                    'description' => $post['description'],
                    'display_order' => $post['display_order'],
                    'status' => $post['status'],
                    'branch_id' => $branch_id
                ];

                $this->skills_model->saveRating($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('skills/ratings');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        // Check if super admin is filtering by branch via GET parameter
        $filter_branch_id = $this->input->get('branch_id');
        if (is_superadmin_loggedin() && !empty($filter_branch_id)) {
            $this->data['ratings'] = $this->skills_model->getRatings($filter_branch_id);
        } else {
            $this->data['ratings'] = $this->skills_model->getRatings($this->data['branch_id']);
        }
        $this->data['title'] = translate('skills_ratings');
        $this->data['sub_page'] = 'skills/ratings';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/ratings';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Edit rating
     */
    public function edit_rating($id = '')
    {
        if (!get_permission('skills_ratings', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $this->rating_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $data = [
                    'label' => $post['label'],
                    'numeric_value' => $post['numeric_value'],
                    'description' => $post['description'],
                    'display_order' => $post['display_order'],
                    'status' => $post['status']
                ];

                $this->skills_model->saveRating($data, $id);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('skills/ratings');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['rating'] = $this->skills_model->getRatingById($id);
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('edit_skills_rating');
        $this->data['sub_page'] = 'skills/edit_rating';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/ratings';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Delete rating
     */
    public function delete_rating($id = '')
    {
        if (!get_permission('skills_ratings', 'is_delete')) {
            ajax_access_denied();
        }

        $this->skills_model->deleteRating($id);
        echo json_encode(array('status' => 'success'));
    }

    /**
     * Rating validation rules
     */
    protected function rating_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('label', translate('label'), 'trim|required');
        $this->form_validation->set_rules('numeric_value', translate('numeric_value'), 'trim|required|numeric');
        $this->form_validation->set_rules('display_order', translate('display_order'), 'trim|numeric');
        $this->form_validation->set_rules('status', translate('status'), 'trim|required');
    }

    // =====================================================
    // STUDENT SKILLS RATING ENTRY
    // =====================================================

    /**
     * Skills rating entry interface
     */
    public function rating_entry()
    {
        if (!get_permission('skills_rating_entry', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('skills_rating_entry');
        $this->data['sub_page'] = 'skills/rating_entry';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/rating_entry';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Get students for rating (AJAX)
     */
    public function getStudents()
    {
        if (!get_permission('skills_rating_entry', 'is_view')) {
            ajax_access_denied();
        }

        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $exam_id = $this->input->post('exam_id');
        $branch_id = $this->input->post('branch_id');

        // Get session_id from the exam
        $exam = $this->db->get_where('exam', array('id' => $exam_id))->row();
        $session_id = $exam ? $exam->session_id : get_session_id();

        // Debug: Check what we're querying
        $debug_info = array(
            'class_id' => $class_id,
            'section_id' => $section_id,
            'exam_id' => $exam_id,
            'branch_id' => $branch_id,
            'session_id' => $session_id,
            'exam_found' => $exam ? true : false
        );

        // Get students enrolled in the exam's session
        $this->db->select('e.*, s.photo, CONCAT_WS(" ", s.first_name, s.last_name) as fullname, s.register_no');
        $this->db->from('enroll e');
        $this->db->join('student s', 'e.student_id = s.id', 'inner');
        $this->db->where('e.class_id', $class_id);
        $this->db->where('e.section_id', $section_id);
        $this->db->where('e.session_id', $session_id);
        $this->db->where('e.branch_id', $branch_id);
        $this->db->order_by('e.roll', 'ASC');

        $students = $this->db->get()->result_array();

        // Temporary debug output
        if (empty($students)) {
            // Check if there are ANY students in this class/section regardless of session
            $this->db->select('e.session_id, COUNT(*) as count');
            $this->db->from('enroll e');
            $this->db->where('e.class_id', $class_id);
            $this->db->where('e.section_id', $section_id);
            $this->db->where('e.branch_id', $branch_id);
            $this->db->group_by('e.session_id');
            $all_sessions = $this->db->get()->result_array();

            $debug_info['students_by_session'] = $all_sessions;
            $debug_info['message'] = 'No students found for the selected session. Check students_by_session to see which sessions have students.';
        }

        echo json_encode(array(
            'students' => $students,
            'debug' => $debug_info
        ));
    }

    /**
     * Save student ratings
     */
    public function save_ratings()
    {
        if (!get_permission('skills_rating_entry', 'is_add')) {
            ajax_access_denied();
        }

        $post = $this->input->post();
        $teacher_id = get_loggedin_user_id();

        // Get session_id, term_id, and branch_id from exam table
        $exam = $this->db->get_where('exam', array('id' => $post['exam_id']))->row();
        if (!$exam) {
            echo json_encode(array('status' => 'fail', 'error' => 'Exam not found'));
            return;
        }

        $session_id = $exam->session_id;
        $term_id = $exam->term_id;
        $branch_id = $exam->branch_id;

        // Parse ratings data
        $ratings = [];
        if (!isset($post['ratings']) || !is_array($post['ratings'])) {
            echo json_encode(array('status' => 'fail', 'error' => 'No ratings data received', 'debug' => $post));
            return;
        }

        foreach ($post['ratings'] as $student_id => $student_ratings) {
            $teacher_remark = isset($post['teacher_remarks'][$student_id]) && trim($post['teacher_remarks'][$student_id]) !== ''
                ? trim($post['teacher_remarks'][$student_id])
                : null;
            $head_teacher_remark = isset($post['head_teacher_remarks'][$student_id]) && trim($post['head_teacher_remarks'][$student_id]) !== ''
                ? trim($post['head_teacher_remarks'][$student_id])
                : null;

            foreach ($student_ratings as $skill_item_id => $rating_id) {
                if (!empty($rating_id)) {
                    $ratings[] = [
                        'student_id' => $student_id,
                        'enroll_id' => $post['enroll_ids'][$student_id],
                        'skill_item_id' => $skill_item_id,
                        'rating_id' => $rating_id,
                        'exam_id' => $post['exam_id'],
                        'term_id' => $term_id,
                        'class_id' => $post['class_id'],
                        'section_id' => $post['section_id'],
                        'session_id' => $session_id,
                        'branch_id' => $branch_id,
                        'teacher_id' => $teacher_id,
                        'teacher_remarks' => $teacher_remark,
                        'head_teacher_remarks' => $head_teacher_remark
                    ];
                }
            }
        }

        if (empty($ratings)) {
            echo json_encode(array('status' => 'fail', 'error' => 'No ratings selected. Please rate at least one skill.'));
            return;
        }

        if ($this->skills_model->bulkSaveStudentRatings($ratings)) {
            set_alert('success', translate('skills_ratings_saved_successfully'));
            $array = array('status' => 'success', 'message' => count($ratings) . ' ratings saved successfully');
        } else {
            $array = array('status' => 'fail', 'error' => translate('an_error_occurred'));
        }

        echo json_encode($array);
    }

    /**
     * Get existing ratings for a student (AJAX)
     */
    public function getExistingRatings()
    {
        if (!get_permission('skills_rating_entry', 'is_view')) {
            ajax_access_denied();
        }

        $student_id = $this->input->post('student_id');
        $exam_id = $this->input->post('exam_id');
        $session_id = $this->application_model->get_session_id();

        $ratings = $this->skills_model->getStudentRatings([
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'session_id' => $session_id
        ]);

        echo json_encode($ratings);
    }

    /**
     * Get skills by class level (AJAX)
     */
    public function getSkillsByLevel()
    {
        if (!get_permission('skills_rating_entry', 'is_view')) {
            ajax_access_denied();
        }

        $class_level = $this->input->post('class_level');
        $branch_id = $this->input->post('branch_id');

        // Get all items for this branch filtered by class level
        $this->db->select('si.*, sc.name as category_name, sc.type as category_type, sc.class_level, sc.id as category_id');
        $this->db->from('skills_items si');
        $this->db->join('skills_categories sc', 'si.category_id = sc.id', 'inner');
        $this->db->where('sc.branch_id', $branch_id);
        $this->db->where('sc.class_level', $class_level);
        $this->db->where('sc.status', 'active');
        $this->db->where('si.status', 'active');
        $this->db->order_by('sc.type', 'ASC');
        $this->db->order_by('si.display_order', 'ASC');
        $this->db->order_by('si.item_name', 'ASC');

        $skills = $this->db->get()->result_array();

        echo json_encode($skills);
    }

    /**
     * Get ratings scale (AJAX)
     */
    public function getRatingsScale()
    {
        if (!get_permission('skills_rating_entry', 'is_view')) {
            ajax_access_denied();
        }

        $branch_id = $this->input->post('branch_id');
        $ratings = $this->skills_model->getRatings($branch_id, 'active');

        echo json_encode($ratings);
    }

    /**
     * Get existing ratings for all students in a class (AJAX)
     */
    public function getExistingRatingsForClass()
    {
        if (!get_permission('skills_rating_entry', 'is_view')) {
            ajax_access_denied();
        }

        $exam_id = $this->input->post('exam_id');
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');

        // Get exam to get session_id
        $exam = $this->db->get_where('exam', array('id' => $exam_id))->row();
        if (!$exam) {
            echo json_encode(array());
            return;
        }

        $session_id = $exam->session_id;

        // Get all ratings for this class/section/exam
        $this->db->select('ssr.student_id, ssr.skill_item_id, ssr.rating_id, ssr.teacher_remarks, ssr.head_teacher_remarks');
        $this->db->from('skills_students_ratings ssr');
        $this->db->where('ssr.exam_id', $exam_id);
        $this->db->where('ssr.class_id', $class_id);
        $this->db->where('ssr.section_id', $section_id);
        $this->db->where('ssr.session_id', $session_id);
        $ratings = $this->db->get()->result_array();

        // Organize by student_id and skill_item_id
        $organized = array();
        $teacher_remarks = array();
        $head_teacher_remarks = array();
        foreach ($ratings as $rating) {
            $student_id = $rating['student_id'];
            $skill_item_id = $rating['skill_item_id'];

            if (!isset($organized[$student_id])) {
                $organized[$student_id] = array();
            }

            $organized[$student_id][$skill_item_id] = $rating['rating_id'];

            // Store remarks (one per student, so we'll use the last one)
            if (!empty($rating['teacher_remarks'])) {
                $teacher_remarks[$student_id] = $rating['teacher_remarks'];
            }
            if (!empty($rating['head_teacher_remarks'])) {
                $head_teacher_remarks[$student_id] = $rating['head_teacher_remarks'];
            }
        }

        // Add remarks to the response
        $organized['teacher_remarks'] = $teacher_remarks;
        $organized['head_teacher_remarks'] = $head_teacher_remarks;

        echo json_encode($organized);
    }

    // =====================================================
    // JUNIOR REPORT CARD (SKILLS-BASED)
    // =====================================================

    /**
     * Junior report card interface
     */
    public function junior_report_card()
    {
        if (!get_permission('skills_junior_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required');
            $this->form_validation->set_rules('exam_id', translate('exam'), 'required');
            $this->form_validation->set_rules('session_id', translate('academic_year'), 'required');

            if ($this->form_validation->run() == true) {
                $sessionID = $this->input->post('session_id');
                $examID = $this->input->post('exam_id');
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');

                // Get students who have skills ratings
                $this->db->select('DISTINCT s.id, s.*, e.roll, e.id as enrollID');
                $this->db->from('skills_students_ratings ssr');
                $this->db->join('student s', 'ssr.student_id = s.id', 'inner');
                $this->db->join('enroll e', 'e.student_id = s.id AND e.session_id = ssr.session_id AND e.class_id = ssr.class_id AND e.section_id = ssr.section_id', 'inner');
                $this->db->where('ssr.exam_id', $examID);
                $this->db->where('ssr.class_id', $classID);
                $this->db->where('ssr.section_id', $sectionID);
                $this->db->where('ssr.session_id', $sessionID);
                $this->db->where('ssr.branch_id', $branchID);
                $this->db->where('s.active', 1);
                $this->db->order_by('e.roll', 'ASC');

                $this->data['examID'] = $examID;
                $this->data['student'] = $this->db->get()->result_array();
            }
        }

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-select/dist/css/bootstrap-select.min.css',
            ),
            'js' => array(
                'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
            ),
        );
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'skills/junior_report_card';
        $this->data['main_menu'] = 'exam';
        $this->data['sub_menu'] = 'skills/junior_report_card';
        $this->data['title'] = translate('junior_report_card');
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Print junior report card
     */
    public function reportCardPrint()
    {
        if ($_POST) {
            if (!get_permission('skills_junior_report', 'is_view')) {
                ajax_access_denied();
            }
            $this->data['exam_id'] = $this->input->post('exam_id');
            $this->data['student_array'] = $this->input->post('student_id');
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['print_date'] = $this->input->post('print_date');
            $this->data['sessionID'] = $this->input->post('session_id');
            $this->data['template_id'] = $this->input->post('template_id');
            $this->data['branchID'] = $this->application_model->get_branch_id();
            echo $this->load->view('skills/junior_report_card_print', $this->data, true);
        }
    }

    /**
     * Generate PDF for junior report card
     */
    public function reportCardPdf()
    {
        if ($_POST) {
            if (!get_permission('skills_junior_report', 'is_view')) {
                ajax_access_denied();
            }

            $this->data['exam_id'] = $this->input->post('exam_id');
            $this->data['student_array'] = $this->input->post('student_id');
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['print_date'] = $this->input->post('print_date');
            $this->data['sessionID'] = $this->input->post('session_id');
            $this->data['template_id'] = $this->input->post('template_id');
            $this->data['branchID'] = $this->application_model->get_branch_id();

            $this->load->model('marksheet_template_model');
            $html = $this->load->view('skills/junior_report_card_pdf', $this->data, true);

            // Generate PDF
            $this->load->library('mpdf');
            $mpdf = $this->mpdf->load([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);
            $mpdf->WriteHTML($html);
            $mpdf->Output('junior_report_card.pdf', 'D');
        }
    }

    // =====================================================
    // JUNIOR TABULATION SHEET (SKILLS-BASED)
    // =====================================================

    /**
     * Junior tabulation sheet - shows skills ratings in tabular format
     */
    public function tabulation_sheet()
    {
        if (!get_permission('tabulation_sheet', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;

        if (!empty($this->input->post('submit'))) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $examID = $this->input->post('exam_id');
            $sessionID = $this->input->post('session_id');

            // Get students enrolled in this class/section/session
            $this->db->select('e.*, s.*, e.id as enrollID, CONCAT_WS(" ", s.first_name, s.last_name) as fullname');
            $this->db->from('enroll e');
            $this->db->join('student s', 'e.student_id = s.id', 'inner');
            $this->db->where('e.class_id', $classID);
            $this->db->where('e.section_id', $sectionID);
            $this->db->where('e.session_id', $sessionID);
            $this->db->where('e.branch_id', $branchID);
            $this->db->where('s.active', 1);
            $this->db->order_by('e.roll', 'ASC');
            $this->data['students_list'] = $this->db->get()->result();

            // Get exam details
            $this->data['exam_details'] = $this->db->where('id', $examID)->get('exam')->row_array();

            // Get skill categories and items for this branch (all levels)
            $this->db->select('sc.*, sc.id as category_id, sc.name as category_name');
            $this->db->from('skills_categories sc');
            $this->db->where('sc.branch_id', $branchID);
            $this->db->where('sc.status', 'active');
            $this->db->order_by('sc.type', 'ASC');
            $categories = $this->db->get()->result_array();

            // Get items for each category
            $skills_with_items = array();
            foreach ($categories as $category) {
                $this->db->select('si.*');
                $this->db->from('skills_items si');
                $this->db->where('si.category_id', $category['category_id']);
                $this->db->where('si.status', 'active');
                $this->db->order_by('si.display_order', 'ASC');
                $items = $this->db->get()->result_array();

                if (!empty($items)) {
                    $skills_with_items[] = array(
                        'category' => $category,
                        'items' => $items
                    );
                }
            }

            $this->data['skills_categories'] = $skills_with_items;

            // Get all ratings for this exam/class/section
            $this->db->select('ssr.*');
            $this->db->from('skills_students_ratings ssr');
            $this->db->where('ssr.exam_id', $examID);
            $this->db->where('ssr.class_id', $classID);
            $this->db->where('ssr.section_id', $sectionID);
            $this->db->where('ssr.session_id', $sessionID);
            $this->db->where('ssr.branch_id', $branchID);
            $ratings = $this->db->get()->result_array();

            // Organize ratings by student and skill item
            $organized_ratings = array();
            foreach ($ratings as $rating) {
                $student_id = $rating['student_id'];
                $skill_item_id = $rating['skill_item_id'];
                if (!isset($organized_ratings[$student_id])) {
                    $organized_ratings[$student_id] = array();
                }
                $organized_ratings[$student_id][$skill_item_id] = $rating['rating_id'];
            }

            $this->data['ratings'] = $organized_ratings;

            // Get ratings scale for display
            $this->data['ratings_scale'] = $this->skills_model->getRatings($branchID, 'active');
        }

        $this->data['title'] = translate('junior_tabulation_sheet');
        $this->data['sub_page'] = 'skills/tabulation_sheet';
        $this->data['main_menu'] = 'exam_reports';
        $this->data['sub_menu'] = 'skills/tabulation_sheet';
        $this->load->view('layout/index', $this->data);
    }
}
