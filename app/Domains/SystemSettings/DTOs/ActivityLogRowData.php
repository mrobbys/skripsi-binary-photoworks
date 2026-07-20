<?php

namespace App\Domains\SystemSettings\DTOs;

use Spatie\Activitylog\Models\Activity;
use Spatie\LaravelData\Data;
use App\Support\Formatter;

class ActivityLogRowData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $waktu_sesi,
    public readonly string $pelaku,
    public readonly string $modul,
    public readonly string $aktivitas,
    public readonly ?array $properties,
  ) {}

  public static function fromModel(Activity $activity): self
  {
    $subjectClass = $activity->subject_type
      ? class_basename($activity->subject_type)
      : '-';

    $modul = $activity->subject_id
      ? "{$subjectClass} (ID: {$activity->subject_id})"
      : $subjectClass;

    return new self(
      id: $activity->id,
      waktu_sesi: Formatter::dateId($activity->created_at, 'd M Y, H:i'),
      pelaku: $activity->causer?->name ?? 'Sistem',
      modul: $modul,
      aktivitas: $activity->description,
      properties: $activity->properties?->toArray(),
    );
  }
}
