<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KegiatanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Http::preventStrayRequests();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('kegiatan.index'))->assertRedirect(route('login'));
        $this->get(route('portal.kegiatan'))->assertRedirect(route('login'));
    }

    public function test_admin_can_list_kegiatan_with_fixed_photo_wrapper_and_pagination(): void
    {
        $this->fakeKegiatanList();

        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kegiatan')
            ->assertSee(route('kegiatan.index'));

        $this->actingAs($this->admin())
            ->get(route('kegiatan.index'))
            ->assertOk()
            ->assertSee('Kegiatan Tes')
            ->assertSee('Ringkasan kegiatan')
            ->assertSee('aspect-video')
            ->assertSee('object-cover')
            ->assertSee('https://pergabi.id/foto-card.jpg')
            ->assertSee('Baca selengkapnya')
            ->assertSee('rel="next"', false);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://pergabi.id/wp-json/wp/v2/posts')
                && (int) $request['categories'] === 11
                && (int) $request['per_page'] === 12
                && (int) $request['page'] === 1
                && (int) $request['_embed'] === 1;
        });
    }

    public function test_admin_can_open_second_page(): void
    {
        Http::fake([
            'pergabi.id/wp-json/wp/v2/posts*' => Http::response([
                $this->wpPost(3061, 'Kegiatan Halaman Dua'),
            ], 200, [
                'X-WP-Total' => '25',
                'X-WP-TotalPages' => '3',
            ]),
        ]);

        $this->actingAs($this->admin())
            ->get(route('kegiatan.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Kegiatan Halaman Dua');

        Http::assertSent(fn ($request) => (int) $request['page'] === 2);
    }

    public function test_admin_can_open_kegiatan_detail(): void
    {
        $this->fakeKegiatanDetail();

        $this->actingAs($this->admin())
            ->get(route('kegiatan.show', 3060))
            ->assertOk()
            ->assertSee('Kegiatan Tes')
            ->assertSee('Isi lengkap kegiatan')
            ->assertSee('aspect-video')
            ->assertSee('object-cover')
            ->assertSee('Buka di website PERGABI');
    }

    public function test_member_sees_kegiatan_in_portal_not_admin_layout(): void
    {
        $this->fakeKegiatanList();
        $user = $this->makeMember();

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('Kegiatan')
            ->assertSee(route('portal.kegiatan'));

        $this->actingAs($user)
            ->get(route('kegiatan.index'))
            ->assertRedirect(route('portal.kegiatan'));

        $this->actingAs($user)
            ->get(route('portal.kegiatan'))
            ->assertOk()
            ->assertSee('Kegiatan Tes')
            ->assertSee('aspect-video')
            ->assertSee('object-cover')
            ->assertDontSee('Master')
            ->assertDontSee('User Management');
    }

    public function test_member_can_open_kegiatan_detail_in_portal(): void
    {
        $this->fakeKegiatanDetail();
        $user = $this->makeMember();

        $this->actingAs($user)
            ->get(route('portal.kegiatan.show', 3060))
            ->assertOk()
            ->assertSee('Kegiatan Tes')
            ->assertSee('Isi lengkap kegiatan');
    }

    public function test_pengurus_is_redirected_from_portal_kegiatan_to_admin(): void
    {
        $this->actingAs($this->admin())
            ->get(route('portal.kegiatan'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_failed_website_request_shows_empty_state(): void
    {
        Http::fake([
            'pergabi.id/wp-json/wp/v2/posts*' => Http::response('forbidden', 403),
        ]);

        $this->actingAs($this->admin())
            ->get(route('kegiatan.index'))
            ->assertOk()
            ->assertSee('Konten kegiatan sedang tidak dapat dimuat');
    }

    public function test_cached_listing_renders_after_classless_cache_unserialize(): void
    {
        $cachePath = storage_path('framework/cache/kegiatan-test');
        File::deleteDirectory($cachePath);

        config([
            'cache.default' => 'file',
            'cache.stores.file.path' => $cachePath,
            'cache.serializable_classes' => false,
            'pergabi.wp.cache_ttl' => 600,
        ]);

        $this->fakeKegiatanList();

        $this->actingAs($this->admin())
            ->get(route('kegiatan.index'))
            ->assertOk()
            ->assertSee('Kegiatan Tes');

        Http::fake();

        $this->actingAs($this->admin())
            ->get(route('kegiatan.index'))
            ->assertOk()
            ->assertSee('Kegiatan Tes')
            ->assertSee('aspect-video')
            ->assertSee('object-cover');

        File::deleteDirectory($cachePath);
    }

    /**
     * @return array<string, mixed>
     */
    private function wpPost(int $id, string $title, bool $withImage = true): array
    {
        return [
            'id' => $id,
            'slug' => 'kegiatan-tes',
            'date' => '2026-09-08T05:12:13',
            'link' => 'https://pergabi.id/kegiatan-tes/',
            'categories' => [11],
            'title' => ['rendered' => $title],
            'excerpt' => ['rendered' => '<p>Ringkasan kegiatan</p>'],
            'content' => ['rendered' => '<p>Isi lengkap kegiatan</p>'],
            '_embedded' => [
                'wp:featuredmedia' => $withImage ? [[
                    'source_url' => 'https://pergabi.id/foto.jpg',
                    'media_details' => [
                        'width' => 1080,
                        'height' => 1350,
                        'sizes' => [
                            'et-pb-post-main-image' => [
                                'source_url' => 'https://pergabi.id/foto-card.jpg',
                                'width' => 400,
                                'height' => 250,
                            ],
                        ],
                    ],
                ]] : [],
            ],
        ];
    }

    private function fakeKegiatanList(): void
    {
        Http::fake([
            'pergabi.id/wp-json/wp/v2/posts*' => Http::response([
                $this->wpPost(3060, 'Kegiatan Tes'),
            ], 200, [
                'X-WP-Total' => '25',
                'X-WP-TotalPages' => '3',
            ]),
        ]);
    }

    private function fakeKegiatanDetail(): void
    {
        Http::fake([
            'pergabi.id/wp-json/wp/v2/posts/3060*' => Http::response($this->wpPost(3060, 'Kegiatan Tes'), 200),
        ]);
    }

    private function admin(): User
    {
        return User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    private function makeMember(): User
    {
        $user = User::factory()->create([
            'name' => 'Guru Portal',
            'email' => 'guru.kegiatan@example.com',
            'email_verified_at' => now(),
        ]);
        $user->assignRole(Role::ANGGOTA);

        Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => '3201010101010088',
            'nama' => 'Guru Portal',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Buddha',
            'status_perkawinan' => 'Kawin',
            'hp' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => $user->email,
            'alamat' => 'Jl. Melati No. 1',
            'provinsi_kode' => '36',
            'kabupaten_kode' => '36.71',
            'kecamatan_kode' => '36.71.01',
            'kelurahan_kode' => '36.71.01.1001',
            'kode_pos' => '15111',
            'status_guru' => 'ASN',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pd_kode' => '36',
            'pc_kode' => '36.71',
            'status' => Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
        ]);

        return $user->fresh(['anggota', 'roles']);
    }
}
