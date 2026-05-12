/* ============================================
   Payments Page - JavaScript
   وظائف صفحة عمليات السداد
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    console.log('💰 Payments Page Loaded');
    
    // تفعيل جميع الوظائف
    initModal();
    initQuickPay();
    initFilters();
    initTableActions();
    initPagination();
    initFormValidation();
});

// ============================================
// 1. Modal - نافذة تسجيل دفعة
// ============================================
function initModal() {
    const modal = document.getElementById('addPaymentModal');
    const addBtn = document.getElementById('addPaymentBtn');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const form = document.getElementById('addPaymentForm') || document.getElementById('paymentForm');
    
    // فتح Modal
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            openModal();
        });
    }
    
    // إغلاق Modal
    function closeModal() {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            if (form) form.reset();
            updatePaymentSummary();
        }
    }
    
    function openModal() {
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // تعيين تاريخ اليوم افتراضياً
            const dateInput = form.querySelector('[name="payment_date"]');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
        }
    }
    
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    
    // إغلاق عند الضغط خارج المحتوى
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
    
    // معالجة إرسال النموذج
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handlePaymentSubmit(new FormData(form));
        });
    }
}

function handlePaymentSubmit(formData) {
    const paymentData = {
        debt_id: formData.get('debt_id') || formData.get('debt'),
        amount: formData.get('amount'),
        payment_date: formData.get('payment_date'),
        payment_method: formData.get('payment_method'),
        transaction_id: formData.get('transaction_id'),
        notes: formData.get('notes')
    };
    
    console.log('💳 Processing payment:', paymentData);
    
    // عرض Loading
    showLoading();
    
    // محاكاة معالجة الدفع
    setTimeout(() => {
        hideLoading();
        
        // إضافة السجل للجدول
        addPaymentToTable(paymentData);
        
        // تحديث الإحصائيات
        updateStats();
        
        // إغلاق Modal
        document.getElementById('addPaymentModal').classList.remove('active');
        document.body.style.overflow = '';
        
        // إظهار رسالة نجاح
        showSuccessMessage();
        
    }, 2000);
}

function addPaymentToTable(data) {
    const tbody = document.querySelector('.payments-table tbody') || document.getElementById('paymentsBody');
    if (!tbody) return;
    
    const debtName = getDebtName(data.debt_id);
    const methodName = getMethodName(data.payment_method);
    const methodClass = data.payment_method || 'cash';
    
    const row = document.createElement('tr');
    row.className = 'payment-row';
    row.innerHTML = `
        <td>
            <div class="date-cell">
                <i class="fas fa-calendar"></i>
                <span>اليوم - ${getCurrentTime()}</span>
            </div>
        </td>
        <td>
            <div class="debt-cell">
                <i class="fas fa-file-invoice"></i>
                <span>${debtName}</span>
            </div>
        </td>
        <td>
            <span class="amount-cell">${formatNumber(data.amount)} ج.م</span>
        </td>
        <td>
            <div class="method-badge ${methodClass}">
                <i class="fas ${getMethodIcon(data.payment_method)}"></i>
                <span>${methodName}</span>
            </div>
        </td>
        <td>
            <span class="status-badge success">
                <i class="fas fa-check-circle"></i>
                مكتمل
            </span>
        </td>
        <td>
            <div class="action-buttons">
                <button class="btn-icon" title="عرض">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-icon" title="طباعة">
                    <i class="fas fa-print"></i>
                </button>
                <button class="btn-icon danger" title="حذف">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    // إضافة في البداية
    tbody.insertBefore(row, tbody.firstChild);
    
    // تفعيل الأزرار
    initRowActions(row);
    
    // تأثير Animation
    row.style.opacity = '0';
    setTimeout(() => {
        row.style.transition = 'opacity 0.5s ease';
        row.style.opacity = '1';
    }, 100);
}

// ============================================
// 2. Quick Pay - الدفع السريع
// ============================================
function initQuickPay() {
    const quickPayButtons = document.querySelectorAll('.btn-quick-pay');
    
    quickPayButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.quick-pay-card');
            const debtName = card.querySelector('h3').textContent;
            const amount = card.querySelector('.quick-amount').textContent.replace(/[^\d]/g, '');
            
            handleQuickPay(debtName, amount);
        });
    });
}

function handleQuickPay(debtName, amount) {
    if (confirm(`هل تريد دفع ${formatNumber(amount)} ج.م لـ ${debtName}؟`)) {
        showLoading();
        
        setTimeout(() => {
            hideLoading();
            
            // إضافة الدفعة
            const paymentData = {
                debt_id: debtName,
                amount: amount,
                payment_date: new Date().toISOString().split('T')[0],
                payment_method: 'cash'
            };
            
            addPaymentToTable(paymentData);
            updateStats();
            showSuccessMessage();
            
        }, 1500);
    }
}

// ============================================
// 3. Filters - الفلاتر
// ============================================
function initFilters() {
    const searchInput = document.getElementById('searchInput');
    const timeFilter = document.getElementById('timeFilter');
    const methodFilter = document.getElementById('methodFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    
    if (timeFilter) {
        timeFilter.addEventListener('change', applyFilters);
    }
    
    if (methodFilter) {
        methodFilter.addEventListener('change', applyFilters);
    }
}

function applyFilters() {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const timeValue = document.getElementById('timeFilter')?.value || 'all';
    const methodValue = document.getElementById('methodFilter')?.value || 'all';
    
    const rows = document.querySelectorAll('.payment-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const debtText = row.querySelector('.debt-cell span')?.textContent.toLowerCase() || '';
        const methodBadge = row.querySelector('.method-badge');
        const methodClass = methodBadge ? Array.from(methodBadge.classList).find(c => 
            ['cash', 'card', 'transfer', 'online'].includes(c)
        ) : '';
        
        // فلترة البحث
        const searchMatch = searchValue === '' || debtText.includes(searchValue);
        
        // فلترة طريقة الدفع
        const methodMatch = methodValue === 'all' || methodClass === methodValue;
        
        // عرض/إخفاء الصف
        if (searchMatch && methodMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // تحديث عدد النتائج
    const countElement = document.querySelector('.results-count');
    if (countElement) {
        countElement.innerHTML = `عرض <strong>${visibleCount}</strong> دفعة`;
    }
}

// ============================================
// 4. Table Actions - أزرار الجدول
// ============================================
function initTableActions() {
    const rows = document.querySelectorAll('.payment-row');
    rows.forEach(row => initRowActions(row));
}

function initRowActions(row) {
    const buttons = row.querySelectorAll('.btn-icon');
    
    buttons.forEach((button, index) => {
        button.addEventListener('click', function() {
            const debtName = row.querySelector('.debt-cell span').textContent;
            const amount = row.querySelector('.amount-cell').textContent;
            
            switch(index) {
                case 0: // عرض
                    viewPaymentDetails(row);
                    break;
                case 1: // طباعة
                    printReceipt(row);
                    break;
                case 2: // حذف
                    deletePayment(row);
                    break;
            }
        });
    });
}

function viewPaymentDetails(row) {
    const debtName = row.querySelector('.debt-cell span').textContent;
    const amount = row.querySelector('.amount-cell').textContent;
    const date = row.querySelector('.date-cell span').textContent;
    const method = row.querySelector('.method-badge span').textContent;
    
    showNotification(`
        <strong>تفاصيل الدفعة:</strong><br>
        الدين: ${debtName}<br>
        المبلغ: ${amount}<br>
        التاريخ: ${date}<br>
        الطريقة: ${method}
    `, 'info');
}

function printReceipt(row) {
    showNotification('جاري تحضير إيصال الدفع للطباعة...', 'info');
    
    setTimeout(() => {
        // window.print();
        showNotification('تم تحضير إيصال الدفع!', 'success');
    }, 1000);
}

function deletePayment(row) {
    const debtName = row.querySelector('.debt-cell span').textContent;
    
    if (confirm(`هل تريد حذف دفعة "${debtName}"؟`)) {
        row.style.transition = 'all 0.5s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
            row.remove();
            showNotification('تم حذف الدفعة', 'success');
            updateStats();
            applyFilters();
        }, 500);
    }
}

// ============================================
// 5. Pagination
// ============================================
function initPagination() {
    const pageButtons = document.querySelectorAll('.page-btn');
    
    pageButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            
            // إزالة active من جميع الأزرار
            pageButtons.forEach(btn => btn.classList.remove('active'));
            
            // إضافة active للزر المضغوط (إذا كان رقم)
            if (!isNaN(this.textContent)) {
                this.classList.add('active');
            }
            
            // تحميل الصفحة
            const pageNum = this.textContent;
            console.log('Loading page:', pageNum);
            
            // هنا يمكن تحميل البيانات من الـ API
            // loadPage(pageNum);
        });
    });
}

// ============================================
// 6. Form Validation & Updates
// ============================================
function initFormValidation() {
    const form = document.getElementById('addPaymentForm') || document.getElementById('paymentForm');
    if (!form) return;
    
    // تحديث الملخص عند تغيير المبلغ
    const amountInput = form.querySelector('[name="amount"]');
    if (amountInput) {
        amountInput.addEventListener('input', updatePaymentSummary);
    }
    
    // تحديث الملخص عند تغيير طريقة الدفع
    const methodInputs = form.querySelectorAll('[name="payment_method"]');
    methodInputs.forEach(input => {
        input.addEventListener('change', updatePaymentSummary);
    });
}

function updatePaymentSummary() {
    const form = document.getElementById('addPaymentForm') || document.getElementById('paymentForm');
    if (!form) return;
    
    const amount = form.querySelector('[name="amount"]')?.value || 0;
    const method = form.querySelector('[name="payment_method"]:checked')?.value || 'cash';
    
    const summaryAmount = document.getElementById('summaryAmount');
    const summaryMethod = document.getElementById('summaryMethod');
    
    if (summaryAmount) {
        summaryAmount.textContent = formatNumber(amount) + ' ج.م';
    }
    
    if (summaryMethod) {
        summaryMethod.textContent = getMethodName(method);
    }
}

// ============================================
// 7. Update Stats
// ============================================
function updateStats() {
    // حساب الإحصائيات من الجدول
    const rows = document.querySelectorAll('.payment-row');
    let total = 0;
    let thisMonth = 0;
    let count = 0;
    
    rows.forEach(row => {
        const amountText = row.querySelector('.amount-cell')?.textContent || '0';
        const amount = parseFloat(amountText.replace(/[^\d.]/g, ''));
        
        total += amount;
        count++;
        
        // التحقق إذا كانت من هذا الشهر
        const dateText = row.querySelector('.date-cell span')?.textContent || '';
        if (dateText.includes('اليوم') || dateText.includes('أمس') || dateText.includes('منذ')) {
            thisMonth += amount;
        }
    });
    
    const average = count > 0 ? total / count : 0;
    
    // تحديث البطاقات
    const statCards = document.querySelectorAll('.payment-stat-card');
    if (statCards[0]) {
        statCards[0].querySelector('.stat-number').textContent = formatNumber(total) + ' ج.م';
    }
    if (statCards[1]) {
        statCards[1].querySelector('.stat-number').textContent = formatNumber(thisMonth) + ' ج.م';
        statCards[1].querySelector('.stat-label').textContent = count + ' دفعات';
    }
    if (statCards[2]) {
        statCards[2].querySelector('.stat-number').textContent = formatNumber(average) + ' ج.م';
    }
}

// ============================================
// 8. Export to Excel
// ============================================
const exportBtn = document.querySelector('.btn-export');
if (exportBtn) {
    exportBtn.addEventListener('click', function() {
        exportToExcel();
    });
}

function exportToExcel() {
    showNotification('جاري تصدير البيانات إلى Excel...', 'info');
    
    setTimeout(() => {
        // هنا يمكن إضافة كود التصدير الفعلي
        showNotification('تم تصدير البيانات بنجاح!', 'success');
        
        // محاكاة التحميل
        // const blob = new Blob([csvContent], { type: 'text/csv' });
        // const url = window.URL.createObjectURL(blob);
        // const a = document.createElement('a');
        // a.href = url;
        // a.download = 'payments.csv';
        // a.click();
    }, 1500);
}

// ============================================
// 9. Helper Functions
// ============================================
function formatNumber(num) {
    return new Intl.NumberFormat('ar-EG').format(num);
}

function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString('ar-EG', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getDebtName(debtId) {
    const debts = {
        '1': 'قسط البنك الشهري',
        '2': 'فاتورة الكهرباء',
        '3': 'اشتراك الإنترنت',
        '4': 'قسط السيارة',
        '5': 'دين لأحمد'
    };
    return debts[debtId] || debtId || 'دين';
}

function getMethodName(method) {
    const methods = {
        'cash': 'نقدي',
        'card': 'بطاقة',
        'transfer': 'تحويل بنكي',
        'online': 'محفظة إلكترونية'
    };
    return methods[method] || 'نقدي';
}

function getMethodIcon(method) {
    const icons = {
        'cash': 'fa-money-bill',
        'card': 'fa-credit-card',
        'transfer': 'fa-exchange-alt',
        'online': 'fa-mobile-alt'
    };
    return icons[method] || 'fa-money-bill';
}

function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = `
        <div class="loading-spinner"></div>
    `;
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

function showSuccessMessage() {
    const message = document.createElement('div');
    message.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--gradient-success);
        color: white;
        padding: 2rem 3rem;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        z-index: 10003;
        text-align: center;
        font-family: 'Cairo', sans-serif;
    `;
    
    message.innerHTML = `
        <div class="payment-success-icon" style="font-size: 4rem; margin-bottom: 1rem;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 style="margin-bottom: 0.5rem;">تم السداد بنجاح!</h2>
        <p>تم تسجيل الدفعة وتحديث السجلات</p>
    `;
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        message.style.transition = 'opacity 0.5s ease';
        message.style.opacity = '0';
        setTimeout(() => {
            message.remove();
        }, 500);
    }, 3000);
}

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
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 4000);
}

// ============================================
// 10. Keyboard Shortcuts
// ============================================
document.addEventListener('keydown', function(e) {
    // Ctrl + P = تسجيل دفعة جديدة
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        document.getElementById('addPaymentBtn')?.click();
    }
    
    // Ctrl + E = تصدير Excel
    if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        exportToExcel();
    }
});

