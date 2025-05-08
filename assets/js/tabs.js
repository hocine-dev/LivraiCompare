// assets/js/tabs.js

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tab-trigger').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-trigger').forEach(b => b.removeAttribute('data-state'));
        btn.setAttribute('data-state', 'active');
      });
    });
  });
  