<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Extracurricular extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->data['title'] = 'Extracurricular Activities';
        $this->data['sub_page'] = 'extracurricular/index';
        $this->data['main_menu'] = 'extracurricular';

        $this->data['activities'] = $this->db->get('extracurricular_types')->result();
        // Just mock some data for now
        $this->data['enrollments'] = $this->db->get('extracurricular_enrollment')->result();

        $this->load->view('layout/index', $this->data);
    }

    public function create() {
        $name = $this->input->post('name');
        $description = $this->input->post('description');
        if($name) {
            $this->db->insert('extracurricular_types', ['name' => $name, 'description' => $description]);
            $this->session->set_flashdata('success', 'Activity added successfully.');
        }
        redirect('extracurricular/index');
    }
    public function assign($activity_id) {
        $this->data['title'] = 'Assign Extracurricular Activity';
        $this->data['sub_page'] = 'extracurricular/assign';
        $this->data['main_menu'] = 'extracurricular';
        
        $this->data['activity'] = $this->db->get_where('extracurricular_types', ['id' => $activity_id])->row();
        if(!$this->data['activity']) {
            redirect('extracurricular/index');
        }
        
        $this->data['students'] = $this->db->select('id, first_name, last_name, register_no')->get('student')->result_array();
        $this->data['enrollments'] = $this->db->select('extracurricular_enrollment.*, student.first_name, student.last_name, student.register_no')
                                              ->from('extracurricular_enrollment')
                                              ->join('student', 'student.id = extracurricular_enrollment.student_id')
                                              ->where('activity_id', $activity_id)
                                              ->get()->result_array();

        $this->load->view('layout/index', $this->data);
    }
    
    public function save_assign() {
        if ($this->input->post()) {
            $activity_id = $this->input->post('activity_id');
            $student_id = $this->input->post('student_id');
            $date = date('Y-m-d');
            
            $exists = $this->db->get_where('extracurricular_enrollment', ['student_id' => $student_id, 'activity_id' => $activity_id])->num_rows();
            if ($exists == 0) {
                $this->db->insert('extracurricular_enrollment', [
                    'student_id' => $student_id,
                    'activity_id' => $activity_id,
                    'enrollment_date' => $date
                ]);
                $this->session->set_flashdata('success', 'Student enrolled successfully.');
            } else {
                $this->session->set_flashdata('error', 'Student is already enrolled in this activity.');
            }
            redirect('extracurricular/assign/' . $activity_id);
        }
    }
}
