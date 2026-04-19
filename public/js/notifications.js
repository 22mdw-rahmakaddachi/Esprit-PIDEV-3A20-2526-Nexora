/**
 * TripShop — Notifications (polling léger, pas de SSE)
 */
(function () {
    'use strict';

    const POLL_INTERVAL = 15000; // 15 secondes
    const ICONS = { reaction:'👍', commentaire:'💬', avis:'⭐', default:'🔔' };

    let lastId      = 0;
    let unreadCount = 0;
    let badgeEl, panelEl, listEl, bellEl;

    function init() {
        badgeEl = document.getElementById('notif-badge');
        panelEl = document.getElementById('notif-panel');
        listEl  = document.getElementById('notif-list');
        bellEl  = document.getElementById('notif-bell');

        if (!bellEl) return;

        bellEl.addEventListener('click', togglePanel);
        document.addEventListener('click', e => {
            if (panelEl && !panelEl.contains(e.target) && !bellEl.contains(e.target)) {
                panelEl.classList.add('d-none');
            }
        });

        // Premier chargement
        poll();
        // Polling toutes les 15s
        setInterval(poll, POLL_INTERVAL);
    }

    function poll() {
        fetch('/notifications/api?since=' + lastId)
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                data.notifications.forEach(n => {
                    if (n.id > lastId) {
                        lastId = n.id;
                        addNotification(n, true);
                    }
                });
                // Sync badge avec le serveur
                if (lastId === 0) {
                    unreadCount = data.unread;
                    updateBadge();
                }
            })
            .catch(() => {});
    }

    function addNotification(notif, isNew) {
        if (!listEl) return;

        const icon = ICONS[notif.type] || ICONS.default;

        // Vider le placeholder "Aucune notification"
        const placeholder = listEl.querySelector('.notif-placeholder');
        if (placeholder) placeholder.remove();

        if (isNew) {
            unreadCount++;
            updateBadge();
            showToast(notif);
        }

        const item = document.createElement('div');
        item.className = 'notif-item' + (isNew ? ' notif-unread' : '');
        item.innerHTML = `
            <div class="notif-icon">${icon}</div>
            <div class="notif-body">
                <div class="notif-msg">${esc(notif.message)}</div>
                <div class="notif-time">${timeAgo(notif.created_at)}</div>
            </div>`;

        listEl.insertBefore(item, listEl.firstChild);

        // Max 20 items
        const all = listEl.querySelectorAll('.notif-item');
        if (all.length > 20) all[all.length - 1].remove();
    }

    function showToast(notif) {
        const icon  = ICONS[notif.type] || ICONS.default;
        const toast = document.createElement('div');
        toast.className = 'notif-toast';
        toast.innerHTML = `
            <span class="notif-toast-icon">${icon}</span>
            <span class="notif-toast-msg">${esc(notif.message)}</span>
            <button class="notif-toast-close" onclick="this.parentElement.remove()">✕</button>`;
        document.body.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('notif-toast-show'));
        setTimeout(() => {
            toast.classList.remove('notif-toast-show');
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }

    function togglePanel() {
        if (!panelEl) return;
        const hidden = panelEl.classList.toggle('d-none');
        if (!hidden && unreadCount > 0) {
            fetch('/notifications/read', { method: 'POST' });
            unreadCount = 0;
            updateBadge();
            listEl.querySelectorAll('.notif-unread').forEach(el => el.classList.remove('notif-unread'));
        }
    }

    function updateBadge() {
        if (!badgeEl) return;
        badgeEl.textContent = unreadCount > 99 ? '99+' : unreadCount;
        badgeEl.style.display = unreadCount > 0 ? 'flex' : 'none';
    }

    function timeAgo(str) {
        if (!str) return '';
        const d    = new Date(str.replace(' ', 'T'));
        const diff = Math.floor((Date.now() - d) / 1000);
        if (diff < 60)    return 'À l\'instant';
        if (diff < 3600)  return Math.floor(diff / 60) + ' min';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h';
        return d.toLocaleDateString('fr-FR');
    }

    function esc(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
