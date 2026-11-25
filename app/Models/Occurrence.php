<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\{HttpError, OccurrenceState};

final class Occurrence
{
    public static function filters(array $input): array
    {
        $where = ["o.status IN ('PUBLICADA','EM_ACOMPANHAMENTO','RESOLVIDA')"];
        $params = [];
        foreach (['categoria' => 'categoria_id', 'bairro' => 'bairro_id'] as $key => $column) {
            if (!empty($input[$key])) {
                $where[] = "o.$column=?";
                $params[] = (int)$input[$key];
            }
        }
        if (!empty($input['situacao']) && in_array($input['situacao'], OccurrenceState::PUBLIC, true)) {
            $where[] = 'o.status=?';
            $params[] = $input['situacao'];
        }
        if (!empty($input['q']) && is_string($input['q'])) {
            $where[] = '(o.titulo LIKE ? OR o.descricao LIKE ?)';
            $params[] = '%' . mb_substr($input['q'], 0, 100) . '%';
            $params[] = end($params);
        }
        foreach (['inicio' => '>=', 'fim' => '<='] as $key => $operator) {
            if (!empty($input[$key]) && is_string($input[$key]) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $input[$key])) {
                $where[] = "o.publicado_em $operator ?";
                $params[] = $input[$key] . ($key === 'fim' ? ' 23:59:59' : ' 00:00:00');
            }
        }
        if (!empty($input['bbox']) && is_string($input['bbox'])) {
            $bounds = explode(',', $input['bbox']);
            if (count($bounds) === 4 && count(array_filter($bounds, 'is_numeric')) === 4) {
                $where[] = 'o.longitude BETWEEN ? AND ? AND o.latitude BETWEEN ? AND ?';
                array_push($params, (float)$bounds[0], (float)$bounds[2], (float)$bounds[1], (float)$bounds[3]);
            }
        }
        return [implode(' AND ', $where), $params];
    }
    public static function listing(array $input, bool $map = false): array
    {
        [$where, $params] = self::filters($input);
        $page = max(1, (int)($input['page'] ?? 1));
        $limit = $map ? 500 : 12;
        $offset = $map ? 0 : ($page - 1) * $limit;
        $rows = Database::all("SELECT o.id,o.titulo,o.descricao,o.latitude,o.longitude,o.status,o.publicado_em,o.demonstracao,c.nome categoria,c.icone,c.cor,b.nome bairro,
            (SELECT COUNT(*) FROM confirmacoes cf WHERE cf.ocorrencia_id=o.id) confirmacoes
            FROM ocorrencias o JOIN categorias c ON c.id=o.categoria_id JOIN bairros b ON b.id=o.bairro_id WHERE $where ORDER BY o.publicado_em DESC,o.id DESC LIMIT $limit OFFSET $offset", $params);
        $total = (int)Database::one("SELECT COUNT(*) total FROM ocorrencias o WHERE $where", $params)['total'];
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $limit), 'limited' => $map && $total > $limit];
    }
    public static function find(int $id, ?array $user): array
    {
        $row = Database::one('SELECT o.*,c.nome categoria,b.nome bairro,s.nome subcategoria FROM ocorrencias o JOIN categorias c ON c.id=o.categoria_id JOIN bairros b ON b.id=o.bairro_id LEFT JOIN subcategorias s ON s.id=o.subcategoria_id WHERE o.id=?', [$id]);
        if (!$row || (!in_array($row['status'], OccurrenceState::PUBLIC, true) && (!$user || (!in_array($user['perfil'], ['admin', 'operador'], true) && (int)$user['id'] !== (int)$row['usuario_id'])))) throw new HttpError(404, 'Ocorrência não encontrada.');
        return $row;
    }
    public static function taxonomy(): array
    {
        return ['categories' => Database::all('SELECT * FROM categorias WHERE ativo=1 ORDER BY nome'), 'neighborhoods' => Database::all('SELECT * FROM bairros WHERE ativo=1 ORDER BY nome'), 'subcategories' => Database::all('SELECT * FROM subcategorias WHERE ativo=1 ORDER BY nome')];
    }
    public static function similar(array $row): array
    {
        return Database::all("SELECT id,titulo,status,latitude,longitude FROM ocorrencias WHERE categoria_id=? AND id<>? AND status IN ('PUBLICADA','EM_ACOMPANHAMENTO','RESOLVIDA') AND criado_em>=DATE_SUB(NOW(),INTERVAL 30 DAY) AND ST_DISTANCE_SPHERE(POINT(longitude,latitude),POINT(?,?))<=300 LIMIT 8", [$row['categoria_id'], $row['id'] ?? 0, $row['longitude'], $row['latitude']]);
    }
}
