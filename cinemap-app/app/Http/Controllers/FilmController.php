<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FilmController extends Controller
{
    public function index(): View
    {
        return view('films.index', [
            'films' => Film::query()
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('films.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $film = Film::query()->create($this->validatedData($request));

        return redirect()
            ->route('films.show', $film)
            ->with('success', 'Film créé avec succès.');
    }

    public function show(Film $film): View
    {
        return view('films.show', [
            'film' => $film,
        ]);
    }

    public function edit(Film $film): View
    {
        return view('films.edit', [
            'film' => $film,
        ]);
    }

    public function update(Request $request, Film $film): RedirectResponse
    {
        $film->update($this->validatedData($request, $film));

        return redirect()
            ->route('films.show', $film)
            ->with('success', 'Film modifié avec succès.');
    }

    public function destroy(Film $film): RedirectResponse
    {
        $film->delete();

        return redirect()
            ->route('films.index')
            ->with('success', 'Film supprimé avec succès.');
    }

    protected function validatedData(Request $request, ?Film $film = null): array
    {
        $film ??= new Film();

        return $request->validate([
            'name'         => ['required', 'string', 'max:255', Rule::unique('films')->ignore($film)],
            'producer'     => ['required', 'string', 'max:255'],
            'release_year' => ['required', 'integer', 'min:1888', 'max:' . date('Y')],
            'time'         => ['required', 'integer', 'min:1'],
            'genres'       => ['required', 'string', 'max:255'],
            'synopsis'     => ['required', 'string'],
            'poster_url'   => ['required', 'url'],
            'trailer_url'  => ['required', 'url'],
            'actors'       => ['required', 'string', 'max:255'],
        ]);
    }
}
