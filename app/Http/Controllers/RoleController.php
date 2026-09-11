<?php

namespace App\Http\Controllers;

use App\Exports\RolesExport;
use App\Helpers\Area;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::query()->visibleTo($request->user());

            return DataTables::of($roles)
                ->addColumn('area_label', function (Role $role) {
                    return e($role->areaLabel());
                })
                ->addColumn('status', function (Role $role) {
                    if ($role->isSuperAdmin()) {
                        return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>';
                    }

                    $label = $role->is_active ? 'Aktif' : 'Nonaktif';
                    $class = $role->is_active
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';

                    return '<button type="button" class="toggle-role-status inline-flex items-center px-2 py-0.5 rounded text-xs font-medium '.$class.'" data-url="'.route('roles.toggle', $role).'">'.$label.'</button>';
                })
                ->addColumn('actions', function (Role $role) {
                    $actions = '';

                    if (auth()->user()->hasPermission('show-roles')) {
                        $actions .= '<a href="'.route('roles.show', $role).'" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }

                    if (auth()->user()->hasPermission('edit-roles')) {
                        $actions .= '<a href="'.route('roles.edit', $role).'" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }

                    if (auth()->user()->hasPermission('delete-roles') && ! $role->isSuperAdmin()) {
                        $actions .= '<form action="'.route('roles.destroy', $role).'" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }

                    return $actions ?: '-';
                })
                ->editColumn('created_at', function (Role $role) {
                    return $role->created_at->format('M d, Y');
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('roles.index');
    }

    public function export()
    {
        return Excel::download(new RolesExport, 'roles-'.date('Y-m-d').'.xlsx');
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $areas = Area::options();

        return view('roles.create', compact('groupedPermissions', 'areas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'area' => ['nullable', 'in:'.implode(',', Area::codes())],
            'is_active' => ['sometimes', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $slug = Str::slug($validated['name']);

        if ($slug === Role::SUPER_ADMIN || Role::query()->where('slug', $slug)->exists()) {
            return back()->withInput()->withErrors(['name' => 'This role name is reserved or already used.']);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'area' => $validated['area'] ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return to_route('roles.index')->with('status', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $this->ensureRoleVisible($role);

        $role->load('permissions', 'users');

        return view('roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $this->ensureRoleVisible($role);

        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $areas = Area::options();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'groupedPermissions', 'areas'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->ensureRoleVisible($role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'area' => ['nullable', 'in:'.implode(',', Area::codes())],
            'is_active' => ['sometimes', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'area' => $role->isSuperAdmin() ? Area::PUSAT : ($validated['area'] ?: null),
        ];

        if (! $role->isSuperAdmin()) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        $role->update($payload);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return to_route('roles.index')->with('status', 'Role updated successfully.');
    }

    public function toggle(Request $request, Role $role): JsonResponse|RedirectResponse
    {
        $this->ensureRoleVisible($role);

        if ($role->isSuperAdmin()) {
            abort(403, 'Super Admin cannot be deactivated.');
        }

        $role->update([
            'is_active' => ! $role->is_active,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'is_active' => $role->is_active,
                'status' => $role->is_active ? 'Aktif' : 'Nonaktif',
            ]);
        }

        return back()->with('status', 'Role status updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->ensureRoleVisible($role);

        if ($role->isSuperAdmin()) {
            return back()->with('error', 'Super Admin cannot be deleted.');
        }

        $role->delete();

        return to_route('roles.index')->with('status', 'Role deleted successfully.');
    }

    private function ensureRoleVisible(Role $role): void
    {
        if ($role->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            abort(404);
        }
    }

    private function groupPermissions($permissions): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);

            if (count($parts) >= 2) {
                $action = $parts[0];
                $resource = implode('-', array_slice($parts, 1));
            } else {
                $action = 'other';
                $resource = $permission->name;
            }

            if (! isset($grouped[$resource])) {
                $grouped[$resource] = [
                    'name' => ucfirst(str_replace('-', ' ', $resource)),
                    'permissions' => [],
                ];
            }

            $grouped[$resource]['permissions'][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $action,
                'label' => ucfirst($action),
            ];
        }

        ksort($grouped);

        foreach ($grouped as &$group) {
            usort($group['permissions'], function ($a, $b) {
                $order = ['view' => 1, 'show' => 2, 'create' => 3, 'edit' => 4, 'download' => 5, 'delete' => 6];
                $orderA = $order[$a['action']] ?? 99;
                $orderB = $order[$b['action']] ?? 99;

                return $orderA <=> $orderB;
            });
        }

        return $grouped;
    }
}
