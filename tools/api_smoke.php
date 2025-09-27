<?php

function request_json(string $method, string $url, array $headers = [], $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    // Ensure API responses are JSON (avoid 302 HTML redirects)
    $headers = array_merge(['Accept' => 'application/json'], $headers);
    $hdrs = [];
    foreach ($headers as $k => $v) { $hdrs[] = $k . ': ' . $v; }
    if ($body !== null) {
        $json = json_encode($body);
        $hdrs[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    }
    if ($hdrs) curl_setopt($ch, CURLOPT_HTTPHEADER, $hdrs);
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) throw new Exception($err);
    $data = json_decode($resp, true);
    return [$status, $data, $resp];
}

function request_form(string $url, array $headers = [], array $fields = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    $headers = array_merge(['Accept' => 'application/json', 'Content-Type' => 'application/x-www-form-urlencoded'], $headers);
    $hdrs = [];
    foreach ($headers as $k => $v) { $hdrs[] = $k . ': ' . $v; }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $hdrs);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) throw new Exception($err);
    $data = json_decode($resp, true);
    return [$status, $data, $resp];
}

$base = 'http://127.0.0.1:8000';
$summary = [];

// --- Auth: Register, Login (success), Login (failure) ---
// Use a unique email per run
$regEmail = 'reg.' . time() . '@example.com';
$regPassword = 'password123';

// Register new user and expect token in response
[$regCode, $regData, $regRaw] = request_json('POST', $base . '/api/register', [], [
    'name' => 'Smoke Register',
    'email' => $regEmail,
    'password' => $regPassword,
    'password_confirmation' => $regPassword,
    'role' => 'beneficiary',
]);
$summary['register'] = [
    'status' => $regCode,
    'token_present' => isset($regData['token']) && is_string($regData['token']) && $regData['token'] !== '',
    'token_type' => $regData['token_type'] ?? null,
    'email' => $regEmail,
];

// Login with the newly registered user
[$loginOkCode, $loginOkData, $loginOkRaw] = request_json('POST', $base . '/api/login', [], [
    'email' => $regEmail,
    'password' => $regPassword,
]);
$summary['login_success'] = [
    'status' => $loginOkCode,
    'token_present' => isset($loginOkData['token']) && is_string($loginOkData['token']) && $loginOkData['token'] !== '',
    'token_type' => $loginOkData['token_type'] ?? null,
];

// Login failure scenario (wrong password)
[$loginBadCode, $loginBadData, $loginBadRaw] = request_json('POST', $base . '/api/login', [], [
    'email' => $regEmail,
    'password' => 'wrong-password',
]);
$summary['login_failure'] = [
    'status' => $loginBadCode,
    'message' => $loginBadData['message'] ?? null,
];

// Login
[$code, $data] = request_json('POST', $base . '/api/login', [], [
    'email' => 'admin@humanitarian.aid',
    'password' => 'password'
]);
if ($code !== 200 || empty($data['token'])) {
    echo json_encode(['error' => 'login_failed', 'code' => $code, 'data' => $data]);
    exit(1);
}
$token = $data['token'];
$headers = ['Authorization' => 'Bearer ' . $token];
$summary['login_user'] = $data['user']['email'] ?? null;

// /api/user
[$code, $me] = request_json('GET', $base . '/api/user', $headers);
$summary['user'] = ['id' => $me['id'] ?? null, 'role' => $me['role'] ?? null];

// Users helpers
[$code, $vols] = request_json('GET', $base . '/api/users/volunteers', $headers);
[$code, $bens] = request_json('GET', $base . '/api/users/beneficiaries', $headers);
if (is_array($vols)) { $vols = array_values($vols); }
if (is_array($bens)) { $bens = array_values($bens); }
$summary['volunteers_count'] = is_array($vols) ? count($vols) : 0;
$summary['beneficiaries_count'] = is_array($bens) ? count($bens) : 0;
$summary['volunteer_sample'] = is_array($vols) && isset($vols[0]) ? $vols[0] : null;
$summary['beneficiary_sample'] = is_array($bens) && isset($bens[0]) ? $bens[0] : null;

// Ensure at least one volunteer and one beneficiary exist and are visible
if (empty($vols) || empty($bens)) {
    // Create a volunteer
[$code, $newVol, $rawVol] = request_json('POST', $base . '/api/users', $headers, [
        'name' => 'Smoke Volunteer',
        'email' => 'smoke.volunteer+'.time().'@example.com',
        'password' => 'password',
        'role' => 'volunteer'
    ]);
    // Create a beneficiary
[$code, $newBen, $rawBen] = request_json('POST', $base . '/api/users', $headers, [
        'name' => 'Smoke Beneficiary',
        'email' => 'smoke.beneficiary+'.time().'@example.com',
        'password' => 'password',
        'role' => 'beneficiary'
    ]);
    // Try reload lists
    [$code, $vols] = request_json('GET', $base . '/api/users/volunteers', $headers);
    [$code, $bens] = request_json('GET', $base . '/api/users/beneficiaries', $headers);
    if (is_array($vols)) { $vols = array_values($vols); }
    if (is_array($bens)) { $bens = array_values($bens); }
    $summary['volunteers_count'] = is_array($vols) ? count($vols) : 0;
    $summary['beneficiaries_count'] = is_array($bens) ? count($bens) : 0;
    // Fallback to direct IDs if lists still empty
    if ((empty($vols) || empty($bens)) && isset($newVol['id']) && isset($newBen['id'])) {
        $vols = [ $newVol ];
        $bens = [ $newBen ];
    }
}

// Donations
[$code, $don] = request_json('POST', $base . '/api/donations', $headers, [
    'donor_name' => 'QA Donor', 'type' => 'food', 'quantity' => 5
]);
[$code, $donApproved] = request_json('POST', $base . '/api/donations/' . $don['id'] . '/approve', $headers);
$summary['donation'] = ['id' => $donApproved['id'] ?? null, 'status' => $donApproved['status'] ?? null];

// Distribution
$volId = $vols[0]['id'] ?? null;
$benId = $bens[0]['id'] ?? null;
$summary['selected_volunteer_id'] = $volId;
$summary['selected_beneficiary_id'] = $benId;

// Try JSON first
[$code, $dist, $rawDist] = request_json('POST', $base . '/api/distributions', $headers, [
    'volunteer_id' => $volId,
    'beneficiary_id' => $benId,
    'donation_id' => $donApproved['id'] ?? null
]);
if (!isset($dist['id'])) {
    // Fallback to form-encoded
    [$code, $dist, $rawDist] = request_form($base . '/api/distributions', $headers, [
        'volunteer_id' => $volId,
        'beneficiary_id' => $benId,
        'donation_id' => $donApproved['id'] ?? null
    ]);
}
$summary['distribution'] = ['id' => $dist['id'] ?? null, 'delivery_status' => $dist['delivery_status'] ?? null];
if (!$summary['distribution']['id']) {
    $summary['distribution_error'] = ['status' => $code, 'body' => $rawDist];
}

// Aid requests index
[$code, $aidIndex] = request_json('GET', $base . '/api/aid-requests', $headers);
$summary['aid_requests_count'] = is_array($aidIndex) ? count($aidIndex) : 0;

// Dashboard
[$code, $stats] = request_json('GET', $base . '/api/dashboard/stats', $headers);
[$code, $activity] = request_json('GET', $base . '/api/dashboard/activity', $headers);
[$code, $charts] = request_json('GET', $base . '/api/dashboard/charts', $headers);
$summary['dashboard_stats'] = $stats;

// Notifications
[$code, $notifs] = request_json('GET', $base . '/api/notifications', $headers);
[$code, $unread] = request_json('GET', $base . '/api/notifications/unread-count', $headers);
[$code, $mark] = request_json('POST', $base . '/api/notifications/mark-all-read', $headers, []);
[$code, $unread2] = request_json('GET', $base . '/api/notifications/unread-count', $headers);
$summary['notifications_before'] = $unread['count'] ?? null;
$summary['notifications_after'] = $unread2['count'] ?? null;

echo json_encode($summary, JSON_PRETTY_PRINT);


