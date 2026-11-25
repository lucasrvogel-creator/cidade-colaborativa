<?php
declare(strict_types=1);

function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = '/', array $query = []): string {
    return '/index.php?' . http_build_query(['r' => $path] + $query);
}
function redirect(string $path, array $query = []): never { header('Location: ' . url($path, $query)); exit; }
function csrf(): string { return '<input type="hidden" name="_csrf" value="' . e($_SESSION['csrf']) . '">'; }
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function datebr(?string $value): string { return $value ? date('d/m/Y H:i', strtotime($value)) : '—'; }
function status_label(string $value): string { return match ($value) {
    'PENDENTE' => 'Pendente', 'CORRECAO_SOLICITADA' => 'Correção solicitada', 'PUBLICADA' => 'Publicada',
    'EM_ACOMPANHAMENTO' => 'Em acompanhamento', 'RESOLVIDA' => 'Resolvida', 'REJEITADA' => 'Rejeitada',
    'DUPLICADA' => 'Duplicada', 'ARQUIVADA' => 'Arquivada', default => $value}; }
