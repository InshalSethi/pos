<?php

namespace App\Traits;

use Carbon\Carbon;
use DateTimeInterface;

trait HasUtcDatabaseTimezones
{
    /**
     * Convert a DateTime to a storable string.
     *
     * @param  mixed  $value
     * @return string|null
     */
    public function fromDateTime($value)
    {
        if (empty($value)) {
            return $value;
        }

        // Convert the date to UTC timezone before formatting for database storage
        return $this->asDateTime($value)->setTimezone('UTC')->format($this->getDateFormat());
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Illuminate\Support\Carbon
     */
    protected function asDateTime($value)
    {
        if (empty($value)) {
            return parent::asDateTime($value);
        }

        if (is_string($value)) {
            // Normalize standard SQL formats (remove milliseconds if any)
            $cleanValue = preg_replace('/\.\d+$/', '', $value);
            if (!preg_match('/[+-]\d{2}:?\d{2}$/', $value) && !str_ends_with($value, 'Z')) {
                try {
                    // Try to parse using UTC as database stores in UTC
                    return Carbon::parse($cleanValue, 'UTC')->setTimezone(config('app.timezone'));
                } catch (\Exception $e) {
                    // Fallback to parent
                }
            }
        }

        $dt = parent::asDateTime($value);
        if ($dt instanceof Carbon) {
            return $dt->setTimezone(config('app.timezone'));
        }
        return $dt;
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        // Convert to the runtime timezone (config('app.timezone')) and output in ISO 8601 format
        return Carbon::instance($date)->setTimezone(config('app.timezone'))->toIso8601String();
    }
}
