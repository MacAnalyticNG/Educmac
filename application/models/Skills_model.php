<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Skills Model
 *
 * Handles database operations for the Skills Assessment Module
 * (Affective/Psychomotor/Cognitive Skills for Junior Report Cards)
 *
 * @package     Academium-extes
 * @subpackage  Models
 * @category    Skills
 */
class Skills_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =====================================================
    // SKILLS CATEGORIES METHODS
    // =====================================================

    /**
     * Get all skills categories
     *
     * @param int|null $branch_id Optional branch filter
     * @param string|null $status Optional status filter (active/inactive)
     * @param string|null $class_level Optional class level filter
     * @return array
     */
    public function getCategories($branch_id = null, $status = null, $class_level = null)
    {
        $this->db->select('*');
        $this->db->from('skills_categories');

        if ($branch_id !== null) {
            $this->db->where('branch_id', $branch_id);
        }

        if ($status !== null) {
            $this->db->where('status', $status);
        }

        if ($class_level !== null) {
            $this->db->where('class_level', $class_level);
        }

        $this->db->order_by('type', 'ASC');
        $this->db->order_by('name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get single category by ID
     *
     * @param int $id Category ID
     * @return array|null
     */
    public function getCategoryById($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('skills_categories')->row_array();
    }

    /**
     * Save (insert or update) skills category
     *
     * @param array $data Category data
     * @param int|null $id Category ID for update
     * @return int|bool Insert ID or update result
     */
    public function saveCategory($data, $id = null)
    {
        if ($id === null) {
            // Insert
            $this->db->insert('skills_categories', $data);
            return $this->db->insert_id();
        } else {
            // Update
            $this->db->where('id', $id);
            return $this->db->update('skills_categories', $data);
        }
    }

    /**
     * Delete skills category
     *
     * @param int $id Category ID
     * @return bool
     */
    public function deleteCategory($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('skills_categories');
    }

    // =====================================================
    // SKILLS ITEMS METHODS
    // =====================================================

    /**
     * Get all skills items
     *
     * @param int|null $branch_id Optional branch filter (or category_id if second param is null)
     * @param string|null $status Optional status filter
     * @return array
     */
    public function getItems($branch_id = null, $status = null)
    {
        $this->db->select('si.*, sc.name as category_name, sc.type as category_type, sc.class_level');
        $this->db->from('skills_items si');
        $this->db->join('skills_categories sc', 'si.category_id = sc.id', 'left');

        if ($branch_id !== null) {
            $this->db->where('sc.branch_id', $branch_id);
        }

        if ($status !== null) {
            $this->db->where('si.status', $status);
        }

        $this->db->order_by('si.category_id', 'ASC');
        $this->db->order_by('si.display_order', 'ASC');
        $this->db->order_by('si.item_name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get single skill item by ID
     *
     * @param int $id Item ID
     * @return array|null
     */
    public function getItemById($id)
    {
        $this->db->select('si.*, sc.name as category_name, sc.type as category_type');
        $this->db->from('skills_items si');
        $this->db->join('skills_categories sc', 'si.category_id = sc.id', 'left');
        $this->db->where('si.id', $id);

        return $this->db->get()->row_array();
    }

    /**
     * Save (insert or update) skills item
     *
     * @param array $data Item data
     * @param int|null $id Item ID for update
     * @return int|bool Insert ID or update result
     */
    public function saveItem($data, $id = null)
    {
        if ($id === null) {
            // Insert
            $this->db->insert('skills_items', $data);
            return $this->db->insert_id();
        } else {
            // Update
            $this->db->where('id', $id);
            return $this->db->update('skills_items', $data);
        }
    }

    /**
     * Delete skills item
     *
     * @param int $id Item ID
     * @return bool
     */
    public function deleteItem($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('skills_items');
    }

    /**
     * Get maximum display order for items
     *
     * @param int|null $branch_id Optional branch filter
     * @return int
     */
    public function getMaxDisplayOrder($branch_id = null)
    {
        $this->db->select_max('si.display_order');
        $this->db->from('skills_items si');

        if ($branch_id !== null) {
            $this->db->join('skills_categories sc', 'si.category_id = sc.id', 'left');
            $this->db->where('sc.branch_id', $branch_id);
        }

        $result = $this->db->get()->row_array();
        return (int) ($result['display_order'] ?? 0);
    }

    // =====================================================
    // SKILLS RATINGS METHODS
    // =====================================================

    /**
     * Get all skills ratings
     *
     * @param int|null $branch_id Optional branch filter
     * @param string|null $status Optional status filter
     * @return array
     */
    public function getRatings($branch_id = null, $status = null)
    {
        $this->db->select('*');
        $this->db->from('skills_ratings');

        if ($branch_id !== null) {
            $this->db->where('branch_id', $branch_id);
        }

        if ($status !== null) {
            $this->db->where('status', $status);
        }

        $this->db->order_by('numeric_value', 'DESC');
        $this->db->order_by('display_order', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get single rating by ID
     *
     * @param int $id Rating ID
     * @return array|null
     */
    public function getRatingById($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('skills_ratings')->row_array();
    }

    /**
     * Save (insert or update) skills rating
     *
     * @param array $data Rating data
     * @param int|null $id Rating ID for update
     * @return int|bool Insert ID or update result
     */
    public function saveRating($data, $id = null)
    {
        if ($id === null) {
            // Insert
            $this->db->insert('skills_ratings', $data);
            return $this->db->insert_id();
        } else {
            // Update
            $this->db->where('id', $id);
            return $this->db->update('skills_ratings', $data);
        }
    }

    /**
     * Delete skills rating
     *
     * @param int $id Rating ID
     * @return bool
     */
    public function deleteRating($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('skills_ratings');
    }

    // =====================================================
    // STUDENT SKILLS RATINGS METHODS
    // =====================================================

    /**
     * Get student skills ratings
     *
     * @param array $filters Array of filters (student_id, exam_id, term_id, class_id, etc.)
     * @return array
     */
    public function getStudentRatings($filters = [])
    {
        $this->db->select('ssr.*, si.item_name, si.display_order, sc.name as category_name,
                          sc.type as category_type, sr.label as rating_label, sr.numeric_value,
                          sr.description as rating_description, st.name as teacher_name');
        $this->db->from('skills_students_ratings ssr');
        $this->db->join('skills_items si', 'ssr.skill_item_id = si.id', 'left');
        $this->db->join('skills_categories sc', 'si.category_id = sc.id', 'left');
        $this->db->join('skills_ratings sr', 'ssr.rating_id = sr.id', 'left');
        $this->db->join('staff st', 'ssr.teacher_id = st.id', 'left');

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $this->db->where('ssr.' . $key, $value);
            }
        }

        $this->db->order_by('sc.type', 'ASC');
        $this->db->order_by('si.display_order', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get student skills ratings grouped by category
     *
     * @param int $student_id Student ID
     * @param int $exam_id Exam ID
     * @param int $session_id Session ID
     * @return array
     */
    public function getStudentRatingsByCategory($student_id, $exam_id, $session_id)
    {
        $ratings = $this->getStudentRatings([
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'session_id' => $session_id
        ]);

        // Group by category
        $grouped = [];
        foreach ($ratings as $rating) {
            $category_type = $rating['category_type'];
            if (!isset($grouped[$category_type])) {
                $grouped[$category_type] = [
                    'category_name' => $rating['category_name'],
                    'category_type' => $category_type,
                    'items' => []
                ];
            }
            $grouped[$category_type]['items'][] = $rating;
        }

        return $grouped;
    }

    /**
     * Save student skill rating
     *
     * @param array $data Rating data
     * @param int|null $id Rating ID for update
     * @return int|bool Insert ID or update result
     */
    public function saveStudentRating($data, $id = null)
    {
        if ($id === null) {
            // Check if rating already exists
            $existing = $this->db->where([
                'student_id' => $data['student_id'],
                'skill_item_id' => $data['skill_item_id'],
                'exam_id' => $data['exam_id'],
                'term_id' => $data['term_id'],
                'session_id' => $data['session_id']
            ])->get('skills_students_ratings')->row_array();

            if ($existing) {
                // Update existing
                $this->db->where('id', $existing['id']);
                return $this->db->update('skills_students_ratings', $data);
            } else {
                // Insert new
                $this->db->insert('skills_students_ratings', $data);
                return $this->db->insert_id();
            }
        } else {
            // Update
            $this->db->where('id', $id);
            return $this->db->update('skills_students_ratings', $data);
        }
    }

    /**
     * Bulk save student ratings for a class
     *
     * @param array $ratings Array of rating data
     * @return bool
     */
    public function bulkSaveStudentRatings($ratings)
    {
        $this->db->trans_start();

        foreach ($ratings as $rating) {
            $this->saveStudentRating($rating);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Delete student rating
     *
     * @param int $id Rating ID
     * @return bool
     */
    public function deleteStudentRating($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('skills_students_ratings');
    }

    /**
     * Get students for skills rating entry
     *
     * @param int $class_id Class ID
     * @param int $section_id Section ID
     * @param int $session_id Session ID
     * @return array
     */
    public function getStudentsForRating($class_id, $section_id, $session_id)
    {
        $this->db->select('s.id as student_id, s.first_name, s.last_name, s.register_no,
                          e.id as enroll_id, e.roll, e.class_id, e.section_id');
        $this->db->from('enroll e');
        $this->db->join('student s', 'e.student_id = s.id', 'inner');
        $this->db->where('e.class_id', $class_id);
        $this->db->where('e.section_id', $section_id);
        $this->db->where('e.session_id', $session_id);
        $this->db->order_by('e.roll', 'ASC');
        $this->db->order_by('s.first_name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Check if student has been rated for an exam
     *
     * @param int $student_id Student ID
     * @param int $exam_id Exam ID
     * @param int $session_id Session ID
     * @return bool
     */
    public function hasStudentBeenRated($student_id, $exam_id, $session_id)
    {
        $this->db->where('student_id', $student_id);
        $this->db->where('exam_id', $exam_id);
        $this->db->where('session_id', $session_id);
        $count = $this->db->count_all_results('skills_students_ratings');

        return $count > 0;
    }

    /**
     * Get class skills rating summary
     *
     * @param int $class_id Class ID
     * @param int $section_id Section ID
     * @param int $exam_id Exam ID
     * @param int $session_id Session ID
     * @return array
     */
    public function getClassRatingSummary($class_id, $section_id, $exam_id, $session_id)
    {
        $this->db->select('COUNT(DISTINCT ssr.student_id) as rated_count');
        $this->db->from('skills_students_ratings ssr');
        $this->db->where('ssr.class_id', $class_id);
        $this->db->where('ssr.section_id', $section_id);
        $this->db->where('ssr.exam_id', $exam_id);
        $this->db->where('ssr.session_id', $session_id);

        $result = $this->db->get()->row_array();

        // Get total students
        $this->db->where('class_id', $class_id);
        $this->db->where('section_id', $section_id);
        $this->db->where('session_id', $session_id);
        $total_students = $this->db->count_all_results('enroll');

        return [
            'rated_count' => $result['rated_count'] ?? 0,
            'total_students' => $total_students,
            'pending_count' => $total_students - ($result['rated_count'] ?? 0)
        ];
    }

    /**
     * Get student remarks (teacher and head teacher)
     *
     * @param int $student_id Student ID
     * @param int $exam_id Exam ID
     * @param int $session_id Session ID
     * @return array Array with 'teacher_remarks' and 'head_teacher_remarks' keys
     */
    public function getStudentRemarks($student_id, $exam_id, $session_id)
    {
        $this->db->select('teacher_remarks, head_teacher_remarks');
        $this->db->from('skills_students_ratings');
        $this->db->where('student_id', $student_id);
        $this->db->where('exam_id', $exam_id);
        $this->db->where('session_id', $session_id);
        $this->db->limit(1);

        $result = $this->db->get()->row_array();

        return [
            'teacher_remarks' => $result['teacher_remarks'] ?? '',
            'head_teacher_remarks' => $result['head_teacher_remarks'] ?? ''
        ];
    }
}
