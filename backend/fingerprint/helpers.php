<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

/**
 * Check if fingerprint is already enrolled for a user.
 * 
 * @param PDO $pdo
 * @param string $fingerprintHash
 * @return bool
 */
function isFingerprintEnrolled(PDO $pdo, string $fingerprintHash): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM fingerprints WHERE fingerprint_hash = :hash");
    $stmt->execute([':hash' => $fingerprintHash]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Enroll fingerprint data for a student.
 * 
 * @param PDO $pdo
 * @param int $studentId
 * @param string $fingerprintHash
 * @return bool
 */
function enrollFingerprint(PDO $pdo, int $studentId, string $fingerprintHash): bool
{
    // Avoid duplicate enrollment
    if (isFingerprintEnrolled($pdo, $fingerprintHash)) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO fingerprints (student_id, fingerprint_hash, enrolled_at) 
        VALUES (:studentId, :hash, NOW())
    ");
    return $stmt->execute([
        ':studentId' => $studentId,
        ':hash' => $fingerprintHash
    ]);
}

/**
 * Get student ID from fingerprint hash.
 * 
 * @param PDO $pdo
 * @param string $fingerprintHash
 * @return int|null Returns student ID if found, else null
 */
function getStudentIdByFingerprint(PDO $pdo, string $fingerprintHash): ?int
{
    $stmt = $pdo->prepare("SELECT student_id FROM fingerprints WHERE fingerprint_hash = :hash LIMIT 1");
    $stmt->execute([':hash' => $fingerprintHash]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['student_id'] : null;
}
