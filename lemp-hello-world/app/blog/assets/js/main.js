function openModal(sel){ document.querySelector(sel).classList.remove('hidden'); }
function closeModal(sel){ document.querySelector(sel).classList.add('hidden'); }
document.querySelectorAll('[data-close]').forEach(el=>el.addEventListener('click', e=>{
  const modal = e.target.closest('.modal'); if (modal) modal.classList.add('hidden');
}));

// Helpers HTML escape (para prevenir XSS en inyecciones peligrosas)
function escapeHtml(s){ return (s||'').replace(/[&<>"'`=\/]/g, t=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'}[t])); }
function decodeHtml(s){ const txt=document.createElement('textarea'); txt.innerHTML=s; return txt.value; }
