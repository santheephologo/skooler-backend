<?php

namespace App\Repository;

use Illuminate\Http\Request;

interface IComplaintRepo
{
    public function FetchComplaints($id);
    public function FetchUserComplaints($id);
    public function LodgeComplaint($validatedData);
    public function UpdateComplaint($validatedData);
    public function DeleteComplaint($complaintId);
}
