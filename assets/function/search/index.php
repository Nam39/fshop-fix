<?php function search($conn, $tableName, $searchFields = ['Ten', 'MoTa'], $idField = 'id', $limit = 10, $page_num = 1)
{
    $offset = ($page_num - 1) * $limit;
    $result = null;

    if (isset($_GET['query']) && !empty($_GET['query'])) {
        $search = '%' . trim($_GET['query']) . '%';

        // Tạo phần WHERE cho nhiều cột tìm kiếm
        $whereParts = [];
        foreach ($searchFields as $field) {
            $whereParts[] = "$field LIKE ?";
        }
        $whereClause = implode(" OR ", $whereParts);

        $sql = "SELECT * FROM $tableName WHERE $whereClause LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);

        // Chuẩn bị các tham số tìm kiếm
        $types = str_repeat('s', count($searchFields)) . "ii";
        $params = array_fill(0, count($searchFields), $search);
        $params[] = $limit;
        $params[] = $offset;

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } elseif (isset($_GET['queryid']) && !empty($_GET['queryid'])) {
        $search = '%' . trim($_GET['queryid']) . '%';
        $sql = "SELECT * FROM $tableName WHERE $idField LIKE ? LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $search, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM $tableName LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    return $result;
}
