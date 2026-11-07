<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DepartmentRequest;
use App\Http\Resources\Admin\V1\DepartmentResource;
use App\Models\Department;
use App\Services\Content\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Departments — the "Why Businesses Choose Nwafth" service cards.
 */
class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departments) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('dashboard/Departments/Index', [
            'departments' => DepartmentResource::collection($this->departments->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('dashboard/Departments/Form', ['department' => null]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $this->departments->create($request->attributesExcept(['cover']), [
            Department::MEDIA_COLLECTION_COVER => $request->file('cover'),
        ]);

        return redirect()->route('dashboard.departments.index')->with('success', __('Department created.'));
    }

    public function edit(Department $department): Response
    {
        return Inertia::render('dashboard/Departments/Form', [
            'department' => (new DepartmentResource($department))->resolve(),
        ]);
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->departments->update($department, $request->attributesExcept(['cover']), [
            Department::MEDIA_COLLECTION_COVER => $request->file('cover'),
        ]);

        return redirect()->route('dashboard.departments.index')->with('success', __('Department updated.'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->departments->delete($department);

        return back()->with('success', __('Department deleted.'));
    }
}
