<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Ramom school management system
 * @version : 7.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Exam.php
 * @copyright : Reserved RamomCoder Team
 */

class Exam extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('exam_model');
        $this->load->model('subject_model');
        $this->load->model('sms_model');
        $this->load->model('email_model');
        $this->load->model('marksheet_template_model');
        $this->load->model('exam_progress_model');
    }

    /* exam form validation rules */
    protected function exam_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('name', translate('name'), 'trim|required');
        $this->form_validation->set_rules('type_id', translate('exam_type'), 'trim|required');
        $this->form_validation->set_rules('mark_distribution[]', translate('mark_distribution'), 'trim|required');
    }

    public function index()
    {
        if (!get_permission('exam', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('exam', 'is_view')) {
                ajax_access_denied();
            }
            $this->exam_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->exam_model->exam_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('exam');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['examlist'] = $this->exam_model->getExamList();
        $this->data['title'] = translate('exam_list');
        $this->data['sub_page'] = 'exam/index';
        $this->data['main_menu'] = 'exam';
        $this->load->view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('exam', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $this->exam_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->exam_model->exam_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('exam');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['exam'] = $this->app_lib->getTable('exam', array('t.id' => $id), true);
        $this->data['title'] = translate('exam_list');
        $this->data['sub_page'] = 'exam/edit';
        $this->data['main_menu'] = 'exam';
        $this->load->view('layout/index', $this->data);
    }

    // exam information delete stored in the database here
    public function delete($id)
    {
        if (!get_permission('exam', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('exam');
    }

    /**
     * DEPRECATED: Term management moved to Sessions module
     * Redirects to Sessions page where terms are managed centrally
     */
    public function term()
    {
        set_alert('info', 'Term management has been moved to the Sessions module. Please manage terms from the Sessions page.');
        redirect(base_url('sessions'));
    }

    /**
     * DEPRECATED: Term editing moved to Sessions module
     */
    public function term_edit()
    {
        echo json_encode([
            'status' => 'fail',
            'url' => base_url('sessions'),
            'error' => array('general' => 'Term management has been moved to the Sessions module.')
        ]);
    }

    /**
     * DEPRECATED: Term deletion moved to Sessions module
     */
    public function term_delete($id)
    {
        set_alert('info', 'Term management has been moved to the Sessions module. Please manage terms from the Sessions page.');
        redirect(base_url('sessions'));
    }

    public function mark_distribution()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('mark_distribution', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('name', translate('name'), 'trim|required');
            if ($this->form_validation->run() !== false) {
                // save mark distribution information in the database file
                $arrayDistribution = array(
                    'name' => $this->input->post('name'),
                    'branch_id' => $this->application_model->get_branch_id(),
                );
                $this->db->insert('exam_mark_distribution', $arrayDistribution);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }
        $this->data['termlist'] = $this->app_lib->getTable('exam_mark_distribution');
        $this->data['sub_page'] = 'exam/mark_distribution';
        $this->data['main_menu'] = 'exam';
        $this->data['title'] = translate('mark_distribution');
        $this->load->view('layout/index', $this->data);
    }

    public function mark_distribution_edit()
    {
        if ($_POST) {
            if (!get_permission('mark_distribution', 'is_edit')) {
                ajax_access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('name', translate('name'), 'trim|required');
            if ($this->form_validation->run() !== false) {
                // save mark distribution information in the database file
                $arrayDistribution = array(
                    'name' => $this->input->post('name'),
                    'branch_id' => $this->application_model->get_branch_id(),
                );
                $this->db->where('id', $this->input->post('distribution_id'));
                $this->db->update('exam_mark_distribution', $arrayDistribution);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('exam/mark_distribution');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function mark_distribution_delete($id)
    {
        if (!get_permission('mark_distribution', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('exam_mark_distribution');
    }

    /* hall form validation rules */
    protected function hall_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('hall_no', translate('hall_no'), 'trim|required|callback_unique_hall_no');
        $this->form_validation->set_rules('no_of_seats', translate('no_of_seats'), 'trim|required|numeric');
    }

    /* exam hall information moderator and page */
    public function hall($action = '', $id = '')
    {
        if (isset($_POST['save'])) {
            if (!get_permission('exam_hall', 'is_add')) {
                access_denied();
            }
            $this->hall_validation();
            if ($this->form_validation->run() !== false) {
                //save exam hall information in the database file
                $this->exam_model->hallSave($this->input->post());
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }
        $this->data['halllist'] = $this->app_lib->getTable('exam_hall');
        $this->data['title'] = translate('exam_hall');
        $this->data['sub_page'] = 'exam/hall';
        $this->data['main_menu'] = 'exam';
        $this->load->view('layout/index', $this->data);
    }

    public function hall_edit()
    {
        if ($_POST) {
            if (!get_permission('exam_hall', 'is_edit')) {
                ajax_access_denied();
            }
            $this->hall_validation();
            if ($this->form_validation->run() !== false) {
                //save exam hall information in the database file
                $this->exam_model->hallSave($this->input->post());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('exam/hall');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function hall_delete($id)
    {
        if (!get_permission('exam_hall', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('exam_hall');
    }

    /* exam hall number exists validation */
    public function unique_hall_no($hall_no)
    {
        $branchID = $this->application_model->get_branch_id();
        $term_id = $this->input->post('term_id');
        if (!empty($term_id)) {
            $this->db->where_not_in('id', $term_id);
        }
        $this->db->where(array('hall_no' => $hall_no, 'branch_id' => $branchID));
        $uniform_row = $this->db->get('exam_hall')->num_rows();
        if ($uniform_row == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_hall_no", translate('already_taken'));
            return false;
        }
    }

    /* exam mark information are prepared and stored in the database here */
    public function mark_entry()
    {
        if (!get_permission('exam_mark', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        $subjectID = $this->input->post('subject_id');
        $examID = $this->input->post('exam_id');

        $this->data['branch_id'] = $branchID;
        $this->data['class_id'] = $classID;
        $this->data['section_id'] = $sectionID;
        $this->data['subject_id'] = $subjectID;
        $this->data['exam_id'] = $examID;
        if (isset($_POST['search'])) {
            $this->data['timetable_detail'] = $this->exam_model->getTimetableDetail($classID, $sectionID, $examID, $subjectID);
            $this->data['student'] = $this->exam_model->getMarkAndStudent($branchID, $classID, $sectionID, $examID, $subjectID);
        }

        $this->data['sub_page'] = 'exam/marks_register';
        $this->data['main_menu'] = 'mark';
        $this->data['title'] = translate('mark_entries');
        $this->load->view('layout/index', $this->data);
    }

    public function mark_save()
    {
        if ($_POST) {
            if (!get_permission('exam_mark', 'is_add')) {
                ajax_access_denied();
            }
            $inputMarks = $this->input->post('mark');
            foreach ($inputMarks as $key => $value) {
                if (!isset($value['absent'])) {
                    foreach ($value['assessment'] as $i => $row) {
                        $field = "mark[{$key}][assessment][{$i}]";
                        $this->form_validation->set_rules($field, translate('mark'), "trim|numeric|callback_valid_Mark[$i]");
                    }
                }
            }
            if ($this->form_validation->run() !== false) {
                $branchID = $this->application_model->get_branch_id();
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                $subjectID = $this->input->post('subject_id');
                $examID = $this->input->post('exam_id');
                $inputMarks = $this->input->post('mark');
                foreach ($inputMarks as $key => $value) {
                    $assMark = array();
                    foreach ($value['assessment'] as $i => $row) {
                        $assMark[$i] = $row;
                    }
                    $arrayMarks = array(
                        'student_id' => $value['student_id'],
                        'exam_id' => $examID,
                        'class_id' => $classID,
                        'section_id' => $sectionID,
                        'subject_id' => $subjectID,
                        'branch_id' => $branchID,
                        'session_id' => get_session_id(),
                    );
                    $inputMark = (isset($value['absent']) ? null : json_encode($assMark));
                    $absent = (isset($value['absent']) ? 'on' : '');
                    $query = $this->db->get_where('mark', $arrayMarks);
                    if ($query->num_rows() > 0) {
                        if (in_array('', $assMark) & !isset($value['absent'])) {
                            $this->db->where('id', $query->row()->id);
                            $this->db->delete('mark');
                        } else {
                            $this->db->where('id', $query->row()->id);
                            $this->db->update('mark', array('mark' => $inputMark, 'absent' => $absent));
                        }
                    } else {
                        if (!in_array('', $assMark) || isset($value['absent'])) {
                            $arrayMarks['mark'] = $inputMark;
                            $arrayMarks['absent'] = $absent;
                            $this->db->insert('mark', $arrayMarks);
                            // send exam results sms
                            $this->sms_model->send_sms($arrayMarks, 5);
                        }
                    }
                }
                $message = translate('information_has_been_saved_successfully');
                $array = array('status' => 'success', 'message' => $message);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    //exam mark register validation check
    public function valid_Mark($val, $i)
    {
        $fullMark = $this->input->post('max_mark_' . $i);
        if ($fullMark < $val) {
            $this->form_validation->set_message("valid_Mark", translate("invalid_marks"));
            return false;
        }
        return true;
    }

    /* exam grade form validation rules */
    protected function grade_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('name', translate('name'), 'trim|required');
        $this->form_validation->set_rules('grade_point', translate('grade_point'), 'trim|required|numeric');
        $this->form_validation->set_rules('lower_mark', translate('mark_from'), 'trim|required');
        $this->form_validation->set_rules('upper_mark', translate('mark_upto'), 'trim|required');
    }

    /* exam grade information are prepared and stored in the database here */
    public function grade($action = '')
    {
        if (!get_permission('exam_grade', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('exam_grade', 'is_view')) {
                ajax_access_denied();
            }
            $this->grade_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->exam_model->gradeSave($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('exam/grade');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['title'] = translate('grades_range');
        $this->data['sub_page'] = 'exam/grade';
        $this->data['main_menu'] = 'mark';
        $this->load->view('layout/index', $this->data);
    }

    // exam grade information updating here
    public function grade_edit($id = '')
    {
        if (!get_permission('exam_grade', 'is_edit')) {
            ajax_access_denied();
        }

        if ($_POST) {
            $this->grade_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->exam_model->gradeSave($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('exam/grade');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['grade'] = $this->app_lib->getTable('grade', array('t.id' => $id), true);
        $this->data['sub_page'] = 'exam/grade_edit';
        $this->data['title'] = translate('grades_range');
        $this->data['main_menu'] = 'exam';
        $this->load->view('layout/index', $this->data);
    }

    public function grade_delete($id = '')
    {
        if (get_permission('exam_grade', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('grade');
        }
    }

    public function marksheet()
    {
        if (!get_permission('report_card', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('session_id', translate('academic_year'), 'trim|required');
            $this->form_validation->set_rules('exam_id', translate('exam'), 'trim|required');
            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            $this->form_validation->set_rules('template_id', translate('marksheet') . " " . translate('template'), 'trim|required');
            if ($this->form_validation->run() == true) {
                $sessionID = $this->input->post('session_id');
                $examID = $this->input->post('exam_id');
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                $this->db->select('e.roll,e.id as enrollID,s.*,c.name as category');
                $this->db->from('enroll as e');
                $this->db->join('student as s', 'e.student_id = s.id', 'inner');
                $this->db->join('mark as m', 's.id = m.student_id', 'inner');
                $this->db->join('student_category as c', 'c.id = s.category_id', 'left');
                $this->db->join('exam_rank as r', 'r.exam_id = m.exam_id and r.enroll_id = e.id', 'left');
                $this->db->where('e.session_id', $sessionID);
                $this->db->where('s.active', 1);
                $this->db->where('m.session_id', $sessionID);
                $this->db->where('m.class_id', $classID);
                $this->db->where('m.section_id', $sectionID);
                $this->db->where('e.branch_id', $branchID);
                $this->db->where('m.exam_id', $examID);
                $this->db->group_by('m.student_id');
                $this->db->order_by('r.rank', 'ASC');
                $this->data['student'] = $this->db->get()->result_array();
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'exam/marksheet';
        $this->data['main_menu'] = 'exam_reports';
        $this->data['title'] = translate('report_card');
        $this->load->view('layout/index', $this->data);
    }

    public function reportCardPrint()
    {
        if ($_POST) {
            if (!get_permission('report_card', 'is_view')) {
                ajax_access_denied();
            }
            $this->data['student_array'] = $this->input->post('student_id');
            $this->data['print_date'] = $this->input->post('print_date');
            $this->data['examID'] = $this->input->post('exam_id');
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['sessionID'] = $this->input->post('session_id');
            $this->data['templateID'] = $this->input->post('template_id');
            $this->data['branchID'] = $this->application_model->get_branch_id();

            echo $this->load->view('exam/reportCard', $this->data, true);
        }
    }

    public function reportCardPdf()
    {
        if ($_POST) {
            if (!get_permission('report_card', 'is_view')) {
                ajax_access_denied();
            }
            $this->data['student_array'] = $this->input->post('student_id');
            $this->data['print_date'] = $this->input->post('print_date');
            $this->data['examID'] = $this->input->post('exam_id');
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['sessionID'] = $this->input->post('session_id');
            $this->data['templateID'] = $this->input->post('template_id');
            $this->data['branchID'] = $this->application_model->get_branch_id();
            $this->data['marksheet_template'] = $this->marksheet_template_model->getTemplate($this->data['templateID'], $this->data['branchID']);
            $html = $this->load->view('exam/reportCard_PDF', $this->data, true);

            $this->load->library('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/pdf-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->autoScriptToLang  = true;
            $this->html2pdf->mpdf->baseScript        = 1;
            $this->html2pdf->mpdf->autoLangToFont    = true;
            header("Content-Type: application/pdf");
            echo $this->html2pdf->mpdf->Output("", "S");
        }
    }

    public function pdf_sendByemail()
    {
        if ($_POST) {
            if (!get_permission('report_card', 'is_view')) {
                ajax_access_denied();
            }
            $enrollID = $this->input->post('enrollID');
            $this->data['student_array'] = [$this->input->post('student_id')];
            $this->data['print_date'] = $this->input->post('print_date');
            $this->data['examID'] = $this->input->post('exam_id');
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['sessionID'] = $this->input->post('session_id');
            $this->data['templateID'] = $this->input->post('template_id');
            $this->data['branchID'] = $this->application_model->get_branch_id();
            $this->data['marksheet_template'] = $this->marksheet_template_model->getTemplate($this->data['templateID'], $this->data['branchID']);
            $html = $this->load->view('exam/reportCard_PDF', $this->data, true);

            $this->load->library('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/pdf-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->autoScriptToLang  = true;
            $this->html2pdf->mpdf->baseScript        = 1;
            $this->html2pdf->mpdf->autoLangToFont    = true;

            $file = $this->html2pdf->mpdf->Output(time() . '.pdf', "S");
            $data['exam_name'] = get_type_name_by_id('exam', $this->data['examID']);
            $data['file'] = $file;
            $data['enroll_id'] = $enrollID;
            $response = $this->email_model->emailPDFexam_marksheet($data);
            if ($response == true) {
                $array = array('status' => 'success', 'message' => translate('mail_sent_successfully'));
            } else {
                $array = array('status' => 'error', 'message' => translate('something_went_wrong'));
            }
            echo json_encode($array);
        }
    }

    /* tabulation sheet report generating here */
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

            $this->data['students_list'] = $this->exam_model->searchExamStudentsByRank($classID, $sectionID, $sessionID, $examID, $branchID);
            $this->data['exam_details'] = $this->exam_model->getExamByID($examID);
            $this->data['get_subjects'] = $this->exam_model->getSubjectList($examID, $classID, $sectionID, $sessionID);
        }
        $this->data['title'] = translate('tabulation_sheet');
        $this->data['sub_page'] = 'exam/tabulation_sheet';
        $this->data['main_menu'] = 'exam_reports';
        $this->load->view('layout/index', $this->data);
    }

    public function getDistributionByBranch()
    {
        $html = "";
        $table = $this->input->post('table');
        $branch_id = $this->application_model->get_branch_id();
        if (!empty($branch_id)) {
            $result = $this->db->select('id,name')->where('branch_id', $branch_id)->get('exam_mark_distribution')->result_array();
            if (count($result)) {
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            }
        }
        echo $html;
    }


    // exam publish status
    public function publish_status()
    {
        if (get_permission('exam', 'is_add')) {
            $id = $this->input->post('id');
            $status = $this->input->post('status');
            if ($status == 'true') {
                $arrayData['status'] = 1;
            } else {
                $arrayData['status'] = 0;
            }
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->update('exam', $arrayData);
            $return = array('msg' => translate('information_has_been_updated_successfully'), 'status' => true);
            echo json_encode($return);
        }
    }

    // exam result publish status
    public function publish_result_status()
    {
        if (get_permission('exam', 'is_add')) {
            $id = $this->input->post('id');
            $status = $this->input->post('status');
            if ($status == 'true') {
                $arrayData['publish_result'] = 1;
            } else {
                $arrayData['publish_result'] = 0;
            }
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->update('exam', $arrayData);
            $return = array('msg' => translate('information_has_been_updated_successfully'), 'status' => true);
            echo json_encode($return);
        }
    }


    public function class_position()
    {
        if (!get_permission('generate_position', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        if (!empty($this->input->post('submit'))) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $examID = $this->input->post('exam_id');
            $sessionID = $this->input->post('session_id');
            $this->data['students_list'] = $this->exam_model->searchExamStudentsByRank($classID, $sectionID, $sessionID, $examID, $branchID);
            $this->data['exam_details'] = $this->exam_model->getExamByID($examID);
            $this->data['get_subjects'] = $this->exam_model->getSubjectList($examID, $classID, $sectionID, $sessionID);
        }
        $this->data['title'] = translate('class_position');
        $this->data['sub_page'] = 'exam/class_position';
        $this->data['main_menu'] = 'mark';
        $this->load->view('layout/index', $this->data);
    }

    public function save_position()
    {
        if ($_POST) {
            if (!get_permission('generate_position', 'is_view')) {
                ajax_access_denied();
            }
            $rank = $this->input->post('rank');
            foreach ($rank as $key => $value) {
                $this->form_validation->set_rules('rank[' . $key . '][position]', translate('position'), 'trim|numeric|required');
            }
            if ($this->form_validation->run() == true) {
                $examID = $this->input->post('exam_id');
                foreach ($rank as $key => $value) {
                    $q = $this->db->select('id')->where(array('exam_id' => $examID, 'enroll_id' => $value['enroll_id']))->get('exam_rank');
                    if ($q->num_rows() == 0) {
                        $arrayRank = array(
                            'rank' => $value['position'],
                            'teacher_comments' => $value['teacher_comments'],
                            'principal_comments' => $value['principal_comments'],
                            'enroll_id' => $value['enroll_id'],
                            'exam_id' => $examID,
                        );
                        $this->db->insert('exam_rank', $arrayRank);
                    } else {
                        $this->db->where('id', $q->row()->id);
                        $this->db->update('exam_rank', ['rank' => $value['position'], 'teacher_comments' => $value['teacher_comments'], 'principal_comments' => $value['principal_comments']]);
                    }
                }
                $message = translate('information_has_been_saved_successfully');
                $array = array('status' => 'success', 'message' => $message);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function import_marks()
    {
        if (!get_permission('exam_mark', 'is_add')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        if (isset($_POST['upload'])) {
            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required');
            $this->form_validation->set_rules('exam_id', translate('exam'), 'trim|required');
            $is_surepass = $this->input->post('is_surepass') == '1';
            if ($is_surepass) {
                $this->form_validation->set_rules('distribution_id', 'Mark Distribution', 'trim|required');
            }
            if ($this->form_validation->run() == true) {
                if (empty($_FILES['excel_file']['name'])) {
                    set_alert('error', 'Please select an Excel file');
                    redirect(current_url());
                }
                $upload_path = FCPATH . 'uploads/temp/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'xlsx|xls';
                $config['max_size'] = 2048;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                $originalFilename = '';
                if (!empty($_FILES['excel_file']['name'])) {
                    $originalFilename = pathinfo($_FILES['excel_file']['name'], PATHINFO_FILENAME);
                }
                if ($this->upload->do_upload('excel_file')) {
                    $fileData = $this->upload->data();
                    $filePath = $fileData['full_path'];
                    $temp_path = FCPATH . 'uploads/temp/';
                    if (is_dir($temp_path)) {
                        $files = glob($temp_path . '*');
                        $now = time();
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                if ($now - filemtime($file) >= 3600) {
                                    unlink($file);
                                }
                            }
                        }
                    }
                    $classID = $this->input->post('class_id');
                    $sectionID = $this->input->post('section_id');
                    $subjectID = $this->input->post('subject_id');
                    $examID = $this->input->post('exam_id');
                    $sessionID = get_session_id();
                    if ($is_surepass) {
                        $this->process_surepass_import($filePath, $classID, $sectionID, $subjectID, $examID, $branchID, $sessionID);
                    } else {
                        $this->process_regular_import($filePath, $classID, $sectionID, $subjectID, $examID, $branchID, $sessionID, $originalFilename);
                    }
                } else {
                    $error = $this->upload->display_errors('', '');
                    set_alert('error', $error);
                    redirect(current_url());
                }
            }
        }
        $this->data['filename_warning'] = $this->session->userdata('filename_warning');
        $this->session->unset_userdata('filename_warning');
        $this->data['import_errors'] = $this->session->userdata('import_errors');
        $this->session->unset_userdata('import_errors');
        $this->data['title'] = translate('import_marks');
        $this->data['sub_page'] = 'exam/import_marks';
        $this->data['main_menu'] = 'mark';
        $this->load->view('layout/index', $this->data);
    }

    private function process_surepass_import($filePath, $classID, $sectionID, $subjectID, $examID, $branchID, $sessionID)
    {
        $distributionID = $this->input->post('distribution_id');
        require_once FCPATH . 'vendor/autoload.php';
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (Exception $e) {
            unlink($filePath);
            set_alert('error', 'Error reading Excel file: ' . $e->getMessage());
            redirect(current_url());
        }
        $timetable_detail = $this->exam_model->getTimetableDetail($classID, $sectionID, $examID, $subjectID);
        if (empty($timetable_detail)) {
            unlink($filePath);
            set_alert('error', 'No timetable found for this exam configuration');
            redirect(current_url());
        }
        $distributions = json_decode($timetable_detail['mark_distribution'], true);
        if (!isset($distributions[$distributionID])) {
            unlink($filePath);
            set_alert('error', 'Selected mark distribution not found in exam timetable');
            redirect(current_url());
        }
        $max_mark = $distributions[$distributionID]['full_mark'];
        $success_count = 0;
        $error_count = 0;
        $errors = array();
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row[0])) {
                continue;
            }
            $email = trim($row[0]);
            $score_column = isset($row[2]) ? trim($row[2]) : '';
            if (empty($score_column)) {
                continue;
            }
            $parts = explode('/', $score_column);
            if (count($parts) !== 2) {
                $error_count++;
                $errors[] = array(
                    'student_email' => $email,
                    'error' => 'Invalid score format: ' . $score_column
                );
                continue;
            }
            $mark_obtained = trim($parts[0]);
            if (!is_numeric($mark_obtained)) {
                $error_count++;
                $errors[] = array(
                    'student_email' => $email,
                    'error' => 'Invalid mark value: ' . $mark_obtained
                );
                continue;
            }
            $mark_obtained = floatval($mark_obtained);
            if ($mark_obtained > $max_mark) {
                $error_count++;
                $errors[] = array(
                    'student_email' => $email,
                    'mark_obtained' => $mark_obtained,
                    'max_mark' => $max_mark,
                    'error' => 'Mark exceeds maximum'
                );
                continue;
            }
            $this->db->select('s.id as student_id');
            $this->db->from('student as s');
            $this->db->join('enroll as en', 'en.student_id = s.id', 'inner');
            $this->db->where('s.email', $email);
            $this->db->where('en.class_id', $classID);
            $this->db->where('en.section_id', $sectionID);
            $this->db->where('en.branch_id', $branchID);
            $this->db->where('en.session_id', $sessionID);
            $this->db->where('s.active', 1);
            $student = $this->db->get()->row();
            if (!$student) {
                $error_count++;
                $errors[] = array(
                    'student_email' => $email,
                    'error' => 'Student not found in selected class/section'
                );
                continue;
            }
            $student_id = $student->student_id;
            $arrayMarks = array(
                'student_id' => $student_id,
                'exam_id' => $examID,
                'class_id' => $classID,
                'section_id' => $sectionID,
                'subject_id' => $subjectID,
                'branch_id' => $branchID,
                'session_id' => $sessionID,
            );
            $query = $this->db->get_where('mark', $arrayMarks);
            $existing_marks = array();
            if ($query->num_rows() > 0) {
                $existing_mark = $query->row();
                $existing_marks = json_decode($existing_mark->mark, true);
                if (empty($existing_marks)) {
                    $existing_marks = array();
                }
            }
            $existing_marks[$distributionID] = $mark_obtained;
            $inputMark = json_encode($existing_marks);
            if ($query->num_rows() > 0) {
                $this->db->where('id', $existing_mark->id);
                $this->db->update('mark', array('mark' => $inputMark, 'absent' => ''));
            } else {
                $arrayMarks['mark'] = $inputMark;
                $arrayMarks['absent'] = '';
                $this->db->insert('mark', $arrayMarks);
            }
            $success_count++;
        }
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        if ($success_count > 0) {
            set_alert('success', $success_count . ' marks imported successfully from SurePass');
        } else {
            set_alert('error', 'No marks were imported');
        }
        if ($error_count > 0) {
            $this->session->set_userdata('import_errors', $errors);
        }
        redirect(current_url());
    }

    private function process_regular_import($filePath, $classID, $sectionID, $subjectID, $examID, $branchID, $sessionID, $originalFilename)
    {
        $class_name = get_type_name_by_id('class', $classID);
        $section_name = get_type_name_by_id('section', $sectionID);
        $subject_name = get_type_name_by_id('subject', $subjectID);
        $exam_details = $this->exam_model->getExamByID($examID);
        $term_name = isset($exam_details->term_name) ? $exam_details->term_name : 'exam';
        $expectedFilename = slugify($class_name) . '_' . slugify($section_name) . '_' . slugify($subject_name) . '_' . slugify($term_name);
        if (!empty($originalFilename) && $originalFilename !== $expectedFilename) {
            $warningMsg = '<strong>Warning:</strong> The uploaded file name does not match the selected filters.<br>';
            $warningMsg .= 'Expected: <strong>' . $expectedFilename . '.xlsx</strong><br>';
            $warningMsg .= 'Uploaded: <strong>' . $originalFilename . '.xlsx</strong><br>';
            $warningMsg .= 'Please ensure you are importing the correct file for this class, section, subject, and term.';
            $this->session->set_userdata('filename_warning', $warningMsg);
        }
        require_once FCPATH . 'vendor/autoload.php';
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (Exception $e) {
            unlink($filePath);
            set_alert('error', 'Error reading Excel file: ' . $e->getMessage());
            redirect(current_url());
        }
        $timetable_detail = $this->exam_model->getTimetableDetail($classID, $sectionID, $examID, $subjectID);
        if (empty($timetable_detail)) {
            unlink($filePath);
            set_alert('error', 'No timetable found for this configuration. Please setup exam timetable first.');
            redirect(current_url());
        }
        $distributions = json_decode($timetable_detail['mark_distribution'], true);
        $success_count = 0;
        $error_count = 0;
        $errors = array();
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }
            $student_id = $row[0];
            $student_name = isset($row[1]) ? trim($row[1]) : 'Unknown Student';
            $is_absent = isset($row[4]) && strtolower(trim($row[4])) === 'yes' ? true : false;
            $is_optional = isset($row[5]) && strtolower(trim($row[5])) === 'yes' ? true : false;
            $assMark = array();
            $col_index = 6;
            foreach ($distributions as $dist_id => $dist_data) {
                if (!$is_absent) {
                    $mark = isset($row[$col_index]) ? trim($row[$col_index]) : '';
                    if ($mark !== '' && is_numeric($mark)) {
                        if ($mark > $dist_data['full_mark']) {
                            $dist_name = get_type_name_by_id('exam_mark_distribution', $dist_id);
                            $errors[] = array(
                                'student_name' => $student_name,
                                'distribution' => $dist_name,
                                'mark_entered' => $mark,
                                'max_mark' => $dist_data['full_mark']
                            );
                            $error_count++;
                            continue 2;
                        }
                        $assMark[$dist_id] = $mark;
                    } else {
                        $assMark[$dist_id] = '';
                    }
                }
                $col_index++;
            }
            $arrayMarks = array(
                'student_id' => $student_id,
                'exam_id' => $examID,
                'class_id' => $classID,
                'section_id' => $sectionID,
                'subject_id' => $subjectID,
                'branch_id' => $branchID,
                'session_id' => $sessionID,
            );
            $inputMark = ($is_absent || $is_optional) ? null : json_encode($assMark);
            $absent = $is_absent ? 'on' : '';
            $is_optional_value = $is_optional ? '1' : '0';
            $query = $this->db->get_where('mark', $arrayMarks);
            if ($query->num_rows() > 0) {
                if (in_array('', $assMark) && !$is_absent && !$is_optional) {
                    $this->db->where('id', $query->row()->id);
                    $this->db->delete('mark');
                } else {
                    $this->db->where('id', $query->row()->id);
                    $this->db->update('mark', array('mark' => $inputMark, 'absent' => $absent, 'is_optional' => $is_optional_value));
                }
            } else {
                if (!in_array('', $assMark) || $is_absent || $is_optional) {
                    $arrayMarks['mark'] = $inputMark;
                    $arrayMarks['absent'] = $absent;
                    $arrayMarks['is_optional'] = $is_optional_value;
                    $this->db->insert('mark', $arrayMarks);
                }
            }
            $success_count++;
        }
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        if ($success_count > 0) {
            set_alert('success', $success_count . ' marks imported successfully');
        } else {
            set_alert('error', 'No marks were imported');
        }
        if ($error_count > 0) {
            $this->session->set_userdata('import_errors', $errors);
        }
        redirect(current_url());
    }

    public function download_marks_template()
    {
        if (!get_permission('exam_mark', 'is_add')) {
            ajax_access_denied();
        }
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        $subjectID = $this->input->post('subject_id');
        $examID = $this->input->post('exam_id');
        $branchID = $this->application_model->get_branch_id();
        $sessionID = get_session_id();
        if (empty($classID) || empty($sectionID) || empty($subjectID) || empty($examID)) {
            echo json_encode(array('status' => 'error', 'message' => 'All fields are required'));
            exit;
        }
        $timetable_detail = $this->exam_model->getTimetableDetail($classID, $sectionID, $examID, $subjectID);
        if (empty($timetable_detail)) {
            echo json_encode(array('status' => 'error', 'message' => 'No timetable found for this configuration. Please setup exam timetable first.'));
            exit;
        }
        $this->db->select('en.student_id, en.roll, st.first_name, st.last_name, st.register_no, m.mark as get_mark, IFNULL(m.absent, 0) as get_abs, IFNULL(m.is_optional, 0) as is_optional');
        $this->db->from('enroll as en');
        $this->db->join('student as st', 'st.id = en.student_id', 'inner');
        $this->db->join('mark as m', 'm.student_id = en.student_id and m.class_id = en.class_id and m.section_id = en.section_id and m.exam_id = ' . $this->db->escape($examID) . ' and m.subject_id = ' . $this->db->escape($subjectID), 'left');
        $this->db->where('en.class_id', $classID);
        $this->db->where('en.section_id', $sectionID);
        $this->db->where('en.branch_id', $branchID);
        $this->db->where('en.session_id', $sessionID);
        $this->db->where('st.active', 1);
        $this->db->order_by('en.roll', 'ASC');
        $students = $this->db->get()->result_array();
        if (empty($students)) {
            echo json_encode(array('status' => 'error', 'message' => 'No students enrolled in this class and section (Branch: ' . $branchID . ', Session: ' . $sessionID . ')'));
            exit;
        }
        require_once FCPATH . 'vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Student ID');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->setCellValue('C1', 'Register No');
        $sheet->setCellValue('D1', 'Roll');
        $sheet->setCellValue('E1', 'Is Absent (Yes/No)');
        $sheet->setCellValue('F1', 'Optional (Yes/No)');
        $col_letter = 'G';
        $distributions = json_decode($timetable_detail['mark_distribution'], true);
        foreach ($distributions as $dist_id => $dist_data) {
            $dist_name = get_type_name_by_id('exam_mark_distribution', $dist_id);
            $sheet->setCellValue($col_letter . '1', $dist_name . ' (Max: ' . $dist_data['full_mark'] . ')');
            $col_letter++;
        }
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:' . chr(ord($col_letter) - 1) . '1')->applyFromArray($headerStyle);
        $row = 2;
        foreach ($students as $student) {
            $sheet->setCellValue('A' . $row, $student['student_id']);
            $sheet->setCellValue('B' . $row, $student['first_name'] . ' ' . $student['last_name']);
            $sheet->setCellValue('C' . $row, $student['register_no']);
            $sheet->setCellValue('D' . $row, $student['roll']);
            $sheet->setCellValue('E' . $row, isset($student['get_abs']) && $student['get_abs'] == '1' ? 'Yes' : 'No');
            $sheet->setCellValue('F' . $row, isset($student['is_optional']) && $student['is_optional'] == '1' ? 'Yes' : 'No');
            $getDetails = array();
            if (!empty($student['get_mark'])) {
                $getDetails = json_decode($student['get_mark'], true);
            }
            $col_letter = 'G';
            foreach ($distributions as $dist_id => $dist_data) {
                $existMark = isset($getDetails[$dist_id]) ? $getDetails[$dist_id] : '';
                $sheet->setCellValue($col_letter . $row, $existMark);
                $col_letter++;
            }
            $row++;
        }
        foreach (range('A', chr(ord($col_letter) - 1)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $class_name = get_type_name_by_id('class', $classID);
        $section_name = get_type_name_by_id('section', $sectionID);
        $subject_name = get_type_name_by_id('subject', $subjectID);
        $exam_details = $this->exam_model->getExamByID($examID);
        $term_name = isset($exam_details->term_name) ? $exam_details->term_name : 'exam';
        $filename = slugify($class_name) . '_' . slugify($section_name) . '_' . slugify($subject_name) . '_' . slugify($term_name) . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $temp_file = FCPATH . 'uploads/temp/' . $filename;
        if (!is_dir(FCPATH . 'uploads/temp/')) {
            mkdir(FCPATH . 'uploads/temp/', 0777, true);
        }
        $writer->save($temp_file);
        echo json_encode(array(
            'status' => 'success',
            'filename' => $filename,
            'download_url' => base_url('uploads/temp/' . $filename)
        ));
    }
}
