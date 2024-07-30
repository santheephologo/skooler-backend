<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Repository\IComplaintRepo;
use Illuminate\Routing\Controller;

class ComplaintController extends Controller
{
    private IComplaintRepo $complaintRepo;

    public function __construct(IComplaintRepo $complaintRepo)
    {
        $this->complaintRepo = $complaintRepo;
    }
    public function fetchUserComplaints($id)
    {
        try {
            $complaints = $this->complaintRepo->FetchUserComplaints($id);
            return response()->json(['complaints' => $complaints], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function lodgeComplaint(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'order_id' => 'required|exists:sales_history,id',
                'user_id' => 'required|exists:users,id',
                'product_id' => 'required|exists:products,id',
                'product_name' => 'required|string',
                'qty' => 'required|integer',
                'type' => 'required|string',
                'description' => 'required|string',
                'status' => 'required|string',
                'images' => 'nullable|json',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $validatedData = $validator->validated();
                $response = $this->complaintRepo->LodgeComplaint($validatedData);
                if ($response === true) {
                    return response()->json(['message' => 'Complaint lodged', 'status' => 201], 201);
                } else if ($response === "exists") {
                    return response()->json(['message' => 'already exists', 'status' => 422], 400);
                } else {
                    return response()->json(['message' => 'something went wrong', 'status' => 500], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function changeComplaintStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'user_id' => 'required|exists:users,id',
                'id' => 'required|exists:complaints,id',
                'status' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $validatedData = $validator->validated();
                $response = $this->complaintRepo->UpdateComplaint($validatedData);
                if ($response) {
                    return response()->json(['message' => 'Complaint updated'], 200);
                } else {
                    return response()->json(['message' => 'complaint not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function deleteComplaint($complaintId)
    {
        try {
            $response = $this->complaintRepo->DeleteComplaint($complaintId);
            if ($response) {
                return response()->json(['message' => 'Complaint deleted'], 200);
            } else {
                return response()->json(['error' => $response], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function fetchComplaints($id)
    {
        try {
            $complaints = $this->complaintRepo->FetchComplaints($id);
            if ($complaints) {
                return response()->json(['complaints' => $complaints], 200);
            } else {
                return response()->json(['error' => $complaints], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
}
