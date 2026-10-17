<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        return view('applications.index', [
            'applications' => $request->user()->applications()->latest()->get(),
            'resumes' => $request->user()->resumes()->orderBy('title')->get(),
            'letters' => $request->user()->coverLetters()->orderBy('title')->get(),
            'offers' => $request->user()->jobOffers()->orderBy('title')->get(),
            'statuses' => ApplicationStatus::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $application = $request->user()->applications()->create($this->validateApplication($request));

        return redirect()->route('applications.index')->with('success', 'Candidature « '.$application->position.' » enregistrée.');
    }

    public function update(Request $request, JobApplication $application)
    {
        $this->authorize('update', $application);
        $application->update($this->validateApplication($request));

        return back()->with('success', 'Candidature mise à jour.');
    }

    public function destroy(JobApplication $application)
    {
        $this->authorize('delete', $application);
        $application->delete();

        return back()->with('success', 'Candidature supprimée.');
    }

    private function validateApplication(Request $request): array
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:160'],
            'position' => ['required', 'string', 'max:160'],
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'notes' => ['nullable', 'string', 'max:4000'],
            'applied_at' => ['nullable', 'date'],
            'resume_id' => ['nullable', 'integer', Rule::exists('resumes', 'id')->where('user_id', $request->user()->id)],
            'cover_letter_id' => ['nullable', 'integer', Rule::exists('cover_letters', 'id')->where('user_id', $request->user()->id)],
            'job_offer_id' => ['nullable', 'integer', Rule::exists('job_offers', 'id')->where('user_id', $request->user()->id)],
        ]);

        return $data;
    }
}
