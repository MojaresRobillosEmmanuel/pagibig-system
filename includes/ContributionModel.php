<?php
require_once __DIR__ . '/Model.php';

class ContributionModel extends Model {
    protected $table = 'contributions';

    public function getContributionsByMonthYear($month, $year) {
        return $this->db->fetchAll(
            "SELECT 
                pagibig_no,
                id_no,
                last_name,
                first_name,
                middle_name,
                ee,
                er,
                tin,
                birthdate
            FROM {$this->table}
            WHERE month = ? AND year = ?",
            [$month, $year]
        );
    }

    public function getTotalContributions($month, $year) {
        return $this->db->fetchOne(
            "SELECT 
                SUM(ee) as total_ee,
                SUM(er) as total_er
            FROM {$this->table}
            WHERE month = ? AND year = ?",
            [$month, $year]
        );
    }

    public function saveContribution($data) {
        // Validate required fields
        $required = ['pagibig_no', 'id_no', 'last_name', 'first_name', 'ee', 'er'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        return $this->create($data);
    }
}
