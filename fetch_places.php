<?php
include "includes/connection.php";

// --- CONFIG ---
$limit = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_query = "";
if ($search) {
    $search_query = " WHERE name LIKE '%$search%' OR detail LIKE '%$search%' ";
}

// Count Total
$sql_count = "SELECT COUNT(*) as total FROM places" . $search_query;
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_items = $row_count['total'];
$total_pages = ceil($total_items / $limit);

// Fetch Items
$sql = "SELECT * FROM places" . $search_query . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<div class="grid">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): 
            $img = !empty($row['image']) ? 'assets/images/'.$row['image'] : 'https://via.placeholder.com/400x300.png?text=No+Image';
            $short = mb_strlen($row['detail']) > 150 ? mb_substr($row['detail'], 0, 150).'...' : $row['detail'];
        ?>
        <div class="card" onclick="window.location.href='place_detail.php?id=<?php echo $row['id']; ?>'">
            <div class="card-img-container">
                <img src="<?php echo $img; ?>" class="card-img">
                <div class="card-badge"><i class="fas fa-eye"></i> <?php echo number_format($row['views']); ?></div>
            </div>
            <div class="card-body">
                <h3 class="card-title"><?php echo $row['name']; ?></h3>
                <p class="card-text"><?php echo $short; ?></p>
                <div class="card-footer">
                    <div class="view-more">รายละเอียด <i class="fas fa-arrow-right"></i></div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state" style="text-align:center; padding:50px; grid-column: 1/-1;">
            <h3>ไม่พบข้อมูล</h3>
            <?php if($search): ?><a href="index.php" style="color:var(--primary);">กลับหน้าแรก</a><?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination (Send back new links) -->
<?php if($total_pages > 1): ?>
<div class="pagination">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
           class="page-link <?php echo (($i==$page)?'active':''); ?>"
           onclick="event.preventDefault(); loadPage(<?php echo $i; ?>);">
           <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
