<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\AdminRequest;
use App\Http\Resources\Admin\V1\AdminResource;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administrator accounts. Every action is restricted to super admins by
 * App\Policies\AdminPolicy (store/update authorize in AdminRequest).
 */
class AdminController extends Controller
{
    public function __construct(private readonly AdminService $admins) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Admin::class);

        $search = $request->string('search')->trim()->value();

        return Inertia::render('dashboard/Admins/Index', [
            'admins' => AdminResource::collection($this->admins->paginate(['search' => $search])),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Admin::class);

        return Inertia::render('dashboard/Admins/Form', [
            'admin' => null,
            'roles' => $this->roles(),
        ]);
    }

    public function store(AdminRequest $request): RedirectResponse
    {
        $this->admins->create($request->validated());

        return redirect()->route('dashboard.admins.index')->with('success', __('Administrator created.'));
    }

    public function edit(Admin $admin): Response
    {
        Gate::authorize('update', $admin);

        return Inertia::render('dashboard/Admins/Form', [
            'admin' => (new AdminResource($admin))->resolve(),
            'roles' => $this->roles(),
        ]);
    }

    public function update(AdminRequest $request, Admin $admin): RedirectResponse
    {
        $this->admins->update($admin, $request->validated(), $request->user());

        return redirect()->route('dashboard.admins.index')->with('success', __('Administrator updated.'));
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        Gate::authorize('delete', $admin);

        $this->admins->delete($admin);

        return back()->with('success', __('Administrator deleted.'));
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function roles(): array
    {
        return collect(AdminRole::cases())
            ->map(fn (AdminRole $role) => ['value' => $role->value, 'label' => __($role->label())])
            ->all();
    }
}
