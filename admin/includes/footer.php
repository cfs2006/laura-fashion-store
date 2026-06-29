    </main>
</div> <!-- End Main Content Container -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('admin-menu-btn');
    const closeBtn = document.getElementById('admin-close-menu-btn');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-sidebar-overlay');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    }

    if(menuBtn) menuBtn.addEventListener('click', openSidebar);
    if(closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if(overlay) overlay.addEventListener('click', closeSidebar);
});
</script>
</body>
</html>
