        </main>
    </div> <!-- End Main Wrapper -->
</div> <!-- End Wrapper -->

<!-- Simple script for mobile sidebar toggle -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
    });
</script>
</body>
</html>
