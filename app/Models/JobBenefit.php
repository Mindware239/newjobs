<?php

class JobBenefit
{
    private $db;
    private $table = "job_benefits";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function deleteByJob($job_id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE job_id = :job_id");
        return $stmt->execute(['job_id' => $job_id]);
    }
}
