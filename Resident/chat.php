<?php
ob_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === CONFIGURATION ===
$useKobold = true;
$koboldUrl = 'https://loomy.canefly.xyz/api';
$openaiUrl = 'http://localhost:3000/v1';

function checkEndpoint($url, $suffix = '/extra/version') {
    $ch = curl_init($url . $suffix);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

if ($useKobold && (!checkEndpoint($koboldUrl))) {
    $useKobold = false;
}
$endpointUrl = $useKobold ? $koboldUrl . '/generate' : $openaiUrl . '/generate';

// === READ INCOMING DATA ===
$raw = file_get_contents("php://input");
$input = json_decode($raw, true);
$userMessage = isset($input['message']) ? trim($input['message']) : '';

if ($userMessage === '') {
    ob_end_clean();
    echo json_encode(["reply" => "⚠️ No message received."]);
    exit;
}

// === DEFINE MEMORY — permanent system persona context ===
$memory = <<<EOT
[This is a chat between a user and TanodAI, the built-in assistant for the TanodAI WebApp. TanodAI helps explain how the system works, focusing on real-world use, not roleplay or fiction.]

Purpose: TanodAI helps barangay admins and residents understand how to use the platform clearly and correctly. It explains feature logic, system flow, and backend behavior without going off-topic.

What TanodAI Knows:

Resident Verification Levels
    Level 0: Guest
    Level 1: Partial (view only)
    Level 2: Verified (full access)

Document Request System
    Residents can request documents (e.g., clearance)
    Admins approve or reject with status + remarks
    Notifications are sent automatically

Service Directory
    Residents can apply to list services (Licensed or Informal)
    Admin reviews submissions (Pending → Approved/Rejected)
    Verified listings are shown to others with tags and contact info

Announcements & Events
    Admins post barangay news or activities
    Residents can mark events as "Interested"

Push Notifications
    Triggered by: approvals, new announcements, events, etc.

🗣️ Tone & Behavior:

Strictly English

No roleplay, no small talk

Focused on system support and usage help
EOT;

// === BUILD FULL PROMPT SEPARATELY ===
$fullPrompt = "User: {$userMessage}\nTanodAI:";

// === FINAL PAYLOAD ===
$payload = [
    "memory" => $memory,
    "prompt" => $fullPrompt,
    "n" => 1,
    "max_context_length" => 4096,
    "max_length" => 240,
    "rep_pen" => 1.18,
    "temperature" => 0.75,
    "top_p" => 0.96,
    "top_k" => 40,
    "typical" => 1,
    "sampler_order" => [6, 0, 1, 3, 4, 2, 5],
    "trim_stop" => true,
    "stop_sequence" => ["User:", "\nUser ", "\nTanodAI:"]
];

// === SEND REQUEST ===
$ch = curl_init($endpointUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

$reply = "⚠️ No reply from Tanod.";
if (!$error && $response) {
    $data = json_decode($response, true);
    $reply = isset($data['choices'][0]['text']) ? trim($data['choices'][0]['text']) : ($data['response'] ?? $reply);
}

ob_end_clean();
echo json_encode(["reply" => $reply]);
exit;
