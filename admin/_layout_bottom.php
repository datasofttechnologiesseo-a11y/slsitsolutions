    </div><!-- /.content -->
  </div><!-- /.main -->
</div><!-- /.app -->

<!-- Logout confirmation modal -->
<div class="modal-back" id="logoutModal">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">
    <div class="modal-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
    <h3 id="logoutTitle">Sign out?</h3>
    <p>You'll be signed out of the admin panel and returned to the login page.</p>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost" onclick="closeLogout()">Cancel</button>
      <a href="logout.php" class="btn btn-danger"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
    </div>
  </div>
</div>

<script>
function toggleSidebar() {
  var s = document.getElementById('sidebar');
  var o = document.getElementById('sidebarOverlay');
  s.classList.toggle('open');
  o.classList.toggle('open');
}

function toggleDropdown() {
  document.getElementById('profileDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e){
  var w = document.getElementById('profileWrap');
  if (w && !w.contains(e.target)) {
    var dd = document.getElementById('profileDropdown');
    if (dd) dd.classList.remove('open');
  }
});

function confirmLogout(e) {
  if (e) e.preventDefault();
  document.getElementById('logoutModal').classList.add('open');
  document.getElementById('profileDropdown').classList.remove('open');
}
function closeLogout() {
  document.getElementById('logoutModal').classList.remove('open');
}
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') closeLogout();
});
document.getElementById('logoutModal').addEventListener('click', function(e){
  if (e.target === this) closeLogout();
});
</script>

</body>
</html>
