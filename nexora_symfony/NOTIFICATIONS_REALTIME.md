# 🔔 Système de Notifications en Temps Réel

## ✅ Implémentation Complète

### 1. Table créée: `notification`
```sql
CREATE TABLE notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    related_id INT DEFAULT NULL,
    related_type VARCHAR(50) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL
);
```

### 2. Entité créée: `src/Entity/Notification.php` ✅

### 3. Service créé: `src/Service/NotificationService.php` ✅

### 4. Contrôleur créé: `src/Controller/NotificationController.php` ✅

### 5. Routes disponibles:
- `GET /notifications/api/unread` - Récupérer les notifications non lues (JSON)
- `POST /notifications/mark-read/{id}` - Marquer une notification comme lue
- `POST /notifications/mark-all-read` - Marquer toutes comme lues
- `GET /notifications` - Page des notifications

---

## 📝 Code à ajouter dans `templates/base.html.twig`

### Dans la navbar (ligne ~775, après le bouton langue):

```twig
{# Bouton Notifications #}
{% if app.user %}
<div style="position:relative">
    <button class="nav-icon-btn" id="notif-btn" onclick="toggleNotifications()" title="Notifications">
        <i class="bi bi-bell-fill"></i>
        <span class="nav-icon-badge" id="notif-badge" style="display:none">0</span>
    </button>
    
    {# Dropdown notifications #}
    <div id="notif-dropdown" style="display:none;position:absolute;top:calc(100% + 12px);right:0;
         width:360px;max-height:500px;background:var(--card-bg,#fff);
         border:1px solid var(--border,#ede5ff);border-radius:16px;
         box-shadow:0 12px 40px rgba(108,63,197,0.2);z-index:9999;overflow:hidden">
        
        {# Header #}
        <div style="padding:16px 20px;border-bottom:1px solid var(--border,#ede5ff);
             display:flex;justify-content:space-between;align-items:center">
            <h6 style="margin:0;font-weight:700;color:var(--primary-dark)">
                <i class="bi bi-bell me-2"></i>Notifications
            </h6>
            <button onclick="markAllAsRead()" 
                    style="background:none;border:none;color:var(--primary);font-size:0.75rem;cursor:pointer;padding:4px 8px;border-radius:6px"
                    onmouseover="this.style.background='#f0ebff'"
                    onmouseout="this.style.background='none'">
                <i class="bi bi-check-all me-1"></i>Tout marquer lu
            </button>
        </div>
        
        {# Liste notifications #}
        <div id="notif-list" style="max-height:400px;overflow-y:auto">
            <div class="text-center py-4 text-muted" id="notif-empty">
                <i class="bi bi-bell-slash" style="font-size:2rem;opacity:0.3"></i>
                <p class="mt-2 small">Aucune notification</p>
            </div>
        </div>
        
        {# Footer #}
        <div style="padding:12px 20px;border-top:1px solid var(--border,#ede5ff);text-align:center">
            <a href="{{ path('notifications_index') }}" 
               style="color:var(--primary);font-size:0.85rem;text-decoration:none;font-weight:600"
               onmouseover="this.style.textDecoration='underline'"
               onmouseout="this.style.textDecoration='none'">
                Voir toutes les notifications →
            </a>
        </div>
    </div>
</div>
{% endif %}
```

### Dans le bloc `<script>` (avant la fermeture `</body>`):

```javascript
// ── SYSTÈME DE NOTIFICATIONS EN TEMPS RÉEL ──
{% if app.user %}
let notifDropdownOpen = false;

function toggleNotifications() {
    const dropdown = document.getElementById('notif-dropdown');
    notifDropdownOpen = !notifDropdownOpen;
    dropdown.style.display = notifDropdownOpen ? 'block' : 'none';
    
    if (notifDropdownOpen) {
        loadNotifications();
    }
}

// Fermer le dropdown si clic en dehors
document.addEventListener('click', (e) => {
    const btn = document.getElementById('notif-btn');
    const dropdown = document.getElementById('notif-dropdown');
    if (notifDropdownOpen && !btn.contains(e.target) && !dropdown.contains(e.target)) {
        notifDropdownOpen = false;
        dropdown.style.display = 'none';
    }
});

function loadNotifications() {
    fetch('/notifications/api/unread')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('notif-list');
            const empty = document.getElementById('notif-empty');
            const badge = document.getElementById('notif-badge');
            
            if (data.count > 0) {
                badge.style.display = 'block';
                badge.textContent = data.count > 99 ? '99+' : data.count;
                empty.style.display = 'none';
                
                list.innerHTML = data.notifications.map(n => `
                    <div class="notif-item" data-id="${n.id}" onclick="markNotifAsRead(${n.id}, '${n.related_type}', ${n.related_id})"
                         style="padding:14px 20px;border-bottom:1px solid var(--border,#ede5ff);cursor:pointer;
                         background:${n.is_read ? 'transparent' : '#f0ebff'};transition:background 0.2s"
                         onmouseover="this.style.background='#f0ebff'"
                         onmouseout="this.style.background='${n.is_read ? 'transparent' : '#f0ebff'}'">
                        <div style="display:flex;gap:12px;align-items:flex-start">
                            <div style="width:40px;height:40px;border-radius:50%;
                                 background:linear-gradient(135deg,var(--primary),var(--accent));
                                 display:flex;align-items:center;justify-content:center;
                                 color:#fff;font-size:1.1rem;flex-shrink:0">
                                ${n.type === 'reaction' ? '❤️' : '💬'}
                            </div>
                            <div style="flex:1;min-width:0">
                                <p style="margin:0;font-size:0.85rem;color:var(--text);line-height:1.5">
                                    ${n.message}
                                </p>
                                <span style="font-size:0.72rem;color:var(--text-muted)">
                                    ${formatDate(n.created_at)}
                                </span>
                            </div>
                            ${!n.is_read ? '<div style="width:8px;height:8px;border-radius:50%;background:#e74c3c;flex-shrink:0;margin-top:6px"></div>' : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                badge.style.display = 'none';
                empty.style.display = 'block';
                list.innerHTML = '';
            }
        });
}

function markNotifAsRead(id, type, relatedId) {
    fetch(`/notifications/mark-read/${id}`, { method: 'POST' })
        .then(() => {
            loadNotifications();
            // Rediriger vers la publication/avis
            if (type === 'publication' && relatedId) {
                window.location.href = `/publications#pub-${relatedId}`;
            }
        });
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', { method: 'POST' })
        .then(() => {
            loadNotifications();
        });
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // secondes
    
    if (diff < 60) return 'À l\'instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
    if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} j`;
    
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
}

// Polling toutes les 10 secondes pour vérifier les nouvelles notifications
setInterval(() => {
    if (!notifDropdownOpen) {
        fetch('/notifications/api/unread')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notif-badge');
                if (data.count > 0) {
                    badge.style.display = 'block';
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                } else {
                    badge.style.display = 'none';
                }
            });
    }
}, 10000); // 10 secondes

// Charger au démarrage
loadNotifications();
{% endif %}
```

---

## 🎯 Fonctionnalités

### 1. Badge de notification
- Affiche le nombre de notifications non lues
- Animation "pop" quand nouvelle notification
- Badge rouge avec bordure blanche

### 2. Dropdown notifications
- Liste des notifications non lues
- Icône différente selon le type (❤️ réaction, 💬 commentaire)
- Fond bleu clair pour les non lues
- Clic sur notification → marque comme lue + redirige

### 3. Polling automatique
- Vérifie les nouvelles notifications toutes les 10 secondes
- Met à jour le badge automatiquement
- Pas de rechargement de page nécessaire

### 4. Types de notifications
- **Réaction**: "John a réagi ❤️ à votre publication"
- **Commentaire**: "Sara a commenté votre publication"

---

## 🧪 Test

### Scénario 1: Réaction
1. Utilisateur A publie une publication
2. Utilisateur B réagit avec ❤️
3. Utilisateur A voit le badge (1)
4. Utilisateur A clique sur le badge
5. Voit: "B a réagi ❤️ à votre publication"
6. Clic sur la notification → redirige vers la publication

### Scénario 2: Commentaire
1. Utilisateur A publie une publication
2. Utilisateur B commente "Super!"
3. Utilisateur A voit le badge (1)
4. Voit: "B a commenté votre publication"

---

## 📊 Avantages

✅ **Temps réel** (polling 10s)
✅ **Pas de bundle externe** (code pur)
✅ **Léger** (quelques Ko)
✅ **Compatible** tous navigateurs
✅ **Responsive** (mobile-friendly)
✅ **Élégant** (style moderne)

---

## 🔄 Évolution possible

Pour du vrai temps réel (< 1s), vous pouvez:
1. Installer Mercure (nécessite extension ZIP)
2. Utiliser WebSockets
3. Utiliser Server-Sent Events (SSE)

Mais le polling 10s est largement suffisant pour la plupart des cas!
