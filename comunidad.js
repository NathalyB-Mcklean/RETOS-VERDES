// ============================================
// PÁGINA DE COMUNIDAD - JAVASCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // FILTROS DEL FEED
    // ============================================
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remover clase active de todos
            filterTabs.forEach(t => t.classList.remove('active'));
            // Agregar clase active al clickeado
            this.classList.add('active');
            
            // Aquí puedes agregar lógica para filtrar publicaciones
            const filter = this.textContent.trim().toLowerCase();
            console.log('Filtro seleccionado:', filter);
            
            // Animación de carga
            showLoadingAnimation();
        });
    });
    
    // ============================================
    // ACCIONES DE PUBLICACIONES
    // ============================================
    
    // Me gusta
    const likeButtons = document.querySelectorAll('.post-action-button');
    likeButtons.forEach(button => {
        if (button.textContent.includes('Me gusta')) {
            button.addEventListener('click', function() {
                this.classList.toggle('liked');
                
                if (this.classList.contains('liked')) {
                    this.style.color = '#e74c3c';
                    this.querySelector('span').textContent = '❤️';
                    showNotification('¡Te gustó esta publicación!');
                } else {
                    this.style.color = '';
                    this.querySelector('span').textContent = '👍';
                }
            });
        }
    });
    
    // Comentar
    likeButtons.forEach(button => {
        if (button.textContent.includes('Comentar')) {
            button.addEventListener('click', function() {
                const postCard = this.closest('.post-card');
                openCommentModal(postCard);
            });
        }
    });
    
    // Compartir
    likeButtons.forEach(button => {
        if (button.textContent.includes('Compartir')) {
            button.addEventListener('click', function() {
                showShareOptions();
            });
        }
    });
    
    // ============================================
    // UNIRSE A GRUPOS
    // ============================================
    const joinButtons = document.querySelectorAll('.btn-join');
    
    joinButtons.forEach(button => {
        button.addEventListener('click', function() {
            const groupName = this.closest('.group-item').querySelector('.group-name').textContent;
            
            if (this.textContent === 'Unirse') {
                this.textContent = 'Unido ✓';
                this.style.background = '#95a5a6';
                showNotification(`Te uniste al grupo: ${groupName}`);
            } else {
                this.textContent = 'Unirse';
                this.style.background = '';
            }
        });
    });
    
    // ============================================
    // ASISTIR A EVENTOS
    // ============================================
    const eventButtons = document.querySelectorAll('.btn-event');
    
    eventButtons.forEach(button => {
        button.addEventListener('click', function() {
            const eventTitle = this.closest('.event-item').querySelector('.event-title').textContent;
            
            if (this.textContent === 'Asistir') {
                this.textContent = 'Asistiré ✓';
                this.style.background = 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)';
                showNotification(`Confirmaste tu asistencia a: ${eventTitle}`);
            } else {
                this.textContent = 'Asistir';
                this.style.background = '';
            }
        });
    });
    
    // ============================================
    // CARGAR MÁS PUBLICACIONES
    // ============================================
    const loadMoreBtn = document.querySelector('.btn-load-more');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            this.textContent = 'Cargando...';
            this.disabled = true;
            
            // Simular carga de más publicaciones
            setTimeout(() => {
                showNotification('Nuevas publicaciones cargadas');
                this.textContent = 'Cargar más publicaciones';
                this.disabled = false;
            }, 1500);
        });
    }
    
    // ============================================
    // MENÚ DE OPCIONES DE PUBLICACIÓN
    // ============================================
    const postMenus = document.querySelectorAll('.post-menu');
    
    postMenus.forEach(menu => {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
            showPostOptionsMenu(this);
        });
    });
    
    // ============================================
    // TAGS CLICKEABLES
    // ============================================
    const tags = document.querySelectorAll('.tag');
    
    tags.forEach(tag => {
        tag.addEventListener('click', function(e) {
            e.preventDefault();
            const tagName = this.textContent;
            showNotification(`Explorando ${tagName}`);
            // Aquí puedes agregar lógica para filtrar por hashtag
        });
    });
    
});

// ============================================
// FUNCIONES AUXILIARES
// ============================================

function openPostModal() {
    showNotification('Función de crear publicación próximamente');
    // Aquí puedes abrir un modal para crear publicaciones
}

function openCommentModal(postCard) {
    showNotification('Función de comentarios próximamente');
    // Aquí puedes abrir un modal para comentar
}

function showShareOptions() {
    const options = confirm('¿Compartir esta publicación en tu perfil?');
    if (options) {
        showNotification('¡Publicación compartida!');
    }
}

function showPostOptionsMenu(button) {
    const menu = document.createElement('div');
    menu.className = 'post-options-menu';
    menu.innerHTML = `
        <div class="menu-option" onclick="showNotification('Publicación guardada')">💾 Guardar</div>
        <div class="menu-option" onclick="showNotification('Publicación ocultada')">🙈 Ocultar</div>
        <div class="menu-option" onclick="showNotification('Usuario reportado')">⚠️ Reportar</div>
    `;
    
    // Posicionar el menú
    const rect = button.getBoundingClientRect();
    menu.style.position = 'fixed';
    menu.style.top = rect.bottom + 'px';
    menu.style.right = (window.innerWidth - rect.right) + 'px';
    menu.style.background = 'white';
    menu.style.borderRadius = '8px';
    menu.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    menu.style.padding = '8px';
    menu.style.zIndex = '1000';
    
    document.body.appendChild(menu);
    
    // Cerrar al hacer click fuera
    setTimeout(() => {
        document.addEventListener('click', function closeMenu() {
            menu.remove();
            document.removeEventListener('click', closeMenu);
        });
    }, 100);
}

function showLoadingAnimation() {
    const feed = document.querySelector('.posts-container');
    feed.style.opacity = '0.5';
    
    setTimeout(() => {
        feed.style.opacity = '1';
    }, 500);
}

function showNotification(message) {
    // Crear notificación
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-weight: 600;
    `;
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Agregar animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .menu-option {
        padding: 10px 16px;
        cursor: pointer;
        border-radius: 6px;
        transition: background 0.3s ease;
        font-size: 14px;
        color: #2c3e50;
    }
    
    .menu-option:hover {
        background: #f8f9fa;
    }
`;
document.head.appendChild(style);