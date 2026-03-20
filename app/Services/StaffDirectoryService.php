<?php

namespace App\Services;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StaffDirectoryService
{
    private const METADATA_RELATIVE_PATH = 'app/staff-directory.json';

    private array $tableExistsCache = [];
    private array $columnExistsCache = [];
    private ?array $metadataCache = null;

    public function resolveUserProfile(?User $user): array
    {
        if ($user === null) {
            return [
                'kind' => 'user',
                'display_name' => 'User',
                'full_name' => 'User',
                'username' => '-',
                'role' => '',
                'role_label' => 'ผู้ใช้งาน',
                'branch_name' => '-',
                'phone' => '-',
                'position' => '-',
                'nickname' => '-',
                'avatar' => $this->buildFallbackAvatar('guest'),
                'image_path' => '',
                'has_profile_image' => false,
                'profile_url' => '#',
                'staff_id' => null,
                'masseuse_id' => null,
                'linked_user_id' => null,
            ];
        }

        $username = (string) ($user->username ?? '-');
        $branchName = $this->resolveBranchName(isset($user->branch_id) ? (int) $user->branch_id : null);
        $baseProfile = [
            'kind' => 'user',
            'display_name' => $username !== '' ? $username : 'User',
            'full_name' => $username !== '' ? $username : 'User',
            'username' => $username !== '' ? $username : '-',
            'role' => (string) ($user->role ?? ''),
            'role_label' => $this->formatRoleLabel((string) ($user->role ?? '')),
            'branch_name' => $branchName,
            'phone' => '-',
            'position' => '-',
            'nickname' => '-',
            'avatar' => $this->buildFallbackAvatar($username !== '' ? $username : ('user-' . (string) $user->id)),
            'image_path' => '',
            'has_profile_image' => false,
            'profile_url' => route('profile.show'),
            'staff_id' => null,
            'masseuse_id' => null,
            'linked_user_id' => (int) $user->id,
        ];

        $masseuse = $this->findMasseuseProfile((int) $user->id);
        if ($masseuse !== null) {
            $imagePath = (string) ($masseuse->profile_image ?? '');

            return array_merge($baseProfile, [
                'kind' => 'masseuse',
                'display_name' => $this->firstNonEmpty([
                    $masseuse->full_name ?? null,
                    $masseuse->nickname ?? null,
                    $username,
                ], 'หมอนวด'),
                'full_name' => $this->firstNonEmpty([
                    $masseuse->full_name ?? null,
                    $masseuse->nickname ?? null,
                    $username,
                ], 'หมอนวด'),
                'branch_name' => (string) ($masseuse->branch_name ?? $branchName),
                'position' => 'หมอนวด',
                'nickname' => $this->firstNonEmpty([$masseuse->nickname ?? null], '-'),
                'avatar' => $this->resolveAvatarPath($imagePath, 'masseuse-' . $user->id),
                'image_path' => $imagePath,
                'has_profile_image' => $imagePath !== '',
                'masseuse_id' => (int) $masseuse->id,
            ]);
        }

        $staffId = $this->resolveStaffIdFromUser($user);
        if ($staffId === null) {
            return $baseProfile;
        }

        $staff = $this->findStaffProfile($staffId);
        if ($staff === null) {
            return $baseProfile;
        }

        $imagePath = $this->getStaffProfileImagePath($staffId);

        return array_merge($baseProfile, [
            'kind' => 'staff',
            'display_name' => (string) ($staff->name ?? $baseProfile['display_name']),
            'full_name' => (string) ($staff->name ?? $baseProfile['full_name']),
            'branch_name' => (string) ($staff->branch_name ?? $branchName),
            'phone' => $this->firstNonEmpty([$staff->phone ?? null], '-'),
            'position' => $this->firstNonEmpty([$staff->position ?? null], '-'),
            'nickname' => $this->firstNonEmpty([$staff->nickname ?? null], '-'),
            'avatar' => $this->resolveAvatarPath($imagePath, 'staff-' . $staffId),
            'image_path' => $imagePath,
            'has_profile_image' => $imagePath !== '',
            'staff_id' => $staffId,
        ]);
    }

    public function getStaffProfileImagePath(int $staffId): string
    {
        $metadata = $this->getMetadata();
        $imagePath = $metadata['staff_profiles'][(string) $staffId]['image_path'] ?? '';

        return is_string($imagePath) ? $imagePath : '';
    }

    public function getStaffAvatar(?int $staffId, ?string $seed = null): string
    {
        if ($staffId === null || $staffId <= 0) {
            return $this->buildFallbackAvatar($seed ?? 'staff');
        }

        return $this->resolveAvatarPath(
            $this->getStaffProfileImagePath($staffId),
            $seed ?? ('staff-' . $staffId)
        );
    }

    public function getMasseuseAvatar(?int $masseuseId, ?string $seed = null): string
    {
        if ($masseuseId === null || $masseuseId <= 0 || !$this->tableExists('masseuses')) {
            return $this->buildFallbackAvatar($seed ?? 'masseuse');
        }

        $profileImage = DB::table('masseuses')
            ->where('id', $masseuseId)
            ->value('profile_image');

        return $this->resolveAvatarPath(
            is_string($profileImage) ? $profileImage : '',
            $seed ?? ('masseuse-' . $masseuseId)
        );
    }

    public function saveStaffProfileImage(int $staffId, UploadedFile $profileImage): string
    {
        $metadata = $this->getMetadata();
        $existingPath = (string) ($metadata['staff_profiles'][(string) $staffId]['image_path'] ?? '');
        $newPath = $this->storeManagedStaffImage($profileImage);

        $metadata['staff_profiles'] = $metadata['staff_profiles'] ?? [];
        $metadata['staff_profiles'][(string) $staffId] = [
            'image_path' => $newPath,
            'updated_at' => now()->toDateTimeString(),
        ];

        $this->persistMetadata($metadata);

        if ($existingPath !== '' && $existingPath !== $newPath) {
            $this->deleteManagedStaffImage($existingPath);
        }

        return $newPath;
    }

    public function removeStaffProfileImage(int $staffId): void
    {
        $metadata = $this->getMetadata();
        $existingPath = (string) ($metadata['staff_profiles'][(string) $staffId]['image_path'] ?? '');

        if (isset($metadata['staff_profiles'][(string) $staffId])) {
            unset($metadata['staff_profiles'][(string) $staffId]);
            $this->persistMetadata($metadata);
        }

        if ($existingPath !== '') {
            $this->deleteManagedStaffImage($existingPath);
        }
    }

    public function removeStaffReferences(int $staffId): void
    {
        $this->removeStaffProfileImage($staffId);
    }

    public function getLinkedUserIdForStaff(int $staffId): ?int
    {
        if ($staffId <= 0 || !$this->tableExists('users') || !$this->hasColumn('users', 'staff_id')) {
            return null;
        }

        $userId = DB::table('users')
            ->where('staff_id', $staffId)
            ->value('id');

        if ($userId === null) {
            return null;
        }

        $resolvedUserId = (int) $userId;

        return $resolvedUserId > 0 ? $resolvedUserId : null;
    }

    public function formatRoleLabel(string $role): string
    {
        $map = [
            'super_admin' => 'Super Admin',
            'branch_manager' => 'ผู้จัดการสาขา',
            'cashier' => 'แคชเชียร์',
            'masseuse' => 'หมอนวด',
        ];

        return $map[$role] ?? ($role !== '' ? $role : 'ผู้ใช้งาน');
    }

    private function resolveStaffIdFromUser(User $user): ?int
    {
        if (!$this->hasColumn('users', 'staff_id')) {
            return null;
        }

        $staffId = isset($user->staff_id) ? (int) $user->staff_id : 0;

        return $staffId > 0 ? $staffId : null;
    }

    private function findMasseuseProfile(int $userId): ?object
    {
        if (!$this->tableExists('masseuses') || !$this->hasColumn('masseuses', 'user_id')) {
            return null;
        }

        return DB::table('masseuses as m')
            ->leftJoin('branches as b', 'm.branch_id', '=', 'b.id')
            ->where('m.user_id', $userId)
            ->first([
                'm.id',
                'm.nickname',
                'm.full_name',
                'm.profile_image',
                'b.name as branch_name',
            ]);
    }

    private function findStaffProfile(int $staffId): ?object
    {
        if (!$this->tableExists('staff')) {
            return null;
        }

        return DB::table('staff as s')
            ->leftJoin('branches as b', 's.branch_id', '=', 'b.id')
            ->where('s.id', $staffId)
            ->first([
                's.id',
                's.name',
                's.nickname',
                's.phone',
                's.position',
                's.branch_id',
                'b.name as branch_name',
            ]);
    }

    private function resolveBranchName(?int $branchId): string
    {
        if ($branchId === null || $branchId <= 0 || !$this->tableExists('branches')) {
            return '-';
        }

        $branchName = DB::table('branches')
            ->where('id', $branchId)
            ->value('name');

        return is_string($branchName) && $branchName !== '' ? $branchName : '-';
    }

    private function resolveAvatarPath(string $imagePath, string $seed): string
    {
        if ($imagePath === '') {
            return $this->buildFallbackAvatar($seed);
        }

        if (preg_match('#^https?://#i', $imagePath) === 1) {
            return $imagePath;
        }

        return asset(ltrim($imagePath, '/'));
    }

    private function buildFallbackAvatar(string $seed): string
    {
        $hash = substr(md5($seed), 0, 6);
        $bgPrimary = '#' . substr($hash . '2d8ff0', 0, 6);
        $bgSecondary = '#14b89a';
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bgPrimary}" />
      <stop offset="100%" stop-color="{$bgSecondary}" />
    </linearGradient>
  </defs>
  <rect width="160" height="160" rx="40" fill="url(#g)" />
  <circle cx="80" cy="58" r="26" fill="rgba(255,255,255,0.94)" />
  <path d="M36 132c6-24 25-38 44-38s38 14 44 38" fill="rgba(255,255,255,0.94)" />
</svg>
SVG;

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    private function storeManagedStaffImage(UploadedFile $profileImage): string
    {
        $directory = public_path('uploads/staff');
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($profileImage->getClientOriginalExtension());
        $safeExtension = $extension !== '' ? $extension : 'jpg';
        $filename = 'staff-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $safeExtension;

        $profileImage->move($directory, $filename);

        return 'uploads/staff/' . $filename;
    }

    private function deleteManagedStaffImage(string $imagePath): void
    {
        if ($imagePath === '' || preg_match('#^https?://#i', $imagePath) === 1) {
            return;
        }

        $normalizedPath = ltrim($imagePath, '/');
        if (strpos($normalizedPath, 'uploads/staff/') !== 0) {
            return;
        }

        $absolutePath = public_path($normalizedPath);
        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }

    private function getMetadata(): array
    {
        if ($this->metadataCache !== null) {
            return $this->metadataCache;
        }

        $path = storage_path(self::METADATA_RELATIVE_PATH);
        if (!File::exists($path)) {
            $this->metadataCache = [
                'staff_profiles' => [],
            ];

            return $this->metadataCache;
        }

        $decoded = json_decode((string) File::get($path), true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $decoded['staff_profiles'] = isset($decoded['staff_profiles']) && is_array($decoded['staff_profiles'])
            ? $decoded['staff_profiles']
            : [];

        $this->metadataCache = $decoded;

        return $this->metadataCache;
    }

    private function persistMetadata(array $metadata): void
    {
        $path = storage_path(self::METADATA_RELATIVE_PATH);
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($path, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->metadataCache = $metadata;
    }

    private function firstNonEmpty(array $values, string $fallback): string
    {
        foreach ($values as $value) {
            $normalized = trim((string) $value);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return $fallback;
    }

    private function tableExists(string $table): bool
    {
        if (!array_key_exists($table, $this->tableExistsCache)) {
            $this->tableExistsCache[$table] = Schema::hasTable($table);
        }

        return (bool) $this->tableExistsCache[$table];
    }

    private function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;

        if (!array_key_exists($cacheKey, $this->columnExistsCache)) {
            $this->columnExistsCache[$cacheKey] = $this->tableExists($table) && Schema::hasColumn($table, $column);
        }

        return (bool) $this->columnExistsCache[$cacheKey];
    }
}
