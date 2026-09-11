            <aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
                class="bg-sidebar text-sidebar-foreground border-r border-[#e4ddd3] dark:border-gold-400/20 sidebar-transition overflow-hidden">
                <!-- Sidebar Content -->
                <div class="h-full flex flex-col">
                    <!-- Sidebar Menu -->
                    <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
                        <ul class="space-y-1 px-2">
                            <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon='fas-house'
                                :active="request()->routeIs('dashboard*')">Dashboard</x-layouts.sidebar-link>

                            @if(auth()->user()->hasPermission('view-wilayah') || auth()->user()->hasPermission('view-area'))
                            <x-layouts.sidebar-two-level-link-parent title="Master" icon="fas-database"
                                :active="request()->routeIs('wilayah*') || request()->routeIs('area*')">
                                @if(auth()->user()->hasPermission('view-wilayah'))
                                <x-layouts.sidebar-two-level-link href="{{ route('wilayah.index') }}" icon='fas-map'
                                    :active="request()->routeIs('wilayah*')">Wilayah</x-layouts.sidebar-two-level-link>
                                @endif
                                @if(auth()->user()->hasPermission('view-area'))
                                <x-layouts.sidebar-two-level-link href="{{ route('area.index') }}" icon='fas-sitemap'
                                    :active="request()->routeIs('area*')">Area</x-layouts.sidebar-two-level-link>
                                @endif
                            </x-layouts.sidebar-two-level-link-parent>
                            @endif

                            @if(auth()->user()->hasPermission('view-organisasi') || auth()->user()->hasPermission('view-pendaftaran') || auth()->user()->hasPermission('view-cache'))
                            <x-layouts.sidebar-two-level-link-parent title="Setting" icon="fas-gear"
                                :active="request()->routeIs('settings.organisasi*') || request()->routeIs('settings.pendaftaran*') || request()->routeIs('cache*')">
                                @if(auth()->user()->hasPermission('view-organisasi'))
                                <x-layouts.sidebar-two-level-link href="{{ route('settings.organisasi.edit') }}" icon='fas-id-card'
                                    :active="request()->routeIs('settings.organisasi*')">Identitas Organisasi</x-layouts.sidebar-two-level-link>
                                @endif
                                @if(auth()->user()->hasPermission('view-pendaftaran'))
                                <x-layouts.sidebar-two-level-link href="{{ route('settings.pendaftaran.edit') }}" icon='fas-sliders'
                                    :active="request()->routeIs('settings.pendaftaran*')">Pengaturan Pendaftaran</x-layouts.sidebar-two-level-link>
                                @endif
                                @if(auth()->user()->hasPermission('view-cache'))
                                <x-layouts.sidebar-two-level-link href="{{ route('cache.index') }}" icon='fas-server'
                                    :active="request()->routeIs('cache*')">Cache</x-layouts.sidebar-two-level-link>
                                @endif
                            </x-layouts.sidebar-two-level-link-parent>
                            @endif

                            @if(auth()->user()->hasPermission('view-users') || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view-permissions'))
                            <x-layouts.sidebar-two-level-link-parent title="User Management" icon="fas-users"
                                :active="request()->routeIs('users*') || request()->routeIs('roles*') || request()->routeIs('permissions*')">
                                @if(auth()->user()->hasPermission('view-users'))
                                <x-layouts.sidebar-two-level-link href="{{ route('users.index') }}" icon='fas-user'
                                    :active="request()->routeIs('users*')">Users</x-layouts.sidebar-two-level-link>
                                @endif
                                @if(auth()->user()->isSuperAdmin())
                                <x-layouts.sidebar-two-level-link href="{{ route('roles.index') }}" icon='fas-shield'
                                    :active="request()->routeIs('roles*')">Roles</x-layouts.sidebar-two-level-link>
                                @endif
                                @if(auth()->user()->hasPermission('view-permissions'))
                                <x-layouts.sidebar-two-level-link href="{{ route('permissions.index') }}" icon='fas-key'
                                    :active="request()->routeIs('permissions*')">Permissions</x-layouts.sidebar-two-level-link>
                                @endif
                            </x-layouts.sidebar-two-level-link-parent>
                            @endif

                            @if(auth()->user()->hasPermission('view-anggota'))
                            <x-layouts.sidebar-link href="{{ route('anggota.index') }}" icon='fas-id-badge'
                                :active="request()->routeIs('anggota*')">Anggota</x-layouts.sidebar-link>
                            @endif
                        </ul>
                    </nav>
                </div>
            </aside>
