<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Adicionar Álbum — Discogs</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<main class="wrap">
  <header><h1>Adicionar Álbum</h1><a class="btn" href="inventory.php">Inventário</a></header>

  <section class="card">
    <label for="q">Pesquisar álbum (masters + versões)</label>
    <input id="q" placeholder="Digite o álbum ou artista">
    <div id="suggestions" class="suggestions" role="list"></div>
  </section>

  <section class="card" id="formCard">
    <h2>Detalhes do álbum</h2>
    <div class="form-grid">
      <div class="cover-preview"><img id="coverImg" src="" alt="" /></div>
      <div class="fields">
        <label>Nome (título)</label><input id="title">
        <label>Artista</label><input id="artist">
        <label>Data (ano)</label><input id="year">
        <label>Gênero</label><input id="genre">
        <label>Style(s)</label><input id="style">
        <label>Variações</label><input id="variations" readonly>
        <div class="actions"><button id="saveBtn">Salvar álbum</button></div>
      </div>
    </div>

    <div id="tracklistArea"></div>
  </section>
</main>

<script>
const qs = (s)=>document.querySelector(s);
const q = qs('#q');
const suggestions = qs('#suggestions');
const coverImg = qs('#coverImg');
const titleInput = qs('#title');
const artistInput = qs('#artist');
const yearInput = qs('#year');
const genreInput = qs('#genre');
const styleInput = qs('#style');
const variationsInput = qs('#variations');
const tracklistArea = qs('#tracklistArea');
const saveBtn = qs('#saveBtn');

let debounce = null;

q.addEventListener('input', ()=>{
  clearTimeout(debounce);
  suggestions.innerHTML = '';
  if (!q.value.trim()) return;
  debounce = setTimeout(()=> searchMasters(q.value.trim()), 300);
});

async function searchMasters(term){
  try {
    const res = await fetch('api_search.php?q='+encodeURIComponent(term));
    const data = await res.json();
    if (data.error) { suggestions.innerHTML = '<div class="error">Erro: '+data.error+'</div>'; return; }
    renderMasters(data.results || []);
  } catch (e){ console.error(e); suggestions.innerHTML = '<div class="error">Erro ao buscar masters</div>'; }
}

function renderMasters(items){
  suggestions.innerHTML = '';
  items.forEach(m=>{
    const img = m.thumb || m.cover_image || '';
    const div = document.createElement('div');
    div.className = 'suggestion';
    div.innerHTML = `
      <img src="${img}" class="thumb" data-master="${m.id}">
      <div class="meta">
        <div class="tit">${escapeHtml(m.title)}</div>
        <div class="sub">${escapeHtml((m.year||'') + ' • ' + (m.format||''))}</div>
        <div class="small">Master ID: ${m.id} • ${escapeHtml(m.type||'')}</div>
        <div class="v-actions"><button class="btn small" data-master="${m.id}">Ver versões</button></div>
      </div>
    `;
    suggestions.appendChild(div);
  });
}

// delegate clicks inside suggestions: button[data-master] to load versions, image or title click to load versions too
suggestions.addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-master]');
  const img = e.target.closest('img[data-master]');
  const masterId = btn ? btn.getAttribute('data-master') : (img ? img.getAttribute('data-master') : null);
  if (masterId) {
    await loadVersions(masterId);
  }
  // select release button will be handled below as delegation after rendering versions
});

async function loadVersions(masterId){
  suggestions.innerHTML = '<div class="loading">Carregando versões...</div>';
  try {
    const res = await fetch('api_get_versions.php?master_id='+masterId);
    const data = await res.json();
    if (data.error) { suggestions.innerHTML = '<div class="error">Erro: '+data.error+'</div>'; return; }
    renderVersionsList(data.versions || []);
  } catch (e){ console.error(e); suggestions.innerHTML = '<div class="error">Erro ao buscar versões.</div>'; }
}

function renderVersionsList(versions){
  suggestions.innerHTML = '';
  versions.forEach(v=>{
    const thumb = v.thumb || '';
    const title = v.title || '';
    const year = v.year || '';
    const format = v.format ? (Array.isArray(v.format) ? v.format.join(', ') : v.format) : (v.formats? v.formats.map(f=>f.name).join(', '): '');
    const labels = v.label ? (Array.isArray(v.label)? v.label.join(', '): v.label) : (v.labels? v.labels.map(l=>l.name).join(', '): '');
    const li = document.createElement('div');
    li.className = 'version';
    li.innerHTML = `
      <img src="${thumb}" class="thumb">
      <div class="meta">
        <div class="tit">${escapeHtml(title)}</div>
        <div class="sub">${escapeHtml(year + ' • ' + format)}</div>
        <div class="small">${escapeHtml(labels)}</div>
        <div class="v-actions">
          <button class="btn select" data-release="${v.id}">Selecionar</button>
          <span class="note">${escapeHtml(v.notes || '')}</span>
        </div>
      </div>
    `;
    suggestions.appendChild(li);
  });

  // delegate select clicks
  suggestions.addEventListener('click', async function handler(e){
    const sel = e.target.closest('button.select');
    if (!sel) return;
    const releaseId = sel.getAttribute('data-release');
    // remove this temporary handler so it doesn't stack
    suggestions.removeEventListener('click', handler);
    await selectRelease(releaseId);
  });
}

async function selectRelease(releaseId){
  try {
    const res = await fetch('api_get_release.php?release_id='+releaseId);
    const data = await res.json();
    if (data.error) { alert('Erro release: '+data.error); return; }
    fillFormFromRelease(data);
    suggestions.innerHTML = '';
  } catch(e){ console.error(e); alert('Erro ao buscar release'); }
}

function simpleTitleFrom(str){
  if (!str) return '';
  const parts = str.split(' - ');
  if (parts.length >= 2) return parts.slice(1).join(' - ');
  return str;
}

function detectVariationsFromRelease(release){
  const text = (release.title||'') + ' ' + (release.notes||'') + ' ' + (release.version||'') + ' ' + (release.formats?JSON.stringify(release.formats):'');
  const t = text.toLowerCase();
  const variants = [];
  const keywords = ['remaster','remastered','reissue','deluxe','expanded','limited','anniversary','mono','stereo','picture disc','promo','bonus','edition','repress','remix','box set','import'];
  keywords.forEach(k=>{ if (t.indexOf(k) !== -1) variants.push(k); });
  return [...new Set(variants)].map(v=>capitalizeWords(v)).join(', ');
}

function capitalizeWords(s){ return s.replace(/\b\w/g, c=>c.toUpperCase()); }

function fillFormFromRelease(release){
  const onlyTitle = simpleTitleFrom(release.title || release.master_title || '');
  titleInput.value = onlyTitle;
  artistInput.value = (release.artists && release.artists.length)? release.artists.map(a=>a.name).join(', ') : (release.artists_sort || '');
  yearInput.value = release.year || '';
  genreInput.value = (release.genres && release.genres.length)? release.genres.join(', '): '';
  styleInput.value = (release.styles && release.styles.length)? release.styles.join(', '): '';
  coverImg.src = (release.images && release.images.length)? (release.images[0].uri || release.images[0].uri150 || '') : (release.thumb || '');
  const variations = detectVariationsFromRelease(release);
  variationsInput.value = variations;
  tracklistArea.innerHTML = '';
  if (release.tracklist && release.tracklist.length){
    const ol = document.createElement('ol');
    release.tracklist.forEach(tr=>{
      const li = document.createElement('li');
      li.textContent = (tr.position? tr.position + ' - ' : '') + (tr.title || '') + (tr.duration? ' ('+tr.duration+')':'');
      ol.appendChild(li);
    });
    tracklistArea.appendChild(document.createElement('h3')).textContent = 'Tracklist';
    tracklistArea.appendChild(ol);
  } else {
    tracklistArea.innerHTML = '<p class="muted">Nenhuma tracklist disponível.</p>';
  }
}

// save logic unchanged
saveBtn.addEventListener('click', async ()=>{
  const payload = {
    title: titleInput.value.trim(),
    artist: artistInput.value.trim(),
    release_year: yearInput.value.trim(),
    genre: genreInput.value.trim(),
    style: styleInput.value.trim(),
    cover_url: coverImg.src || '',
    variations: variationsInput.value || '',
    tracklist: []
  };
  const ol = tracklistArea.querySelector('ol');
  if (ol) payload.tracklist = Array.from(ol.querySelectorAll('li')).map(li=>li.textContent);
  try {
    const r = await fetch('save_album.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const res = await r.json();
    if (res.success) {
      alert('Álbum salvo com ID: ' + res.id);
      titleInput.value = artistInput.value = yearInput.value = genreInput.value = styleInput.value = variationsInput.value = '';
      coverImg.src = '';
      tracklistArea.innerHTML = '';
    } else {
      alert('Erro ao salvar: ' + (res.error||JSON.stringify(res)));
    }
  } catch (e){ console.error(e); alert('Erro ao salvar'); }
});

function escapeHtml(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>