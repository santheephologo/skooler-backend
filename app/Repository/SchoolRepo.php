<?php

namespace App\Repository;

use App\Models\School;
use App\Models\Admin;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SchoolRepo implements ISchoolRepo
{
    //Fetching all schools
    public function all()
    {
    $schools = School::all();
    $schools->each(function($school) {
        $admin = Admin::firstOrCreate(
            ['school_id' => $school->id],
            ['name' => 'First Admin', 'email' => 'admin@school.com'] 
        );
        $school->admin = $admin;
    });
    return $schools;
    }

    // Storing a new school
    public function store($schoolData)
    {
        $school = School::create($schoolData);
        return  $school ? $school : false;
    }

    public function CheckSchoolID($id)
    {
        $schoolCheck = School::where('id', $id)->first();
        return $schoolCheck == null ? true : false;
    }
    //Fetching a particular school
    public function fetchSchool($id)
    {
        $school = School::findOrFail($id);

        // Checking if subscription_expiry is in the past
        $currentDateTime = now();  // Current date & time

        if (($currentDateTime > $school->subscription_expiry) || !($school->is_active)) {
            // Subscription expired, update is_active to false
            $school->is_active = false;
            $school->save();
            return null;
        } else {
            return $school;
        }
    }
    //will be saved in s3 bucket
    public function AddSchoolLogo(Request $request)
    {
        $path = $request->file('logo')->store(
            'public/logo',
            's3'
        );
        Storage::disk('s3')->setVisibility($path, 'public');

        if ($path) {
            return $path;
        } else {
            return false;
        }
    }
    public function UpdateSchoolLogo(Request $request)
    {
        $school = School::find($request->input('id'));
        //Checking for the existence. If logo is already available, existing one will be deleted
        if ($school->logo) {
            Storage::disk('s3')->delete($school->logo);
        }
        $path = $request->file('logo')->store(
            'public/logo',
            's3'
        );

        Storage::disk('s3')->setVisibility($path, 'public');
        if ($path) {
            $school->logo = $path;
            $school->save();
            return true;
        } else {
            return false;
        }
    }
    public function updateUI($ui, $id)
    {
        try {
            $school = School::findOrFail($id);
            $school->ui = $ui;
            $school->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function updateStatus($is_active, $id)
    {
        try {
            $school = School::findOrFail($id);
            $school->is_active = $is_active;
            $school->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function updateExpiry($subscription_expiry, $id)
    {
        try {
            $school = School::findOrFail($id);
            $school->subscription_expiry = $subscription_expiry;
            $school->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function updateInfo($id, $name, $address,  $email,  $phone, $country, $country_code, $currency, $delivery, $pickup)
    {
        try {
            $school = School::findOrFail($id);
            $school->name = $name;
            $school->address = $address;
            $school->email = $email;
            $school->phone = $phone;
            $school->country = $country;
            $school->country_code = $country_code;
            $school->currency = $currency;
            $school->delivery = $delivery;
            $school->pickup = $pickup;
            $school->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function updateAdmin(Request $request)
    {
        try {
            $admin = Admin::where('school_id', $request->school_id)->first();
            if (!$admin) {
                return false;
            }
            $admin->first_name = $request->first_name;
            $admin->last_name = $request->last_name;
            $admin->email = $request->email;
            $admin->mobile_no = $request->mobile_no;
            if ($request->filled('password')){
                $admin->password = Hash::make($request->password);
            }
            $admin->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function deleteSchool($id)
    {
        try {
            // Find the school by ID
            $school = School::find($id);

            if (!$school) {
                return "School with ID $id not found.";
            }
            // Delete the school
            $school->delete();

            return "School with ID $id deleted successfully.";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
