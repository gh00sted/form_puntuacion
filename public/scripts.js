// Función para actualizar visibilidad de elementos condicionales
function updateConditionalFields() {
    // LLEGO_A_TIEMPO = 0 (No) → mostrar INFORMO_ASEG_TRAM
    const llegoAtiempo = document.querySelector('input[name="llego_a_tiempo"]:checked');
    const groupInformo = document.getElementById('group_informo_aseg');
    if (llegoAtiempo) {
        groupInformo.classList.toggle('hidden', llegoAtiempo.value !== '0');
    }

    // LOCALIZO_AVERIA = 0 (No) → mostrar LLAMO_ENCARGADO_1
    const localizoAveria = document.querySelector('input[name="localizo_averia"]:checked');
    const groupLlamoEnc1 = document.getElementById('group_llamo_encargado_1');
    if (localizoAveria) {
        groupLlamoEnc1.classList.toggle('hidden', localizoAveria.value !== '0');
    }

    // REPARO_PRIMERA = 1 (Sí) → mostrar grupo SI
    // REPARO_PRIMERA = 0 (No) → mostrar grupo NO
    const reparoPrimera = document.querySelector('input[name="reparo_primera"]:checked');
    const groupReparoSi = document.getElementById('group_reparo_si');
    const groupReparoNo = document.getElementById('group_reparo_no');
    
    if (reparoPrimera) {
        if (reparoPrimera.value === '1') {
            groupReparoSi.classList.remove('hidden');
            groupReparoNo.classList.add('hidden');
        } else {
            groupReparoSi.classList.add('hidden');
            groupReparoNo.classList.remove('hidden');
        }
    }
}

// Función para verificar si un elemento es visible
function isVisible(element) {
    // Recorrer el árbol DOM hacia arriba para verificar si algún elemento tiene la clase 'hidden'
    let current = element;
    while (current) {
        if (current.classList && current.classList.contains('hidden')) {
            return false;
        }
        current = current.parentElement;
    }
    return true;
}

// Puntuación con mapeo de items
function calculateScore() {
    let total = 0;

    // Items individuales con sus puntos
    const itemPoints = {
        'llego_a_tiempo': 1.00,
        'informo_aseg_tram': 0.50,
        'fotos_antes': 0.50,
        'localizo_averia': 1.00,
        'foto_durante': 0.50,
        'reparo_primera': 1.00,
        'justificado': 0.50,
        'foto_despues': 0.50,
        'segundo_gremio': 0.33,
        'tomo_datos': 0.33,
        'tomo_medidas': 0.33,
        'firma_asegurado': 0.25,
        'expediente_cerrado': 0.25
    };

    // Sumar puntos de items individuales marcados como "Sí" (value="1") y que sean visibles
    for (const [fieldName, points] of Object.entries(itemPoints)) {
        const radio = document.querySelector(`input[name="${fieldName}"]:checked`);
        if (radio && radio.value === '1' && isVisible(radio)) {
            total += points;
        }
    }

    // Sumar "Llamó a encargado" - 0.5 por cada uno marcado como Sí (solo si es visible)
    const llamoEnc1 = document.querySelector('input[name="llamo_encargado_1"]:checked');
    const llamoEnc2 = document.querySelector('input[name="llamo_encargado_2"]:checked');
    
    let contadorLlamoEnc = 0;
    if (llamoEnc1 && llamoEnc1.value === '1' && isVisible(llamoEnc1)) {
        contadorLlamoEnc++;
    }
    if (llamoEnc2 && llamoEnc2.value === '1' && isVisible(llamoEnc2)) {
        contadorLlamoEnc++;
    }
    
    if (contadorLlamoEnc === 1) {
        total += 0.50;  // 0.5 si solo uno está marcado
    } else if (contadorLlamoEnc === 2) {
        total += 1.00;  // 1.0 si ambos están marcados
    }

    document.getElementById('puntuacion').value = total.toFixed(2);
}

// Manejar scroll al buscar expedientes
function handleSearchScroll() {
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // Guardar la posición actual en sessionStorage
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });
    }
    
    // Restaurar la posición si existe
    const savedPosition = sessionStorage.getItem('scrollPosition');
    if (savedPosition !== null) {
        window.scrollTo(0, parseInt(savedPosition));
        sessionStorage.removeItem('scrollPosition');
    }
}

// Configurar datepickers
function setupDatePickers() {
    document.querySelectorAll('input[type="date"]').forEach(dateInput => {
        dateInput.addEventListener('click', function() {
            this.showPicker();
        });
        dateInput.addEventListener('focus', function() {
            this.showPicker();
        });
    });
}

// Inicializar todas las funcionalidades cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar scroll
    handleSearchScroll();
    
    // Configurar datepickers
    setupDatePickers();
    
    // Agregar listeners a botones condicionales
    document.querySelectorAll('.conditional-trigger').forEach(radio => {
        radio.addEventListener('change', updateConditionalFields);
    });

    // Agregar listeners a todos los radios para calcular puntuación
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', calculateScore);
    });
    
    // Calcular puntuación inicial
    calculateScore();
});
