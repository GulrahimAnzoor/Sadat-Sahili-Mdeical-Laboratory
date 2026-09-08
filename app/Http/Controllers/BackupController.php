<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestoreBackupRequest;
use App\Support\DatabaseBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(DatabaseBackup $backups): View
    {
        return view('settings.backups', [
            'backups' => $backups->list(),
        ]);
    }

    public function store(DatabaseBackup $backups): RedirectResponse
    {
        try {
            $name = $backups->create();
        } catch (Throwable $exception) {
            Log::error('Database backup failed.', ['exception' => $exception->getMessage()]);

            return redirect()
                ->route('settings.backups.index')
                ->with('error', __('The database backup could not be created. Check that storage is writable.'));
        }

        return redirect()
            ->route('settings.backups.index')
            ->with('success', __('Backup created: :name', ['name' => $name]));
    }

    public function download(string $backup, DatabaseBackup $backups): BinaryFileResponse
    {
        return response()->download($backups->resolvedPath($backup));
    }

    public function restore(RestoreBackupRequest $request, DatabaseBackup $backups): RedirectResponse
    {
        try {
            $backups->restore($request->validated('backup'));
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('settings.backups.index')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Database restore failed.', ['exception' => $exception->getMessage()]);

            return redirect()
                ->route('settings.backups.index')
                ->with('error', __('The backup could not be restored. Existing data was left unchanged where possible.'));
        }

        return redirect()
            ->route('settings.backups.index')
            ->with('success', __('Database restored from backup. Sign in again if you are asked to log in.'));
    }
}
