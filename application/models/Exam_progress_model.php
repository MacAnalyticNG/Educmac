<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Exam_progress_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function ordinal($number)
    {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        } else {
            return $number . $ends[$number % 10];
        }
    }

    public function getExamTotalMark($studentID, $sessionID, $subjectID = '', $examID = '', $class_id = '', $section_id = '')
    {
        $this->db->select('m.mark as get_mark,IFNULL(m.absent, 0) as get_abs,te.mark_distribution');
        $this->db->from('mark as m');
        $this->db->join('timetable_exam as te', 'te.exam_id = m.exam_id and te.class_id = m.class_id and te.section_id = m.section_id and te.subject_id = m.subject_id', 'left');
        $this->db->join('exam as e', 'e.id = m.exam_id', 'inner');
        $this->db->where('m.exam_id', $examID);
        $this->db->where('m.student_id', $studentID);
        $this->db->where('m.class_id', $class_id);
        $this->db->where('m.section_id', $section_id);
        $this->db->where('m.session_id', $sessionID);
        $this->db->where('m.subject_id', $subjectID);
        $getMarksList = $this->db->get()->row_array();
        $grand_obtain_marks = 0;
        $grand_full_marks = 0;
        if (!empty($getMarksList)) {
            $fullMarkDistribution = json_decode($getMarksList['mark_distribution'], true);
            $obtainedMark = json_decode($getMarksList['get_mark'], true);
            $total_obtain_marks = 0;
            $total_full_marks = 0;
            foreach ($fullMarkDistribution as $i => $val) {
                $obtained_mark = floatval($obtainedMark[$i]);
                $fullMark = floatval($val['full_mark']);
                if ($getMarksList['get_abs'] != 'on') {
                    $total_full_marks += $fullMark;
                    $total_obtain_marks += $obtained_mark;
                }
            }
            $grand_obtain_marks += $total_obtain_marks;
            $grand_full_marks += $total_full_marks;
        }
        if (!empty($grand_obtain_marks) || !empty($grand_full_marks)) {
            return ['grand_obtain_marks' => $grand_obtain_marks, 'grand_full_marks' => $grand_full_marks];
        } else {
            return ['grand_obtain_marks' => 0, 'grand_full_marks' => 0];
        }
    }

    public function getClassAverage($examID, $sessionID, $subjectID = '')
    {
        $this->db->select('m.mark as get_mark,IFNULL(m.absent, 0) as get_abs');
        $this->db->from('mark as m');
        if (is_array($examID)) {
            $this->db->where_in('m.exam_id', $examID);
        } else {
            $this->db->where('m.exam_id', $examID);
        }
        $this->db->where('m.session_id', $sessionID);
        $this->db->where('m.subject_id', $subjectID);
        $getMarksList = $this->db->get()->result_array();
        $count = count($getMarksList);
        $grand_obtain_marks = 0;
        foreach ($getMarksList as $row) {
            $obtainedMark = json_decode($row['get_mark'], true);
            $total_obtain_marks = 0;
            foreach ($obtainedMark as $i => $val) {
                $obtained_mark = floatval($obtainedMark[$i]);
                if ($row['get_abs'] != 'on') {
                    $total_obtain_marks += $obtained_mark;
                }
            }
            $grand_obtain_marks += $total_obtain_marks;
        }
        if (!empty($grand_obtain_marks)) {
            $grand_percentage = $grand_obtain_marks / $count;
        } else {
            $grand_percentage = 0;
        }

        $cumulative = number_format($grand_percentage, 2, '.', '');
        return $cumulative . "";
    }

    public function getSubjectPosition($classID = '', $sectionID = '', $examID = [], $sessionID = '', $subjectID = '', $mark = 0)
    {
        $this->db->select('student_id as id');
        $this->db->where('class_id', $classID);
        $this->db->where('section_id', $sectionID);
        $this->db->where('session_id', $sessionID);
        $enroll = $this->db->get('enroll')->result();
        $grand_obtain_marks = [];
        foreach ($enroll as $key => $value) {
            $this->db->select('m.mark as get_mark,IFNULL(m.absent, 0) as get_abs');
            $this->db->from('mark as m');
            $this->db->where_in('m.exam_id', $examID);
            $this->db->where('m.student_id', $value->id);
            $this->db->where('m.session_id', $sessionID);
            $this->db->where('m.subject_id', $subjectID);
            $getMarksList = $this->db->get()->result_array();
            $obtain_marks = 0;
            foreach ($getMarksList as $row) {
                if (!empty($row['get_mark'])) {
                    $obtainedMark = json_decode($row['get_mark'], true);
                    $total_obtain_marks = 0;
                    if (is_array($obtainedMark)) {
                        foreach ($obtainedMark as $i => $val) {
                            $obtained_mark = floatval($obtainedMark[$i]);
                            if ($row['get_abs'] != 'on') {
                                $obtain_marks += $obtained_mark;
                            }
                        }
                    }

                }
            }
            $grand_obtain_marks[] = $obtain_marks;
        }
        array_multisort($grand_obtain_marks, SORT_DESC, $grand_obtain_marks);
        $f = array_keys($grand_obtain_marks, $mark);
        if (empty($f)) {
            return 'N/A';
        } else {
            return $this->ordinal($f[0] + 1);
        }
    }

    public function get_grade($mark, $branch_id)
    {
        $this->db->where('branch_id', $branch_id);
        $query = $this->db->get('grade');
        $grades = $query->result_array();
        foreach ($grades as $row) {
            if ($mark >= $row['lower_mark'] && $mark <= $row['upper_mark']) {
                return $row;
            }
        }
    }

    public function get_grade_scale($branch_id)
    {
        $this->db->where('branch_id', $branch_id);
        $this->db->order_by('upper_mark', 'DESC');
        $query = $this->db->get('grade');
        return $query->result_array();
    }

    public function getGrandClassAverage($examID, $sessionID)
    {
        $this->db->select('m.mark as get_mark,IFNULL(m.absent, 0) as get_abs');
        $this->db->from('mark as m');
        if (is_array($examID)) {
            $this->db->where_in('m.exam_id', $examID);
        } else {
            $this->db->where('m.exam_id', $examID);
        }
        $this->db->where('m.session_id', $sessionID);
        $getMarksList = $this->db->get()->result_array();

        if (empty($getMarksList)) {
            return 0;
        }

        $count = count($getMarksList);
        $grand_obtain_marks = 0;

        foreach ($getMarksList as $row) {
            $obtainedMark = json_decode($row['get_mark'], true);
            $total_obtain_marks = 0;
            if (is_array($obtainedMark)) {
                foreach ($obtainedMark as $i => $val) {
                    $obtained_mark = floatval($obtainedMark[$i]);
                    if ($row['get_abs'] != 'on') {
                        $total_obtain_marks += $obtained_mark;
                    }
                }
            }
            $grand_obtain_marks += $total_obtain_marks;
        }

        if (!empty($grand_obtain_marks)) {
            $grand_percentage = $grand_obtain_marks / $count;
        } else {
            $grand_percentage = 0;
        }

        return $grand_percentage;
    }

    public function getOverallPosition($classID = '', $sectionID = '', $examID = [], $sessionID = '', $mark = 0)
    {
        $this->db->select('student_id as id');
        $this->db->where('class_id', $classID);
        $this->db->where('section_id', $sectionID);
        $this->db->where('session_id', $sessionID);
        $enroll = $this->db->get('enroll')->result();

        $student_marks = [];
        foreach ($enroll as $key => $value) {
            $this->db->select('m.mark as get_mark, m.subject_id, IFNULL(m.absent, 0) as get_abs');
            $this->db->from('mark as m');
            if (is_array($examID)) {
                $this->db->where_in('m.exam_id', $examID);
            } else {
                $this->db->where('m.exam_id', $examID);
            }
            $this->db->where('m.student_id', $value->id);
            $this->db->where('m.session_id', $sessionID);
            $getMarksList = $this->db->get()->result_array();

            $total_obtain_marks = 0;
            foreach ($getMarksList as $row) {
                if (!empty($row['get_mark'])) {
                    $obtainedMark = json_decode($row['get_mark'], true);
                    if (is_array($obtainedMark)) {
                        foreach ($obtainedMark as $i => $val) {
                            $obtained_mark = floatval($obtainedMark[$i]);
                            if ($row['get_abs'] != 'on') {
                                $total_obtain_marks += $obtained_mark;
                            }
                        }
                    }
                }
            }
            $student_marks[] = $total_obtain_marks;
        }

        // Sort marks in descending order
        arsort($student_marks);

        // Find position
        $position = array_search($mark, $student_marks);
        if ($position === false) {
            return 'N/A';
        }

        return $this->ordinal($position + 1);
    }

    public function getStudentAttendancePercentage($studentID, $sessionID)
    {
        // Get the student's enroll_id
        $this->db->select('id as enroll_id');
        $this->db->from('enroll');
        $this->db->where('student_id', $studentID);
        $this->db->where('session_id', $sessionID);
        $enroll = $this->db->get()->row();

        if (empty($enroll)) {
            return '0.0';
        }

        $enrollID = $enroll->enroll_id;

        // Get school year to extract year
        $schoolYear = get_type_name_by_id('schoolyear', $sessionID, 'school_year');
        $year = explode('-', $schoolYear);
        $academicYear = $year[0];

        // Get total working days
        $getTotalWorking = $this->db->where(array('enroll_id' => $enrollID, 'year(date)' => $academicYear))->get('student_attendance')->num_rows();

        if ($getTotalWorking == 0) {
            return '0.0';
        }

        // Get total days attended (Present)
        $getTotalAttendance = $this->db->where(array('enroll_id' => $enrollID, 'status' => 'P', 'year(date)' => $academicYear))->get('student_attendance')->num_rows();

        // Calculate percentage
        $percentage = ($getTotalAttendance * 100) / $getTotalWorking;
        return number_format($percentage, 1, '.', '');
    }

    public function getStudentReportCard($studentID = "", $sessionID = "", $class_id = "", $section_id = "")
    {
        $result = array();
        $this->db->select('s.*,CONCAT_WS(" ",s.first_name, s.last_name) as name,e.id as enrollID,e.roll,e.branch_id,e.session_id,e.class_id,e.section_id,c.name as class,se.name as section,sc.name as category,IFNULL(p.father_name,"N/A") as father_name,IFNULL(p.mother_name,"N/A") as mother_name,br.name as institute_name,br.email as institute_email,br.address as institute_address,br.mobileno as institute_mobile_no');
        $this->db->from('enroll as e');
        $this->db->join('student as s', 'e.student_id = s.id', 'left');
        $this->db->join('class as c', 'e.class_id = c.id', 'left');
        $this->db->join('section as se', 'e.section_id = se.id', 'left');
        $this->db->join('student_category as sc', 's.category_id=sc.id', 'left');
        $this->db->join('parent as p', 'p.id=s.parent_id', 'left');
        $this->db->join('branch as br', 'br.id = e.branch_id', 'left');
        $this->db->where('e.student_id', $studentID);
        $this->db->where('e.session_id', $sessionID);
        $this->db->where('e.class_id', $class_id);
        $this->db->where('e.section_id', $section_id);
        $result['student'] = $this->db->get()->row_array();
        return $result;
    }
}
