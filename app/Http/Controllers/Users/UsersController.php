<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Job\Application;
use App\Models\Job\JobSaved;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{

    public function profile() {

        $profile = User::find(Auth::user()->id);

        return view('users.profile', compact('profile'));
    }

    public function applications() {

        $applications = Application::where('user_id', '=', Auth::user()->id)
        ->get();

        return view('users.applications', compact('applications'));
    }

    public function savedJobs() {

        $savedJobs = JobSaved::where('user_id', '=', Auth::user()->id)
        ->get();

        return view('users.savedjobs', compact('savedJobs'));
    }

    public function editDetails() {

        $userDetails = User::find(Auth::user()->id);

        return view('users.editdetails', compact('userDetails'));
    }

    public function updateDetails(Request $request) {

        Request()->validate([
            "name" => "required|max:40",
            "job_title" => "required|max:40",
            "bio" => "required",
            "facebook" => "required|max:140",
            "twitter" => "required|max:140",
            "linkedin" => "required|max:140",
        ]);

        $userDetailsUpdate = User::find(Auth::user()->id);
        $userDetailsUpdate->update([
            "name" => $request->name,
            "job_title" => $request->job_title,
            "bio" => $request->bio,
            "facebook" => $request->facebook,
            "twitter" => $request->twitter,
            "linkedin" => $request->linkedin,
        ]);

        if($userDetailsUpdate) {
            return redirect('/users/edit-details/')->with('update', 'User details updated successfully');
        }
    }

    public function editCV() {

        return view('users.editcv');
    }

    public function updateCV(Request $request, $id) {
        // Find the user by ID
        $oldCV = User::find($id);
    
        // Check if the user is found
        if ($oldCV) {
            // Construct the path to the existing CV file
            $path = 'assets/cvs/' . $oldCV->cv;
    
            // Check if the file exists in the storage disk
            if (Storage::exists($path)) {
                // Delete the existing file
                Storage::delete($path);
            } else {
                // File does not exist
                // You can handle this case if needed
            }
    
            // Set the destination path for the new CV
            $destinationPath = 'assets/cv';
    
            // Get the original name of the uploaded file
            $mycv = $request->cv->getClientOriginalName();
    
            // Move the uploaded file to the destination path with the original name
            $request->cv->storeAs($destinationPath, $mycv);
    
            // Update the user's CV property
            $oldCV->update([
                "cv" => $mycv
            ]);
    
            return redirect('/users/profile/')->with('update', 'CV updated Successfully');
        } else {
            // User not found, handle this case as needed
            return redirect('/users/profile/')->with('error', 'User not found');
        }
    }
    

    

}
