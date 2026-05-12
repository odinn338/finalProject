/* ============================================
   Reports Page - JavaScript
   وظائف صفحة التقارير المالية
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Reports Page Loaded');
    
    // تفعيل جميع الوظائف
    initPeriodFilter();
    initCharts();
    initExportFunctions();
});

// ============================================
// 1. Period Filter - فلتر الفترة
// ============================================
function initPeriodFilter() {
    const periodButtons = document.querySelectorAll('.period-btn');
    const customDateRange = document.querySelector('.custom-date-range');
    
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // إزالة active من جميع الأزرار
            periodButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const period = this.getAttribute('data-period');
            
            // إظهار/إخفاء نطاق التاريخ المخصص
            if (period === 'custom') {
                customDateRange.style.display = 'flex';
            } else {
                customDateRange.style.display = 'none';
                // تحديث التقرير حسب الفترة
                updateReportData(period);
            }
        });
    });
    
    // زر تطبيق النطاق المخصص
    const applyBtn = document.querySelector('.btn-apply');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (startDate && endDate) {
                updateReportData('custom', startDate, endDate);
                showNotification('تم تحديث التقرير للفترة المخصصة', 'success');
            } else {
                showNotification('يرجى اختيار تاريخ البداية والنهاية', 'warning');
            }
        });
    }
}

function updateReportData(period, startDate = null, endDate = null) {
    console.log('Updating report for period:', period);
    
    // عرض مؤشر التحميل
    showLoading();
    
    // محاكاة تحميل البيانات
    setTimeout(() => {
        hideLoading();
        
        // تحديث الرسوم البيانية
        updateAllCharts(period);
        
        // تحديث الإحصائيات
        updateSummaryCards(period);
        
        showNotification(`تم تحديث التقرير لفترة: ${getPeriodName(period)}`, 'success');
    }, 1000);
}

function getPeriodName(period) {
    const names = {
        'week': 'أسبوع',
        'month': 'شهر',
        'quarter': '3 أشهر',
        'year': 'سنة',
        'custom': 'مخصص'
    };
    return names[period] || 'شهر';
}

// ============================================
// 2. Charts Initialization - تفعيل الرسوم البيانية
// ============================================
let monthlyTrendChart, categoryChart, paymentMethodsChart;

function initCharts() {
    initMonthlyTrendChart();
    initCategoryChart();
    initPaymentMethodsChart();
    initChartControls();
}

// Monthly Trend Chart
function initMonthlyTrendChart() {
    const ctx = document.getElementById('monthlyTrendChart');
    if (!ctx) return;
    
    monthlyTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر', 'يناير', 'فبراير'],
            datasets: [
                {
                    label: 'الديون الجديدة',
                    data: [5000, 6500, 5800, 7200, 6300, 5200],
                    borderColor: '#F44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#F44336',
                    pointBorderColor: '#FBE4D8',
                    pointBorderWidth: 2
                },
                {
                    label: 'المدفوعات',
                    data: [2000, 3200, 2800, 3500, 3100, 2500],
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#4CAF50',
                    pointBorderColor: '#FBE4D8',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    rtl: true,
                    labels: {
                        color: '#DFB6B2',
                        font: {
                            family: 'Cairo',
                            size: 12,
                            weight: '600'
                        },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    rtl: true,
                    backgroundColor: 'rgba(43, 18, 76, 0.95)',
                    titleColor: '#FBE4D8',
                    bodyColor: '#DFB6B2',
                    borderColor: '#854F6C',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(223, 182, 178, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#DFB6B2',
                        font: {
                            family: 'Cairo',
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(223, 182, 178, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#DFB6B2',
                        font: {
                            family: 'Cairo',
                            size: 11
                        },
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

// Category Pie Chart
function initCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['بنوك', 'فواتير', 'قروض', 'أخرى'],
            datasets: [{
                data: [10400, 6500, 7800, 1300],
                backgroundColor: [
                    '#2196F3',
                    '#4CAF50',
                    '#FF9800',
                    '#9C27B0'
                ],
                borderColor: [
                    '#2196F3',
                    '#4CAF50',
                    '#FF9800',
                    '#9C27B0'
                ],
                borderWidth: 2,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    rtl: true,
                    backgroundColor: 'rgba(43, 18, 76, 0.95)',
                    titleColor: '#FBE4D8',
                    bodyColor: '#DFB6B2',
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Payment Methods Bar Chart
function initPaymentMethodsChart() {
    const ctx = document.getElementById('paymentMethodsChart');
    if (!ctx) return;
    
    paymentMethodsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['نقدي', 'بطاقة', 'تحويل بنكي', 'محفظة إلكترونية'],
            datasets: [{
                label: 'عدد المعاملات',
                data: [15, 28, 12, 8],
                backgroundColor: [
                    'rgba(76, 175, 80, 0.8)',
                    'rgba(33, 150, 243, 0.8)',
                    'rgba(255, 152, 0, 0.8)',
                    'rgba(156, 39, 176, 0.8)'
                ],
                borderColor: [
                    '#4CAF50',
                    '#2196F3',
                    '#FF9800',
                    '#9C27B0'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    rtl: true,
                    backgroundColor: 'rgba(43, 18, 76, 0.95)',
                    titleColor: '#FBE4D8',
                    bodyColor: '#DFB6B2',
                    padding: 12
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#DFB6B2',
                        font: {
                            family: 'Cairo',
                            size: 10
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(223, 182, 178, 0.1)'
                    },
                    ticks: {
                        color: '#DFB6B2',
                        font: {
                            family: 'Cairo'
                        }
                    }
                }
            }
        }
    });
}

// Chart Controls
function initChartControls() {
    const chartButtons = document.querySelectorAll('.chart-btn');
    
    chartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.querySelectorAll('.chart-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const type = this.textContent.trim();
            updateMonthlyChart(type);
        });
    });
}

function updateMonthlyChart(type) {
    if (!monthlyTrendChart) return;
    
    const datasets = monthlyTrendChart.data.datasets;
    
    switch(type) {
        case 'الديون':
            datasets[0].hidden = false;
            datasets[1].hidden = true;
            break;
        case 'المدفوعات':
            datasets[0].hidden = true;
            datasets[1].hidden = false;
            break;
        case 'كلاهما':
            datasets[0].hidden = false;
            datasets[1].hidden = false;
            break;
    }
    
    monthlyTrendChart.update();
}

function updateAllCharts(period) {
    // هنا يمكن تحديث بيانات الرسوم البيانية حسب الفترة
    if (monthlyTrendChart) monthlyTrendChart.update();
    if (categoryChart) categoryChart.update();
    if (paymentMethodsChart) paymentMethodsChart.update();
}

// ============================================
// 3. Update Summary Cards
// ============================================
function updateSummaryCards(period) {
    // محاكاة تحديث البيانات
    const cards = document.querySelectorAll('.summary-card');
    
    cards.forEach(card => {
        const value = card.querySelector('.card-value');
        if (value) {
            // تأثير animation
            value.style.opacity = '0.5';
            setTimeout(() => {
                value.style.opacity = '1';
            }, 300);
        }
    });
}

// ============================================
// 4. Export Functions - وظائف التصدير
// ============================================
function initExportFunctions() {
    const printBtn = document.getElementById('printReportBtn');
    const exportBtn = document.getElementById('exportReportBtn');
    
    if (printBtn) {
        printBtn.addEventListener('click', printReport);
    }
    
    if (exportBtn) {
        exportBtn.addEventListener('click', exportToPDF);
    }
}

function printReport() {
    showNotification('جاري تحضير التقرير للطباعة...', 'info');
    
    setTimeout(() => {
        window.print();
    }, 500);
}

function exportToPDF() {
    showNotification('جاري تصدير التقرير إلى PDF...', 'info');
    
    // عرض شريط التقدم
    showExportProgress();
    
    // محاكاة عملية التصدير
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        updateExportProgress(progress);
        
        if (progress >= 100) {
            clearInterval(interval);
            hideExportProgress();
            showNotification('تم تصدير التقرير بنجاح! 📄', 'success');
            
            // هنا يمكن إضافة كود التصدير الفعلي باستخدام مكتبة مثل jsPDF
            // downloadPDF();
        }
    }, 200);
}

function showExportProgress() {
    const progress = document.createElement('div');
    progress.className = 'export-progress';
    progress.id = 'exportProgress';
    progress.innerHTML = `
        <h4>
            <i class="fas fa-file-pdf"></i>
            جاري التصدير...
        </h4>
        <div class="export-progress-bar">
            <div class="export-progress-fill" id="progressFill" style="width: 0%;"></div>
        </div>
    `;
    document.body.appendChild(progress);
}

function updateExportProgress(percent) {
    const fill = document.getElementById('progressFill');
    if (fill) {
        fill.style.width = percent + '%';
    }
}

function hideExportProgress() {
    const progress = document.getElementById('exportProgress');
    if (progress) {
        progress.style.opacity = '0';
        setTimeout(() => {
            progress.remove();
        }, 300);
    }
}

// ============================================
// 5. Helper Functions
// ============================================
function formatCurrency(value) {
    return new Intl.NumberFormat('ar-EG', {
        style: 'currency',
        currency: 'EGP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value).replace('EGP', 'ج.م');
}

function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.remove();
}

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
        z-index: 10001;
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
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// ============================================
// 6. Auto-refresh Data
// ============================================
let autoRefreshInterval;

function startAutoRefresh() {
    // تحديث البيانات كل 5 دقائق
    autoRefreshInterval = setInterval(() => {
        console.log('Auto-refreshing report data...');
        const activePeriod = document.querySelector('.period-btn.active');
        if (activePeriod) {
            updateReportData(activePeriod.getAttribute('data-period'));
        }
    }, 300000); // 5 minutes
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

// بدء التحديث التلقائي
// startAutoRefresh();

