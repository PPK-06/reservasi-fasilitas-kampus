<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * A3 Daftar Akun — penerapan D9 sampai D16 `keputusan-m1-tahap2.md`.
 *
 * DatabaseTransactions, bukan RefreshDatabase: `Pest.php` sengaja tidak
 * memakai RefreshDatabase, dan halaman ini hanya membaca. Seluruh akun yang
 * dibuat di sini di-rollback setelah tiap test.
 */
uses(DatabaseTransactions::class);

/*
 * Test ini berjalan di atas database yang sudah diisi DatabaseSeeder.
 *
 * `phpunit.xml` mengarahkan suite ke SQLite in-memory dan `Pest.php` sengaja
 * tidak memakai RefreshDatabase, sehingga tanpa penjaga ini seluruh berkas
 * gagal karena tabelnya memang tidak ada. Jalankan dengan:
 *
 *   DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas php artisan test
 *
 * Penjaga ini bisa dilepas begitu suite punya database sendiri — yang hari ini
 * terhalang tabrakan nama index `idx_queue` antara migration reservations dan
 * reports, karena SQLite memperlakukan nama index sebagai global per-database.
 */
beforeEach(function (): void {
    if (! Schema::hasTable('users') || User::where('role', 'admin')->doesntExist()) {
        $this->markTestSkipped('Butuh database yang sudah diisi DatabaseSeeder.');
    }
});

/**
 * Navbar `layouts/app.blade.php` memanggil dua nama route milik modul lain yang
 * pemiliknya belum mendaftarkannya: `admin.facilities.index` (M2) dan
 * `admin.recap.index` (M5). Tanpa keduanya, layout melempar
 * RouteNotFoundException dan A3 tidak bisa dirender sama sekali.
 *
 * Didaftarkan di sini supaya test bisa jalan; `routes/web.php` tidak disentuh.
 * Baris ini boleh dihapus begitu kedua modul mendaftarkan route-nya.
 */
function daftarkanRouteNavbarModulLain(): void
{
    Route::get('/__test/admin/facilities', fn () => '')->name('admin.facilities.index');
    Route::get('/__test/admin/recap', fn () => '')->name('admin.recap.index');

    // Nama route dipasang setelah route masuk koleksi, jadi peta namanya perlu disegarkan.
    Route::getRoutes()->refreshNameLookups();
}

function akunAdmin(): User
{
    return User::where('role', 'admin')->firstOrFail();
}

it('menampilkan seluruh akun tanpa filter apa pun secara default (D12)', function (): void {
    daftarkanRouteNavbarModulLain();

    $response = $this->actingAs(akunAdmin())->get(route('admin.users.index'));

    $response->assertOk();

    expect($response->viewData('activeStatus'))->toBeNull()
        ->and($response->viewData('activeRole'))->toBeNull()
        ->and($response->viewData('users')->total())->toBe(User::count());
});

it('mengambil angka penanda pending dari seluruh tabel, bukan dari daftar yang difilter (D13)', function (): void {
    daftarkanRouteNavbarModulLain();

    User::factory()->count(4)->pending()->create();

    $jumlahPendingSebenarnya = User::where('status', 'pending')->count();
    $admin = akunAdmin();

    $default = $this->actingAs($admin)->get(route('admin.users.index'));
    $tabMenunggu = $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'pending']));
    $tabTerverifikasi = $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'verified']));
    $tabDitolak = $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'rejected']));

    expect($default->viewData('pendingCount'))->toBe($jumlahPendingSebenarnya)
        ->and($tabMenunggu->viewData('pendingCount'))->toBe($jumlahPendingSebenarnya)
        ->and($tabTerverifikasi->viewData('pendingCount'))->toBe($jumlahPendingSebenarnya)
        ->and($tabDitolak->viewData('pendingCount'))->toBe($jumlahPendingSebenarnya);

    // Angka penanda tidak ikut menyusut walau daftarnya menyusut.
    expect($tabTerverifikasi->viewData('users')->total())
        ->toBeLessThan($default->viewData('users')->total());
});

it('menyaring daftar sesuai status yang diminta', function (): void {
    daftarkanRouteNavbarModulLain();

    User::factory()->count(3)->pending()->create();

    $response = $this->actingAs(akunAdmin())->get(route('admin.users.index', ['status' => 'pending']));

    $response->assertOk();

    expect($response->viewData('activeStatus'))->toBe('pending')
        ->and($response->viewData('users')->pluck('status')->unique()->all())->toBe(['pending'])
        ->and($response->viewData('users')->total())->toBe(User::where('status', 'pending')->count());
});

it('menjalankan filter status dan role bersamaan', function (): void {
    daftarkanRouteNavbarModulLain();

    User::factory()->count(3)->pending()->create();

    $response = $this->actingAs(akunAdmin())
        ->get(route('admin.users.index', ['status' => 'pending', 'role' => 'pengguna']));

    $response->assertOk();

    expect($response->viewData('activeStatus'))->toBe('pending')
        ->and($response->viewData('activeRole'))->toBe('pengguna')
        ->and($response->viewData('users')->pluck('status')->unique()->all())->toBe(['pending'])
        ->and($response->viewData('users')->pluck('role')->unique()->all())->toBe(['pengguna'])
        ->and($response->viewData('users')->total())
        ->toBe(User::where('status', 'pending')->where('role', 'pengguna')->count());
});

it('mengabaikan nilai status yang tidak dikenal tanpa error dan tanpa redirect (D14)', function (): void {
    daftarkanRouteNavbarModulLain();

    $response = $this->actingAs(akunAdmin())
        ->get(route('admin.users.index', ['status' => 'bukan-status-yang-sah']));

    $response->assertOk();

    expect($response->viewData('activeStatus'))->toBeNull()
        ->and($response->viewData('users')->total())->toBe(User::count());
});

it('mengabaikan nilai role yang tidak dikenal tanpa error dan tanpa redirect (D14)', function (): void {
    daftarkanRouteNavbarModulLain();

    $response = $this->actingAs(akunAdmin())->get(route('admin.users.index', ['role' => 'xyz']));

    $response->assertOk();

    expect($response->viewData('activeRole'))->toBeNull()
        ->and($response->viewData('users')->total())->toBe(User::count());
});

it('membawa query string filter ke tautan halaman berikutnya (D16)', function (): void {
    daftarkanRouteNavbarModulLain();

    User::factory()->count(20)->pending()->create();

    $response = $this->actingAs(akunAdmin())
        ->get(route('admin.users.index', ['status' => 'pending', 'role' => 'pengguna']));

    $response->assertOk();

    $users = $response->viewData('users');

    expect($users->perPage())->toBe(15)
        ->and($users->lastPage())->toBeGreaterThan(1)
        ->and($users->url(2))->toContain('status=pending')
        ->and($users->url(2))->toContain('role=pengguna');

    // Tautan paginasi yang benar-benar dirender juga membawa filternya.
    $response->assertSee('status=pending', false)
        ->assertSee('role=pengguna', false);
});

it('mengurutkan akun pending di atas, lalu created_at menurun', function (): void {
    daftarkanRouteNavbarModulLain();

    $urutanSeharusnya = User::query()
        ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
        ->orderByDesc('created_at')
        ->pluck('status')
        ->take(15)
        ->all();

    $response = $this->actingAs(akunAdmin())->get(route('admin.users.index'));

    expect($response->viewData('users')->pluck('status')->all())->toBe($urutanSeharusnya);
});

it('menolak akses petugas dengan 403', function (): void {
    $petugas = User::where('role', 'petugas')->firstOrFail();

    $this->actingAs($petugas)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('mengarahkan tamu ke halaman login', function (): void {
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
});
