<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Prevent browser back-button cache
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/db_connect.php';

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function isLoggedIn(): bool { return isset($_SESSION['user']); }
function currentUser(): ?array { return $_SESSION['user'] ?? null; }
function isAdmin(): bool { return isLoggedIn() && ($_SESSION['user']['role'] ?? 'user') === 'admin'; }
function redirect(string $path): void { header('Location: ' . $path); exit; }
function requireLogin(): void { sendNoCacheHeaders();   if (!isLoggedIn()) { setFlash('error', 'Please login first.'); redirect('/larong_pinoy/login.php'); } }
function requireAdmin(): void {  sendNoCacheHeaders();  if (!isAdmin()) { setFlash('error', 'Admin access required.'); redirect('/larong_pinoy/index.php'); } }
function setFlash(string $type, string $message): void { $_SESSION['flash'] = ['type'=>$type,'message'=>$message]; }
function getFlash(): ?array { if (!isset($_SESSION['flash'])) return null; $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f; }

function refreshUserSession(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare('SELECT user_id, username, email, first_name, last_name, role FROM User_Account WHERE user_id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if ($user) { $_SESSION['user'] = $user; }
}

function riskColor(string $level): string {
    return $level === 'High' ? '#c0392b' : ($level === 'Medium' ? '#d68910' : '#1e8449');
}

function sendNoCacheHeaders(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
}