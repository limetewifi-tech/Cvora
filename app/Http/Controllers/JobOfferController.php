<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\Resume;
use App\Services\Resume\ResumeWriter;
use Illuminate\Http\Request;

class JobOfferController extends Controller
{
    public function index(Request $request)
    {
        return view('jobs.index', [
            'offers' => $request->user()->jobOffers()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:12000'],
            'source_url' => ['nullable', 'url', 'max:255'],
        ]);

        $offer = $request->user()->jobOffers()->create($data);

        return redirect()->route('jobs.show', $offer)->with('success', 'Offre enregistrée. Elle n\'a pas modifié vos CV.');
    }

    public function show(Request $request, JobOffer $offer)
    {
        $this->authorize('view', $offer);

        return view('jobs.show', [
            'offer' => $offer,
            'resumes' => $request->user()->resumes()->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, JobOffer $offer)
    {
        $this->authorize('update', $offer);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:12000'],
            'source_url' => ['nullable', 'url', 'max:255'],
        ]);
        $offer->update($data);

        return back()->with('success', 'Offre mise à jour.');
    }

    public function destroy(JobOffer $offer)
    {
        $this->authorize('delete', $offer);
        $offer->delete();

        return redirect()->route('jobs.index')->with('success', 'Offre supprimée.');
    }

    public function clearAnalysis(JobOffer $offer)
    {
        $this->authorize('update', $offer);
        $offer->forceFill(['analysis' => null])->save();

        return back()->with('success', 'L\'analyse a été retirée. Le CV n\'a pas été modifié.');
    }

    public function adapt(Request $request, JobOffer $offer, ResumeWriter $writer)
    {
        $this->authorize('view', $offer);
        $data = $request->validate(['resume_id' => ['required', 'integer']]);
        $resume = Resume::query()->with('user')->where('user_id', $request->user()->id)->findOrFail($data['resume_id']);

        try {
            $copy = $writer->duplicate($resume);
        } catch (\App\Exceptions\PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        $suggestion = $offer->analysis['summary_proposal'] ?? null;

        return redirect()->route('cvs.edit', $copy)->with('success', 'Une copie du CV a été créée. Aucun texte n\'a été modifié automatiquement.')->with('suggestion', $suggestion);
    }
}
