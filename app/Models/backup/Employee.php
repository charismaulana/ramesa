<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'employee_number',
        'name',
        'company',
        'position',
        'department',
        'location',
        'accommodation',
        'active_status',
        'employee_status',
        'qr_code_path',
    ];

    protected static function booted(): void
    {
        static::created(function (Employee $employee) {
            $employee->generateQrCode();
        });

        static::updated(function (Employee $employee) {
            if ($employee->wasChanged('employee_number')) {
                $employee->generateQrCode();
            }
        });
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function generateQrCode(): void
    {
        $qrCodePath = 'qrcodes/' . $this->employee_number . '.svg';

        $qrCode = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($this->employee_number);

        Storage::disk('public')->put($qrCodePath, $qrCode);

        $this->updateQuietly(['qr_code_path' => $qrCodePath]);
    }

    public function getQrCodeUrlAttribute(): string
    {
        return $this->qr_code_path
            ? Storage::url($this->qr_code_path)
            : '';
    }

    /**
     * Generate employee number for visitors and subcontractors
     * Format: PREFIX-PEP-001 (e.g., VIS-PEP-001, SUBA-PEP-001)
     */
    public static function generateEmployeeNumber(?string $company): string
    {
        $prefix = self::getCompanyPrefix($company);
        $date = Carbon::now();

        // Get the last employee with the same prefix for today
        $lastEmployee = self::where('employee_number', 'like', $prefix . '-PEP-%')
            ->orderBy('employee_number', 'desc')
            ->first();

        if ($lastEmployee) {
            // Extract the sequence number
            preg_match('/(\d+)$/', $lastEmployee->employee_number, $matches);
            $sequence = isset($matches[1]) ? ((int) $matches[1]) + 1 : 1;
        } else {
            $sequence = 1;
        }

        return $prefix . '-PEP-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get prefix from company name
     */
    protected static function getCompanyPrefix(?string $company): string
    {
        if (empty($company)) {
            return 'VIS';
        }

        $company = strtoupper(trim($company));

        // Check for specific keywords
        if (str_contains($company, 'VISITOR') || str_contains($company, 'TAMU')) {
            return 'VIS';
        }

        // Remove common prefixes
        $company = preg_replace('/^(PT|CV|UD|PERUSAHAAN|YAYASAN)\s*/i', '', $company);

        // Get first 4 characters (or less if company name is shorter)
        $prefix = substr(preg_replace('/[^A-Z0-9]/', '', $company), 0, 4);

        return $prefix ?: 'VIS';
    }
}

