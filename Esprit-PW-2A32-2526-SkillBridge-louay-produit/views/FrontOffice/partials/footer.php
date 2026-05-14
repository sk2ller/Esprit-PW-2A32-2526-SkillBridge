
<footer style="background: rgba(30,30,32,.96); border-top: 1px solid rgba(255,255,255,.06); padding: 3rem 2rem; margin-top: 5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
  <div style="max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div style="color:#fff; font-weight:700; font-family:'Playfair Display',serif; font-size:1.15rem;">
      <i class="fas fa-bolt" style="color:var(--amber)"></i> SkillBridge
    </div>
    <div style="color:rgba(222,228,242,.55);">© <?= date('Y') ?> SkillBridge Produits. Tous droits réservés.</div>
    <div style="display:flex; gap:1rem;">
      <a href="#" style="color:rgba(222,228,242,.45); text-decoration:none; transition:color 0.2s; width:36px; height:36px; border-radius:12px; background:rgba(255,255,255,.06); display:flex; align-items:center; justify-content:center;" onmouseover="this.style.color='var(--amber)';this.style.background='rgba(224,112,32,.12)'" onmouseout="this.style.color='rgba(222,228,242,.45)';this.style.background='rgba(255,255,255,.06)'"><i class="fab fa-twitter"></i></a>
      <a href="#" style="color:rgba(222,228,242,.45); text-decoration:none; transition:color 0.2s; width:36px; height:36px; border-radius:12px; background:rgba(255,255,255,.06); display:flex; align-items:center; justify-content:center;" onmouseover="this.style.color='var(--amber)';this.style.background='rgba(224,112,32,.12)'" onmouseout="this.style.color='rgba(222,228,242,.45)';this.style.background='rgba(255,255,255,.06)'"><i class="fab fa-linkedin"></i></a>
      <a href="#" style="color:rgba(222,228,242,.45); text-decoration:none; transition:color 0.2s; width:36px; height:36px; border-radius:12px; background:rgba(255,255,255,.06); display:flex; align-items:center; justify-content:center;" onmouseover="this.style.color='var(--amber)';this.style.background='rgba(224,112,32,.12)'" onmouseout="this.style.color='rgba(222,228,242,.45)';this.style.background='rgba(255,255,255,.06)'"><i class="fab fa-github"></i></a>
    </div>
  </div>
</footer>

<!-- AI Assistant Widget -->
<link rel="stylesheet" href="views/assets/css/ai-assistant.css">
<script src="views/assets/js/ai-assistant.js"></script>
<script>
<?php
  $aiRole = $_SESSION['role'] ?? 'client';
  $aiContext = [
    'currentPage' => $_GET['page'] ?? 'home',
    'userName' => $_SESSION['user']['name'] ?? 'Invité'
  ];
?>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof AiAssistant !== 'undefined') {
    AiAssistant.init({
      role: '<?= $aiRole ?>',
      context: <?= json_encode($aiContext, JSON_UNESCAPED_UNICODE) ?>
    });
  }
});
</script>
</body>
</html>
