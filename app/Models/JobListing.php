<?php

class JobListing {

    private $db;

    public function __construct($pdo){
        $this->db = $pdo;
    }

    /* ===============================
       CREATE DRAFT (STEP 1)
    =============================== */

    public function createDraft($data){

        $sql = "INSERT INTO job_listings (
            candidate_type,
            organization_selected,
            organization_name,
            organization_type,
            is_agency,
            website,
            ein,
            staff_count,
            mission_focus_area,
            organization_mission,
            organization_impact,
            status
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?, 'draft')";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $data['candidate_type'],
            $data['organization_selected'],
            $data['organization_name'],
            $data['organization_type'],
            $data['is_agency'],
            $data['website'],
            $data['ein'],
            $data['staff_count'],
            $data['mission_focus_area'],
            $data['organization_mission'],
            $data['organization_impact']
        ]);

        return $this->db->lastInsertId(); // important for next steps
    }


    /* ===============================
       UPDATE STEP 2 (ROLE)
    =============================== */

    public function updateRole($id, $data){

        $sql = "UPDATE job_listings SET
            role_name=?,
            time_commitment=?,
            time_details=?,
            work_category=?,
            experience_years=?,
            education_level=?,
            pay_type=?,
            min_pay=?,
            max_pay=?,
            role_mission_focus=?,
            short_description=?,
            full_description=?
        WHERE id=?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['role_name'],
            $data['time_commitment'],
            $data['time_details'],
            $data['work_category'],
            $data['experience_years'],
            $data['education_level'],
            $data['pay_type'],
            $data['min_pay'],
            $data['max_pay'],
            $data['role_mission_focus'],
            $data['short_description'],
            $data['full_description'],
            $id
        ]);
    }


    /* ===============================
       UPDATE STEP 3 (LOCATION)
    =============================== */

    public function updateLocation($id, $data){

        $sql = "UPDATE job_listings SET
            workplace_option=?,
            workplace_details=?,
            job_location=?,
            location_details=?
        WHERE id=?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['workplace_option'],
            $data['workplace_details'],
            $data['job_location'],
            $data['location_details'],
            $id
        ]);
    }


    /* ===============================
       UPDATE STEP 4 (OPTIONS)
    =============================== */

    public function updateOptions($id, $data){

        $sql = "UPDATE job_listings SET
            publish_type=?,
            publish_date=?,
            apply_method=?,
            notification_emails=?,
            screening_questions=?
        WHERE id=?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['publish_type'],
            $data['publish_date'],
            $data['apply_method'],
            json_encode($data['notification_emails']),
            json_encode($data['screening_questions']),
            $id
        ]);
    }


    /* ===============================
       FINAL PUBLISH
    =============================== */

    public function publish($id){

        $sql = "UPDATE job_listings SET status='published' WHERE id=?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }


    /* ===============================
       FETCH SINGLE LISTING
    =============================== */

    public function find($id){

        $stmt = $this->db->prepare("SELECT * FROM job_listings WHERE id=?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* ===============================
       LIST ALL JOBS
    =============================== */

    public function all(){

        $stmt = $this->db->query("SELECT * FROM job_listings ORDER BY created_at DESC");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ===============================
       DELETE JOB
    =============================== */

    public function delete($id){

        $stmt = $this->db->prepare("DELETE FROM job_listings WHERE id=?");

        return $stmt->execute([$id]);
    }

}
