<?php

namespace App\Http\Controllers\Api\V1\App\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\UserBackup;
use App\Services\User\UserService;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
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
                'ليس هناك نسخة احتياطية للمستخدم'
                [],
                HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        }

        $fileContent = Storage::disk('public')->get($userBackup->path);

        // Decode and return the JSON content
        $json = json_decode($fileContent, true);

        return ApiResponse::success(
            $json,
            'تم استرجاع النسخة الاحتياطية بنجاح'
        );

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $backupData = $request->input('backupData');

            // Decode if it's a JSON string
            if (is_string($backupData)) {
                $backupData = json_decode($backupData, true);
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ApiResponse::error('بيانات النسخة الاحتياطية غير صالحة (ليست JSON)', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $userBackup = UserBackup::where('user_id', $user->id)->first();

            if ($userBackup && Storage::disk('public')->exists($userBackup->path)) {
                // Overwrite the existing file
                Storage::disk('public')->put($userBackup->path, json_encode($backupData, JSON_PRETTY_PRINT));

                // Update timestamp
                $userBackup->touch();
            } else {
                // Format path: backups/{userId}_{timestamp}.json
                $timestamp = now()->format('Ymd_His');
                $path = "backups/{$user->id}_{$timestamp}.json";

                // Save the new backup file
                Storage::disk('public')->put($path, json_encode($backupData, JSON_PRETTY_PRINT));

                // Store new record in UserBackup
                UserBackup::create([
                    'user_id' => $user->id,
                    'path'    => $path,
                ]);
            }

            DB::commit();

            return ApiResponse::success([], 'تم حفظ النسخة الاحتياطية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('حدث خطأ أثناء الحفظ', ['error' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }


}
