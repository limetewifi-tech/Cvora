<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitException;
use App\Models\Resume;
use App\Models\ResumeVersion;
use App\Services\Billing\QuotaService;
use App\Services\Resume\ResumeWriter;
use Illuminate\Http\Request;

class ResumeVersionController extends Controller
{
    public function index(Request $request, Resume $resume, QuotaService $quota)
    {
        $this->authorize('view', $resume);

        return view('resumes.versions', [
            'resume' => $resume,
            'versions' => $resume->versions()->latest()->get(),
            'historyEnabled' => $quota->allows($request->user(), 'version_history'),
        ]);
    }

    public function store(Request $request, Resume $resume, ResumeWriter $writer)
    {
        $this->authorize('update', $resume);
        $data = $request->validate(['note' => ['nullable', 'string', 'max:160']]);

        try {
            $writer->snapshot($resume, 'manual', $data['note'] ?? 'Version manuelle');
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        return back()->with('success', 'Version enregistrée.');
    }

    public function restore(Request $request, Resume $resume, ResumeVersion $version, ResumeWriter $writer)
    {
        $this->authorize('update', $resume);
        $writer->restore($resume, $version);

        return redirect()->route('cvs.edit', $resume)->with('success', 'La version a été restaurée. L\'état précédent a été conservé.');
    }
}
