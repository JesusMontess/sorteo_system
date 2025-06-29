// Scripts personalizados para el Sistema de Sorteos

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers de Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-ocultar alertas después de 5 segundos
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Confirmación para acciones de eliminación
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message') || '¿Estás seguro de que quieres eliminar este elemento?';
            if (confirm(message)) {
                window.location.href = this.href;
            }
        });
    });

    // Animaciones de entrada
    const animatedElements = document.querySelectorAll('.fade-in-up');
    animatedElements.forEach(function(element, index) {
        setTimeout(function() {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Funciones para el sistema de sorteos

// Selección de balota
function selectBalota(numero, elemento) {
    // Remover selección anterior
    const balotas = document.querySelectorAll('.balota');
    balotas.forEach(b => b.classList.remove('selected'));
    
    // Agregar selección actual
    elemento.classList.add('selected');
    
    // Actualizar campo oculto si existe
    const hiddenInput = document.getElementById('numero_balota');
    if (hiddenInput) {
        hiddenInput.value = numero;
    }
    
    // Habilitar botón de participar
    const btnParticipar = document.getElementById('btnParticipar');
    if (btnParticipar) {
        btnParticipar.disabled = false;
        btnParticipar.innerHTML = '<i class="fas fa-check me-2"></i>Participar con el número ' + numero;
    }
}

// Generar número aleatorio
function generarNumeroAleatorio() {
    const balotas = document.querySelectorAll('.balota:not(.disabled)');
    if (balotas.length === 0) {
        alert('No hay números disponibles');
        return;
    }
    
    const randomIndex = Math.floor(Math.random() * balotas.length);
    const randomBalota = balotas[randomIndex];
    
    selectBalota(randomBalota.textContent, randomBalota);
    
    // Efecto visual
    randomBalota.classList.add('pulse');
    setTimeout(() => {
        randomBalota.classList.remove('pulse');
    }, 2000);
}

// Confirmar participación
function confirmarParticipacion() {
    const numeroSeleccionado = document.getElementById('numero_balota')?.value;
    
    if (!numeroSeleccionado) {
        alert('Por favor selecciona un número antes de participar');
        return false;
    }
    
    return confirm(`¿Confirmas tu participación con el número ${numeroSeleccionado}?`);
}

// Ejecutar sorteo (solo admin)
function ejecutarSorteo(sorteoId) {
    if (!confirm('¿Estás seguro de que quieres ejecutar este sorteo? Esta acción no se puede deshacer.')) {
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    // Mostrar loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Ejecutando...';
    
    fetch('api/endpoints.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'ejecutar_sorteo',
            sorteo_id: sorteoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`¡Sorteo ejecutado! Ganador: ${data.ganador.nombre} con el número ${data.ganador.numero_balota}`);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al ejecutar el sorteo');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Validación de formularios
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validar email
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (field.value && !emailRegex.test(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Formatear números
function formatNumber(num) {
    return new Intl.NumberFormat('es-ES').format(num);
}

// Formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Copiar al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostrar mensaje de éxito
        showToast('Copiado al portapapeles', 'success');
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
        showToast('Error al copiar', 'danger');
    });
}

// Mostrar toast notifications
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remover el toast después de que se oculte
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Crear contenedor de toasts
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Actualizar estado de sorteo en tiempo real
function updateSorteoStatus() {
    const statusElements = document.querySelectorAll('[data-sorteo-id]');
    
    statusElements.forEach(element => {
        const sorteoId = element.getAttribute('data-sorteo-id');
        
        fetch(`api/endpoints.php?action=get_sorteo_status&id=${sorteoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.innerHTML = data.status;
                    element.className = `badge status-${data.estado}`;
                }
            })
            .catch(error => console.error('Error updating status:', error));
    });
}

// Actualizar cada 30 segundos
setInterval(updateSorteoStatus, 30000);

// Manejar formularios AJAX
function submitAjaxForm(formId, callback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm(formId)) {
            showToast('Por favor completa todos los campos requeridos', 'warning');
            return;
        }
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
        
        fetch(form.action, {
            method: form.method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                if (callback) callback(data);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al procesar la solicitud', 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
}

// Inicializar DataTables si está disponible
if (typeof $ !== 'undefined' && $.fn.DataTable) {
    $(document).ready(function() {
        $('.data-table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']]
        });
    });
}