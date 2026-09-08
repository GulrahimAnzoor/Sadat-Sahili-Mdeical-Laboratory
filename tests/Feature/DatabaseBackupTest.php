<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Support\DatabaseBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/private/testing-backups'));

        parent::tearDown();
    }

    public function test_settings_backup_page_creates_a_file(): void
    {
        $this->get(route('settings.backups.index'))
            ->assertOk()
            ->assertSee('Backup database');

        $this->post(route('settings.backups.store'))
            ->assertRedirect(route('settings.backups.index'))
            ->assertSessionHas('success');

        $this->assertNotSame([], app(DatabaseBackup::class)->list());
    }

    public function test_restore_replaces_later_records_and_keeps_the_backup_file(): void
    {
        $kept = Patient::factory()->create(['name' => 'Amina']);

        $this->post(route('settings.backups.store'));

        $backup = app(DatabaseBackup::class)->list()[0]['name'];

        Patient::factory()->create(['name' => 'Temporary']);

        $this->from(route('settings.backups.index'))
            ->post(route('settings.backups.restore'), [
                'backup' => $backup,
                'confirm' => '1',
            ])
            ->assertRedirect(route('settings.backups.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('patients', ['name' => 'Amina', 'id' => $kept->id]);
        $this->assertDatabaseMissing('patients', ['name' => 'Temporary']);
        $this->assertTrue(File::exists(storage_path('app/private/testing-backups/'.$backup)));
    }

    public function test_restore_requires_confirmation(): void
    {
        $this->post(route('settings.backups.store'));
        $backup = app(DatabaseBackup::class)->list()[0]['name'];

        $this->from(route('settings.backups.index'))
            ->post(route('settings.backups.restore'), [
                'backup' => $backup,
            ])
            ->assertRedirect(route('settings.backups.index'))
            ->assertSessionHasErrors('confirm');
    }
}
