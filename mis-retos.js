// ============================================
// MIS RETOS - JAVASCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // TABS FUNCTIONALITY
    // ============================================
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remover active de todos los botones y contenidos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Agregar active al botón clickeado
            this.classList.add('active');
            
            // Mostrar el contenido correspondiente
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
            
            // Guardar tab activo en localStorage
            localStorage.setItem('activeTab', targetTab);
            
            // Scroll suave hacia arriba
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
    
    // Restaurar tab activo al cargar la página
    const savedTab = localStorage.getItem('activeTab');
    if (savedTab) {
        const tabToActivate = document.querySelector(`[data-tab="${savedTab}"]`);
        if (tabToActivate) {
            tabToActivate.click();
        }
    }
    
    // ============================================
    // BOTONES DE CONTINUAR RETO
    // ============================================
    const continueButtons = document.querySelectorAll('.btn-continue');
    
    continueButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Agregar efecto visual
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
    
    // ============================================
    // COMPARTIR RETOS COMPLETADOS
    // ============================================
    const shareButtons = document.querySelectorAll('.btn-share');
    
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.completed-card');
            const retoTitle = card.querySelector('.completed-title').textContent;
            const puntos = card.querySelector('.completed-stat .stat-value').textContent;
            
            // Crear mensaje para compartir
            const mensaje = `¡Acabo de completar el reto "${retoTitle}" y gané ${puntos}! 🎉🌱 #RetosVerdes #PanamáVerde`;
            
            // Intentar usar Web Share API si está disponible
            if (navigator.share) {
                navigator.share({
                    title: 'Reto Completado - Retos Verdes',
                    text: mensaje,
                    url: window.location.href
                }).then(() => {
                    showNotification('¡Compartido exitosamente! 🎉');
                }).catch((error) => {
                    if (error.name !== 'AbortError') {
                        copyToClipboard(mensaje);
                    }
                });
            } else {
                // Fallback: copiar al clipboard
                copyToClipboard(mensaje);
            }
        });
    });
    
    // ============================================
    // REPETIR RETO
    // ============================================
    const repeatButtons = document.querySelectorAll('.btn-repeat');
    
    repeatButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.completed-card');
            const retoTitle = card.querySelector('.completed-title').textContent;
            
            const confirmar = confirm(`¿Quieres volver a participar en el reto "${retoTitle}"?`);
            
            if (confirmar) {
                showNotification('¡Te has inscrito nuevamente en el reto! 🎯');
                
                // Animación de éxito
                card.style.transform = 'scale(1.05)';
                card.style.boxShadow = '0 12px 32px rgba(46, 204, 113, 0.3)';
                
                setTimeout(() => {
                    card.style.transform = '';
                    card.style.boxShadow = '';
                }, 300);
                
                // Aquí puedes agregar la lógica para inscribir al usuario
                // inscribirEnReto(retoId);
            }
        });
    });
    
    // ============================================
    // ANIMACIÓN DE PROGRESO
    // ============================================
    const progressBars = document.querySelectorAll('.progress-fill-large');
    
    // Animar barras de progreso cuando sean visibles
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target;
                const width = progressBar.style.width;
                progressBar.style.width = '0%';
                
                setTimeout(() => {
                    progressBar.style.width = width;
                }, 100);
                
                observer.unobserve(progressBar);
            }
        });
    }, observerOptions);
    
    progressBars.forEach(bar => observer.observe(bar));
    
    // ============================================
    // CONTADOR DE ESTADÍSTICAS ANIMADO
    // ============================================
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
        animateCounter(stat, 0, finalValue, 1500);
    });
    
    // ============================================
    // HOVER EFFECTS EN CARDS
    // ============================================
    const challengeCards = document.querySelectorAll('.challenge-card-progress');
    
    challengeCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
    
    // ============================================
    // DÍAS RESTANTES - ACTUALIZACIÓN DINÁMICA
    // ============================================
    const daysLeftElements = document.querySelectorAll('.days-left');
    
    daysLeftElements.forEach(element => {
        const days = parseInt(element.textContent);
        
        if (days <= 3) {
            element.style.color = '#e74c3c';
            element.style.fontWeight = '700';
            element.textContent = `⚠️ ${days} días restantes`;
        } else if (days <= 7) {
            element.style.color = '#f39c12';
            element.style.fontWeight = '600';
        }
    });
    
});

// ============================================
// FUNCIONES AUXILIARES
// ============================================

function showNotification(mensaje, tipo = 'success') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = 'custom-notification';
    
    const icon = tipo === 'success' ? '✓' : 'ℹ';
    const bgColor = tipo === 'success' 
        ? 'linear-gradient(135deg, #2ecc71 0%, #27ae60 100%)'
        : 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)';
    
    notification.innerHTML = `
        <span class="notification-icon">${icon}</span>
        <span class="notification-text">${mensaje}</span>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: slideInRight 0.3s ease;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('¡Mensaje copiado al portapapeles! 📋');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('¡Mensaje copiado al portapapeles! 📋');
    } catch (err) {
        showNotification('No se pudo copiar el mensaje', 'info');
    }
    
    document.body.removeChild(textArea);
}

function animateCounter(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            current = end;
            clearInterval(timer);
        }
        
        // Formatear con comas para miles
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// ============================================
// ANIMACIONES CSS
// ============================================
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .notification-icon {
        font-size: 20px;
        font-weight: bold;
    }
    
    .notification-text {
        font-size: 15px;
    }
    
    /* Smooth transitions */
    .challenge-card-progress,
    .completed-card,
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Pulse animation para días restantes */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .days-left[style*="color: #e74c3c"] {
        animation: pulse 2s ease-in-out infinite;
    }
`;
document.head.appendChild(styleSheet);