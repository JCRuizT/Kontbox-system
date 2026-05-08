<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Activity;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador para la gestión de actividades del catálogo.
 * Cada actividad pertenece a un microservicio (relación N:1).
 */
class ActivityController extends Controller
{
    public function index(): View
    {
        $activities = Activity::with('microservice')
            ->orderByDesc('created_at')
            ->paginate(config('kontbox.items_per_page'));
        return view('activities.index', compact('activities'));
    }

    public function create(): View
    {
        return view('activities.form', [
            'microservices' => Microservice::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'microservice_id' => 'required|exists:microservices,id,is_active,1',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $activity = Activity::create($validated);
        AuditService::logCreate($activity, 'Actividad', $validated);

        return to_route('activities.index')
            ->with('success', __('domain.activity.created'));
    }

    public function edit(Activity $activity): View
    {
        return view('activities.form', [
            'activity' => $activity,
            'microservices' => Microservice::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'microservice_id' => 'required|exists:microservices,id,is_active,1',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $original = $activity->getOriginal();
        $activity->update($validated);
        $changes = $activity->getChanges();

        AuditService::logUpdate($activity, 'Actividad', $original, $changes);
        return to_route('activities.index')
            ->with('success', __('domain.activity.updated'));
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        AuditService::logDelete($activity, 'Actividad');
        $activity->update(['is_active' => false]);

        return to_route('activities.index')
            ->with('success', __('domain.activity.deactivated'));
    }
}
