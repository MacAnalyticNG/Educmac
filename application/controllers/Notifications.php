<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notifications extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->data['title'] = 'Notification Center';
        $this->data['sub_page'] = 'notifications/index';
        $this->data['main_menu'] = 'notifications';

        // Mock current user
        $user_id = $this->session->userdata('user_id') ?? 1;
        $role = $this->session->userdata('user_type') ?? 'admin';

        $this->db->where('user_id', $user_id);
        $this->db->where('role', $role);
        $this->db->order_by('created_at', 'DESC');
        $this->data['notifications'] = $this->db->get('notifications')->result();

        $this->load->view('layout/index', $this->data);
    }

    public function mark_read($id) {
        $this->db->where('id', $id);
        $this->db->update('notifications', ['is_read' => 1]);
        redirect('notifications/index');
    }

    public function send() {
        $title = $this->input->post('title');
        $message = $this->input->post('message');
        $role = $this->input->post('role'); // e.g., 'student', 'parent', 'teacher'
        
        // This is a naive implementation that creates a notification for user_id 1 just as mock
        // Real implementation would loop over users or use a global broadcast approach
        if($title) {
            $this->db->insert('notifications', [
                'user_id' => 1, 
                'role' => $role, 
                'title' => $title, 
                'message' => $message,
                'is_read' => 0
            ]);
            $this->session->set_flashdata('success', 'Notification sent successfully.');
        }
        
        redirect('notifications/index');
    }
}
