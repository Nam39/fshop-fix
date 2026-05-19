<?php
// Build dynamic query parameters for pagination to prevent conflicts
$queryParams = $_GET;
unset($queryParams['p']); // remove current 'p' parameter to override it
$queryString = http_build_query($queryParams);
$baseLink = $queryString ? '?' . $queryString . '&p=' : '?p=';
?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mt-4">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $baseLink . ($page - 1); ?>">&laquo;</a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo $baseLink . $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $baseLink . ($page + 1); ?>">&raquo;</a>
        </li>
    </ul>
</nav>