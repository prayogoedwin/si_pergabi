<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Helpers\Area;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::query()->visibleTo($request->user())->with('roles');

            return DataTables::of($users)
                ->addColumn('roles', function ($user) {
                    $badges = '';
                    foreach ($user->roles as $role) {
                        if ($role->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
                            continue;
                        }

                        $badges .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-1">'.$role->name.'</span>';
                    }

                    return $badges ?: '<span class="text-sm text-gray-500 dark:text-gray-400">No roles</span>';
                })
                ->addColumn('actions', function ($user) {
                    $actions = '';

                    if (auth()->user()->hasPermission('show-users')) {
                        $actions .= '<a href="'.route('users.show', $user).'" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }

                    if (auth()->user()->hasPermission('edit-users')) {
                        $actions .= '<a href="'.route('users.edit', $user).'" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                        $actions .= '<form action="'.route('users.reset-password', $user).'" method="POST" class="inline mr-3" onsubmit="return confirm(\'Reset password pengguna ini? Password baru akan ditampilkan sekali.\')">
                            '.csrf_field().'
                            <button type="submit" class="text-amber-700 dark:text-gold-400 hover:underline">Reset password</button>
                        </form>';
                    }

                    if (auth()->user()->hasPermission('delete-users')) {
                        $actions .= '<form action="'.route('users.destroy', $user).'" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }

                    return $actions;
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('M d, Y');
                })
                ->rawColumns(['roles', 'actions'])
                ->make(true);
        }

        return view('users.index');
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users-'.date('Y-m-d').'.xlsx');
    }

    public function create(): View
    {
        return view('users.create', $this->userFormData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->userRules());
        $wilayah = $this->resolvedWilayah($request, $validated['roles'] ?? []);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'pd_kode' => $wilayah['pd_kode'],
            'pc_kode' => $wilayah['pc_kode'],
        ]);

        if (! empty($validated['roles'])) {
            $user->roles()->sync($request->user()->filterAssignableRoleIds($validated['roles']));
        }

        return to_route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->ensureUserVisible($user);

        $user->load(['roles.permissions', 'pd', 'pc']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->ensureUserVisible($user);

        $user->load('roles');

        return view('users.edit', array_merge($this->userFormData($user), compact('user')));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserVisible($user);

        $validated = $request->validate($this->userRules($user));
        $wilayah = $this->resolvedWilayah($request, $validated['roles'] ?? []);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'pd_kode' => $wilayah['pd_kode'],
            'pc_kode' => $wilayah['pc_kode'],
        ]);

        if (! empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $user->roles()->sync($request->user()->filterAssignableRoleIds($validated['roles'] ?? [], $user));

        return to_route('users.index')->with('status', 'User updated successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->ensureUserVisible($user);

        $plain = $user->resetPasswordToTemporary();

        return back()->with([
            'status' => 'Password berhasil direset. Salin password baru ini dan berikan kepada pengguna, lalu minta mereka mengganti sendiri.',
            'password_reset' => $plain,
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureUserVisible($user);

        $user->delete();

        return to_route('users.index')->with('status', 'User deleted successfully.');
    }

    private function ensureUserVisible(User $user): void
    {
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function userRules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $user ? 'unique:users,email,'.$user->id : 'unique:users'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::in(auth()->user()->assignableRoles()->pluck('id')->all())],
            'pd_kode' => ['nullable', 'string', 'max:13', 'exists:wilayah,kode'],
            'pc_kode' => ['nullable', 'string', 'max:13', 'exists:wilayah,kode'],
        ];
    }

    /**
     * @param  array<int|string>  $roleIds
     * @return array{pd_kode: ?string, pc_kode: ?string}
     */
    private function resolvedWilayah(Request $request, array $roleIds): array
    {
        $assignable = $request->user()->filterAssignableRoleIds($roleIds);
        $areas = Role::query()->whereIn('id', $assignable)->pluck('area');
        $needsCabang = $areas->contains(Area::CABANG);
        $needsDaerah = $needsCabang || $areas->contains(Area::DAERAH);

        $pd = $request->string('pd_kode')->toString() ?: null;
        $pc = $request->string('pc_kode')->toString() ?: null;

        if ($needsCabang && blank($pc)) {
            throw ValidationException::withMessages([
                'pc_kode' => 'Kabupaten/kota wajib untuk pengurus PC.',
            ]);
        }

        if ($needsDaerah && blank($pd)) {
            throw ValidationException::withMessages([
                'pd_kode' => 'Provinsi wajib untuk pengurus PD/PC.',
            ]);
        }

        if ($pc && $pd && ! str_starts_with($pc, $pd.'.')) {
            throw ValidationException::withMessages([
                'pc_kode' => 'Kabupaten/kota harus berada di provinsi yang dipilih.',
            ]);
        }

        if (! $needsDaerah) {
            return ['pd_kode' => null, 'pc_kode' => null];
        }

        return [
            'pd_kode' => $pd,
            'pc_kode' => $needsCabang ? $pc : null,
        ];
    }

    /**
     * @return array{roles: Collection<int, Role>, provinsiOptions: Collection<int, Wilayah>, kabupatenOptions: Collection<int, Wilayah>}
     */
    private function userFormData(?User $user = null): array
    {
        $pd = old('pd_kode', $user?->pd_kode);

        return [
            'roles' => auth()->user()->assignableRoles(),
            'provinsiOptions' => Wilayah::query()->anak(null)->orderBy('nama')->get(['kode', 'nama']),
            'kabupatenOptions' => filled($pd)
                ? Wilayah::query()->anak($pd)->orderBy('nama')->get(['kode', 'nama'])
                : collect(),
        ];
    }
}
