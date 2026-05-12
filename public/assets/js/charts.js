/* ============================================
   Debt Mate - Charts JavaScript
   ملف الرسوم البيانية بإستخدام Chart.js
   ============================================ */

// ============================================
// 1. تشغيل الرسوم البيانية بعد تحميل الصفحة
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Initializing Charts...');

    // تفعيل الرسوم البيانية
    initProgressChart();
    initMonthlyChart();
});

// ============================================
// 2. Progress Doughnut Chart - الرسم الدائري
// ============================================
function initProgressChart() {
    const ctx = document.getElementById('progressChart');
    if (!ctx) return;

    // البيانات من Laravel
    const paidAmount      = window.chartData.paid;
    const remainingAmount = window.chartData.remaining;
    const totalDebt       = paidAmount + remainingAmount;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['المبلغ المسدد', 'المتبقي للسداد'],
            datasets: [{
                data: [paidAmount, remainingAmount],
                backgroundColor: ['#4CAF50', '#FF9800'],
                borderColor:     ['#4CAF50', '#FF9800'],
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    rtl: true,
                    backgroundColor: 'rgba(43, 18, 76, 0.95)',
                    titleColor: '#FBE4D8',
                    bodyColor: '#DFB6B2',
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed || 0;
                            const pct = totalDebt > 0 ? ((value / totalDebt) * 100).toFixed(1) : 0;
                            return `${context.label}: ${formatCurrency(value)} (${pct}%)`;
                        }
                    }
                }
            },
            animation: { duration: 2000, easing: 'easeInOutQuart' }
        }
    });
}

// ============================================
// 3. Monthly Line Chart - الرسم الخطي الشهري
// ============================================
function initMonthlyChart() {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;

    // البيانات من Laravel
    const labels   = window.chartData.monthly.labels;
    const debts    = window.chartData.monthly.debts;
    const payments = window.chartData.monthly.payments;

    window.monthlyChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'الديون الجديدة',
                    data: debts,
                    borderColor: '#FF9800',
                    backgroundColor: 'rgba(255, 152, 0, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#FF9800',
                    pointBorderColor: '#FBE4D8',
                    pointBorderWidth: 2
                },
                {
                    label: 'المبالغ المسددة',
                    data: payments,
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
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    rtl: true,
                    labels: {
                        color: '#DFB6B2',
                        font: { family: 'Cairo', size: 12, weight: '600' },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    rtl: true,
                    backgroundColor: 'rgba(43, 18, 76, 0.95)',
                    titleColor: '#FBE4D8',
                    bodyColor: '#DFB6B2',
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${formatCurrency(context.parsed.y)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid:  { color: 'rgba(223, 182, 178, 0.1)' },
                    ticks: { color: '#DFB6B2', font: { family: 'Cairo', size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid:  { color: 'rgba(223, 182, 178, 0.1)' },
                    ticks: {
                        color: '#DFB6B2',
                        font: { family: 'Cairo', size: 11 },
                        callback: value => formatCurrency(value)
                    }
                }
            },
            animation: { duration: 2000, easing: 'easeInOutQuart' }
        }
    });
}

// ============================================
// 4. تنسيق العملة - Format Currency
// ============================================
function formatCurrency(value) {
    return new Intl.NumberFormat('ar-EG', {
        style: 'currency',
        currency: 'EGP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value).replace('EGP', 'ج.م');
}

// ============================================
// 5. تحديث الرسم البياني الشهري
// ============================================
function updateMonthlyChart(period) {
    if (!window.monthlyChartInstance) {
        console.error('❌ Chart instance not found');
        return;
    }

    const chart = window.monthlyChartInstance;

    // بيانات مختلفة حسب الفترة
    let newData;

    switch(period) {
        case 'شهر':
            newData = {
                labels: ['أسبوع 1', 'أسبوع 2', 'أسبوع 3', 'أسبوع 4'],
                debts: [1500, 1800, 1200, 1700],
                payments: [800, 1000, 700, 1000]
            };
            break;

        case '3 أشهر':
            newData = {
                labels: ['ديسمبر', 'يناير', 'فبراير'],
                debts: [7200, 6300, 5200],
                payments: [3500, 3100, 2500]
            };
            break;

        case 'سنة':
            newData = {
                labels: ['مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر', 'يناير', 'فبراير'],
                debts: [5500, 6000, 5200, 5800, 6200, 5900, 5000, 6500, 5800, 7200, 6300, 5200],
                payments: [2500, 2800, 2200, 2600, 3000, 2700, 2000, 3200, 2800, 3500, 3100, 2500]
            };
            break;

        default:
            newData = {
                labels: ['سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر', 'يناير', 'فبراير'],
                debts: [5000, 6500, 5800, 7200, 6300, 5200],
                payments: [2000, 3200, 2800, 3500, 3100, 2500]
            };
    }

    // تحديث البيانات
    chart.data.labels = newData.labels;
    chart.data.datasets[0].data = newData.debts;
    chart.data.datasets[1].data = newData.payments;

    // تحديث الرسم البياني مع animation
    chart.update('active');

    console.log('📊 Chart updated for period:', period);
}

// ============================================
// 6. ربط أزرار الفلتر بتحديث الرسم البياني
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const timeFilterButtons = document.querySelectorAll('.time-filter button');

    timeFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.textContent.trim();
            updateMonthlyChart(period);
        });
    });
});

// ============================================
// 7. رسم بياني إضافي - Bar Chart (اختياري)
// ============================================
function createBarChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);

    if (!ctx) return;

    const barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: data.label,
                data: data.values,
                backgroundColor: [
                    'rgba(133, 79, 108, 0.8)',
                    'rgba(82, 43, 91, 0.8)',
                    'rgba(223, 182, 178, 0.8)',
                    'rgba(76, 175, 80, 0.8)',
                    'rgba(255, 152, 0, 0.8)'
                ],
                borderColor: [
                    '#854F6C',
                    '#522B5B',
                    '#DFB6B2',
                    '#4CAF50',
                    '#FF9800'
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
                },
                x: {
                    grid: {
                        display: false
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

    return barChart;
}

// ============================================
// 8. رسم بياني للفئات - Category Chart
// ============================================
function createCategoryChart() {
    // يمكن استخدامه لعرض الديون حسب الفئة
    const categoryData = {
        labels: ['البنك', 'الفواتير', 'القروض', 'المشتريات', 'أخرى'],
        values: [8000, 3500, 6000, 4500, 4000]
    };

    // يمكن استدعاء createBarChart هنا
    // createBarChart('categoryChart', categoryData);
}

// ============================================
// 9. تصدير الرسم البياني كصورة
// ============================================
function exportChartAsImage(chartId, filename) {
    const canvas = document.getElementById(chartId);

    if (!canvas) {
        console.error('Canvas not found');
        return;
    }

    // تحويل الرسم البياني لصورة
    const url = canvas.toDataURL('image/png');

    // إنشاء رابط تحميل
    const link = document.createElement('a');
    link.download = filename || 'chart.png';
    link.href = url;
    link.click();

    console.log('📥 Chart exported as image');
}

// ============================================
// 10. Refresh Charts - تحديث جميع الرسوم البيانية
// ============================================
function refreshAllCharts() {
    console.log('🔄 Refreshing all charts...');

    // إعادة تحميل البيانات من الـ API أو Database
    // ثم تحديث الرسوم البيانية

    if (window.monthlyChartInstance) {
        window.monthlyChartInstance.update();
    }

    console.log('✅ All charts refreshed');
}

// ============================================
// 11. Animate Chart on Scroll
// ============================================
function animateChartsOnScroll() {
    const chartCards = document.querySelectorAll('.chart-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // تفعيل animation الرسم البياني
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.2
    });

    chartCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.8s ease';
        observer.observe(card);
    });
}

// تفعيل animation عند السكرول
window.addEventListener('load', animateChartsOnScroll);

// ============================================
// 12. Chart Configurations - إعدادات عامة
// ============================================
Chart.defaults.font.family = 'Cairo';
Chart.defaults.color = '#DFB6B2';
Chart.defaults.borderColor = 'rgba(223, 182, 178, 0.1)';

// ============================================
// تصدير الوظائف للاستخدام الخارجي
// ============================================
window.chartFunctions = {
    updateMonthlyChart,
    exportChartAsImage,
    refreshAllCharts,
    createBarChart,
    createCategoryChart
};

