/* ============================================
   Profile Page - JavaScript
   وظائف صفحة الملف الشخصي
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    console.log('👤 Profile Page Loaded');
    
    // تفعيل جميع الوظائف
    initAvatarUpload();
    initEditButtons();
    initSecurityButtons();
    initConnectedAccounts();
    initAchievements();
});

// ============================================
// 1. Avatar Upload - رفع صورة البروفايل
// ============================================
function initAvatarUpload() {
    const avatarBtn = document.querySelector('.change-avatar-btn');
    
    if (avatarBtn) {
        avatarBtn.addEventListener('click', function() {
            // إنشاء input مخفي
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    uploadAvatar(file);
                }
            };
            
            input.click();
        });
    }
}

function uploadAvatar(file) {
    // التأكد من أن الملف صورة
    if (!file.type.startsWith('image/')) {
        showNotification('يرجى اختيار ملف صورة صحيح', 'error');
        return;
    }
    
    // قراءة الملف وعرضه
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const avatarImg = document.querySelector('.profile-avatar img');
        if (avatarImg) {
            avatarImg.src = e.target.result;
            showNotification('تم تغيير الصورة بنجاح!', 'success');
        }
    };
    
    reader.readAsDataURL(file);
    
    // هنا يمكن إضافة كود لرفع الصورة للسيرفر
    // uploadToServer(file);
}

// ============================================
// 2. Edit Buttons - أزرار التعديل
// ============================================
function initEditButtons() {
    const editButtons = document.querySelectorAll('.btn-edit');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.info-card');
            const cardTitle = card.querySelector('h3').textContent;
            
            console.log('📝 Editing:', cardTitle);
            
            // يمكن فتح Modal للتعديل
            openEditModal(cardTitle, card);
        });
    });
    
    // زر تعديل الملف الشخصي الرئيسي
    const mainEditBtn = document.querySelector('.page-header .btn-primary');
    if (mainEditBtn) {
        mainEditBtn.addEventListener('click', function() {
            openFullEditMode();
        });
    }
}

function openEditModal(title, card) {
    showNotification(`فتح نافذة تعديل: ${title}`, 'info');
    
    // هنا يمكن إضافة Modal حقيقي
    // مثلاً:
    // const modal = createEditModal(title, card);
    // document.body.appendChild(modal);
}

function openFullEditMode() {
    // تحويل جميع الحقول لوضع التعديل
    const infoItems = document.querySelectorAll('.info-item');
    
    infoItems.forEach(item => {
        const value = item.querySelector('.info-value');
        if (value) {
            const currentText = value.textContent;
            value.innerHTML = `<input type="text" value="${currentText}" class="edit-input">`;
        }
    });
    
    showNotification('وضع التعديل مفعّل', 'info');
}

// ============================================
// 3. Security Buttons - أزرار الأمان
// ============================================
function initSecurityButtons() {
    const securityButtons = document.querySelectorAll('.security-item .btn-change');
    
    securityButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            const securityItem = this.closest('.security-item');
            const title = securityItem.querySelector('h4').textContent;
            
            switch(index) {
                case 0: // تغيير كلمة المرور
                    openPasswordChangeModal();
                    break;
                case 1: // إعدادات التحقق بخطوتين
                    openTwoFactorSettings();
                    break;
                case 2: // عرض سجل الدخول
                    showLoginHistory();
                    break;
            }
        });
    });
}

function openPasswordChangeModal() {
    showNotification('فتح نافذة تغيير كلمة المرور', 'info');
    // هنا يمكن إضافة Modal لتغيير كلمة المرور
}

function openTwoFactorSettings() {
    showNotification('فتح إعدادات التحقق بخطوتين', 'info');
    // هنا يمكن إضافة Modal لإعدادات 2FA
}

function showLoginHistory() {
    showNotification('عرض سجل الدخول', 'info');
    // هنا يمكن إضافة Modal لعرض سجل الدخول
}

// ============================================
// 4. Connected Accounts - الحسابات المرتبطة
// ============================================
function initConnectedAccounts() {
    const connectButtons = document.querySelectorAll('.btn-connect');
    const disconnectButtons = document.querySelectorAll('.btn-disconnect');
    
    // أزرار الربط
    connectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const accountItem = this.closest('.account-item');
            const accountName = accountItem.querySelector('h4').textContent;
            
            connectAccount(accountName, accountItem);
        });
    });
    
    // أزرار الفصل
    disconnectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const accountItem = this.closest('.account-item');
            const accountName = accountItem.querySelector('h4').textContent;
            
            disconnectAccount(accountName, accountItem);
        });
    });
}

function connectAccount(name, element) {
    // محاكاة عملية الربط
    showNotification(`جاري ربط حساب ${name}...`, 'info');
    
    setTimeout(() => {
        element.classList.remove('not-connected');
        element.classList.add('connected');
        
        element.querySelector('p').textContent = 'مرتبط';
        element.querySelector('p').style.color = 'var(--color-success)';
        
        const button = element.querySelector('.btn-connect');
        button.className = 'btn-disconnect';
        button.textContent = 'فصل';
        
        showNotification(`تم ربط حساب ${name} بنجاح!`, 'success');
    }, 1500);
}

function disconnectAccount(name, element) {
    if (confirm(`هل تريد فصل حساب ${name}؟`)) {
        showNotification(`جاري فصل حساب ${name}...`, 'info');
        
        setTimeout(() => {
            element.classList.remove('connected');
            element.classList.add('not-connected');
            
            element.querySelector('p').textContent = 'غير مرتبط';
            element.querySelector('p').style.color = '';
            
            const button = element.querySelector('.btn-disconnect');
            button.className = 'btn-connect';
            button.textContent = 'ربط';
            
            showNotification(`تم فصل حساب ${name}`, 'success');
        }, 1000);
    }
}

// ============================================
// 5. Achievements - الإنجازات
// ============================================
function initAchievements() {
    const achievements = document.querySelectorAll('.achievement-badge');
    
    achievements.forEach(achievement => {
        achievement.addEventListener('click', function() {
            if (this.classList.contains('unlocked')) {
                showAchievementDetails(this);
            } else {
                showLockedAchievement(this);
            }
        });
    });
}

function showAchievementDetails(achievement) {
    const title = achievement.querySelector('h4').textContent;
    const description = achievement.querySelector('p').textContent;
    
    // تأثير بصري
    achievement.style.transform = 'scale(1.05)';
    setTimeout(() => {
        achievement.style.transform = '';
    }, 300);
    
    showNotification(`🏆 ${title}: ${description}`, 'success');
}

function showLockedAchievement(achievement) {
    const title = achievement.querySelector('h4').textContent;
    const description = achievement.querySelector('p').textContent;
    
    showNotification(`🔒 ${title} - ${description}`, 'info');
}

// ============================================
// 6. Timeline Interactions
// ============================================
const timelineItems = document.querySelectorAll('.timeline-item');

timelineItems.forEach(item => {
    item.addEventListener('click', function() {
        const title = this.querySelector('h4').textContent;
        const description = this.querySelector('p').textContent;
        
        console.log('📅 Timeline:', title, '-', description);
        
        // تأثير بصري
        this.style.transform = 'translateX(-10px) scale(1.02)';
        setTimeout(() => {
            this.style.transform = '';
        }, 300);
    });
});

// ============================================
// 7. Download Financial Report
// ============================================
const downloadBtn = document.querySelector('.profile-actions .btn-secondary');
if (downloadBtn && downloadBtn.textContent.includes('تحميل')) {
    downloadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        downloadFinancialReport();
    });
}

function downloadFinancialReport() {
    showNotification('جاري تحضير السيرة المالية...', 'info');
    
    // محاكاة عملية التحميل
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
    this.disabled = true;
    
    setTimeout(() => {
        showNotification('تم تحميل السيرة المالية بنجاح!', 'success');
        this.innerHTML = '<i class="fas fa-download"></i> تحميل السيرة المالية';
        this.disabled = false;
        
        // هنا يمكن تنزيل ملف PDF فعلي
        // window.location.href = '/download/financial-report.pdf';
    }, 2000);
}

// ============================================
// 8. Share Profile
// ============================================
const shareBtn = document.querySelectorAll('.profile-actions .btn-secondary')[1];
if (shareBtn) {
    shareBtn.addEventListener('click', function() {
        shareProfile();
    });
}

function shareProfile() {
    if (navigator.share) {
        navigator.share({
            title: 'Debt Mate - ملفي الشخصي',
            text: 'تحقق من ملفي المالي على Debt Mate',
            url: window.location.href
        }).then(() => {
            showNotification('تمت المشاركة بنجاح!', 'success');
        }).catch(err => {
            console.log('Error sharing:', err);
        });
    } else {
        // نسخ الرابط
        copyToClipboard(window.location.href);
        showNotification('تم نسخ رابط الملف الشخصي!', 'success');
    }
}

function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
}

// ============================================
// 9. Notification System
// ============================================
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
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
        <span>${message}</span>
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
        z-index: 9999;
        transition: transform 0.5s ease;
        font-family: 'Cairo', sans-serif;
        font-weight: 600;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(-100px)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 500);
    }, 3000);
}

// ============================================
// 10. Smooth Scroll for Links
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ============================================
// 11. Auto-save Draft Changes
// ============================================
let autoSaveTimeout;

function autoSaveDraft() {
    clearTimeout(autoSaveTimeout);
    
    autoSaveTimeout = setTimeout(() => {
        console.log('💾 Auto-saving profile changes...');
        showNotification('تم حفظ التغييرات تلقائياً', 'info');
    }, 2000);
}

// مراقبة التغييرات في حقول الإدخال
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('edit-input')) {
        autoSaveDraft();
    }
});

