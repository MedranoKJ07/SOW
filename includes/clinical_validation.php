<?php

function clinical_valid_date(string $value): bool
{
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function clinical_valid_time(string $value): bool
{
    $time = DateTime::createFromFormat('!H:i', $value);
    return $time !== false && $time->format('H:i') === $value;
}

function clinical_patient_exists(mysqli $db, int $patientId): bool
{
    if ($patientId <= 0) {
        return false;
    }
    $stmt = $db->prepare('SELECT 1 FROM pacientes WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_row();
}

function clinical_text(string $value, int $maxLength = 500): string
{
    return mb_substr(trim($value), 0, $maxLength);
}
