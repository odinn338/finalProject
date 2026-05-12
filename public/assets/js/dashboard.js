/* ============================================
   Debt Mate - Dashboard JavaScript
   ملف الجافاسكريبت الرئيسي للتفاعلية
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Debt Mate Dashboard Loaded!');

    initNavigation();
    initStatCards();
    initNotifications();
    initPaymentCards();
    animateNumbers();
    initTooltips();
    initScrollAnimations();

    // Transaction Items
    document.querySelectorAll('.transaction-item').forEach(item => {
        item.addEventListener('click', function() {
            const titleEl = this.querySelector('h4');
            const amountEl = this.querySelector('.transaction-amount');
            if (!titleEl || !amountEl) return;

            this.style.background = 'rgba(223, 182, 178, 0.15)';
            setTimeout(() => {
                this.style.background = 'rgba(0, 0, 0, 0.2)';
            }, 300);
        });
    });

    // Filter Tabs
    const filterButtons = document.querySelectorAll('.filter-tabs button');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const paymentCards = document.querySelectorAll('.payment-card');
            paymentCards.forEach(card => {
                card.style.opacity = '0.3';
                setTimeout(() => { card.style.opacity = '1'; }, 300);
            });
        });
    });

    // Time Filter
    const timeFilterButtons = document.querySelectorAll('.time-filter button');
    timeFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            timeFilterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

// ============================================
// Navigation - القائمة الجانبية
// ============================================
function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // بدون preventDefault عشان الروابط تشتغل عادي
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            playClickSound();
        });
    });
}

// ============================================
// Statistics Cards
// ============================================
function initStatCards() {
    const statCards = document.querySelectorAll('.stat-card');

    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });

        card.addEventListener('click', function() {
            this.style.transform = 'translateY(-5px) scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            }, 150);
        });
    });
}

// ============================================
// Animate Numbers
// ============================================
function animateNumbers() {
    const statNumbers = document.querySelectorAll('.stat-number');

    statNumbers.forEach(element => {
        const finalValue = element.textContent;
        const numericValue = parseFloat(finalValue.replace(/[^\d.]/g, ''));
        const currency = finalValue.replace(/[\d,.\s]/g, '');

        if (!numericValue) return;

        let currentValue = 0;
        const increment = numericValue / 50;
        const stepTime = 1500 / 50;

        element.textContent = '0 ' + currency;

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= numericValue) {
                element.textContent = formatNumber(numericValue) + ' ' + currency;
                clearInterval(timer);
            } else {
                element.textContent = formatNumber(Math.floor(currentValue)) + ' ' + currency;
            }
        }, stepTime);
    });
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// ============================================
// Notifications
// ============================================
function initNotifications() {
    const notificationBtn = document.querySelector('.btn-notification');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            showNotificationPanel();
        });
    }

    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            this.style.opacity = '0.6';
            setTimeout(() => { this.style.opacity = '1'; }, 200);
        });
    });
}

function showNotificationPanel() {
    console.log('📬 Notification Panel Opened');
}

// ============================================
// Payment Cards
// ============================================
function initPaymentCards() {
    const payButtons = document.querySelectorAll('.payment-card .btn-pay');

    payButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const card = this.closest('.payment-card');
            if (!card) return;

            const paymentTitle = card.querySelector('h3')?.textContent;
            const paymentAmount = card.querySelector('.payment-amount')?.textContent;

            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري المعالجة...';
            this.disabled = true;

            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check"></i> تم السداد بنجاح!';
                this.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
                showSuccessMessage(paymentTitle, paymentAmount);

                setTimeout(() => {
                    this.innerHTML = 'سداد الآن';
                    this.disabled = false;
                    this.style.background = '';
                }, 3000);
            }, 2000);
        });
    });
}

function showSuccessMessage(title, amount) {
    const message = document.createElement('div');
    message.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>تم السداد بنجاح!</strong>
            <p>${title} - ${amount}</p>
        </div>
    `;
    message.style.cssText = `
        position: fixed; top: 20px; left: 50%;
        transform: translateX(-50%) translateY(-100px);
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white; padding: 1rem 2rem; border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        display: flex; align-items: center; gap: 1rem;
        z-index: 9999; transition: transform 0.5s ease;
        font-family: 'Cairo', sans-serif;
    `;
    document.body.appendChild(message);
    setTimeout(() => { message.style.transform = 'translateX(-50%) translateY(0)'; }, 100);
    setTimeout(() => {
        message.style.transform = 'translateX(-50%) translateY(-100px)';
        setTimeout(() => { message.remove(); }, 500);
    }, 3000);
}

// ============================================
// Tooltips
// ============================================
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = createTooltip(this.getAttribute('data-tooltip'));
            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            setTimeout(() => { tooltip.style.opacity = '1'; }, 10);
        });

        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.custom-tooltip');
            if (tooltip) {
                tooltip.style.opacity = '0';
                setTimeout(() => { tooltip.remove(); }, 200);
            }
        });
    });
}

function createTooltip(text) {
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: fixed; background: rgba(43,18,76,0.95);
        color: #FBE4D8; padding: 8px 16px; border-radius: 8px;
        font-size: 0.85rem; z-index: 10000; opacity: 0;
        transition: opacity 0.2s ease; pointer-events: none;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    `;
    return tooltip;
}

// ============================================
// Progress Bars Animation
// ============================================
window.addEventListener('load', function() {
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const targetWidth = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => { bar.style.width = targetWidth; }, 500);
    });
});

// ============================================
// Scroll Animations
// ============================================
function initScrollAnimations() {
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -100px 0px' });

    document.querySelectorAll('.stat-card, .chart-card, .info-card, .payment-card').forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s ease';
        observer.observe(element);
    });
}

// ============================================
// Keyboard Shortcuts
// ============================================
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const addBtn = document.querySelector('.btn-primary');
        if (addBtn) addBtn.click();
    }
});

// ============================================
// Shared Functions - مشتركة في كل الصفحات
// ============================================
function showNotification(message, type = 'info') {
    const colors = {
        success: '#4CAF50',
        error: '#F44336',
        info: '#2196F3',
        warning: '#FF9800'
    };

    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed; top: 20px; left: 50%;
        transform: translateX(-50%) translateY(-100px);
        background: ${colors[type] || colors.info};
        color: white; padding: 1rem 2rem; border-radius: 8px;
        font-family: Cairo, sans-serif; font-weight: 600;
        z-index: 10000; transition: transform 0.3s ease;
    `;

    document.body.appendChild(notification);
    setTimeout(() => { notification.style.transform = 'translateX(-50%) translateY(0)'; }, 100);
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(-100px)';
        setTimeout(() => { notification.remove(); }, 300);
    }, 3000);
}

function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading';
    loading.style.cssText = `
        position: fixed; top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex; align-items: center;
        justify-content: center; z-index: 10000;
    `;
    loading.innerHTML = '<div style="width:50px;height:50px;border:4px solid #ddd;border-top-color:#854F6C;border-radius:50%;animation:spin 1s linear infinite;"></div>';
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading');
    if (loading) loading.remove();
}

// ============================================
// Helper Functions
// ============================================
function playClickSound() {
    // اختياري
}

function toggleDarkMode() {
    document.body.classList.toggle('light-mode');
    localStorage.setItem('darkMode', !document.body.classList.contains('light-mode'));
}

function saveToLocalStorage(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

function getFromLocalStorage(key) {
    const data = localStorage.getItem(key);
    return data ? JSON.parse(data) : null;
}
