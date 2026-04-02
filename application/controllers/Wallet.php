<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wallet extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wallet_model');
        $this->load->library('session');
    }

    public function index() {
        $this->data['title'] = 'Student Wallet';
        $this->data['sub_page'] = 'wallet/index';
        $this->data['main_menu'] = 'wallet';
        $role_id = loggedin_role_id();
        $student_id = null;
        $parent_id = null;

        if ($role_id == 7) {
            $student_id = get_loggedin_user_id();
        } elseif ($role_id == 6) {
            $parent_id = get_loggedin_user_id();
        } else {
            $selection_type = $this->input->post('selection_type') ?: $this->session->userdata('wallet_selection_type');
            $user_id = $this->input->post('user_id') ?: $this->session->userdata('wallet_user_id');
            
            if ($selection_type && $user_id) {
                $this->session->set_userdata('wallet_selection_type', $selection_type);
                $this->session->set_userdata('wallet_user_id', $user_id);
                if ($selection_type == 'student') {
                    $student_id = $user_id;
                } else {
                    $parent_id = $user_id;
                }
            }
            $this->data['students'] = $this->db->select('id, first_name, last_name, register_no')->get('student')->result_array();
            $this->data['parents'] = $this->db->select('id, name, email')->get('parent')->result_array();
        }

        if ($student_id || $parent_id) {
            $this->data['wallet'] = $this->Wallet_model->get_wallet($student_id, $parent_id);
            $wallet_id = ($this->data['wallet'] && isset($this->data['wallet']->id)) ? $this->data['wallet']->id : 0;
            $this->data['transactions'] = $this->Wallet_model->get_transactions($wallet_id);
            
            if ($student_id) {
                $stu = $this->db->get_where('student', ['id' => $student_id])->row();
                $this->data['user_info'] = "Student: " . $stu->first_name;
                if ($stu->parent_id) $this->data['user_info'] .= " (Shared Parent Wallet)";
            } else {
                $par = $this->db->get_where('parent', ['id' => $parent_id])->row();
                $this->data['user_info'] = "Parent: " . $par->name;
            }
        } else {
            $this->data['wallet'] = null;
            $this->data['transactions'] = [];
            $this->data['user_info'] = null;
        }

        $this->data['selection_type'] = $selection_type ?? 'student';
        $this->data['user_id'] = $user_id ?? '';
        $this->data['role_id'] = $role_id;
        $this->load->view('layout/index', $this->data);
    }

    public function deposit() {
        $amount = $this->input->post('amount');
        $student_id = $this->input->post('student_id') ?: null;
        $parent_id = $this->input->post('parent_id') ?: null;
        
        if ($amount > 0 && ($student_id || $parent_id)) {
            $this->Wallet_model->deposit($amount, $student_id, $parent_id);
            $this->session->set_flashdata('success', 'Deposit successful.');
        } else {
            $this->session->set_flashdata('error', 'Invalid amount or user.');
        }
        redirect('wallet/index');
    }
}
