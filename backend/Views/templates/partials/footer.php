
  </main><!-- /.main-content -->
</div><!-- /.main-wrapper -->

<script>
  // ── Menu Mobile ──────────────────────────────
  const btnMenu  = document.getElementById('btnMenu');
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('overlay');

  if (btnMenu) {
    btnMenu.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });
  }

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }

  // ── Auto-dismiss flash messages ──────────────
  document.querySelectorAll('.flash-message').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s ease, transform .4s ease';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-8px)';
      setTimeout(() => el.remove(), 400);
    }, 5000);
  });
</script>

</body>
</html>
