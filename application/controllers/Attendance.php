<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Ramom school management system
 * @version : 6.8
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Attendance.php
 * @copyright : Reserved RamomCoder Team
 */

class Attendance extends Admin_Controller
{
    protected $getAttendanceType;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('attendance_model');
        $this->load->model('subject_model');
        $this->load->model('sms_model');
        if (!moduleIsEnabled('attendance')) {
            access_denied();
        }
        $this->getAttendanceType = $this->app_lib->getAttendanceType();
    }

    public function index()
    {
        if (get_loggedin_id()) {
            redirect(base_url('dashboard'), 'refresh');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    // student submitted attendance all data are prepared and stored in the database here
    public function student_entry()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $termID = get_active_term_id();

        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required');
            $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date|callback_check_term_date');
            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                $date = $this->input->post('date');
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendance_model->getStudentAttendence($classID, $sectionID, $date, $branchID, $termID);
            }
        }
        $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendance_model->getHolidays($branchID);
        $this->data['active_term'] = get_active_term();

        if (isset($_POST['save'])) {
            $attendance = $this->input->post('attendance');
            $date = $this->input->post('date');
            foreach ($attendance as $key => $value) {
                $attStatus = (isset($value['status']) ? $value['status'] : "");
                $studentID = $value['student_id'];
                $arrayAttendance = array(
                    'enroll_id' => $value['enroll_id'],
                    'status' => $attStatus,
                    'remark' => $value['remark'],
                    'date' => $date,
                    'term_id' => $termID,
                    'branch_id' => $branchID,
                );
                if (empty($value['attendance_id'])) {
                    $this->db->insert('student_attendance', $arrayAttendance);
                } else {
                    $this->db->where('id', $value['attendance_id']);
                    $this->db->update('student_attendance', array('status' => $attStatus, 'remark' => $value['remark']));
                }
                // send student absent then sms
                if ($attStatus == 'A') {
                    $arrayAttendance['student_id'] = $studentID;
                    $this->sms_model->send_sms($arrayAttendance, 3);
                }
            }
            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance/student_entries';
        $this->data['main_menu'] = 'attendance';
        $this->load->view('layout/index', $this->data);
    }

    public function getWeekendsHolidays()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            ajax_access_denied();
        }
        if ($_POST) {
            $branchID = $this->input->post('branch_id');
            $getWeekends = $this->application_model->getWeekends($branchID);
            $getHolidays = $this->attendance_model->getHolidays($branchID);
            echo json_encode(['getWeekends' => $getWeekends, 'getHolidays' => '["' . $getHolidays . '"]']);
        }
    }

    // employees submitted attendance all data are prepared and stored in the database here
    public function employees_entry()
    {
        if (!get_permission('employee_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('staff_role', translate('role'), 'required');
            $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date');
            if ($this->form_validation->run() == true) {
                $roleID = $this->input->post('staff_role');
                $date = $this->input->post('date');
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendance_model->getStaffAttendence($roleID, $date, $branchID);
            }
        }
        $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
        if (isset($_POST['save'])) {
            $attendance = $this->input->post('attendance');
            $date = $this->input->post('date');
            foreach ($attendance as $key => $value) {
                $attStatus = (isset($value['status']) ? $value['status'] : "");
                $arrayAttendance = array(
                    'staff_id' => $value['staff_id'],
                    'status' => $attStatus,
                    'remark' => $value['remark'],
                    'date' => $date,
                    'branch_id' => $branchID,
                );
                if (empty($value['attendance_id'])) {
                    $this->db->insert('staff_attendance', $arrayAttendance);
                } else {
                    $this->db->where('id', $value['attendance_id']);
                    $this->db->update('staff_attendance', array('status' => $attStatus, 'remark' => $value['remark']));
                }
                // send student absent then sms
                if ($attStatus == 'A') {
                    $this->sms_model->send_sms($arrayAttendance, 3);
                }
            }
            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('employee_attendance');
        $this->data['sub_page'] = 'attendance/employees_entries';
        $this->data['main_menu'] = 'attendance';
        $this->load->view('layout/index', $this->data);
    }

    // exam submitted attendance all data are prepared and stored in the database here
    public function exam_entry()
    {
        if (!get_permission('exam_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('exam_id', translate('exam'), 'required');
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required');
            $this->form_validation->set_rules('subject_id', translate('subject'), 'required');

            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                $examID = $this->input->post('exam_id');
                $subjectID = $this->input->post('subject_id');
                $this->data['class_id'] = $classID;
                $this->data['section_id'] = $sectionID;
                $this->data['exam_id'] = $examID;
                $this->data['subject_id'] = $subjectID;
                $this->data['attendencelist'] = $this->attendance_model->getExamAttendence($classID, $sectionID, $examID, $subjectID, $branchID);
            }
        }

        if (isset($_POST['save'])) {
            $attendance = $this->input->post('attendance');
            $subjectID = $this->input->post('subject_id');
            $examID = $this->input->post('exam_id');
            foreach ($attendance as $key => $value) {
                $attStatus = (isset($value['status']) ? $value['status'] : "");
                $arrayAttendance = array(
                    'student_id' => $value['student_id'],
                    'status' => $attStatus,
                    'remark' => $value['remark'],
                    'exam_id' => $examID,
                    'subject_id' => $subjectID,
                    'branch_id' => $branchID,
                );
                if (empty($value['attendance_id'])) {
                    $this->db->insert('exam_attendance', $arrayAttendance);
                } else {
                    $this->db->where('id', $value['attendance_id']);
                    $this->db->update('exam_attendance', array('status' => $attStatus, 'remark' => $value['remark']));
                }
                // send student absent then sms
                if ($attStatus == 'A') {
                    $this->sms_model->send_sms($arrayAttendance, 4);
                }
            }
            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('exam_attendance');
        $this->data['sub_page'] = 'attendance/exam_entries';
        $this->data['main_menu'] = 'attendance';
        $this->load->view('layout/index', $this->data);
    }

    // student attendance reports are produced here
    public function studentwise_report()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['month'] = date('m', strtotime($this->input->post('timestamp')));
            $this->data['year'] = date('Y', strtotime($this->input->post('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['studentlist'] = $this->attendance_model->getStudentList($branchID, $this->data['class_id'], $this->data['section_id']);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance/student_report';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function student_classreport()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_get_valid_date');
            if ($this->form_validation->run() == true) {
                $this->data['date'] = $this->input->post('date');
                $this->data['attendancelist'] = $this->attendance_model->getDailyStudentReport($branchID, $this->data['date']);
            }
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student') . ' ' . translate('daily_reports');
        $this->data['sub_page'] = 'attendance/student_classreport';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function studentwise_overview()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('attendance_type', translate('attendance_type'), 'required');
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required');
            $this->form_validation->set_rules('daterange', translate('date'), 'required');

            if ($this->form_validation->run() == true) {
                $daterange = explode(' - ', $this->input->post('daterange'));
                $start = date("Y-m-d", strtotime($daterange[0]));
                $end = date("Y-m-d", strtotime($daterange[1]));

                $this->data['class_id'] = $this->input->post('class_id');
                $this->data['section_id'] = $this->input->post('section_id');
                $this->data['start'] = $start;
                $this->data['end'] = $end;
                $this->data['studentlist'] = $this->application_model->getStudentListByClassSection($this->data['class_id'], $this->data['section_id'], $branchID);
            }
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance/studentwise_overview';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    /* employees attendance reports are produced here */
    public function employeewise_report()
    {
        if (!get_permission('employee_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            $this->data['branch_id'] = $this->application_model->get_branch_id();
            $this->data['role_id'] = $this->input->post('staff_role');
            $this->data['month'] = date('m', strtotime($this->input->post('timestamp')));
            $this->data['year'] = date('Y', strtotime($this->input->post('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['stafflist'] = $this->attendance_model->getStaffList($this->data['branch_id'], $this->data['role_id']);
        }
        $this->data['title'] = translate('employee_attendance');
        $this->data['sub_page'] = 'attendance/employees_report';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    /* student exam attendance reports are produced here */
    public function examwise_report()
    {
        if (!get_permission('exam_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['exam_id'] = $this->input->post('exam_id');
            $this->data['subject_id'] = $this->input->post('subject_id');
            $this->data['branch_id'] = $this->application_model->get_branch_id();
            $this->data['examreport'] = $this->attendance_model->getExamReport($this->data);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('exam_attendance');
        $this->data['sub_page'] = 'attendance/exam_report';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function get_valid_date($date)
    {
        $present_date = date('Y-m-d');
        $date = date("Y-m-d", strtotime($date));
        if ($date > $present_date) {
            $this->form_validation->set_message("get_valid_date", "Please Enter Correct Date");
            return false;
        } else {
            return true;
        }
    }

    public function check_holiday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getHolidays = $this->attendance_model->getHolidays($branchID);
        $getHolidaysArray = explode('","', $getHolidays);

        if (!empty($getHolidaysArray)) {
            if (in_array($date, $getHolidaysArray)) {
                $this->form_validation->set_message('check_holiday', 'You have selected a holiday.');
                return false;
            } else {
                return true;
            }
        }
    }

    public function check_weekendday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getWeekendDays = $this->attendance_model->getWeekendDaysSession($branchID);
        if (!empty($getWeekendDays)) {
            if (in_array($date, $getWeekendDays)) {
                $this->form_validation->set_message('check_weekendday', "You have selected a weekend date.");
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    /**
     * Validate that the selected date falls within the active term's date range
     */
    public function check_term_date($date)
    {
        $active_term = get_active_term();

        if (!$active_term) {
            // No active term found - allow date selection
            return true;
        }

        $selected_date = strtotime($date);
        $term_start = strtotime($active_term->start_date);
        $term_end = strtotime($active_term->end_date);

        if ($selected_date < $term_start || $selected_date > $term_end) {
            $this->form_validation->set_message(
                'check_term_date',
                sprintf(
                    'The selected date must be within the active term (%s: %s to %s).',
                    $active_term->term_name,
                    date('M d, Y', $term_start),
                    date('M d, Y', $term_end)
                )
            );
            return false;
        }

        return true;
    }

    /* attendance import/export page */
    public function export_import()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();

        if ($_POST && isset($_POST['upload'])) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required');

            if (isset($_FILES['excel_file']) && empty($_FILES['excel_file']['name'])) {
                $this->form_validation->set_rules('excel_file', 'Excel File', 'required');
            }

            if ($this->form_validation->run() == true) {
                $this->import_attendance_excel();
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['active_term'] = get_active_term();
        $this->data['title'] = translate('attendance') . " " . translate('import') . "/" . translate('export');
        $this->data['sub_page'] = 'attendance/export_import';
        $this->data['main_menu'] = 'attendance';
        $this->load->view('layout/index', $this->data);
    }

    /* download attendance template */
    public function download_attendance_template()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            ajax_access_denied();
        }

        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        $branchID = $this->application_model->get_branch_id();
        $termID = get_active_term_id();
        $active_term = get_active_term();

        if (!$active_term) {
            echo json_encode(array('status' => 'error', 'message' => 'No active term found'));
            return;
        }

        // Get student list
        $students = $this->attendance_model->getStudentList($branchID, $classID, $sectionID);

        if (empty($students)) {
            echo json_encode(array('status' => 'error', 'message' => translate('no_students_found')));
            return;
        }

        // Get school days (excluding weekends and holidays)
        $schoolDays = $this->get_school_days($active_term->start_date, $active_term->end_date, $branchID);

        // Load PhpSpreadsheet
        require_once APPPATH . 'third_party/vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Student ID');
        $sheet->setCellValue('B1', 'Register No');
        $sheet->setCellValue('C1', 'Roll');
        $sheet->setCellValue('D1', 'Student Name');

        $col = 4; // Start from column E
        foreach ($schoolDays as $day) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, date('d-M-Y', strtotime($day)));
            $col++;
        }

        // Style header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:' . $sheet->getCellByColumnAndRow($col, 1)->getCoordinate())->applyFromArray($headerStyle);

        // Fill student data
        $row = 2;
        foreach ($students as $student) {
            $sheet->setCellValue('A' . $row, $student['enroll_id']);
            $sheet->setCellValue('B' . $row, $student['register_no']);
            $sheet->setCellValue('C' . $row, $student['roll']);
            $sheet->setCellValue('D' . $row, $student['first_name'] . ' ' . $student['last_name']);

            // Set data validation for attendance columns
            $col = 4;
            foreach ($schoolDays as $day) {
                $cellCoord = $sheet->getCellByColumnAndRow($col + 1, $row)->getCoordinate();
                $validation = $sheet->getCell($cellCoord)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Input error');
                $validation->setError('Value must be P, A, L, or HD');
                $validation->setPromptTitle('Attendance Status');
                $validation->setPrompt('Select: P=Present, A=Absent, L=Late, HD=Half Day');
                $validation->setFormulaSqref($cellCoord);
                $validation->setFormula1('"P,A,L,HD"');
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate filename
        $filename = 'Attendance_Template_' . date('Y-m-d_His') . '.xlsx';
        $filepath = FCPATH . 'uploads/csv/' . $filename;

        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);

        echo json_encode(array(
            'status' => 'success',
            'download_url' => base_url('attendance/download_file?file=' . urlencode($filename))
        ));
    }

    /* helper function to get school days */
    private function get_school_days($start_date, $end_date, $branchID)
    {
        $schoolDays = array();
        $weekendDays = $this->attendance_model->getWeekendDaysSession($branchID);
        $holidays = explode('","', $this->attendance_model->getHolidays($branchID));

        $current = strtotime($start_date);
        $end = strtotime($end_date);

        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            if (!in_array($dateStr, $weekendDays) && !in_array($dateStr, $holidays)) {
                $schoolDays[] = $dateStr;
            }
            $current = strtotime('+1 day', $current);
        }

        return $schoolDays;
    }

    /* download generated file */
    public function download_file()
    {
        $file = urldecode($this->input->get('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+\.(xlsx|xls)$/i', $file)) {
            $filepath = FCPATH . 'uploads/csv/' . $file;
            if (file_exists($filepath)) {
                $this->load->helper('download');
                force_download($file, file_get_contents($filepath));
                @unlink($filepath);
            }
        }
    }

    /* import attendance from excel */
    private function import_attendance_excel()
    {
        $branchID = $this->application_model->get_branch_id();
        $termID = get_active_term_id();
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');

        require_once APPPATH . 'third_party/vendor/autoload.php';

        $file = $_FILES['excel_file']['tmp_name'];
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $errors = array();
        $success_count = 0;
        $update_count = 0;

        // Process each student row
        for ($row = 2; $row <= $highestRow; $row++) {
            $enrollID = $sheet->getCellByColumnAndRow(1, $row)->getValue();
            $registerNo = $sheet->getCellByColumnAndRow(2, $row)->getValue();

            if (empty($enrollID)) {
                continue;
            }

            // Process each date column
            for ($col = 5; $col <= $highestColumnIndex; $col++) {
                $dateHeader = $sheet->getCellByColumnAndRow($col, 1)->getValue();
                $status = strtoupper(trim($sheet->getCellByColumnAndRow($col, $row)->getValue()));

                if (empty($status)) {
                    continue;
                }

                // Parse date from header
                $date = date('Y-m-d', strtotime($dateHeader));

                // Validate status
                if (!in_array($status, array('P', 'A', 'L', 'HD'))) {
                    $errors[] = array(
                        'row' => $row,
                        'register_no' => $registerNo,
                        'date' => $date,
                        'error' => 'Invalid status: ' . $status
                    );
                    continue;
                }

                // Check if attendance exists
                $this->db->where('enroll_id', $enrollID);
                $this->db->where('date', $date);
                $this->db->where('term_id', $termID);
                $existingAtt = $this->db->get('student_attendance')->row();

                $arrayAttendance = array(
                    'enroll_id' => $enrollID,
                    'status' => $status,
                    'remark' => '',
                    'date' => $date,
                    'term_id' => $termID,
                    'branch_id' => $branchID,
                );

                if ($existingAtt) {
                    $this->db->where('id', $existingAtt->id);
                    $this->db->update('student_attendance', array('status' => $status));
                    $update_count++;
                } else {
                    $this->db->insert('student_attendance', $arrayAttendance);
                    $success_count++;
                }

                // Send SMS for absent students
                if ($status == 'A') {
                    $student = $this->db->select('student_id')->where('id', $enrollID)->get('enroll')->row();
                    if ($student) {
                        $arrayAttendance['student_id'] = $student->student_id;
                        $this->sms_model->send_sms($arrayAttendance, 3);
                    }
                }
            }
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('import_errors', $errors);
        }

        if ($success_count > 0 || $update_count > 0) {
            $msg = '';
            if ($success_count > 0) {
                $msg .= $success_count . ' attendance record(s) added. ';
            }
            if ($update_count > 0) {
                $msg .= $update_count . ' attendance record(s) updated.';
            }
            set_alert('success', $msg);
        }

        redirect(base_url('attendance/export_import'));
    }
}
