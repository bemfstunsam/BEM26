    </main>
</div><!-- /.main content -->

<script>
// Mobile sidebar toggle
(function() {
    var btn = document.getElementById('sidebar-toggle');
    var sb  = document.getElementById('sidebar');
    if (btn && sb) {
        btn.addEventListener('click', function() {
            sb.classList.toggle('-translate-x-full');
        });
    }

    // Auto-dismiss flash message setelah 5 detik
    var flash = document.querySelector('[data-flash]');
    if (flash) {
        setTimeout(function() {
            flash.style.opacity = '0';
            flash.style.transition = 'opacity .5s';
            setTimeout(function() { flash.remove(); }, 500);
        }, 5000);
    }
})();
</script>
</body>
</html>
