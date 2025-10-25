// JavaScript для сайта "КультПросвет"

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initTooltips();
    initAlerts();
    initForms();
    initSeatSelection();
    initPaymentForm();
    initAnalytics();
});

// Инициализация тултипов Bootstrap
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Автоматическое скрытие алертов
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }
    });
}

// Валидация форм
function initForms() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Выбор мест в схеме зала
function initSeatSelection() {
    const seatCheckboxes = document.querySelectorAll('input[name="seat_ids[]"]');
    const selectedSeatsDiv = document.getElementById('selectedSeats');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const totalPriceDiv = document.getElementById('totalPrice');
    const bookButton = document.getElementById('bookButton');
    
    if (!seatCheckboxes.length) return;
    
    // Получаем цены мест из data-атрибутов
    const seatPrices = {};
    seatCheckboxes.forEach(checkbox => {
        const seatElement = checkbox.closest('.seat-btn');
        if (seatElement) {
            seatPrices[checkbox.value] = parseFloat(seatElement.dataset.price) || 0;
        }
    });
    
    seatCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedSeats();
        });
    });
    
    function updateSelectedSeats() {
        const selectedSeats = Array.from(seatCheckboxes).filter(cb => cb.checked);
        const totalPrice = selectedSeats.reduce((sum, cb) => sum + (seatPrices[cb.value] || 0), 0);
        
        if (selectedSeats.length > 0) {
            if (selectedSeatsDiv) {
                selectedSeatsDiv.style.display = 'block';
                if (selectedSeatsList) {
                    selectedSeatsList.innerHTML = selectedSeats.map(cb => {
                        const seatElement = cb.closest('.seat-btn');
                        return seatElement ? seatElement.textContent.trim() : '';
                    }).join(', ');
                }
                if (totalPriceDiv) {
                    totalPriceDiv.textContent = 'Общая стоимость: ' + totalPrice.toLocaleString() + ' ₽';
                }
            }
            if (bookButton) {
                bookButton.disabled = false;
            }
        } else {
            if (selectedSeatsDiv) {
                selectedSeatsDiv.style.display = 'none';
            }
            if (bookButton) {
                bookButton.disabled = true;
            }
        }
    }
}

// Форма оплаты
function initPaymentForm() {
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('cardDetails');
    
    if (!paymentMethodRadios.length) return;
    
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'card') {
                if (cardDetails) {
                    cardDetails.style.display = 'block';
                }
            } else {
                if (cardDetails) {
                    cardDetails.style.display = 'none';
                }
            }
        });
    });
    
    // Форматирование номера карты
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            this.value = formattedValue;
        });
    }
    
    // Форматирование срока действия
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
        });
    }
    
    // Только цифры для CVV
    const cardCvvInput = document.getElementById('card_cvv');
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    }
}

// Аналитика и графики
function initAnalytics() {
    // Здесь можно добавить инициализацию графиков (Chart.js, например)
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
}

// Инициализация графиков
function initCharts() {
    // Пример создания графика продаж
    const salesChartCanvas = document.getElementById('salesChart');
    if (salesChartCanvas) {
        const ctx = salesChartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // Данные из PHP
                datasets: [{
                    label: 'Продажи',
                    data: [], // Данные из PHP
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Показать QR-код
function showQRCode(qrCodeUrl) {
    const qrImage = document.getElementById('qrImage');
    if (qrImage) {
        qrImage.src = qrCodeUrl;
        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    }
}

// Подтверждение удаления
function confirmDelete(message = 'Вы уверены?') {
    return confirm(message);
}

// AJAX запросы
function makeAjaxRequest(url, data, method = 'POST') {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: method === 'POST' ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Ошибка AJAX запроса:', error);
        showNotification('Произошла ошибка при выполнении запроса', 'error');
    });
}

// Показать уведомление
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Автоматически скрыть через 5 секунд
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

// Поиск в реальном времени
function initLiveSearch(inputSelector, resultsSelector, searchUrl) {
    const searchInput = document.querySelector(inputSelector);
    const resultsContainer = document.querySelector(resultsSelector);
    
    if (!searchInput || !resultsContainer) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            makeAjaxRequest(searchUrl, { query: query })
                .then(data => {
                    if (data.success) {
                        resultsContainer.innerHTML = data.html;
                    }
                });
        }, 300);
    });
}

// Копирование в буфер обмена
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Скопировано в буфер обмена', 'success');
        });
    } else {
        // Fallback для старых браузеров
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Скопировано в буфер обмена', 'success');
    }
}

// Печать билета
function printTicket(ticketId) {
    const printWindow = window.open(`/print-ticket.php?id=${ticketId}`, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}

// Валидация email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Валидация телефона
function validatePhone(phone) {
    const re = /^[\+]?[1-9][\d]{0,15}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Форматирование цены
function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0
    }).format(price);
}

// Форматирование даты
function formatDate(date, format = 'dd.mm.yyyy') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    
    switch (format) {
        case 'dd.mm.yyyy':
            return `${day}.${month}.${year}`;
        case 'dd.mm.yyyy hh:mm':
            return `${day}.${month}.${year} ${hours}:${minutes}`;
        default:
            return d.toLocaleDateString('ru-RU');
    }
}

// Ленивая загрузка изображений
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback для старых браузеров
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Инициализация ленивой загрузки
initLazyLoading();
