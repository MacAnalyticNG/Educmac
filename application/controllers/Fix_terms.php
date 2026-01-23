<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fix Missing Terms Controller
 *
 * This controller fixes sessions that don't have all 3 terms
 * Access via: yoursite.com/fix_terms
 */
class Fix_terms extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Only allow superadmin access
        if (!is_superadmin_loggedin()) {
            show_error('Access Denied: Only superadmin can run this tool.', 403, 'Access Denied');
        }
    }

    /**
     * Main method - Check and fix all sessions
     */
    public function index()
    {
        echo "<html><head><title>Fix Missing Terms</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; max-width: 1000px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #333; border-bottom: 3px solid #2196F3; padding-bottom: 15px; }
            h2 { color: #2196F3; margin-top: 30px; border-left: 4px solid #2196F3; padding-left: 10px; }
            .success { color: #4CAF50; padding: 10px; background: #E8F5E9; border-left: 4px solid #4CAF50; margin: 10px 0; }
            .error { color: #f44336; padding: 10px; background: #FFEBEE; border-left: 4px solid #f44336; margin: 10px 0; }
            .warning { color: #FF9800; padding: 10px; background: #FFF3E0; border-left: 4px solid #FF9800; margin: 10px 0; }
            .info { color: #2196F3; padding: 10px; background: #E3F2FD; border-left: 4px solid #2196F3; margin: 10px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #2196F3; color: white; }
            tr:hover { background: #f5f5f5; }
            .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
            .badge-success { background: #4CAF50; color: white; }
            .badge-warning { background: #FF9800; color: white; }
            .badge-danger { background: #f44336; color: white; }
            .btn { display: inline-block; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
            .btn:hover { background: #1976D2; }
        </style></head><body><div class='container'>";

        echo "<h1>🔧 Fix Missing Terms Tool</h1>";

        // Check if academic_terms table exists
        if (!$this->db->table_exists('academic_terms')) {
            echo "<div class='error'><strong>ERROR:</strong> academic_terms table does not exist! Please run the migration first.</div>";
            echo "<a href='" . base_url('sessions') . "' class='btn'>Go Back</a>";
            echo "</div></body></html>";
            return;
        }

        // Get all sessions
        $sessions = $this->db->order_by('school_year', 'DESC')->get('schoolyear')->result();

        if (empty($sessions)) {
            echo "<div class='warning'>No sessions found in the database.</div>";
            echo "</div></body></html>";
            return;
        }

        // Get all branches
        $branches = [];
        if ($this->db->table_exists('branch')) {
            $branch_query = $this->db->select('id, name')->where('status', 1)->get('branch');
            if ($branch_query && $branch_query->num_rows() > 0) {
                $branches = $branch_query->result_array();
            }
        }

        if (empty($branches)) {
            $branches = [['id' => 1, 'name' => 'Main Branch']];
        }

        echo "<div class='info'><strong>Found:</strong> " . count($sessions) . " session(s) and " . count($branches) . " active branch(es)</div>";

        // Check each session
        echo "<h2>Session Analysis</h2>";
        echo "<table>";
        echo "<tr><th>Session</th><th>Branch</th><th>Terms Count</th><th>Status</th><th>Action</th></tr>";

        $sessions_to_fix = [];

        foreach ($sessions as $session) {
            foreach ($branches as $branch) {
                $branch_id = $branch['id'];
                $branch_name = $branch['name'] ?? 'Branch ' . $branch_id;

                // Count terms for this session/branch
                $term_count = $this->db
                    ->where('session_id', $session->id)
                    ->where('branch_id', $branch_id)
                    ->count_all_results('academic_terms');

                echo "<tr>";
                echo "<td><strong>" . $session->school_year . "</strong></td>";
                echo "<td>" . $branch_name . "</td>";
                echo "<td>" . $term_count . " / 3</td>";

                if ($term_count == 3) {
                    echo "<td><span class='badge badge-success'>✓ Complete</span></td>";
                    echo "<td>-</td>";
                } elseif ($term_count == 0) {
                    echo "<td><span class='badge badge-danger'>✗ No terms</span></td>";
                    echo "<td><a href='" . base_url('fix_terms/fix_session/' . $session->id . '/' . $branch_id) . "' class='btn' style='padding: 5px 10px; font-size: 12px;'>Create All</a></td>";
                    $sessions_to_fix[] = ['session' => $session, 'branch_id' => $branch_id, 'count' => $term_count];
                } else {
                    echo "<td><span class='badge badge-warning'>⚠ Incomplete (" . $term_count . ")</span></td>";
                    echo "<td><a href='" . base_url('fix_terms/fix_session/' . $session->id . '/' . $branch_id) . "' class='btn' style='padding: 5px 10px; font-size: 12px;'>Fix</a></td>";
                    $sessions_to_fix[] = ['session' => $session, 'branch_id' => $branch_id, 'count' => $term_count];
                }

                echo "</tr>";
            }
        }

        echo "</table>";

        if (!empty($sessions_to_fix)) {
            echo "<div class='warning'>";
            echo "<strong>⚠ Found " . count($sessions_to_fix) . " session(s) with missing terms!</strong>";
            echo "</div>";
            echo "<a href='" . base_url('fix_terms/fix_all') . "' class='btn' style='background: #4CAF50;'>✓ Fix All Sessions</a>";
        } else {
            echo "<div class='success'><strong>✓ All sessions have complete terms!</strong></div>";
        }

        echo "<br><a href='" . base_url('sessions') . "' class='btn'>Go to Sessions Page</a>";
        echo "</div></body></html>";
    }

    /**
     * Fix a specific session
     */
    public function fix_session($session_id, $branch_id)
    {
        if (empty($session_id) || empty($branch_id)) {
            show_error('Invalid session or branch ID', 400);
        }

        $session = $this->db->get_where('schoolyear', ['id' => $session_id])->row();

        if (!$session) {
            show_error('Session not found', 404);
        }

        // Parse session year
        $years = [];
        if (strpos($session->school_year, '/') !== false) {
            $years = explode('/', $session->school_year);
        } elseif (strpos($session->school_year, '-') !== false) {
            $years = explode('-', $session->school_year);
        }

        if (count($years) != 2) {
            show_error('Invalid session format: ' . $session->school_year, 400);
        }

        $start_year = trim($years[0]);
        $end_year = trim($years[1]);

        // Create all 3 terms
        $terms = [
            [
                'term_name' => 'First Term',
                'term_order' => 1,
                'start_date' => $start_year . '-09-01',
                'end_date' => $start_year . '-12-15',
                'total_weeks' => 15
            ],
            [
                'term_name' => 'Second Term',
                'term_order' => 2,
                'start_date' => $end_year . '-01-15',
                'end_date' => $end_year . '-04-15',
                'total_weeks' => 13
            ],
            [
                'term_name' => 'Third Term',
                'term_order' => 3,
                'start_date' => $end_year . '-05-01',
                'end_date' => $end_year . '-08-15',
                'total_weeks' => 15
            ]
        ];

        $created = 0;
        foreach ($terms as $term) {
            // Check if term already exists
            $exists = $this->db
                ->where('session_id', $session_id)
                ->where('branch_id', $branch_id)
                ->where('term_order', $term['term_order'])
                ->count_all_results('academic_terms');

            if ($exists == 0) {
                $term_data = [
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
                ];

                $this->db->insert('academic_terms', $term_data);
                $created++;
            }
        }

        set_alert('success', "Created $created term(s) for session {$session->school_year}");
        redirect(base_url('fix_terms'));
    }

    /**
     * Fix all sessions at once
     */
    public function fix_all()
    {
        $sessions = $this->db->order_by('school_year', 'ASC')->get('schoolyear')->result();

        $branches = [];
        if ($this->db->table_exists('branch')) {
            $branch_query = $this->db->select('id')->where('status', 1)->get('branch');
            if ($branch_query && $branch_query->num_rows() > 0) {
                $branches = $branch_query->result_array();
            }
        }

        if (empty($branches)) {
            $branches = [['id' => 1]];
        }

        $total_created = 0;
        $sessions_fixed = 0;

        foreach ($sessions as $session) {
            // Parse session year
            $years = [];
            if (strpos($session->school_year, '/') !== false) {
                $years = explode('/', $session->school_year);
            } elseif (strpos($session->school_year, '-') !== false) {
                $years = explode('-', $session->school_year);
            }

            if (count($years) != 2) {
                continue;
            }

            $start_year = trim($years[0]);
            $end_year = trim($years[1]);

            foreach ($branches as $branch) {
                $branch_id = $branch['id'];

                // Check term count
                $term_count = $this->db
                    ->where('session_id', $session->id)
                    ->where('branch_id', $branch_id)
                    ->count_all_results('academic_terms');

                if ($term_count < 3) {
                    // Create missing terms
                    $terms = [
                        [
                            'term_name' => 'First Term',
                            'term_order' => 1,
                            'start_date' => $start_year . '-09-01',
                            'end_date' => $start_year . '-12-15',
                            'total_weeks' => 15
                        ],
                        [
                            'term_name' => 'Second Term',
                            'term_order' => 2,
                            'start_date' => $end_year . '-01-15',
                            'end_date' => $end_year . '-04-15',
                            'total_weeks' => 13
                        ],
                        [
                            'term_name' => 'Third Term',
                            'term_order' => 3,
                            'start_date' => $end_year . '-05-01',
                            'end_date' => $end_year . '-08-15',
                            'total_weeks' => 15
                        ]
                    ];

                    $created_for_session = 0;
                    foreach ($terms as $term) {
                        // Check if term already exists
                        $exists = $this->db
                            ->where('session_id', $session->id)
                            ->where('branch_id', $branch_id)
                            ->where('term_order', $term['term_order'])
                            ->count_all_results('academic_terms');

                        if ($exists == 0) {
                            $term_data = [
                                'session_id' => $session->id,
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
                            ];

                            $this->db->insert('academic_terms', $term_data);
                            $total_created++;
                            $created_for_session++;
                        }
                    }

                    if ($created_for_session > 0) {
                        $sessions_fixed++;
                    }
                }
            }
        }

        set_alert('success', "✓ Fixed $sessions_fixed session(s), created $total_created term(s)!");
        redirect(base_url('fix_terms'));
    }
}
