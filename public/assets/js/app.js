'use strict';
const endpoint = (route, params = {}) => '/index.php?' + new URLSearchParams({ r: route, ...params });
const byId = id => document.getElementById(id);
const statusText = { PUBLICADA: 'Publicada', EM_ACOMPANHAMENTO: 'Em acompanhamento', RESOLVIDA: 'Resolvida' };
const textNode = (tag, text, className = '') => { const el = document.createElement(tag); el.textContent = text; el.className = className; return el; };
async function fetchJson(url, signal) {
  const response = await fetch(url, { headers: { Accept: 'application/json' }, signal });
  const data = await response.json();
  if (!response.ok) throw new Error(data.error || 'Não foi possível consultar os dados.');
  return data;
}
function baseMap(id) {
  const map = L.map(id).setView([-27.214, -49.643], 13);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
  return map;
}
let activeMap, pickedMarker;
const mapStatus = message => { if (byId('map-status')) byId('map-status').textContent = message; };
if (byId('occurrence-map')) {
  activeMap = baseMap('occurrence-map'); const markers = L.layerGroup().addTo(activeMap); let request, timer;
  async function refresh() {
    request?.abort(); request = new AbortController();
    const params = Object.fromEntries(new FormData(byId('filters'))); delete params.r;
    params.bbox = activeMap.getBounds().toBBoxString();
    try {
      const result = await fetchJson(endpoint('/api/ocorrencias', params), request.signal); markers.clearLayers();
      for (const row of result.rows) {
        const popup = document.createElement('div'); popup.append(textNode('p', row.titulo, 'popup-title'));
        popup.append(textNode('p', `${row.categoria} · ${statusText[row.status]}`));
        if (row.demonstracao) popup.append(textNode('p', 'Dado fictício de demonstração'));
        const link = textNode('a', 'Ver detalhes'); link.href = endpoint('/ocorrencia', { id: row.id }); popup.append(link);
        const iconElement = textNode('i', '', /^[-a-z0-9]+$/.test(row.icone) ? `bi bi-${row.icone}` : 'bi bi-geo-alt');
        const icon = L.divIcon({ className: `map-pin ${row.status}`, html: iconElement, iconSize: [32,32] });
        L.marker([Number(row.latitude), Number(row.longitude)], { icon, title: row.titulo }).bindPopup(popup).addTo(markers);
      }
      mapStatus(result.limited ? 'Exibindo até 500 relatos. Aproxime o mapa ou aplique filtros.' : `${result.total} relatos nesta área.`);
    } catch (error) { if (error.name !== 'AbortError') mapStatus(error.message); }
  }
  activeMap.on('moveend', () => { clearTimeout(timer); timer = setTimeout(refresh, 250); });
  byId('filters').addEventListener('submit', event => { event.preventDefault(); refresh(); }); refresh();
}
if (byId('picker-map')) {
  activeMap = baseMap('picker-map');
  function selectPoint(lat, lng) {
    if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat)>90 || Math.abs(lng)>180) return;
    byId('latitude').value = lat.toFixed(7); byId('longitude').value = lng.toFixed(7);
    if (pickedMarker) pickedMarker.setLatLng([lat,lng]); else pickedMarker = L.marker([lat,lng]).addTo(activeMap);
    mapStatus('Local do problema selecionado. Confira antes de enviar.');
    byId('occurrence-form').dispatchEvent(new Event('input', { bubbles: true }));
  }
  activeMap.on('click', event => selectPoint(event.latlng.lat, event.latlng.lng));
  for (const id of ['latitude','longitude']) byId(id).addEventListener('change', () => {
    if (byId('latitude').value && byId('longitude').value) selectPoint(Number(byId('latitude').value), Number(byId('longitude').value));
  });
  if (byId('latitude').value && byId('longitude').value) { selectPoint(Number(byId('latitude').value), Number(byId('longitude').value)); activeMap.setView(pickedMarker.getLatLng(), 15); }
}
byId('locate')?.addEventListener('click', () => {
  if (!navigator.geolocation) { mapStatus('Geolocalização indisponível. Selecione o ponto manualmente.'); return; }
  mapStatus('Solicitando sua autorização para usar a localização…');
  navigator.geolocation.getCurrentPosition(position => {
    activeMap.setView([position.coords.latitude, position.coords.longitude], 16);
    if (byId('picker-map')) activeMap.fire('click', { latlng: L.latLng(position.coords.latitude, position.coords.longitude) });
  }, () => mapStatus('Não foi possível obter a localização. Você pode selecionar o ponto manualmente.'), { enableHighAccuracy: false, timeout: 10000 });
});
if (byId('detail-map')) {
  const el=byId('detail-map'), map=baseMap('detail-map'), point=[Number(el.dataset.lat),Number(el.dataset.lng)];
  map.setView(point,15); L.marker(point).addTo(map);
}
if (byId('occurrence-form')) {
  const form=byId('occurrence-form'); let reviewed=false, previewUrls=[];
  function resetReview() { reviewed=false; byId('review-panel').classList.add('d-none'); byId('submit-report').textContent='Revisar ocorrência →'; }
  form.addEventListener('input',resetReview); form.addEventListener('change',resetReview);
  function updateSubcategories() {
    const category=byId('categoria_id').value;
    for (const option of byId('subcategoria_id').options) {
      option.hidden=!!option.value && option.dataset.category!==category; option.disabled=option.hidden;
      if (option.hidden && option.selected) byId('subcategoria_id').value='';
    }
  }
  byId('categoria_id').addEventListener('change',updateSubcategories); updateSubcategories();
  byId('fotos').addEventListener('change', () => {
    previewUrls.forEach(URL.revokeObjectURL); previewUrls=[]; byId('image-previews').replaceChildren();
    const files=Array.from(byId('fotos').files);
    byId('fotos').setCustomValidity(files.length>3 || files.some(f=>f.size>5*1024*1024) ? 'Envie até três fotografias, cada uma com no máximo 5 MB.' : '');
    for (const file of files.slice(0,3)) {
      if (!['image/jpeg','image/png','image/webp'].includes(file.type)) continue;
      const image=document.createElement('img'), objectUrl=URL.createObjectURL(file); previewUrls.push(objectUrl); image.src=objectUrl; image.alt=`Prévia: ${file.name}`; byId('image-previews').append(image);
    }
  });
  byId('edit-review').addEventListener('click',()=>{resetReview();byId('titulo').focus();});
  form.addEventListener('submit',async event=>{
    if(reviewed) return;
    event.preventDefault(); if(!form.reportValidity()) return;
    const content=byId('review-content'); content.replaceChildren();
    for(const [id,label] of [['titulo','Título'],['descricao','Descrição'],['categoria_id','Categoria'],['subcategoria_id','Subcategoria'],['bairro_id','Bairro'],['endereco_referencia','Referência'],['latitude','Latitude'],['longitude','Longitude']]) {
      const field=byId(id), value=field.tagName==='SELECT'?field.selectedOptions[0]?.textContent:field.value;
      content.append(textNode('p',`${label}: ${value || 'Não informado'}`));
    }
    content.append(textNode('p',`Novas fotografias: ${byId('fotos').files.length}`));
    byId('review-panel').classList.remove('d-none'); byId('review-panel').focus(); byId('submit-report').textContent='Confirmar e enviar para moderação'; reviewed=true;
    const box=byId('similar-results');
    try {
      const data=await fetchJson(endpoint('/api/semelhantes',{latitude:byId('latitude').value,longitude:byId('longitude').value,categoria_id:byId('categoria_id').value}));
      box.replaceChildren();box.classList.toggle('d-none',data.rows.length===0);
      if(data.rows.length){box.append(textNode('strong','Relatos próximos que podem estar relacionados:'));const list=document.createElement('ul');for(const row of data.rows){const li=document.createElement('li'),link=textNode('a',row.titulo);link.href=endpoint('/ocorrencia',{id:row.id});link.target='_blank';link.rel='noopener';li.append(link);list.append(li);}box.append(list);}
    }catch{box.textContent='Não foi possível verificar relatos semelhantes. Você ainda pode enviar seu registro.';box.classList.remove('d-none');}
  });
}
if(document.querySelector('[data-chart]')) fetchJson(endpoint('/api/indicadores')).then(data=>{
  for(const canvas of document.querySelectorAll('[data-chart]')) {
    const rows=data[canvas.dataset.chart];
    new Chart(canvas, {
      type:'bar',
      data:{labels:rows.map(x=>x.label),datasets:[{label:'Relatos públicos',data:rows.map(x=>Number(x.total)),backgroundColor:'#185adb',borderRadius:4}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}
    });
  }
}).catch(()=>{}); // As tabelas HTML continuam disponíveis se o gráfico falhar.
if(byId('hydro-chart')){const rows=[...document.querySelectorAll('#hydro-data tbody tr')].reverse();new Chart(byId('hydro-chart'),{type:'line',data:{labels:rows.map(r=>r.dataset.date),datasets:[{label:'Nível registrado (m)',data:rows.map(r=>Number(r.dataset.level)),borderColor:'#185adb',tension:0}]},options:{responsive:true,maintainAspectRatio:false}});}
