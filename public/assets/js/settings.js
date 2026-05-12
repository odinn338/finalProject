/* Settings Page JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    console.log('⚙️ Settings Page Ready');

    initNavigation();
    initToggles();
    initTheme();
    initButtons();
});

// Navigation between sections
function initNavigation() {
    const navButtons = document.querySelectorAll('.settings-nav-btn');
    const sections = document.querySelectorAll('.settings-section');

    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section') + '-section';

            navButtons.forEach(btn => btn.classList.remove('active'));
            sections.forEach(section => section.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(sectionId).classList.add('active');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
}

// Toggle switches
function initToggles() {
    const toggles = document.querySelectorAll('.toggle-switch input');

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const toggleName = this.closest('.toggle-item').querySelector('h4').textContent;
            const status = this.checked ? 'مفعّل' : 'معطّل';

            showNotification('تم ' + status + ' ' + toggleName, 'success');

            localStorage.setItem(toggleName, this.checked);
        });
    });
}

// Theme and appearance
function initTheme() {
    const themeCards = document.querySelectorAll('.theme-card');
    const colorBoxes = document.querySelectorAll('.color-box');
    const fontOptions = document.querySelectorAll('.font-option');

    themeCards.forEach(card => {
        card.addEventListener('click', function() {
            themeCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            showNotification('تم تغيير السمة', 'success');
        });
    });

    colorBoxes.forEach(box => {
        box.addEventListener('click', function() {
            colorBoxes.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            showNotification('تم تغيير اللون', 'success');
        });
    });

    fontOptions.forEach(option => {
        option.addEventListener('click', function() {
            fontOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            showNotification('تم تغيير حجم الخط', 'success');
        });
    });
}

// Save buttons
function initButtons() {
    const saveButtons = document.querySelectorAll('.btn-save');

    saveButtons.forEach(button => {
        button.addEventListener('click', function() {
            showLoading();
            setTimeout(() => {
                hideLoading();
                showNotification('تم حفظ التغييرات بنجاح!', 'success');
            }, 1000);
        });
    });
}

// Notifications
// function showNotification(message, type) {
//     const colors = {
//         success: '#4CAF50',
//         error: '#F44336',
//         info: '#2196F3'
//     };

//     const notification = document.createElement('div');
//     notification.textContent = message;
//     notification.style.cssText = `
//         position: fixed;
//         top: 20px;
//         left: 50%;
//         transform: translateX(-50%);
//         background: ${colors[type] || colors.info};
//         color: white;
//         padding: 1rem 2rem;
//         border-radius: 8px;
//         font-family: Cairo, sans-serif;
//         font-weight: 600;
//         z-index: 10000;
//         animation: slideDown 0.3s ease;
//     `;

//     document.body.appendChild(notification);

//     setTimeout(() => {
//         notification.style.animation = 'slideUp 0.3s ease';
//         setTimeout(() => notification.remove(), 300);
//     }, 3000);
// }

// function showLoading() {
//     const loading = document.createElement('div');
//     loading.id = 'loading';
//     loading.style.cssText = `
//         position: fixed;
//         top: 0;
//         left: 0;
//         width: 100%;
//         height: 100%;
//         background: rgba(0,0,0,0.8);
//         display: flex;
//         align-items: center;
//         justify-content: center;
//         z-index: 10000;
//     `;
//     loading.innerHTML = '<div style="width: 50px; height: 50px; border: 4px solid #ddd; border-top-color: #854F6C; border-radius: 50%; animation: spin 1s linear infinite;"></div>';
//     document.body.appendChild(loading);
// }

// function hideLoading() {
//     const loading = document.getElementById('loading');
//     if (loading) loading.remove();
// }

// Add animations
// const style = document.createElement('style');
// style.textContent = `
//     @keyframes slideDown {
//         from { transform: translateX(-50%) translateY(-100px); }
//         to { transform: translateX(-50%) translateY(0); }
//     }
//     @keyframes slideUp {
//         from { transform: translateX(-50%) translateY(0); }
//         to { transform: translateX(-50%) translateY(-100px); }
//     }
//     @keyframes spin {
//         to { transform: rotate(360deg); }
//     }
// `;
// document.head.appendChild(style);
