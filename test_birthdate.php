require 'vendor/autoload.php';
use Carbon\Carbon;

Carbon::setTestNow(Carbon::parse('2026-05-14'));

function getAcademicYearCutoff($tahun_ajaran_nama = null) {
    $tahun_sekarang = Carbon::now()->year;
    if ($tahun_ajaran_nama) {
        $tahun_parts = explode('/', $tahun_ajaran_nama);
        $tahun = (int)$tahun_parts[1];
    } else {
        $tahun = $tahun_sekarang;
    }
    $cutoff = Carbon::parse($tahun . '-06-30');
    $min_birth_date = (new Carbon($tahun . '-06-30'))->subYears(7);
    return [
        'tahun_ajaran' => $tahun_ajaran_nama ?? 'fallback',
        'tahun' => $tahun,
        'cutoff_date' => $cutoff->format('Y-m-d'),
        'min_birth_date' => $min_birth_date->format('Y-m-d'),
        'cutoff_carbon' => $cutoff,
        'min_birth_carbon' => $min_birth_date
    ];
}

echo "SIMULATED TODAY: " . Carbon::now()->format('Y-m-d (l)') . PHP_EOL;
echo PHP_EOL;

$s1 = getAcademicYearCutoff();
echo "Scenario 1 - No tahun_ajaran_nama (fallback 2026)" . PHP_EOL;
echo "  Cutoff: " . $s1['cutoff_date'] . " | Min Birth: " . $s1['min_birth_date'] . " | Status: " . (($s1['tahun'] === 2026 && $s1['cutoff_date'] === '2026-06-30') ? 'PASS' : 'FAIL') . PHP_EOL;
echo PHP_EOL;

$s2 = getAcademicYearCutoff('2025/2026');
echo "Scenario 2 - tahun_ajaran_nama = 2025/2026" . PHP_EOL;
echo "  Cutoff: " . $s2['cutoff_date'] . " | Min Birth: " . $s2['min_birth_date'] . " | Status: " . (($s2['tahun'] === 2026 && $s2['cutoff_date'] === '2026-06-30') ? 'PASS' : 'FAIL') . PHP_EOL;
echo PHP_EOL;

$s3 = getAcademicYearCutoff('2026/2027');
echo "Scenario 3 - tahun_ajaran_nama = 2026/2027" . PHP_EOL;
echo "  Cutoff: " . $s3['cutoff_date'] . " | Min Birth: " . $s3['min_birth_date'] . " | Status: " . (($s3['tahun'] === 2027 && $s3['cutoff_date'] === '2027-06-30') ? 'PASS' : 'FAIL') . PHP_EOL;
echo PHP_EOL;

function isBirthDateValid($birth_date, $min_birth_date, $cutoff_date) {
    $birthDate = Carbon::parse($birth_date);
    return $birthDate >= $min_birth_date && $birthDate <= $cutoff_date;
}

echo "Edge Cases (using 2026/2027 ranges):" . PHP_EOL;
echo "  2020-06-30: " . (isBirthDateValid('2020-06-30', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'YES' : 'NO') . " | Expected: NO | " . (!isBirthDateValid('2020-06-30', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'PASS' : 'FAIL') . PHP_EOL;
echo "  2020-07-01: " . (isBirthDateValid('2020-07-01', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'YES' : 'NO') . " | Expected: NO | " . (!isBirthDateValid('2020-07-01', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'PASS' : 'FAIL') . PHP_EOL;
echo "  2021-06-30: " . (isBirthDateValid('2021-06-30', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'YES' : 'NO') . " | Expected: YES | " . (isBirthDateValid('2021-06-30', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'PASS' : 'FAIL') . PHP_EOL;
echo "  2021-07-01: " . (isBirthDateValid('2021-07-01', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'YES' : 'NO') . " | Expected: NO | " . (!isBirthDateValid('2021-07-01', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'PASS' : 'FAIL') . PHP_EOL;
echo "  2027-06-30: " . (isBirthDateValid('2027-06-30', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'YES' : 'NO') . " | Expected: YES | " . (isBirthDateValid('2027-06-30', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'PASS' : 'FAIL') . PHP_EOL;
echo "  2027-07-01: " . (isBirthDateValid('2027-07-01', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'YES' : 'NO') . " | Expected: NO | " . (!isBirthDateValid('2027-07-01', $s3['min_birth_carbon'], $s3['cutoff_carbon']) ? 'PASS' : 'FAIL') . PHP_EOL;
echo PHP_EOL;
echo "All tests completed successfully." . PHP_EOL;
