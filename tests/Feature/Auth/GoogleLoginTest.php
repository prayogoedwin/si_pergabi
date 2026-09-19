<?php

namespace Tests\Feature\Auth;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use RuntimeException;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);
    }

    public function test_login_screen_hides_google_button_when_not_configured(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Masuk dengan Google');
    }

    public function test_login_screen_shows_google_button_when_configured(): void
    {
        $this->enableGoogle();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk dengan Google')
            ->assertSee(route('login.google'), false);
    }

    public function test_google_routes_are_not_found_when_not_configured(): void
    {
        $this->get(route('login.google'))->assertNotFound();
        $this->get(route('login.google.callback'))->assertNotFound();
    }

    public function test_google_redirect_starts_oauth_when_configured(): void
    {
        $this->enableGoogle();
        Socialite::fake('google', GoogleUser::fake());

        $this->get(route('login.google'))
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_existing_pengurus_can_login_with_google_and_keeps_role(): void
    {
        $this->enableGoogle();

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        Socialite::fake('google', GoogleUser::fake([
            'email' => 'admin@example.com',
            'name' => 'Admin Google',
        ]));

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasRole(Role::ADMIN_PP));
        $this->assertSame(1, User::query()->where('email', 'admin@example.com')->count());
    }

    public function test_existing_member_can_login_with_google_to_portal(): void
    {
        $this->enableGoogle();

        $user = $this->makeMember();

        Socialite::fake('google', GoogleUser::fake([
            'email' => strtoupper($user->email),
            'name' => 'Guru Portal',
        ]));

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('portal.show', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasRole(Role::ANGGOTA));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_google_login_verifies_existing_unverified_email(): void
    {
        $this->enableGoogle();

        $user = User::factory()->unverified()->create([
            'email' => 'belum.verifikasi@example.com',
        ]);
        $user->assignRole(Role::ANGGOTA);

        Socialite::fake('google', GoogleUser::fake([
            'email' => 'belum.verifikasi@example.com',
        ]));

        $this->get(route('login.google.callback'))
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_unknown_google_email_is_sent_to_register_and_does_not_create_user(): void
    {
        $this->enableGoogle();

        $before = User::query()->count();

        Socialite::fake('google', GoogleUser::fake([
            'email' => 'baru.google@example.com',
            'name' => 'Guru Baru',
        ]));

        $response = $this->get(route('login.google.callback'));

        $response->assertRedirect(route('daftar', absolute: false));

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('Email Google tersebut belum terdaftar')
            ->assertSee('baru.google@example.com');

        $this->assertGuest();
        $this->assertSame($before, User::query()->count());
    }

    public function test_registered_user_can_still_login_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'guru.password@example.com',
        ]);

        $this->post('/login', [
            'email' => 'guru.password@example.com',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_without_email_returns_to_login(): void
    {
        $this->enableGoogle();

        Socialite::fake('google', GoogleUser::fake([
            'email' => '',
        ]));

        $this->from(route('login'))
            ->get(route('login.google.callback'))
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
    }

    public function test_failed_google_oauth_returns_to_login(): void
    {
        $this->enableGoogle();

        Socialite::fake('google', function () {
            throw new RuntimeException('oauth failed');
        });

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
    }

    private function enableGoogle(): void
    {
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-client-secret',
        ]);
    }

    private function makeMember(): User
    {
        $user = User::factory()->create([
            'name' => 'Guru Portal',
            'email' => 'guru.portal@example.com',
        ]);
        $user->assignRole(Role::ANGGOTA);

        Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => '3201010101010099',
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
