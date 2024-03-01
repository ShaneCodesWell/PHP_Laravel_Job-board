<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job\Job;
use App\Models\Category\Category; 
use App\Models\Job\JobSaved;
use App\Models\Job\Application;
use App\Models\Job\Search;
use Illuminate\Support\Facades\Auth;

class JobsController extends Controller
{
    public function single($id) {

        $job = Job::find($id);

        //getting related jobs

        $relatedJobs = Job::where('category', $job->category)
            ->where('id', '!=', $id)
            ->take(5)
            ->get();
            
        $relatedJobsCount = Job::where('category', $job->category)
            ->where('id', '!=', $id)
            ->take(5)
            ->count();

            //Categories
            $categories = Category::all();

            //Save Job

            if(auth()->user()) {
                $savedJob = JobSaved::where('job_id', $id)
            ->where('user_id', Auth::user()->id)
            ->count();

            //verifying if user applied to job
            $appliedJob = Application::where('user_id', Auth::user()->id)
            ->where('job_id', $id)
            ->count();

            return view('jobs.single', compact('job', 'relatedJobs', 'relatedJobsCount', 'savedJob', 'appliedJob', 'categories'));
                }
                else {
                    return view('jobs.single', compact('job', 'relatedJobs', 'relatedJobsCount', 'categories'));
                }
        }

            

    public function saveJob(Request $request) {

        $saveJob = JobSaved::create([
            'job_id' => $request->job_id,
            'user_id' => $request->user_id,
            'image' => $request->image,
            'job_title' => $request->job_title,
            'job_region' => $request->job_region,
            'job_type' => $request->job_type,
            'company' => $request->company,
        ]); 

        if($saveJob) {
            return redirect('/jobs/single/'.$request->job_id.'')->with('save', 'Job Saved successfully');
        }
    }

    public function applyJob(Request $request) {

        if(Auth::user()->cv == 'No CV') {
            return redirect('/jobs/single/'.$request->job_id.'')->with('apply', 'Upload your CV first on the profile page');
        } else {
            $applyJob = Application::create([
                'cv' => Auth::user()->cv,
                'job_id' => $request->job_id,
                'user_id' => Auth::user()->id,
                'email' => Auth::user()->email,
                'image' => $request->image,
                'job_title' => $request->job_title,
                'job_region' => $request->job_region,
                'job_type' => $request->job_type,
                'company' => $request->company,
            ]);

            if($applyJob) {
                return redirect('/jobs/single/'.$request->job_id.'')->with('applied', 'You have applied to this job successfully');
            }
        }
    } 

    public function search(Request $request)
    {
        Request()->validate([
            "job_title" => "required",
            "job_region" => "required",
            "job_type" => "required",
        ]);

        Search::Create([
            "keyword" => $request->job_title
        ]);

        $job_title = $request->get('job_title');
        $job_region = $request->get('job_region');
        $job_type = $request->get('job_type');

        $searches = Job::select()->where('job_title', 'like', "%$job_title%")
         ->where('job_region', 'like', "%$job_region%")
         ->where('job_type', 'like', "%$job_type%")
         ->get();

        return view('jobs.search', compact('searches'));
    }


    
}
