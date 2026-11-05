<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/script.js"></script>

<!-- 1. เรียกใช้ไลบรารี SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- 2. เรียกใช้สคริปต์แจ้งเตือนตามสิทธิ์การใช้งาน (Admin / User) -->
<?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
    <script src="assets/js/admin_notification.js?v=1"></script>
<?php elseif (isset($_SESSION['user_id'])): ?>
    <script src="assets/js/user_notification.js?v=1"></script>
<?php endif; ?>

</body>

</html>
