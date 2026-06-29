<?php
require_once 'includes/header.php';

// Fetch Logs
$stmt = $conn->query("
    SELECT al.*, u.full_name as admin_name 
    FROM admin_logs al 
    JOIN users u ON al.admin_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>

<div class="mb-8 flex items-center gap-4">
    <div>
        <h1 class="font-serif text-3xl font-bold">Nhật Ký Hệ Thống</h1>
        <p class="text-gray-500 mt-1">Lịch sử hoạt động của các quản trị viên.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm uppercase tracking-widest text-gray-500">
                    <th class="p-4 font-medium">Thời gian</th>
                    <th class="p-4 font-medium">Quản trị viên</th>
                    <th class="p-4 font-medium">Hành động</th>
                    <th class="p-4 font-medium">Chi tiết</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($logs)): ?>
                    <tr><td colspan="4" class="p-6 text-center text-gray-500">Chưa có bản ghi nhật ký nào.</td></tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                            <td class="p-4 font-medium text-brand-black"><?= htmlspecialchars($log['admin_name']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-200 text-gray-700">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td class="p-4 text-gray-600"><?= htmlspecialchars($log['details']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
