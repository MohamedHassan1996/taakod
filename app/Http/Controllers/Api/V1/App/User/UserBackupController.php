<?php

namespace App\Http\Controllers\Api\V1\App\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\UserBackup;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserBackupController extends Controller implements HasMiddleware
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function show()
    {
        $user = Auth::user();
        $userBackup = UserBackup::where('user_id', $user->id)->first();

        if (!$userBackup || !Storage::disk('public')->exists($userBackup->path)) {
            return ApiResponse::error(
                'ليس هناك نسخة احتياطية للمستخدم',
                [],
                HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        }

        // Return only the file path (full URL or relative path as needed)
        return ApiResponse::success([
            'path' => $userBackup->path,
            'url' => Storage::disk('public')->url($userBackup->path)
        ], 'تم العثور على مسار النسخة الاحتياطية');
    }


    /**
     * Update the specified resource in storage.
     */
public function update(Request $request)
{
    try {
        DB::beginTransaction();

        $user = Auth::user();

        // Validate that a file was uploaded
        if (!$request->hasFile('backupData') || !$request->file('backupData')->isValid()) {
            return ApiResponse::error(
                'لم يتم رفع ملف النسخة الاحتياطية أو الملف غير صالح',
                [],
                HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        }

        $uploadedFile = $request->file('backupData');

        $userBackup = UserBackup::where('user_id', $user->id)->first();

        // Delete old file if it exists
        if ($userBackup && Storage::disk('public')->exists($userBackup->path)) {
            Storage::disk('public')->delete($userBackup->path);
        }

        // Store the new file
        $timestamp = now()->format('Ymd_His');
        $filename = "{$user->id}_{$timestamp}.json"; // keep .json if it's JSON backup
        $path = $uploadedFile->storeAs('backups', $filename, 'public');

        // Update or create the backup record
        if ($userBackup) {
            $userBackup->update(['path' => $path]);
        } else {
            $userBackup = UserBackup::create([
                'user_id' => $user->id,
                'path'    => $path,
            ]);
        }

        DB::commit();

        return ApiResponse::success([
            'path' => $userBackup->path,
            'url'  => Storage::disk('public')->url($userBackup->path)
        ], 'تم حفظ النسخة الاحتياطية بنجاح');

    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('حدث خطأ أثناء الحفظ', ['error' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
    }
}


}
