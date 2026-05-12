/* ============================================
   Notifications Page - JavaScript
   وظائف صفحة التنبيهات
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 Notifications Page Loaded');
    
    // تفعيل جميع الوظائف
    initFilters();
    initNotificationActions();
    initBulkActions();
    initSettingsModal();
    initLoadMore();
});

// ============================================
// 1. Filters - الفلاتر
// ============================================
function initFilters() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // إزالة active من جميع التبويبات
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            applyFilter(filter);
        });
    });
}

function applyFilter(filter) {
    const notifications = document.querySelectorAll('.notification-card');
    let visibleCount = 0;
    
    notifications.forEach(notification => {
        const type = notification.getAttribute('data-type');
        const isUnread = notification.classList.contains('unread');
        const isUrgent = notification.classList.contains('urgent') || notification.classList.contains('warning');
        
        let shouldShow = false;
        
        switch(filter) {
            case 'all':
                shouldShow = true;
                break;
            case 'unread':
                shouldShow = isUnread;
                break;
            case 'urgent':
                shouldShow = isUrgent;
                break;
            case 'payment':
                shouldShow = type === 'payment';
                break;
            case 'reminder':
                shouldShow = type === 'reminder';
                break;
        }
        
        if (shouldShow) {
            notification.style.display = '';
            visibleCount++;
        } else {
            notification.style.display = 'none';
        }
    });
    
    // عرض رسالة إذا لم توجد نتائج
    if (visibleCount === 0) {
        showEmptyState();
    } else {
        removeEmptyState();
    }
    
    console.log(`Showing ${visibleCount} notifications for filter: ${filter}`);
}

function showEmptyState() {
    const container = document.querySelector('.notifications-container');
    if (container && !document.querySelector('.empty-state')) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
            <i class="fas fa-bell-slash"></i>
            <h3>لا توجد تنبيهات</h3>
            <p>لا توجد تنبيهات تطابق الفلتر المحدد</p>
        `;
        container.appendChild(emptyState);
    }
}

function removeEmptyState() {
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        emptyState.remove();
    }
}

// ============================================
// 2. Notification Actions - إجراءات التنبيهات
// ============================================
function initNotificationActions() {
    const notifications = document.querySelectorAll('.notification-card');
    notifications.forEach(notification => {
        initNotificationButtons(notification);
    });
}

function initNotificationButtons(notification) {
    const payBtn = notification.querySelector('.btn-pay');
    const markReadBtn = notification.querySelector('.btn-mark-read');
    const deleteBtn = notification.querySelector('.btn-delete');
    const viewBtn = notification.querySelector('.btn-view');
    
    if (payBtn) {
        payBtn.addEventListener('click', () => handlePayAction(notification));
    }
    
    if (markReadBtn) {
        markReadBtn.addEventListener('click', () => markAsRead(notification));
    }
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => deleteNotification(notification));
    }
    
    if (viewBtn) {
        viewBtn.addEventListener('click', () => viewNotification(notification));
    }
}

function handlePayAction(notification) {
    const title = notification.querySelector('h4').textContent;
    showNotification(`جاري فتح صفحة السداد لـ ${title}...`, 'info');
    
    setTimeout(() => {
        window.location.href = 'payments.html';
    }, 1000);
}

function markAsRead(notification) {
    notification.classList.remove('unread');
    
    // إزالة المؤشر
    const indicator = notification.querySelector('.notification-indicator');
    if (indicator) {
        indicator.remove();
    }
    
    // إزالة زر "وضع علامة مقروء"
    const markReadBtn = notification.querySelector('.btn-mark-read');
    if (markReadBtn) {
        markReadBtn.remove();
    }
    
    // تحديث العداد
    updateUnreadCount();
    
    showNotification('تم وضع علامة مقروء', 'success');
}

function deleteNotification(notification) {
    const title = notification.querySelector('h4').textContent;
    
    if (confirm(`هل تريد حذف هذا التنبيه؟\n"${title}"`)) {
        notification.style.transition = 'all 0.5s ease';
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            notification.remove();
            updateAllCounts();
            showNotification('تم حذف التنبيه', 'success');
            
            // التحقق من وجود تنبيهات
            const remainingNotifications = document.querySelectorAll('.notification-card:not([style*="display: none"])');
            if (remainingNotifications.length === 0) {
                showEmptyState();
            }
        }, 500);
    }
}

function viewNotification(notification) {
    const title = notification.querySelector('h4').textContent;
    const content = notification.querySelector('p').textContent;
    
    showNotification(`
        <strong>${title}</strong><br>
        ${content}
    `, 'info');
    
    // وضع علامة مقروء تلقائياً
    if (notification.classList.contains('unread')) {
        markAsRead(notification);
    }
}

// ============================================
// 3. Bulk Actions - الإجراءات الجماعية
// ============================================
function initBulkActions() {
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const deleteReadBtn = document.getElementById('deleteReadBtn');
    
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllAsRead);
    }
    
    if (deleteReadBtn) {
        deleteReadBtn.addEventListener('click', deleteAllRead);
    }
}

function markAllAsRead() {
    const unreadNotifications = document.querySelectorAll('.notification-card.unread');
    
    if (unreadNotifications.length === 0) {
        showNotification('جميع التنبيهات مقروءة بالفعل', 'info');
        return;
    }
    
    if (confirm(`هل تريد وضع علامة مقروء على ${unreadNotifications.length} تنبيه؟`)) {
        unreadNotifications.forEach(notification => {
            markAsRead(notification);
        });
        
        showNotification(`تم وضع علامة مقروء على ${unreadNotifications.length} تنبيه`, 'success');
    }
}

function deleteAllRead() {
    const readNotifications = document.querySelectorAll('.notification-card:not(.unread)');
    
    if (readNotifications.length === 0) {
        showNotification('لا توجد تنبيهات مقروءة لحذفها', 'info');
        return;
    }
    
    if (confirm(`هل تريد حذف ${readNotifications.length} تنبيه مقروء؟`)) {
        readNotifications.forEach(notification => {
            notification.style.transition = 'all 0.5s ease';
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
        });
        
        setTimeout(() => {
            readNotifications.forEach(notification => notification.remove());
            updateAllCounts();
            showNotification(`تم حذف ${readNotifications.length} تنبيه`, 'success');
            
            // التحقق من وجود تنبيهات
            const remainingNotifications = document.querySelectorAll('.notification-card');
            if (remainingNotifications.length === 0) {
                showEmptyState();
            }
        }, 500);
    }
}

// ============================================
// 4. Update Counts - تحديث العدادات
// ============================================
function updateUnreadCount() {
    const unreadCount = document.querySelectorAll('.notification-card.unread').length;
    
    // تحديث بطاقة الإحصائيات
    const unreadStatBox = document.querySelector('.stat-icon.unread').closest('.stat-box');
    if (unreadStatBox) {
        unreadStatBox.querySelector('.stat-number').textContent = unreadCount;
    }
    
    // تحديث عداد التبويب
    const unreadTab = document.querySelector('[data-filter="unread"] .tab-count');
    if (unreadTab) {
        unreadTab.textContent = unreadCount;
    }
    
    // تحديث Badge في Sidebar
    const sidebarBadge = document.querySelector('.nav-menu .badge');
    if (sidebarBadge) {
        if (unreadCount > 0) {
            sidebarBadge.textContent = unreadCount;
            sidebarBadge.style.display = '';
        } else {
            sidebarBadge.style.display = 'none';
        }
    }
}

function updateAllCounts() {
    const allNotifications = document.querySelectorAll('.notification-card');
    const unreadNotifications = document.querySelectorAll('.notification-card.unread');
    const urgentNotifications = document.querySelectorAll('.notification-card.urgent, .notification-card.warning');
    const todayNotifications = getTodayNotifications();
    const paymentNotifications = document.querySelectorAll('[data-type="payment"]');
    const reminderNotifications = document.querySelectorAll('[data-type="reminder"]');
    
    // تحديث بطاقات الإحصائيات
    updateStatBox('total', allNotifications.length);
    updateStatBox('unread', unreadNotifications.length);
    updateStatBox('urgent', urgentNotifications.length);
    updateStatBox('today', todayNotifications);
    
    // تحديث عدادات التبويبات
    updateTabCount('all', allNotifications.length);
    updateTabCount('unread', unreadNotifications.length);
    updateTabCount('urgent', urgentNotifications.length);
    updateTabCount('payment', paymentNotifications.length);
    updateTabCount('reminder', reminderNotifications.length);
    
    // تحديث Badge في Sidebar
    updateUnreadCount();
}

function updateStatBox(type, count) {
    const statBox = document.querySelector(`.stat-icon.${type}`);
    if (statBox) {
        const statNumber = statBox.closest('.stat-box').querySelector('.stat-number');
        if (statNumber) {
            statNumber.textContent = count;
        }
    }
}

function updateTabCount(filter, count) {
    const tab = document.querySelector(`[data-filter="${filter}"] .tab-count`);
    if (tab) {
        tab.textContent = count;
    }
}

function getTodayNotifications() {
    const notifications = document.querySelectorAll('.notification-card');
    let count = 0;
    
    notifications.forEach(notification => {
        const timeText = notification.querySelector('.notification-time span').textContent;
        if (timeText.includes('منذ') && (timeText.includes('دقيقة') || timeText.includes('ساعة'))) {
            count++;
        }
    });
    
    return count;
}

// ============================================
// 5. Settings Modal - نافذة الإعدادات
// ============================================
function initSettingsModal() {
    const settingsBtn = document.getElementById('settingsBtn');
    const modal = document.getElementById('notificationSettingsModal');
    const closeBtn = document.getElementById('closeSettingsModal');
    const cancelBtn = document.getElementById('cancelSettingsBtn');
    const saveBtn = document.getElementById('saveSettingsBtn');
    
    if (settingsBtn) {
        settingsBtn.addEventListener('click', () => {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }
    
    if (saveBtn) {
        saveBtn.addEventListener('click', saveSettings);
    }
}

function saveSettings() {
    const settings = {};
    const toggles = document.querySelectorAll('.toggle-switch input');
    
    toggles.forEach((toggle, index) => {
        const settingName = toggle.closest('.setting-item').querySelector('h4').textContent;
        settings[settingName] = toggle.checked;
    });
    
    console.log('Saving settings:', settings);
    
    // حفظ في localStorage
    localStorage.setItem('notificationSettings', JSON.stringify(settings));
    
    // إغلاق Modal
    document.getElementById('notificationSettingsModal').classList.remove('active');
    document.body.style.overflow = '';
    
    showNotification('تم حفظ إعدادات التنبيهات بنجاح!', 'success');
}

// ============================================
// 6. Load More - تحميل المزيد
// ============================================
function initLoadMore() {
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreNotifications);
    }
}

function loadMoreNotifications() {
    const btn = document.getElementById('loadMoreBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
    btn.disabled = true;
    
    // محاكاة تحميل المزيد
    setTimeout(() => {
        // هنا يمكن إضافة تنبيهات جديدة
        addDummyNotifications(5);
        
        btn.innerHTML = '<i class="fas fa-chevron-down"></i> تحميل المزيد';
        btn.disabled = false;
        
        showNotification('تم تحميل 5 تنبيهات إضافية', 'success');
    }, 1500);
}

function addDummyNotifications(count) {
    const container = document.querySelector('.notifications-container');
    
    for (let i = 0; i < count; i++) {
        const notification = document.createElement('div');
        notification.className = 'notification-card info';
        notification.setAttribute('data-type', 'reminder');
        notification.setAttribute('data-id', Date.now() + i);
        
        notification.innerHTML = `
            <div class="notification-icon info">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="notification-content">
                <h4>تنبيه جديد</h4>
                <p>هذا تنبيه تجريبي تم تحميله ديناميكياً</p>
                <span class="notification-time">
                    <i class="fas fa-clock"></i>
                    الآن
                </span>
            </div>
            <div class="notification-actions">
                <button class="btn-action btn-mark-read">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn-action btn-delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        container.appendChild(notification);
        initNotificationButtons(notification);
        
        // تأثير Animation
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.style.transition = 'opacity 0.5s ease';
            notification.style.opacity = '1';
        }, 100);
    }
    
    updateAllCounts();
}

// ============================================
// 7. Helper Functions
// ============================================
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    
    let icon = 'fa-info-circle';
    let bgColor = 'var(--color-info)';
    
    switch(type) {
        case 'success':
            icon = 'fa-check-circle';
            bgColor = 'var(--color-success)';
            break;
        case 'error':
            icon = 'fa-exclamation-circle';
            bgColor = 'var(--color-danger)';
            break;
        case 'warning':
            icon = 'fa-exclamation-triangle';
            bgColor = 'var(--color-warning)';
            break;
    }
    
    notification.innerHTML = `
        <i class="fas ${icon}"></i>
        <div>${message}</div>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        background: ${bgColor};
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 10001;
        transition: transform 0.5s ease;
        font-family: 'Cairo', sans-serif;
        font-weight: 600;
        max-width: 500px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(-100px)';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// ============================================
// 8. Auto-refresh Notifications
// ============================================
function checkNewNotifications() {
    // محاكاة التحقق من تنبيهات جديدة
    // في التطبيق الحقيقي، يتم استدعاء API
    console.log('Checking for new notifications...');
}

// التحقق كل دقيقة
setInterval(checkNewNotifications, 60000);

