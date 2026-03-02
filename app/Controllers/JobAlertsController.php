<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class JobAlertsController
{
    // show page
    public function index(Request $request, Response $response)
    {
        $response->view('social-candidate/candidatesubscriptions');
    }

    // store form data
    public function store(Request $request, Response $response)
    {
        $db = Database::getInstance();

        $sql = "INSERT INTO job_alerts (
            subject_name,
            notification_email,
            alert_status,
            frequency,
            role_type,
            workplace_option,
            time_commitment,
            role_category,
            minimum_education,
            minimum_experience,
            pay_term,
            minimum_hourly_rate,
            minimum_salary,
            impact_area,
            created_at
        ) VALUES (
            :subject_name,
            :notification_email,
            :alert_status,
            :frequency,
            :role_type,
            :workplace_option,
            :time_commitment,
            :role_category,
            :minimum_education,
            :minimum_experience,
            :pay_term,
            :minimum_hourly_rate,
            :minimum_salary,
            :impact_area,
            :created_at
        )";

        $db->query($sql, [

            'subject_name' => $request->post('subject_name'),
            'notification_email' => $request->post('notification_email'),
            'alert_status' => $request->post('alert_status') ? 1 : 0,
            'frequency' => $request->post('frequency'),

            'role_type' => $request->post('role_type'),
            'workplace_option' => $request->post('workplace_option'),
            'time_commitment' => $request->post('time_commitment'),
            'role_category' => $request->post('role_category'),
            'minimum_education' => $request->post('minimum_education'),
            'minimum_experience' => $request->post('minimum_experience'),

            'pay_term' => $request->post('pay_term'),
            'minimum_hourly_rate' => $request->post('minimum_hourly_rate'),
            'minimum_salary' => $request->post('minimum_salary'),

            'impact_area' => $request->post('impact_area'),

            'created_at' => date('Y-m-d H:i:s')
        ]);

        // redirect back to same page after save
        $response->redirect('/job-alerts');
    }
}
