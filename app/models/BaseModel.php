<?php
class BaseModel {
    protected mysqli $conn;
    public function __construct(mysqli $conn) { $this->conn = $conn; }

    protected function rows(string $sql, string $types = '', array $params = []): array {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { return []; }
        if ($types) { $stmt->bind_param($types, ...$params); }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    protected function row(string $sql, string $types = '', array $params = []): ?array {
        $rows = $this->rows($sql, $types, $params);
        return $rows[0] ?? null;
    }

    protected function execute(string $sql, string $types = '', array $params = []): bool {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { return false; }
        if ($types) { $stmt->bind_param($types, ...$params); }
        return $stmt->execute();
    }

    protected function insert(string $sql, string $types = '', array $params = []): int {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { return 0; }
        if ($types) { $stmt->bind_param($types, ...$params); }
        $stmt->execute();
        return $this->conn->insert_id;
    }
}
?>
