'use strict';

const formFiltros = document.getElementById('form-filtros');
const tabla = document.getElementById('tabla-reservas');
const aviso = document.getElementById('aviso');

/* ---------- Avisos (cargando / éxito / error) ---------- */

function mostrarAviso(texto, tipo = 'info') {
    aviso.textContent = texto;
    aviso.className = `aviso aviso-${tipo}`;
    aviso.hidden = false;
}

/* ---------- Llamada a la API con manejo de errores ---------- */

async function pedirApi(url, opciones = {}) {
    let respuesta;
    try {
        respuesta = await fetch(url, {
            headers: { 'Content-Type': 'application/json' },
            ...opciones,
        });
    } catch {
        // fetch solo falla aquí si no hay red o el servidor no responde
        throw new Error('No se pudo conectar con el servidor.');
    }

    const datos = await respuesta.json().catch(() => null);

    if (!respuesta.ok) {
        // 400 → { errores: {campo: mensaje} } | 404/409/500 → { mensaje }
        const mensaje = datos?.errores
            ? Object.values(datos.errores).join(' ')
            : datos?.mensaje ?? `Error ${respuesta.status}`;
        throw new Error(mensaje);
    }
    return datos;
}

/* ---------- Pintar la tabla ---------- */

function formatearFecha(iso) {
    const [anio, mes, dia] = iso.split('-');
    return `${dia}/${mes}/${anio}`;
}

function formatearImporte(importe) {
    return Number(importe).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        useGrouping: 'always',
    }) + ' €';
}

function celda(texto) {
    const td = document.createElement('td');
    td.textContent = texto; // textContent nunca interpreta HTML → sin XSS
    return td;
}

function crearFila(reserva) {
    const tr = document.createElement('tr');
    tr.append(
        celda(reserva.nombre_huesped),
        celda(reserva.nombre_alojamiento),
        celda(formatearFecha(reserva.fecha_entrada)),
        celda(formatearFecha(reserva.fecha_salida)),
        celda(formatearImporte(reserva.importe)),
    );

    const tdEstado = document.createElement('td');
    const estado = document.createElement('span');
    estado.className = `estado estado-${reserva.estado}`;
    estado.textContent = reserva.estado.charAt(0).toUpperCase() + reserva.estado.slice(1);
    tdEstado.append(estado);

    const tdAcciones = document.createElement('td');
    const enlace = document.createElement('a');
    enlace.href = `/reservas/${reserva.id}`;
    enlace.textContent = 'Ver';
    tdAcciones.append(enlace);

    if (reserva.estado !== 'cancelada') {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn-cancelar';
        boton.dataset.id = reserva.id;
        boton.textContent = 'Cancelar';
        tdAcciones.append(' ', boton);
    }

    tr.append(tdEstado, tdAcciones);
    return tr;
}

function pintarTabla(reservas) {
    if (reservas.length === 0) {
        const tr = document.createElement('tr');
        const td = celda('No hay reservas con esos filtros.');
        td.colSpan = 7;
        tr.append(td);
        tabla.replaceChildren(tr);
        return;
    }
    tabla.replaceChildren(...reservas.map(crearFila));
}

/* ---------- Filtrar sin recargar ---------- */

formFiltros.addEventListener('submit', async (evento) => {
    evento.preventDefault(); // evita que el formulario recargue la página

    const parametros = new URLSearchParams(new FormData(formFiltros));
    const botones = formFiltros.querySelectorAll('button');
    botones.forEach((b) => (b.disabled = true));
    mostrarAviso('Cargando…');

    try {
        const reservas = await pedirApi(`/api/reservas?${parametros}`);
        pintarTabla(reservas);
        mostrarAviso(`${reservas.length} reserva(s) encontrada(s).`, 'exito');
    } catch (error) {
        mostrarAviso(error.message, 'error');
    } finally {
        botones.forEach((b) => (b.disabled = false));
    }
});

// "Limpiar" vacía los campos y vuelve a cargar la lista completa
formFiltros.addEventListener('reset', () => {
    setTimeout(() => formFiltros.requestSubmit()); // se ejecuta tras vaciar los campos
});

/* ---------- Cancelar con confirmación ---------- */

tabla.addEventListener('click', async (evento) => {
    const boton = evento.target.closest('.btn-cancelar');
    if (!boton) return;

    if (!confirm('¿Seguro que quieres cancelar esta reserva?')) return;

    boton.disabled = true;
    mostrarAviso('Cancelando…');

    try {
        const reserva = await pedirApi(`/api/reservas/${boton.dataset.id}`, {
            method: 'PATCH',
            body: JSON.stringify({ estado: 'cancelada' }),
        });
        boton.closest('tr').replaceWith(crearFila(reserva));
        mostrarAviso('Reserva cancelada.', 'exito');
    } catch (error) {
        mostrarAviso(error.message, 'error');
        boton.disabled = false;
    }
});
