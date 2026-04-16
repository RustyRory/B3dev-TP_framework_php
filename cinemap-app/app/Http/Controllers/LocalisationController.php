<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateLocalisationVotes;
use App\Models\Film;
use App\Models\Localisation;
use App\Models\LocalisationVote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocalisationController extends Controller
{
    public function index(): View
    {
        return view('localisations.index', [
            'localisations' => Localisation::query()
                ->with(['film', 'user'])
                ->when(request('film_id'), fn ($q, $id) => $q->where('film_id', $id))
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('localisations.create', [
            'films' => Film::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $localisation = Localisation::query()->create(
            $this->validatedData($request) + ['user_id' => auth()->id()]
        );

        return redirect()
            ->route('films.show', $localisation->film_id)
            ->with('success', 'Localisation ajoutée avec succès.');
    }

    public function show(Localisation $localisation): View
    {
        return view('localisations.show', [
            'localisation' => $localisation,
        ]);
    }

    public function edit(Localisation $localisation): View
    {
        abort_if(
            auth()->id() !== $localisation->user_id && ! auth()->user()->is_admin,
            403
        );

        return view('localisations.edit', [
            'localisation' => $localisation,
            'films'        => Film::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, Localisation $localisation): RedirectResponse
    {
        abort_if(
            auth()->id() !== $localisation->user_id && ! auth()->user()->is_admin,
            403
        );

        $localisation->update($this->validatedData($request, $localisation));

        return redirect()
            ->route('localisations.show', $localisation)
            ->with('success', 'Localisation modifiée avec succès.');
    }

    public function destroy(Localisation $localisation): RedirectResponse
    {
        abort_if(
            auth()->id() !== $localisation->user_id && ! auth()->user()->is_admin,
            403
        );

        $localisation->delete();

        return redirect()
            ->route('films.show', $localisation->film_id)
            ->with('success', 'Localisation supprimée avec succès.');
    }

    public function vote(Localisation $localisation): RedirectResponse
    {
        $existing = LocalisationVote::where([
            'user_id'         => auth()->id(),
            'localisation_id' => $localisation->id,
        ])->first();

        $existing
            ? $existing->delete()
            : LocalisationVote::create([
                'user_id'         => auth()->id(),
                'localisation_id' => $localisation->id,
            ]);

        RecalculateLocalisationVotes::dispatch($localisation);

        return back();
    }

    protected function validatedData(Request $request, ?Localisation $localisation = null): array
    {
        $localisation ??= new Localisation();

        return $request->validate([
            'film_id'     => ['required', 'integer', 'exists:films,id'],
            'name'        => ['required', 'string', 'max:255', Rule::unique('localisations')->ignore($localisation)],
            'city'        => ['required', 'string', 'max:255'],
            'country'     => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'photo_url'   => ['nullable', 'url', 'max:2048'],
        ]);
    }
}
