function money(value) {
  return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(value || 0));
}

async function getJson(url, options = {}) {
  const response = await fetch(url, options);
  const raw = await response.text();
  const cleaned = raw.replace(/^\uFEFF+/, '').trim();
  let data;
  try {
    data = JSON.parse(cleaned);
  } catch (_) {
    throw new Error('Respuesta invalida del servidor: ' + raw.slice(0, 160));
  }
  if (!response.ok || !data.ok) throw new Error(data.message || 'Error en la solicitud.');
  return data;
}

function setResult(el, text, isError = false) {
  if (!el) return;
  el.textContent = text;
  el.classList.toggle('error', isError);
}

function optionsHtml(rows, valueKey, labelKey, empty = 'Seleccione...') {
  return `<option value="">${empty}</option>` + rows.map(r => `<option value="${r[valueKey]}">${r[labelKey]}</option>`).join('');
}

async function loadCatalogBase() { return (await getJson('../backend/catalogo.php?action=list')).data; }
async function loadUbicaciones() { return (await getJson('../backend/ubicaciones.php?action=list')).data; }

async function applyBranding() {
  try {
    const cfg = (await getJson('../backend/configuracion.php?action=get')).data || {};
    const logo = cfg.logo_url || 'assets/img/logo-default.png';

    document.querySelectorAll('.topbar h1').forEach((h1) => {
      if (h1.parentElement.classList.contains('brand-title')) return;
      const wrap = document.createElement('div');
      wrap.className = 'brand-title';
      const img = document.createElement('img');
      img.className = 'app-logo';
      img.onerror = () => { img.src = 'assets/img/logo-default.png'; };
      img.src = logo;
      img.alt = 'Logo';
      h1.parentNode.insertBefore(wrap, h1);
      wrap.appendChild(img);
      wrap.appendChild(h1);
    });

    document.querySelectorAll('.auth-box h1').forEach((h1) => {
      if (h1.dataset.logoApplied === '1') return;
      const img = document.createElement('img');
      img.className = 'login-logo';
      img.onerror = () => { img.src = 'assets/img/logo-default.png'; };
      img.src = logo;
      img.alt = 'Logo';
      h1.parentNode.insertBefore(img, h1);
      h1.dataset.logoApplied = '1';
    });
  } catch (_) {}
}

function initLogin() {
  applyBranding();
  const form = document.getElementById('formLogin');
  const out = document.getElementById('resultadoLogin');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const data = await getJson('../backend/auth_login.php', { method: 'POST', body: new FormData(form) });
      setResult(out, `Bienvenido ${data.data.nombre}. Redirigiendo...`);
      window.location.href = 'index.php';
    } catch (err) { setResult(out, err.message, true); }
  });
}

function initForgot() {
  applyBranding();
  const form = document.getElementById('formForgot');
  const out = document.getElementById('resultadoForgot');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const data = await getJson('../backend/auth_forgot_password.php', { method: 'POST', body: new FormData(form) });
      setResult(out, `Token: ${data.data.token}\nExpira: ${data.data.expira_en}\nUsalo en la pantalla de restablecer.`);
    } catch (err) { setResult(out, err.message, true); }
  });
}

function initReset() {
  applyBranding();
  const form = document.getElementById('formReset');
  const out = document.getElementById('resultadoReset');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      await getJson('../backend/auth_reset_password.php', { method: 'POST', body: new FormData(form) });
      setResult(out, 'Contrasena actualizada. Puedes iniciar sesion.');
      form.reset();
    } catch (err) { setResult(out, err.message, true); }
  });
}

async function logoutAndRedirect() {
  try { await getJson('../backend/auth_logout.php', { method: 'POST' }); } catch (_) {}
  window.location.href = 'login.php';
}

async function initDashboard() {
  applyBranding();
  try {
    const d = (await getJson('../backend/reportes.php?tipo=dashboard')).data;
    document.getElementById('totalCupos').textContent = d.total_cupos;
    document.getElementById('ocupados').textContent = d.ocupados;
    document.getElementById('disponibles').textContent = d.disponibles;
    document.getElementById('salidasHoy').textContent = d.salidas_hoy;
    document.getElementById('ingresosHoy').textContent = money(d.ingresos_hoy);
    document.getElementById('tablaZona').innerHTML = (d.ocupacion_zona || []).map(z => `<tr><td>${z.zona}</td><td>${z.ocupados}</td><td>${z.total}</td></tr>`).join('') || '<tr><td colspan="3">Sin datos</td></tr>';
    document.getElementById('tablaCategoriaActiva').innerHTML = (d.activos_por_categoria || []).map(c => `<tr><td>${c.categoria}</td><td>${c.cantidad}</td></tr>`).join('') || '<tr><td colspan="2">Sin datos</td></tr>';
    const a = await getJson('../backend/reportes.php?tipo=activos');
    document.getElementById('tablaActivos').innerHTML = (a.data || []).map(r => `<tr><td>${r.placa}</td><td>${r.categoria}</td><td>${r.ubicacion}</td><td>${r.hora_entrada}</td><td>${r.ticket_codigo}</td></tr>`).join('') || '<tr><td colspan="5">No hay activos</td></tr>';
  } catch (err) { console.error(err); }
}

async function initEntradaV2() {
  applyBranding();
  const form = document.getElementById('formEntrada');
  const out = document.getElementById('resultadoEntrada');
  const cat = document.getElementById('entradaCategoria');
  const ubi = document.getElementById('entradaUbicacion');
  const btnMakes = document.getElementById('btnCarqueryMakes');
  const btnModels = document.getElementById('btnCarqueryModels');
  const marcaNombre = document.getElementById('entradaMarcaNombre');
  const makeList = document.getElementById('carqueryMakes');
  const modelList = document.getElementById('carqueryModels');

  try {
    const c = await loadCatalogBase();
    cat.innerHTML = optionsHtml(c.categorias.filter(x => Number(x.activo) === 1), 'id', 'nombre');
    const us = await loadUbicaciones();
    const libres = us.filter(u => u.estado === 'LIBRE').map(u => ({ id: u.id, label: `${u.codigo} (Zona ${u.zona})` }));
    ubi.innerHTML = optionsHtml(libres, 'id', 'label');
  } catch (err) { setResult(out, err.message, true); }

  btnMakes?.addEventListener('click', async () => {
    try {
      const res = await getJson('../backend/carquery.php?action=makes');
      const rows = (res.data || []).slice(0, 400);
      makeList.innerHTML = rows.map(m => `<option value="${m.nombre}"></option>`).join('');
      setResult(out, `Marcas cargadas: ${rows.length}${res.source ? ` (fuente: ${res.source})` : ''}`);
    } catch (err) { setResult(out, err.message, true); }
  });

  btnModels?.addEventListener('click', async () => {
    const mk = marcaNombre?.value?.trim();
    if (!mk) return setResult(out, 'Escribe una marca para consultar modelos.', true);
    try {
      const res = await getJson(`../backend/carquery.php?action=models&make=${encodeURIComponent(mk)}`);
      const rows = (res.data || []).slice(0, 400);
      modelList.innerHTML = rows.map(m => `<option value="${m.nombre}"></option>`).join('');
      if (rows.length === 0) {
        setResult(out, `No hay modelos para ${mk}.`);
      } else {
        setResult(out, `Modelos cargados para ${mk}: ${rows.length}${res.source ? ` (fuente: ${res.source})` : ''}`);
      }
    } catch (err) { setResult(out, err.message, true); }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const d = (await getJson('../backend/registrar_entrada.php', { method: 'POST', body: new FormData(form) })).data;
      setResult(out, `Entrada registrada\nPlaca: ${d.placa}\nCategoria: ${d.categoria}\nUbicacion: ${d.ubicacion_codigo}\nTicket: ${d.ticket_codigo}\nTarifa/h: ${money(d.tarifa_hora || 0)}\nHora entrada: ${d.hora_entrada}\nCupos disponibles: ${d.cupos_disponibles}`);
      const ta = document.getElementById('ticketAccion');
      if (ta) ta.innerHTML = `<a class="btnlink" target="_blank" href="ticket_entrada.php?ticket=${encodeURIComponent(d.ticket_codigo)}">Ver / imprimir ticket</a>`;
      form.reset();
    } catch (err) { setResult(out, err.message, true); }
  });
}
function initSalidaV2() {
  applyBranding();
  const form = document.getElementById('formSalida');
  const out = document.getElementById('resultadoSalida');
  document.getElementById('btnCalcular').addEventListener('click', async () => {
    const placa = form.placa.value.trim();
    if (!placa) return setResult(out, 'Ingrese una placa.', true);
    try {
      const d = (await getJson(`../backend/calcular_pago.php?placa=${encodeURIComponent(placa)}`)).data;
      setResult(out, `Preliquidacion\nPlaca: ${d.placa}\nCategoria: ${d.categoria}\nTicket: ${d.ticket_codigo}\nEntrada: ${d.hora_entrada}\nTiempo real: ${d.minutos} min\nMin. gracia: ${d.minutos_gracia || 0}\nMin. cobrables: ${d.minutos_cobrables || 0}\nTarifa/h: ${money(d.tarifa_hora)}\nRegla: ${d.descripcion_cobro} (${d.modo_cobro})\nHoras cobrables: ${d.horas_cobrables}\nTotal: ${money(d.valor_total)}`);
    } catch (err) { setResult(out, err.message, true); }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const d = (await getJson('../backend/registrar_salida.php', { method: 'POST', body: new FormData(form) })).data;
      setResult(out, `Salida registrada\nPlaca: ${d.placa}\nTiempo real: ${d.minutos} min\nMin. gracia: ${d.minutos_gracia || 0}\nMin. cobrables: ${d.minutos_cobrables || 0}\nTarifa/h: ${money(d.tarifa_hora)}\nRegla: ${d.descripcion_cobro} (${d.modo_cobro})\nHoras cobrables: ${d.horas_cobrables}\nTotal pagado: ${money(d.valor_total)}\nFactura: ${d.factura_codigo}`);
      window.open(`factura.php?codigo=${encodeURIComponent(d.factura_codigo)}`, '_blank');
      form.reset();
    } catch (err) { setResult(out, err.message, true); }
  });
}

function initReportesV2() {
  applyBranding();
  const tipo = document.getElementById('tipoReporte');
  const btn = document.getElementById('btnCargarReporte');
  const resumen = document.getElementById('resumenReporte');
  const tabla = document.getElementById('tablaReporte');
  const btnExcel = document.getElementById('btnExcel');
  const btnPdf = document.getElementById('btnPdf');

  function refreshExportLinks() {
    btnExcel.href = `../backend/export_reporte.php?tipo=${encodeURIComponent(tipo.value)}&formato=excel`;
    btnPdf.href = `../backend/export_reporte.php?tipo=${encodeURIComponent(tipo.value)}&formato=pdf`;
  }

  async function crud(action, payload) {
    const fd = new FormData();
    fd.append('action', action);
    Object.entries(payload).forEach(([k, v]) => fd.append(k, v ?? ''));
    return await getJson('../backend/reportes.php', { method: 'POST', body: fd });
  }

  async function cargar() {
    try {
      refreshExportLinks();
      const r = (await getJson(`../backend/reportes.php?tipo=${tipo.value}`)).data;
      resumen.textContent = `Servicios: ${r.resumen.total_servicios} | Ingresos: ${money(r.resumen.total_ingresos)}`;
      tabla.innerHTML = (r.registros || []).map(x => {
        const factura = x.factura_codigo ? `<a href="factura.php?codigo=${encodeURIComponent(x.factura_codigo)}" target="_blank">${x.factura_codigo}</a>` : '';
        const acciones = r.can_crud ? `<button data-rid="${x.id}" data-act="edit">Editar</button> <button data-rid="${x.id}" data-act="del">Eliminar</button>` : '-';
        return `<tr><td>${x.id}</td><td>${x.estado}</td><td>${x.placa}</td><td>${x.marca || ''}</td><td>${x.modelo || ''}</td><td>${x.color || ''}</td><td>${x.categoria}</td><td>${x.ubicacion}</td><td>${x.ticket_codigo || ''}</td><td>${factura}</td><td>${x.hora_entrada || ''}</td><td>${x.hora_salida || ''}</td><td>${x.total_minutos ?? ''}</td><td>${x.metodo || ''}</td><td>${money(x.valor_total || 0)}</td><td>${acciones}</td></tr>`;
      }).join('') || '<tr><td colspan="16">Sin registros</td></tr>';

      if (r.can_crud) {
        tabla.querySelectorAll('button[data-act="del"]').forEach((b) => b.addEventListener('click', async () => {
          const id = b.getAttribute('data-rid');
          if (!confirm(`Eliminar registro ${id}?`)) return;
          try { await crud('delete', { id }); await cargar(); } catch (err) { alert(err.message); }
        }));

        tabla.querySelectorAll('button[data-act="edit"]').forEach((b) => b.addEventListener('click', async () => {
          const id = b.getAttribute('data-rid');
          const tr = b.closest('tr').children;
          const entrada = prompt('Nueva hora entrada (YYYY-MM-DD HH:MM:SS)', tr[10].textContent.trim());
          if (entrada === null) return;
          const salida = prompt('Nueva hora salida (vacio para ACTIVO)', tr[11].textContent.trim());
          if (salida === null) return;
          const valor = prompt('Nuevo valor total', tr[14].textContent.replace(/[^0-9.]/g, ''));
          try { await crud('update', { id, hora_entrada: entrada, hora_salida: salida, valor_total: valor }); await cargar(); } catch (err) { alert(err.message); }
        }));
      }
    } catch (err) {
      resumen.textContent = err.message;
      tabla.innerHTML = '';
    }
  }

  tipo.addEventListener('change', refreshExportLinks);
  btn.addEventListener('click', cargar);
  refreshExportLinks();
  cargar();
}

function attachCrudHandlers(tableEl, rows, fillForm, onDelete) {
  tableEl.querySelectorAll('[data-row]').forEach(tr => {
    const id = Number(tr.dataset.row);
    const row = rows.find(r => Number(r.id) === id);
    tr.querySelector('[data-act="edit"]').addEventListener('click', () => fillForm(row));
    tr.querySelector('[data-act="del"]').addEventListener('click', () => onDelete(row));
  });
}

async function initAdmin() {
  applyBranding();
  const role = window.PARKSYS_ROLE;

  async function refreshCatalog() {
    const data = await loadCatalogBase();
    const tc = document.getElementById('tablaCategorias');
    tc.innerHTML = data.categorias.map(r => `<tr data-row="${r.id}"><td>${r.id}</td><td>${r.nombre}</td><td>${money(r.valor_hora)}</td><td>${Number(r.activo) ? 'SI' : 'NO'}</td><td><button data-act="edit">Editar</button> <button data-act="del">Eliminar</button></td></tr>`).join('');
    attachCrudHandlers(tc, data.categorias, (row) => {
      const f = document.getElementById('formCategoria'); f.id.value = row.id; f.nombre.value = row.nombre; f.valor_hora.value = row.valor_hora; f.activo.value = row.activo;
    }, async (row) => { if (!confirm('Eliminar categoria?')) return; await getJson('../backend/catalogo.php?action=delete_categoria', { method: 'POST', body: new URLSearchParams({ id: row.id }) }); await refreshCatalog(); });

    const tm = document.getElementById('tablaMarcas');
    tm.innerHTML = data.marcas.map(r => `<tr data-row="${r.id}"><td>${r.id}</td><td>${r.nombre}</td><td>${Number(r.activo) ? 'SI' : 'NO'}</td><td><button data-act="edit">Editar</button> <button data-act="del">Eliminar</button></td></tr>`).join('');
    attachCrudHandlers(tm, data.marcas, (row) => {
      const f = document.getElementById('formMarca'); f.id.value = row.id; f.nombre.value = row.nombre; f.activo.value = row.activo;
    }, async (row) => { if (!confirm('Eliminar marca?')) return; await getJson('../backend/catalogo.php?action=delete_marca', { method: 'POST', body: new URLSearchParams({ id: row.id }) }); await refreshCatalog(); });

    const tmo = document.getElementById('tablaModelos');
    tmo.innerHTML = data.modelos.map(r => `<tr data-row="${r.id}"><td>${r.id}</td><td>${r.nombre}</td><td>${r.marca}</td><td>${r.categoria || ''}</td><td>${Number(r.activo) ? 'SI' : 'NO'}</td><td><button data-act="edit">Editar</button> <button data-act="del">Eliminar</button></td></tr>`).join('');
    attachCrudHandlers(tmo, data.modelos, (row) => {
      const f = document.getElementById('formModelo'); f.id.value = row.id; f.nombre.value = row.nombre; f.marca_id.value = row.marca_id; f.categoria_id.value = row.categoria_id || ''; f.activo.value = row.activo;
    }, async (row) => { if (!confirm('Eliminar modelo?')) return; await getJson('../backend/catalogo.php?action=delete_modelo', { method: 'POST', body: new URLSearchParams({ id: row.id }) }); await refreshCatalog(); });

    document.getElementById('modeloMarca').innerHTML = optionsHtml(data.marcas.filter(x => Number(x.activo) === 1), 'id', 'nombre');
    document.getElementById('modeloCategoria').innerHTML = optionsHtml(data.categorias, 'id', 'nombre', 'Sin categoria');
    const simCat = document.getElementById('simCategoria');
    if (simCat) simCat.innerHTML = optionsHtml(data.categorias.filter(x => Number(x.activo) === 1), 'id', 'nombre');
  }

  async function refreshUbicaciones() {
    const rows = await loadUbicaciones();
    const tb = document.getElementById('tablaUbicaciones');
    tb.innerHTML = rows.map(r => `<tr data-row="${r.id}"><td>${r.id}</td><td>${r.codigo}</td><td>${r.zona}</td><td>${r.estado}</td><td>${r.observacion || ''}</td><td><button data-act="edit">Editar</button> <button data-act="del">Eliminar</button></td></tr>`).join('');
    attachCrudHandlers(tb, rows, (row) => {
      const f = document.getElementById('formUbicacion'); f.id.value = row.id; f.codigo.value = row.codigo; f.zona.value = row.zona; f.estado.value = row.estado; f.observacion.value = row.observacion || '';
    }, async (row) => { if (!confirm('Eliminar ubicacion?')) return; await getJson('../backend/ubicaciones.php?action=delete', { method: 'POST', body: new URLSearchParams({ id: row.id }) }); await refreshUbicaciones(); });
  }

  async function refreshUsuarios() {
    if (role !== 'SUPERADMIN') return;
    const rows = (await getJson('../backend/usuarios.php?action=list')).data;
    const tb = document.getElementById('tablaUsuarios');
    tb.innerHTML = rows.map(r => `<tr data-row="${r.id}"><td>${r.id}</td><td>${r.nombre}</td><td>${r.email}</td><td>${r.rol}</td><td>${Number(r.activo) ? 'SI' : 'NO'}</td><td><button data-act="edit">Editar</button> <button data-act="del">Eliminar</button></td></tr>`).join('');
    attachCrudHandlers(tb, rows, (row) => {
      const f = document.getElementById('formUsuario'); f.id.value = row.id; f.nombre.value = row.nombre; f.email.value = row.email; f.rol.value = row.rol; f.activo.value = row.activo; f.password.value = '';
    }, async (row) => { if (!confirm('Eliminar usuario?')) return; await getJson('../backend/usuarios.php?action=delete', { method: 'POST', body: new URLSearchParams({ id: row.id }) }); await refreshUsuarios(); });
  }

  async function refreshAuditoria() {
    const rows = (await getJson('../backend/auditoria.php?limit=120')).data;
    document.getElementById('tablaAuditoria').innerHTML = rows.map(r => `<tr><td>${r.id}</td><td>${r.creado_en}</td><td>${r.usuario_nombre || '-'}<br>${r.email || ''}</td><td>${r.accion}</td><td>${r.detalle || ''}</td><td>${r.ip || ''}</td></tr>`).join('') || '<tr><td colspan="6">Sin datos</td></tr>';
  }

  document.getElementById('formCategoria')?.addEventListener('submit', async (e) => { e.preventDefault(); const out = document.getElementById('resultadoCategoria'); try { await getJson('../backend/catalogo.php?action=save_categoria', { method: 'POST', body: new FormData(e.target) }); setResult(out, 'Categoria guardada.'); e.target.reset(); await refreshCatalog(); } catch (err) { setResult(out, err.message, true); } });
  document.getElementById('formMarca')?.addEventListener('submit', async (e) => { e.preventDefault(); const out = document.getElementById('resultadoMarca'); try { await getJson('../backend/catalogo.php?action=save_marca', { method: 'POST', body: new FormData(e.target) }); setResult(out, 'Marca guardada.'); e.target.reset(); await refreshCatalog(); } catch (err) { setResult(out, err.message, true); } });
  document.getElementById('formModelo')?.addEventListener('submit', async (e) => { e.preventDefault(); const out = document.getElementById('resultadoModelo'); try { await getJson('../backend/catalogo.php?action=save_modelo', { method: 'POST', body: new FormData(e.target) }); setResult(out, 'Modelo guardado.'); e.target.reset(); await refreshCatalog(); } catch (err) { setResult(out, err.message, true); } });
  document.getElementById('formUbicacion')?.addEventListener('submit', async (e) => { e.preventDefault(); const out = document.getElementById('resultadoUbicacion'); try { await getJson('../backend/ubicaciones.php?action=save', { method: 'POST', body: new FormData(e.target) }); setResult(out, 'Ubicacion guardada.'); e.target.reset(); await refreshUbicaciones(); } catch (err) { setResult(out, err.message, true); } });
  document.getElementById('formUsuario')?.addEventListener('submit', async (e) => { e.preventDefault(); const out = document.getElementById('resultadoUsuario'); try { await getJson('../backend/usuarios.php?action=save', { method: 'POST', body: new FormData(e.target) }); setResult(out, 'Usuario guardado.'); e.target.reset(); await refreshUsuarios(); } catch (err) { setResult(out, err.message, true); } });
  document.getElementById('btnAuditoria')?.addEventListener('click', refreshAuditoria);

  document.getElementById('formSimuladorCobro')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const out = document.getElementById('resultadoSimuladorCobro');
    try {
      const d = (await getJson('../backend/simulador_cobro.php', { method: 'POST', body: new FormData(e.target) })).data;
      setResult(out, `Simulacion\nCategoria: ${d.categoria}\nMinutos: ${d.minutos}\nMin. gracia: ${d.minutos_gracia || 0}\nMin. cobrables: ${d.minutos_cobrables || 0}\nTarifa/h: ${money(d.tarifa_hora)}\nRegla: ${d.descripcion_cobro} (${d.modo_cobro})\nHoras cobrables: ${d.horas_cobrables}\nTotal: ${money(d.valor_total)}`);
    } catch (err) { setResult(out, err.message, true); }
  });

  document.getElementById('formModoCobro')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const out = document.getElementById('resultadoModoCobro');
    try {
      const fd = new FormData();
      fd.append('clave', 'modo_cobro');
      fd.append('valor', e.target.valor.value);
      await getJson('../backend/configuracion.php?action=set', { method: 'POST', body: fd });

      const fg = new FormData();
      fg.append('clave', 'minutos_gracia');
      fg.append('valor', String(Math.max(0, Math.min(120, Number(e.target.minutos_gracia.value || 0)))));
      await getJson('../backend/configuracion.php?action=set', { method: 'POST', body: fg });

      setResult(out, 'Regla de cobro guardada.');
    } catch (err) { setResult(out, err.message, true); }
  });

  document.getElementById('formLogo')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const out = document.getElementById('resultadoLogo');
    try {
      await getJson('../backend/upload_logo.php', { method: 'POST', body: new FormData(e.target) });
      setResult(out, 'Logo actualizado correctamente.');
      await applyBranding();
      e.target.reset();
    } catch (err) { setResult(out, err.message, true); }
  });

  document.getElementById('btnAdminCarqueryMakes')?.addEventListener('click', async () => {
    const tb = document.getElementById('tablaCarqueryMakes');
    try {
      const res = await getJson('../backend/carquery.php?action=makes');
      const rows = res.data || [];
      tb.innerHTML = rows.slice(0, 2000).map((r, i) => `<tr><td>${i + 1}</td><td>${r.nombre}</td></tr>`).join('') || '<tr><td colspan="2">Sin marcas disponibles</td></tr>';
    } catch (err) {
      tb.innerHTML = `<tr><td colspan="2">${err.message}</td></tr>`;
    }
  });

  document.getElementById('btnAdminCarqueryModels')?.addEventListener('click', async () => {
    const mk = document.getElementById('adminCarqueryMake')?.value?.trim();
    const tb = document.getElementById('tablaCarqueryModels');
    if (!mk) return tb.innerHTML = '<tr><td colspan="2">Escribe una marca.</td></tr>';
    try {
      const res = await getJson(`../backend/carquery.php?action=models&make=${encodeURIComponent(mk)}`);
      const rows = res.data || [];
      tb.innerHTML = rows.slice(0, 3000).map((r, i) => `<tr><td>${i + 1}</td><td>${r.nombre}</td></tr>`).join('') || '<tr><td colspan="2">Sin modelos para esa marca</td></tr>';
    } catch (err) {
      tb.innerHTML = `<tr><td colspan="2">${err.message}</td></tr>`;
    }
  });
  try {
    const cfg = (await getJson('../backend/configuracion.php?action=get')).data || {};
    const sel = document.getElementById('modoCobroSelect');
    if (sel) sel.value = cfg.modo_cobro || 'POR_MINUTO';
    const mg = document.getElementById('minutosGracia');
    if (mg) mg.value = Number(cfg.minutos_gracia ?? 5);
  } catch (_) {}

  await refreshCatalog();
  await refreshUbicaciones();
  await refreshUsuarios();
  await refreshAuditoria();
}

async function initFactura(codigo) {
  applyBranding();
  const box = document.getElementById('facturaContenido');
  try {
    const f = (await getJson(`../backend/factura.php?codigo=${encodeURIComponent(codigo)}`)).data;
    box.innerHTML = `<p><strong>Factura:</strong> ${f.factura_codigo}</p><p><strong>Ticket:</strong> ${f.ticket_codigo}</p><p><strong>Placa:</strong> ${f.placa} (${f.categoria})</p><p><strong>Ubicacion:</strong> ${f.ubicacion}</p><p><strong>Entrada:</strong> ${f.hora_entrada}</p><p><strong>Salida:</strong> ${f.hora_salida}</p><p><strong>Tiempo:</strong> ${f.total_minutos} minutos</p><p><strong>Metodo pago:</strong> ${f.metodo || 'N/A'}</p><h3>Total: ${money(f.valor_total)}</h3><p>Atendido por: ${f.atendio_salida || f.atendio_entrada}</p>`;
  } catch (err) { box.textContent = err.message; }
}

async function initTicketEntrada(ticket) {
  applyBranding();
  const box = document.getElementById('ticketEntradaContenido');
  try {
    const t = (await getJson(`../backend/ticket_entrada.php?ticket=${encodeURIComponent(ticket)}`)).data;
    box.innerHTML = `
      <p><strong>Ticket:</strong> ${t.ticket_codigo}</p>
      <p><strong>Placa:</strong> ${t.placa}</p>
      <p><strong>Categoria:</strong> ${t.categoria}</p>
      <p><strong>Marca / Modelo:</strong> ${t.marca || 'N/A'} / ${t.modelo || 'N/A'}</p>
      <p><strong>Color:</strong> ${t.color || 'N/A'}</p>
      <p><strong>Ubicacion:</strong> ${t.ubicacion}</p>
      <p><strong>Hora entrada:</strong> ${t.hora_entrada}</p>
      <p><strong>Tarifa por hora:</strong> ${money(t.tarifa_hora || 0)}</p>
      <p><strong>Atendido por:</strong> ${t.operador || 'N/A'}</p>
      <p><em>Conserve este ticket para validar la salida.</em></p>
    `;
  } catch (err) {
    box.textContent = err.message;
  }
}

// Overrides for styled receipt templates
async function initFactura(codigo) {
  applyBranding();
  const box = document.getElementById('facturaContenido');
  const total = document.getElementById('facturaTotal');
  const fecha = document.getElementById('facturaFecha');
  const codeText = document.getElementById('facturaCodeText');

  try {
    const f = (await getJson(`../backend/factura.php?codigo=${encodeURIComponent(codigo)}`)).data;
    if (fecha) fecha.textContent = `DATE: ${f.hora_salida || f.fecha_pago || ''}`;
    if (codeText) codeText.textContent = f.factura_codigo || codigo;
    if (total) total.textContent = money(f.valor_total || 0);

    if (box) {
      box.innerHTML = `
        <p><strong>Ticket:</strong> ${f.ticket_codigo}</p>
        <p><strong>Placa:</strong> ${f.placa}</p>
        <p><strong>Categoria:</strong> ${f.categoria}</p>
        <p><strong>Ubicacion:</strong> ${f.ubicacion}</p>
        <p><strong>Entrada:</strong> ${f.hora_entrada}</p>
        <p><strong>Salida:</strong> ${f.hora_salida}</p>
        <p><strong>Tiempo:</strong> ${f.total_minutos} min</p>
        <p><strong>Metodo:</strong> ${f.metodo || 'N/A'}</p>
        <p><strong>Factura:</strong> ${f.factura_codigo}</p>
      `;
    }
  } catch (err) {
    if (box) box.textContent = err.message;
  }
}

async function initTicketEntrada(ticket) {
  applyBranding();
  const box = document.getElementById('ticketEntradaContenido');
  const tarifa = document.getElementById('ticketTarifa');
  const fecha = document.getElementById('ticketFecha');
  const codeText = document.getElementById('ticketCodeText');

  try {
    const t = (await getJson(`../backend/ticket_entrada.php?ticket=${encodeURIComponent(ticket)}`)).data;
    if (fecha) fecha.textContent = t.hora_entrada || '';
    if (codeText) codeText.textContent = t.ticket_codigo || ticket;
    if (tarifa) tarifa.textContent = money(t.tarifa_hora || 0);

    if (box) {
      box.innerHTML = `
        <p><strong>Ticket:</strong> ${t.ticket_codigo}</p>
        <p><strong>Placa:</strong> ${t.placa}</p>
        <p><strong>Categoria:</strong> ${t.categoria}</p>
        <p><strong>Marca:</strong> ${t.marca || 'N/A'}</p>
        <p><strong>Modelo:</strong> ${t.modelo || 'N/A'}</p>
        <p><strong>Color:</strong> ${t.color || 'N/A'}</p>
        <p><strong>Ubicacion:</strong> ${t.ubicacion}</p>
        <p><strong>Hora entrada:</strong> ${t.hora_entrada}</p>
        <p><strong>Operador:</strong> ${t.operador || 'N/A'}</p>
      `;
    }
  } catch (err) {
    if (box) box.textContent = err.message;
  }
}

// Override branding to always refresh logo src
async function applyBranding() {
  try {
    const cfg = (await getJson('../backend/configuracion.php?action=get')).data || {};
    const logo = cfg.logo_url || 'assets/img/logo-default.png';

    document.querySelectorAll('.topbar h1').forEach((h1) => {
      let wrap = h1.parentElement.classList.contains('brand-title') ? h1.parentElement : null;
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'brand-title';
        h1.parentNode.insertBefore(wrap, h1);
        wrap.appendChild(h1);
      }

      let img = wrap.querySelector('img.app-logo');
      if (!img) {
        img = document.createElement('img');
        img.className = 'app-logo';
        img.alt = 'Logo';
        wrap.insertBefore(img, wrap.firstChild);
      }
      img.onerror = () => { img.src = 'assets/img/logo-default.png'; };
      img.src = logo;
    });

    document.querySelectorAll('.auth-box h1').forEach((h1) => {
      let img = h1.parentNode.querySelector('img.login-logo');
      if (!img) {
        img = document.createElement('img');
        img.className = 'login-logo';
        img.alt = 'Logo';
        h1.parentNode.insertBefore(img, h1);
      }
      img.onerror = () => { img.src = 'assets/img/logo-default.png'; };
      img.src = logo;
    });

    document.querySelectorAll('.receipt-logo, #receiptLogo').forEach((img) => {
      img.onerror = () => { img.src = 'assets/img/logo-default.png'; };
      img.src = logo;
    });
  } catch (_) {}
}
